<?php 
//関数のファイルを読み込む
require_once('function.php');

$host       ='localhost';
$username   ='codecamp34661';
$password   ='codecamp34661';
$dbname     ='codecamp34661';
$charset    ='utf8';
$datetime   = date('Y-m-d H:i:s');

$err_msg    =array();
$str_regex  ='/^[a-zA-Z0-9]{6,8}+$/';

//mysqlの文字列
$dsn = 'mysql:dbname=' .$dbname.';host='.$host.';charset='.$charset;
$password_hash ='';


try {
    // DB接続処理
    $dbh =new PDO ($dsn, $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //エラーがあるときにcatchに飛ぶ
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //エミュレーションを無効にすると、mysql側で実行の準備をする
    
    // セッションスタート
    session_start();
    
    //--- ログイン認証処理 ---
    if ($_SERVER['REQUEST_METHOD']==='POST'){
    
        // 送信されたユーザー名を取得する
        $user_name = get_post_data('user_name');
                
        // 送信されたパスワードを取得する
        $password = get_post_data('password');
  
        
        // ユーザーテーブルに指定のユーザー名とパスワードに合致するユーザーがいるかチェックする
        // DBからselect文でデータ取得（post送信されたuser_nameと同じデータ）
        try {
            
            $sql = 'SELECT user_id, user_name, password FROM ec_user WHERE user_name = :user_name';
           
            $stmt =$dbh->prepare($sql);
            
            $stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
            
            $stmt ->execute();
            
            $user = $stmt->fetch();
        
        } catch (PDOException $e){
            print 'select文でuser_id, user_nameとpasswordを取得できない。理由：'.$e->getMessage();
        }
        
       
        // ユーザーがいた場合
        if (isset ($user['user_name']) ){
            
           
            // 入力されたパスワードとハッシュ化されたパスワードの検証
            if (password_verify ($password, $user['password'])){
                // -----ユーザ名とパスワードが一致している場合-----
                
                // user_name とパスワードが一致していることが確認できたから、
                // $_SESSIONにselect文で取得したuser_idとuser_nameを格納
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];

                // 管理者の場合→管理ページへ
                if ($user_name=== 'admin'){ 
                    header('Location: admin.php');
                    exit;
                } else {
                    header('Location: top.php');
                    exit;
                }
                
            } else {
                $err_msg[]= 'パスワードが違います';
            } // password検証おわり
        
        } else {
            $err_msg[]= 'ユーザー名またはパスワードが違います';
        }// isset($user)おわり     
    
    
    } //$_SERVERおわり

} catch (PDOException $e){
    print 'DBに接続できない。理由：'. $e->getMessage();
}

include_once('./view/login_view.php');