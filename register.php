<?php

//関数のファイルを読み込む
require_once('function.php');

$host       ='localhost';
$username   ='codecamp34661';
$password   ='codecamp34661';
$dbname     ='codecamp34661';
$charset    ='utf8';
$datetime   = date('Y-m-d H:i:s');

$str_regex  ='/^[a-zA-Z0-9]{6,8}+$/';
$err_msg    =array();

$process_kind='';
$result_msg=array();

//mysqlの文字列
$dsn = 'mysql:dbname=' .$dbname.';host='.$host.';charset='.$charset;

try {
    // DB接続処理
    $dbh =new PDO ($dsn, $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //エラーがあるときにcatchに飛ぶ
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //エミュレーションを無効にすると、mysql側で実行の準備をする


    if ($_SERVER['REQUEST_METHOD']==='POST'){
        
        // user_nameが送信されたかチェック→代入 
        $user_name= get_post_data('user_name');
        
        // passwordが送信されたかチェック→代入
        $password= get_post_data('password');
        
        // ユーザー名のエラーチェック
        if (!preg_match($str_regex, $user_name)){
            $err_msg[]='ユーザー名は6文字以上の半角英数字で入力してください';
        }
        
        // パスワードのエラーチェック
        if (!preg_match($str_regex, $password)){
            $err_msg[]='パスワードは6文字以上の半角英数字で入力してください';
        }
     
        // select文で、user_nameを呼び出す
        try {
            
            $sql ='SELECT user_name, password FROM ec_user WHERE user_name = :user_name';
        
            $stmt = $dbh->prepare($sql);
            
            $stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
            
            $stmt->execute();
            
            $user = $stmt->fetch();
            
        } catch (PDOException $e){
            print 'ユーザー名が見つからない'. $e->getMessage();
        }
        
 
        // エラーがなしで、かつ、同じ名前のユーザが存在しなければ、登録
        if (count($err_msg)===0 && !isset ($user['user_name'])){
            
            try {
                
                // insert文で新規登録されたユーザ情報をDBへ
                $sql ='INSERT INTO ec_user (user_name, password, create_datetime, update_datetime)VALUES
                      (:user_name, :password, :create_datetime, :update_datetime)';
            
                // 準備
                $stmt = $dbh->prepare($sql);
                
                // 値をバインド
                $stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
                $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                $stmt->bindValue(':create_datetime', $datetime, PDO::PARAM_STR);
                $stmt->bindValue(':update_datetime', $datetime, PDO::PARAM_STR);
                
                
                // 実行
                $stmt->execute();
                $result_msg[] = 'ユーザー登録完了';
          
            } catch (PDOException $e){
                print '登録できない';
            }
                
  
        } else if (isset ($user['user_name'])) {
            $err_msg[]= '既に登録済のユーザ名です';
        } // (!isset ($result) おわり
        
        
    } //$_SERVER おわり
    
} catch (PDOException $e){
    print 'DB接続できない。理由：'.$e->getMessage();
}

include_once('./view/register_view.php');  