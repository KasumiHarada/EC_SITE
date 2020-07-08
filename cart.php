<?php 
//関数のファイルを読み込む
require_once('function.php');

$host       ='localhost';
$username   ='codecamp34661';
$password   ='codecamp34661';
$dbname     ='codecamp34661';
$charset    ='utf8';
$datetime   = date('Y-m-d H:i:s');

$img_dir    ='./img/'; // アップロードした画像ファイルの保存ディレクトリ
$results    =array();

$err_msg    =array();
$str_regex  ='/^[a-zA-Z0-9]{8}+$/';
$int_regex  ='/^[0-9]+$/';

$flash_msg = '';
$result_msg =array();

//mysqlの文字列
$dsn = 'mysql:dbname=' .$dbname.';host='.$host.';charset='.$charset;

session_start();

 if (isset ($_SESSION['user_id'])){
        $user_id = $_SESSION['user_id'];
    
    } else {
        //ログインしていない
        header('Location: login.php');
        exit;
    }

try {
    // DB接続処理
    $dbh =new PDO ($dsn, $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //エラーがあるときにcatchに飛ぶ
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //エミュレーションを無効にすると、mysql側で実行の準備をする
    

    // ---POST送信時の処理---
    if ($_SERVER['REQUEST_METHOD']==='POST'){
        
        // hidden送信されたitem_idを取得
        $item_id = get_post_data('item_id');
        
        $size = get_post_data('size');
        
        // 必要なデータをDBから取得
        try {
                
            // カートの希望数とストックの在庫数を取得
            $sql = 'SELECT ec_item_master.item_id, 
                ec_item_master.name, 
                ec_item_master.img, 
                ec_item_master.price, 
                ec_item_master.status, 
                ec_cart.amount, 
                ec_item_stock.stock 
                FROM ec_item_master LEFT OUTER JOIN ec_cart ON ec_item_master.item_id = ec_cart.item_id 
                JOIN ec_item_stock ON ec_item_master.item_id =ec_item_stock.item_id 
                WHERE user_id = :user_id';
            
            // 準備
            $stmt =$dbh->prepare($sql);
            // 値をバインド
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            // 実行
            $stmt ->execute();
            // itemsに結果を格納
            $items = $stmt->fetchAll();

        } catch (PDOException $e){
            print 'select文でuser_id, user_nameとpasswordを取得できない。理由：'.$e->getMessage();
        }
        
        //データベースから取り出したデータをそれぞれ$変数へ格納    
        foreach($items as $item){
            $stock = $item['stock'];
            $status = $item['status'];
            $amount = $item['amount'];
        } 
        
        // $actionに処理方法を代入（数量変更or削除or購入）
        $action = get_post_data('action');
        
        // 商品を追加する場合
        if ($action ==='insert_cart'){
            
            // エラー0ならば           
            if (count($err_msg)===0){ 
                
                // select文でカートの情報を取得
                try {
                
                    $sql = 'SELECT user_id, item_id FROM ec_cart '.PHP_EOL
                            .'WHERE user_id = :user_id AND item_id =:item_id';
                   
                    $stmt =$dbh->prepare($sql);
                    
                    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                    
                    $stmt ->execute();
                    
                    $item = $stmt->fetch();
                
                } catch (PDOException $e){
                    print 'select文でuser_id, user_nameとpasswordを取得できない。理由：'.$e->getMessage();
                }
             
                $dbh->beginTransaction();
                try {
                
                    // ユーザーがいた場合、商品がすでにあるのでアップデート
                    if (isset ($item['user_id']) ){
    
                        $sql ='UPDATE ec_cart SET amount = amount+1, size=:size, update_datetime =:update_datetime WHERE item_id =:item_id AND user_id= :user_id;';
            
                        // 準備
                        $stmt = $dbh->prepare($sql);
            
                        // 値をバインド
                        $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                        $stmt->bindValue(':size', $size, PDO::PARAM_INT);
                        $stmt->bindValue(':update_datetime', $datetime, PDO::PARAM_STR);
        
        
                    } else {
    
                        // 商品がカートにないので、INSERT
                        $sql ='INSERT INTO ec_cart (user_id, item_id, amount, create_datetime, update_datetime)
                                VALUES(:user_id, :item_id, :amount, :create_datetime, :update_datetime)';
                  
                        // 準備
                        $stmt = $dbh->prepare($sql);
                    
                        // 値をバインド
                        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                        $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                        $stmt->bindValue(':amount', 1, PDO::PARAM_INT);
                        $stmt->bindValue(':create_datetime', $datetime, PDO::PARAM_STR);
                        $stmt->bindValue(':update_datetime', $datetime, PDO::PARAM_STR);
                        
                    }    
                
                    // 実行
                    $stmt->execute();
        
                    $dbh->commit();
                            
                    $_SESSION['flash_msg']='商品をカートに追加しました';
                    
                    
                    header ('Location: top.php');
                    exit;
                            
                } catch (PDOException $e){
                    // ロールバック処理
                    $dbh->rollback();
                    // 例外をスロー
                    throw $e;
                    print 'カートに追加できない&stockテーブル更新できない'.$e->getMessage();
                }
            
            } //count err おわり
            
        // カートの商品の数を変更
        } else if ($action ==='change_amount'){
            
            // 入力された変更後の値
            $change_amount = get_post_data('change_amount');
                
            // もともとの在庫数
            $amount = get_post_data('amount');
            
            // 公開ステータスが１（公開）かどうか、在庫数がゼロでないかチェック
            if ($stock ===0 || $status ===0){
               $err_msg[]= '売り切れです';
            }
            
            // 在庫数が希望必要数を超えてないかどうかチェック
            if ($change_amount > $stock ){
                $err_msg[]= '在庫がありません';
            }

            
            // エラーチェック
            if (!preg_match($int_regex, $change_amount)){
                $err_msg[] ='数量は半角数字で入力してください';
            }
           
            // エラーがゼロの場合
            if (count ($err_msg)===0 ){
            
                try {
                    
                    // カートの在庫変更
                    $sql ='UPDATE ec_cart 
                            SET amount = :amount, update_datetime =:update_datetime 
                            WHERE user_id = :user_id AND item_id =:item_id';
                    
                    // 準備
                    $stmt = $dbh->prepare($sql);
                    
                    // 値をバインド
                    $stmt->bindValue(':amount', $change_amount, PDO::PARAM_INT);
                    $stmt->bindValue(':update_datetime', $datetime, PDO::PARAM_STR);
                    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                    
                    // 実行
                    $stmt->execute();
                    
                    // 成功したらメッセージを表示
                    $result_msg[]='数量を変更しました';
                
                } catch (PDOException $e){
                    print '在庫の数量変更できない'.$e->getMessage();
                }
                
            } // count err_msg おわり      
        
            
        // 商品を削除する場合    
        } else if ($action ==='delete'){
            
            // hiddenで送られてきたもの変数に格納
            $item_id = get_post_data('item_id');
            
            // ec_cartから削除する処理
            try {
                
                $sql ='DELETE FROM ec_cart WHERE item_id = :item_id';
                
                // 準備
                $stmt=$dbh->prepare($sql);
                
                // 値をバインド
                $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                
                // 実行
                $stmt->execute();
                
                $result_msg[]= '商品をカートから削除しました';
                
            } catch (PDOException $e){
                print '削除できない'.$e->getMessage();
            }
        
        } else if ($action ==='logout'){
            
            // セッション名取得 ※デフォルトはPHPSESSID
            $session_name = session_name();
            // セッション変数を全て削除
            $_SESSION = array();
            
            // ユーザのCookieに保存されているセッションIDを削除
            if (isset($_COOKIE[$session_name])) {
                // sessionに関連する設定を取得
                $params = session_get_cookie_params();
                
                // sessionに利用しているクッキーの有効期限を過去に設定することで無効化
                setcookie($session_name, '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
                );
            }
            
            // セッションIDを無効化
            session_destroy();
            // ログアウトの処理が完了したらログインページへリダイレクト
            header('Location: login.php');
            exit;

        } // logoutおわり
      
    }// $_SERVER おわり
    

    // select文で特定のユーザのec_cartのテーブルデータを取得して表示
    try {
        
        // 表示のためのSQL
        $sql='SELECT 
            ec_item_master.item_id,
            ec_item_master.name,
            ec_item_master.img,
            ec_item_master.price,
            ec_item_master.status,
            ec_cart.amount,
            ec_item_stock.stock
            FROM ec_cart LEFT OUTER JOIN ec_item_master ON ec_cart.item_id = ec_item_master.item_id
            JOIN ec_item_stock ON ec_item_master.item_id =ec_item_stock.item_id
            WHERE user_id = :user_id';
     
        // 準備
        $stmt = $dbh ->prepare($sql);
        
        // 値をバインド
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
         
        // 実行
        $stmt -> execute();
        
        // 結果を保管
        $results = $stmt->fetchAll();

    } catch (PDOException $e){
        print 'select文でテーブルデータ取得できない'.$e->getMessage();
    }  

} catch (PDOException $e) {
    print 'DB接続できない'. $e->getMessage();
}

include_once('./view/cart_view.php');  
        