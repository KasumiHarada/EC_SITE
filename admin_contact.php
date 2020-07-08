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
$email_regex = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/iD';
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
    
    // selet文で内容表示
    try {
        
        $sql='SELECT name, email, message, createdate FROM ec_contact';
        
        $stmt=$dbh->prepare($sql);
        $stmt->execute();
        $results =$stmt->fetchAll();
   
    } catch (PDOException $e){
        print 'DBに接続できない';
    }
    
} catch (PDOException $e){
    print 'DBに接続できない'. $e->getMessage();
} 
?>         


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>CONTACT</title>
</head>

<body>
<h1>お問い合わせ内容管理ページ</h1>     
    <p><a href ="user.php">ユーザー管理ページ</a></p>
    <p><a href ="admin.php">商品管理ページ</a></p>
<table border=1>
    <tr>
        <th>名前</th>
        <th>アドレス</th>
        <th>内容</th>
        <th>送信時間</th>
    </tr>
    
    <?php if ($results>0){ ?>
    <?php foreach ($results as $result){ ?>
    <tr>
        <td><?php print $result['name']?></td>
        <td><?php print $result['email']?></td>
        <td><?php print $result['message']?></td>
        <td><?php print $result['createdate']?></td>
    </tr>
    <?php } // foreachおわり?>
    <?php } // result０おわり?>
</table>
</body>
</html>