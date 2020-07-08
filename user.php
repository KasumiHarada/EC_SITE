<?php
$host       ='localhost';
$username   ='codecamp34661';
$password   ='codecamp34661';
$dbname     ='codecamp34661';
$charset    ='utf8';
$datetime   = date('Y-m-d H:i:s');


//mysqlの文字列
$dsn = 'mysql:dbname=' .$dbname.';host='.$host.';charset='.$charset;

session_start();

// ログイン処理
// post送信されたuser_nameがadminかどうかチェック
if (isset ($_SESSION['user_name'])===TRUE){
    
    if ($_SESSION['user_name'] !== 'admin'){
        header ('Location: login.php');
        exit;
    }
    
} else {
    // ログインしていない→ログインページへ
    header ('Location: login.php');
    exit;
}

// ログアウト処理
if ($_SERVER['REQUEST_METHOD']==='POST'){
    
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

} // $_SERVER おわり


try {
    // DB接続処理
    $dbh =new PDO ($dsn, $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //エラーがあるときにcatchに飛ぶ
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //エミュレーションを無効にすると、mysql側で実行の準備をする


    // select文でuser情報を取得
    $sql = 'SELECT user_id, user_name, password, create_datetime FROM ec_user';
    
    // 準備
    $stmt=$dbh->prepare($sql);
    
    // 実行
    $stmt->execute();
    
    // $results に格納
    $results = $stmt->fetchAll();
   
} catch (PDOException $e){
    print 'データベースに接続できない。'.$e->getMessage();
}


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー管理画面</title>
</head>
<header>
    <form method ="POST">
        <input type="submit" value="ログアウト">
        <input type="hidden" name ="process_kind" value="ログアウト">
    </form>
</header>
<body>
    <h1>ユーザー管理ページ</h1>
    <p><a href ="admin_contact.php">お問い合わせ内容管理ページ</a></p>
    <p><a href ="admin.php">商品管理ページ</a></p>
    
    <table border=1>
        <tr>
            <th>ユーザー名</th>
            <th>登録日時</th>
            <th>パスワード</th>
        </tr>
        
        <?php foreach ($results as $result){ ?>
        
            <tr>
                <th><?php print $result['user_name']; ?></th>
                <th><?php print $result['create_datetime']; ?></th>
                <th><?php print $result['password']; ?></th>
            </tr>
        
        <?php } ?>
            
    </table>
    
    
    
</body>
</html>