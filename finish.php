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

$err_msg    =array();
$str_regex  ='/^[a-zA-Z0-9]{8}+$/';

$flash_message = '';

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

    
    if ($_SERVER['REQUEST_METHOD']==='POST'){
        //--- 購入処理 ---
        
        
        
        $action = get_post_data('action');    
        if ($action ==='purchase'){    
            
            // user_id を条件に、cartテーブルから商品リストを取得する
            try {
                $sql='SELECT 
                    ec_item_master.item_id,
                    ec_item_master.name,
                    ec_item_master.img,
                    ec_item_master.price,
                    ec_item_master.img,
                    ec_item_master.status,
                    ec_cart.amount,
                    ec_item_stock.stock
                    FROM ec_cart LEFT OUTER JOIN ec_item_master ON ec_cart.item_id = ec_item_master.item_id
                    JOIN ec_item_stock ON ec_item_master.item_id =ec_item_stock.item_id
                    WHERE user_id = :user_id';
                    
                $stmt=$dbh->prepare($sql);
                
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                
                $stmt->execute();
                
                $results =$stmt->fetchAll();
    
            } catch (PDOException $e){
                print 'cartテーブル表示できない';
            }
            
            
            foreach($results as $result){
           
                $amount =$result['amount'];
                $status =$result['status'];
                $stock =$result['stock'];
                $name = $result['name'];
                
                
                // この商品のステータスが公開中かどうか？（問題があればエラー配列にメッセージを追加）
                // この商品の在庫数量が購入数量amount以上あるか？（問題があればエラー配列にメッセージを追加）
                
                if ($stock ===0 || $status ===0){
                    $err_msg[] = $name. 'が売り切れです';
                } else if ($amount> $stock){
                    $err_msg[] = $name.'の在庫が足りません';
                }     
           
            } // foreachおわり
            
            
        
            // エラー配列が0件なら在庫の更新処理へ
            if (count ($err_msg)===0){
                
                foreach($results as $result){
                    
                    $item_id =$result['item_id'];
                    $amount =$result['amount'];
                    $status =$result['status'];
                    $stock =$result['stock'];
                    $remain_stock = $stock - $amount;
            
                    $dbh->beginTransaction();
                    try {
                         // stockテーブルの在庫を変更する
                        $sql ='UPDATE ec_item_stock SET stock =:stock, update_datetime =:update_datetime 
                                WHERE item_id=:item_id';
                           
                        // 準備
                        $stmt=$dbh->prepare($sql);
                            
                        // 値をバインド
                        $stmt->bindValue(':stock', $remain_stock, PDO::PARAM_INT);
                        $stmt->bindValue(':update_datetime', $datetime, PDO::PARAM_STR);
                        $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                            
                        // 実行
                        $stmt->execute();
                    
                        $dbh->commit();
                    
                    } catch (PDOException $e){
                        // ロールバック処理
                        $dbh->rollback();
                        // 例外をスロー
                        throw $e;
                        print '購入できない'.$e->getMessage();
                    }
                    
                } //foreachおわり
        
            
            
            
                // user_idを指定してカートテーブルの商品リストを削除する
                try {
                    
                    $sql ='DELETE FROM ec_cart WHERE user_id =:user_id';
                    
                    $stmt=$dbh->prepare($sql);
                    
                    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                    
                    $stmt->execute();
                
                } catch (PDOException $e){
                    print 'テーブルを削除できない';   
                }
            } // count errおわり
            
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
        } //ログアウトおわり
        
    
    } else {
        print '不正なアクセスです';
        exit;
    } //$_SERVERおわり    
    
} catch (PDOException $e) {
    print 'DB接続できない'.$e->getMessage();
}
    include_once('./view/finish_view.php');  
        
     