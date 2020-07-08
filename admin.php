<?php
//関数のファイルを読み込む
require_once('function.php');

$host       ='localhost';
$username   ='codecamp34661';
$password   ='codecamp34661';
$dbname     ='codecamp34661';
$charset    ='utf8';
$datetime   = date('Y-m-d H:i:s');

$int_regex ='/^[0-9]+$/';
$err_msg    =array();

$process_kind='';
$result_msg  ='';

$img_dir    ='./img/'; // アップロードした画像ファイルの保存ディレクトリ
$new_img_filename =''; //アップロードした画像の新しいファイルネーム

//mysqlの文字列
$dsn = 'mysql:dbname=' .$dbname.';host='.$host.';charset='.$charset;


session_start();

// ログイン処理
// ログインできる人かどうかチェック
if (isset($_SESSION['user_name']) === TRUE){
    
    // 本人かはわかってる、権限をチェックしてる
    if ($_SESSION['user_name'] !=='admin'){
        header('Location: top.php');
        exit;
    }

} else {
    // ログインしていない→loginへリダイレクト
    header ('Location: login.php');
    exit;
} // $session username true おわり


try {
    // DB接続処理
    $dbh =new PDO ($dsn, $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //エラーがあるときにcatchに飛ぶ
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //エミュレーションを無効にすると、mysql側で実行の準備をする
    
    
    
    // $_SERVERでpost送信された場合の処理
    if ($_SERVER['REQUEST_METHOD']==='POST'){
        
        
        // 変数を用意する
        $process_kind= get_post_data('process_kind');
        
        if ($process_kind==='logout'){

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

        } else if ($process_kind==='insert_item'){
       
      
            // 商品の名前チェック->代入
            $name =get_post_data('name');
            
            // 前後の空白を取り除く
            trim_space($name);
            
            // 値段をチェック->代入
            $price=get_post_data('price');
            
            // 前後の空白を取り除く
            trim_space($price);
            
            // 在庫数をチェック→代入
            $stock =get_post_data('stock');
            
            // 前後の空白を取り除く
            trim_space($stock);
            
            // ステータスをチェック→代入
            $status =get_post_data('status');
            
            $detail =get_post_data('detail');

            //エラーチェック
            if (mb_strlen($name)===0){
                $err_msg[]='商品名を入力してください';
            } else if (mb_strlen($name)>20){
                $err_msg[]='商品名は20文字以内で入力してください';
            }
            
            if (!preg_match($int_regex, $price)){
                $err_msg[]='値段は半角で入力してください';
            }
            
            if (!preg_match($int_regex, $stock)){
                $err_msg[]='在庫数は半角で入力してください';
            }
            
            // ０か１以外で送信されてないかどうかチェック
            if ($status !=="0" && $status !=="1"){
                $err_msg[]='もう一度やり直してください';
            }
            
            // 詳細情報の入力エラーチェック
            if (mb_strlen($detail)===0){
                $err_msg[]='詳細情報を入力してください';
            } else if (mb_strlen($name)>300){
                $err_msg[]='詳細説明は300文字以内で入力してください';
            }
            
            
            //画像アップロード。
            if (is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE) {
            // 画像の拡張子を取得
            $extension = pathinfo($_FILES['new_img']['name'], PATHINFO_EXTENSION);
                // 指定の拡張子であるかどうかチェック
                if ($extension === 'png' || $extension === 'jpeg' || $extension ==='jpg') {
                      // 保存する新しいファイル名の生成（ユニークな値を設定する）
                      $new_img_filename = sha1(uniqid(mt_rand(), true)). '.' . $extension;
                      // 同名ファイルが存在するかどうかチェック
                      if (is_file($img_dir . $new_img_filename) !== TRUE) {
                            // アップロードされたファイルを指定ディレクトリに移動して保存
                            if (move_uploaded_file($_FILES['new_img']['tmp_name'], $img_dir . $new_img_filename) !== TRUE) {
                                $err_msg[] = 'ファイルアップロードに失敗しました';
                            }
                            } else {
                            $err_msg[] = 'ファイルアップロードに失敗しました。再度お試しください。';
                            }
                    } else {
                      $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPEGまたはPNGのみ利用可能です。';
                    }
                } else {
                  $err_msg[] = 'ファイルを選択してください';
            }
            
            
            //insert文でec_item_masterに接続する
            
            if (count ($err_msg)===0){
                
                // トランザクション開始
                $dbh->beginTransaction();
                try {
                    $sql ='INSERT INTO ec_item_master (name, price, img, status, detail, create_datetime, update_datetime)
                           VALUES (:name, :price, :img, :status, :detail, :create_datetime, :update_datetime)';
                    
                    // 準備
                    $stmt = $dbh->prepare($sql);
                    
                    // 値をバインドする
                    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                    $stmt->bindValue(':price', $price, PDO::PARAM_INT);
                    $stmt->bindValue(':img', $new_img_filename, PDO::PARAM_STR);
                    $stmt->bindValue(':status', $status, PDO::PARAM_INT);
                    $stmt->bindValue(':detail', $detail, PDO::PARAM_STR);
                    $stmt->bindValue(':create_datetime', $datetime, PDO::PARAM_STR);
                    $stmt->bindValue(':update_datetime', $datetime, PDO::PARAM_STR);
                    
                    
                    // 実行
                    $stmt->execute();
                    
                    $item_id = $dbh->lastInsertId('item_id');
                    
                    // insert文でec_item_stockテーブルに接続する
                    
                    $sql='INSERT INTO ec_item_stock (item_id, stock, create_datetime, update_datetime)
                          VALUES(:item_id, :stock, :create_datetime, :update_datetime)';
                    
                    // 準備
                    $stmt = $dbh->prepare($sql);
                    
                    // 値をバインドする
                    $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                    $stmt->bindValue(':stock', $stock, PDO::PARAM_INT);
                    $stmt->bindValue(':create_datetime', $datetime, PDO::PARAM_STR);
                    $stmt->bindValue(':update_datetime', $datetime, PDO::PARAM_STR);
                    
                    // 実行
                    $stmt ->execute();
                    
                    // commit処理
                    $dbh->commit();
                    
                
                
                } catch (PDOException $e){
                    // ロールバック処理
                    $dbh->rollback();
                    // 例外をスロー
                    throw $e;
                      
                }
            
            } //count err_msgゼロ おわり
        
        } else if ($process_kind==='update_stock'){
        
            
            // update_stockを取得する
            $update_stock = get_post_data('update_stock');
         
            // 前後の空白を取り除く
            trim_space($update_stock);
            
            // 在庫数のエラーチェック
            if (!preg_match($int_regex, $update_stock)){
                $err_msg[]='在庫数は半角で入力してください';
            }
            
            // item_idを変数に
            $item_id = get_post_data('item_id');
            
          
            // エラー0だったら
            if (count ($err_msg)===0){
                
                try {
                    
                    // update文でec_item_stockテーブルを更新する
                    $sql = 'UPDATE ec_item_stock SET stock = :update_stock, update_datetime = :update_datetime 
                            WHERE item_id = :item_id';
                    
                    // 準備
                    $stmt = $dbh ->prepare($sql);
                    
                    // 値をバインド
                    $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                    $stmt->bindValue(':update_stock', $update_stock, PDO::PARAM_INT);
                    $stmt->bindValue(':update_datetime', $datetime, PDO::PARAM_STR);
                    
                    // 実行
                    $stmt->execute();
                    
                    
                } catch (PDOException $e){
                    print '在庫数変更失敗。理由：'. $e->getMessage();
                } //catchおわり
                
            } //count err_msgゼロ おわり
        
        } else if ($process_kind==='update_status'){    
            
            // $update_status変数に代入
            $update_status = get_post_data('update_status');
            
            
            // $item_id 変数に代入
            $item_id =get_post_data('item_id');
            
    
            // ０か１以外で送信されてないかどうかチェック
            if ($update_status !=="0" && $update_status !=="1"){
                $err_msg[]='もう一度やり直してください';
            }
            
            // エラー0だった場合の処理
            if (count ($err_msg)===0){
                
                try{
                    // $sql文
                    $sql ='UPDATE ec_item_master SET status = :update_status, update_datetime = :update_datetime
                           WHERE item_id = :item_id';
                           
                    // 準備
                    $stmt=$dbh->prepare($sql);
                    
                    // 値をバインド
                    $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                    $stmt->bindValue(':update_status', $update_status, PDO::PARAM_INT);
                    $stmt->bindValue(':update_datetime', $datetime, PDO::PARAM_STR);
                    
                    // 実行
                    $stmt->execute();
                   
                    
                } catch (PDOException $e){
                    print 'ステータス変更失敗。理由：'. $e->getMessage();
                }     
                
            } //count err_msg0 おわり
            
        } else if ($process_kind==='delete'){
       
            // $item_id 変数に代入
            $item_id =get_post_data('item_id');
            
           
            // ec_item_masterテーブルの指定された番号を削除
            $dbh->beginTransaction();
            try {
                
                // sql文
                $sql = 'DELETE FROM ec_item_master WHERE :item_id = item_id';
                
                // 準備
                $stmt = $dbh->prepare($sql);
                
                // 値をバインド
                $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                
                // 実行
                $stmt->execute();
                
                
                // sql文
                $sql = 'DELETE FROM ec_item_stock WHERE :item_id = item_id';
                
                // 準備
                $stmt = $dbh->prepare($sql);
                
                // 値をバインド
                $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                
                // 実行
                $stmt->execute();
                
            // コミット処理    
            $dbh->commit();    
                
            } catch (PDOException $e){
                print '削除できません。理由：'. $e->getMessage();
            }
            
        } else if ($process_kind==='update_detail'){
            
            // $item_id 変数に代入
            $item_id =get_post_data('item_id');
            
            // $update_detail変数に代入
            $update_detail = get_post_data('update_detail');
     
            if (count($update_detail)>200){
                $err_msg[]='詳細情報は２００文字以内で入力してください';
            }
            
            // エラー0だった場合の処理
            if (count ($err_msg)===0){
                
                try{
                    // $sql文
                    $sql ='UPDATE ec_item_master SET detail = :update_detail, update_datetime = :update_datetime
                           WHERE item_id = :item_id';
                           
                    // 準備
                    $stmt=$dbh->prepare($sql);
                    
                    // 値をバインド
                    $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                    $stmt->bindValue(':update_detail', $update_detail, PDO::PARAM_INT);
                    $stmt->bindValue(':update_datetime', $datetime, PDO::PARAM_STR);
                    
                    // 実行
                    $stmt->execute();
                   
                } catch (PDOException $e){
                    print '詳細情報更新失敗。理由：'. $e->getMessage();
                }     
                
            } //count err_msg0 おわり
        
        } // detail おわり
    
    } //$SERVERおわり


} catch (PDOException $e){
    print 'DBに接続できない。理由：'. $e->getMessage();
} // DBtry~catchおわり


// select文で取得する
$sql = 'SELECT 
        ec_item_master. img,
        ec_item_master. name,
        ec_item_master. price,
        ec_item_stock. stock,
        ec_item_master. status,
        ec_item_master. detail,
        ec_item_master. item_id
        from ec_item_master LEFT OUTER JOIN ec_item_stock ON ec_item_master. item_id = ec_item_stock. item_id ';


// 準備
$stmt = $dbh ->prepare($sql);

// 実行
$stmt->execute();

$results = $stmt->fetchAll();


// 正常に完了した場合の完了メッセージ
if ($process_kind ==='insert_item'){
    $result_msg = '新規商品追加完了';
} else if ($process_kind ==='update_stock'){
    $result_msg = '在庫数変更完了';
} else if ($process_kind ==='update_status'){
    $result_msg = '公開ステータス変更完了';
} else if ($process_kind ==='delete'){
    $result_msg = '削除完了';
} else if ($process_kind ==='update_detail'){
    $result_msg = '詳細情報更新完了';
}
?>

<!DOCTYPE html>
<html lang="jp">
<head>
    <meta charset="UTF-8">
    <title>ECサイト管理画面</title>
    <link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/admin.css">      
</head>
<header>
    <form method="POST">
        <input type="submit" value="ログアウト">
        <input type="hidden" name ="process_kind" value="logout">
    </form>
    
</header>
<div class="container">
    <body>
        <?php if (!empty($result_msg) && count($err_msg)===0 ){?>
        <p><?php print $result_msg; ?></p>
        <?php }?>
        <h1>ECサイト管理画面</h1>
        <p><a href ="user.php">ユーザー管理ページ</a></p>
        <p><a href ="admin_contact.php">お問い合わせ内容管理ページ</a></p>
        <h2>新規商品追加</h2>
        
        
        <?php foreach($err_msg as $value){ ?>
            <ul><li><?php print $value; ?></li></ul>
        <?php } ?>
        
        
        <form method='POST' enctype= "multipart/form-data">
            <p>商品名:<input type="text" name="name"></p>
            <p>値段:<input type="text" name="price"></p>
            <p>在庫数:<input type="text" name="stock"></p> 
            <p>商品画像:<input type="file" name="new_img"></p>
            <p>詳細情報:<textarea type='text' name='detail'></textarea></p>
            <p>公開ステータス:
                <select name="status">
                    <option value="0">非公開</option>
                    <option value="1">公開</option>
                </select>
            </p>
            
            <input type="submit" value="新規追加">
            <input type="hidden" name="process_kind" value="insert_item">
        </form>
        
        
        <h2>追加商品一覧</h2>
        <table border =1>
            <tr>
                <th>商品画像</th>
                <th>商品名</th>
                <th>値段</th>            
                <th>在庫数</th>            
                <th>公開ステータス</th>
                <th>詳細情報</th>
                <th>チェック</th>
            </tr>
            
            <?php foreach ($results as $result){ ?>
                
            
            <tr>
                <th><img src ="<?php print $img_dir. $result['img'] ;?>"></th>
                <th><?php print $result['name'] ;?></th>
                <th><?php print $result['price'] ;?></th>
                <th>
                    <form method ="POST">
                        <input type="number" name="update_stock" size =3 value ="<?php print $result['stock'] ;?>">
                        <input type="submit" value="在庫更新">
                        <input type="hidden" name="process_kind" value ="update_stock">
                        <input type="hidden" name="item_id" value="<?php print $result['item_id'];?>">
                    </form>
                </th>
                <th>
                    <form method="POST">
                        <?php if (($result['status'])===0){ ?>
                        
                            <input type="submit" value="非公開→公開">
                            <input type="hidden" name="update_status" value="1">
                        
                        <?php } else if (($result['status'])===1){?> 
                    
                            <input type="submit" value="公開→非公開">
                            <input type="hidden" name="update_status" value ="0">
                        
                        <?php } ?>
                        
                            <input type="hidden" name="process_kind" value="update_status">
                            <input type="hidden" name="item_id" value="<?php print $result['item_id'] ;?>">
                    </form>
                </th>
                <th>
                    <form method="POST">
                        <textarea name="update_detail" maxlength="200"cols="50" rows="5" ><?php print $result['detail'];?></textarea>
                        <input type="hidden" name="process_kind" value="update_detail">
                        <input type="hidden" name ="item_id" value ="<?php print $result['item_id'];?>">                    
                        <input type="submit" value="更新"/>
                    </form>
                    
                </th>
                <th>
                    <form method ="POST">
                        <input type="submit" value="削除">
                        <input type="hidden" name ="item_id" value ="<?php print $result['item_id'];?>">
                        <input type="hidden" name="process_kind" value="delete">
                    </form>
                </th>
               
               
            </tr>
            <?php } ?>
        </table>
    </body>
</div>
</html>