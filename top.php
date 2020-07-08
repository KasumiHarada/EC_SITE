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
$flash_msg ='';

$err_msg    =array();
$str_regex  ='/^[a-zA-Z0-9]{8}+$/';

$result_msg = array();

//mysqlの文字列
$dsn = 'mysql:dbname=' .$dbname.';host='.$host.';charset='.$charset;

session_start();

try {
    // DB接続処理
    $dbh =new PDO ($dsn, $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //エラーがあるときにcatchに飛ぶ
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //エミュレーションを無効にすると、mysql側で実行の準備をする
    

    // ユーザIDが存在したら、代入
    if (isset ($_SESSION['user_id'])){
        $user_id = $_SESSION['user_id'];
    
    } else {
        //ログインしていない
        header('Location: login.php');
        exit;
    }
    
    if (isset ($_SESSION['user_name'])){
        $user_name = $_SESSION['user_name'];
    } else {
        //ログインしていない
        header('Location: login.php');
        exit;
    }
    
    // 完了メッセージを表示する
    if (isset ($_SESSION['flash_msg'])){
        $flash_msg = $_SESSION['flash_msg'];
        unset ($_SESSION['flash_msg']);
    }
    
    
    try {
    
        $sql = 'SELECT 
                ec_item_master.img,
                ec_item_master.name,
                ec_item_master.price,
                ec_item_master.status,
                ec_item_master.item_id,
                ec_item_stock.stock
                FROM ec_item_master
                INNER JOIN ec_item_stock
                ON ec_item_master.item_id = ec_item_stock.item_id
                WHERE status =1';
     
        // 準備
        $stmt = $dbh->prepare($sql);
        
        // 実行
        $stmt->execute();
        
        // $resultsに値を格納
        $results = $stmt->fetchAll();

    } catch (PDOException $e){
        print 'ページが表示できません。理由：'.$e->getMessage();
    }

   
    //---POST送信された場合---
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){

        $action = get_post_data('action');
        
        // ログアウト処理
        if ($action ==='logout'){
            
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
    
    } //$_SERVER おわり    

} catch (PDOException $e){
    print 'DBに接続できない'. $e->getMessage();
} 
     

include_once('./view/top_view.php');  