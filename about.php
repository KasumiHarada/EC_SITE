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
} catch (PDOException $e){
    print 'DBに接続できない'. $e->getMessage();
} 
?>         


<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="./css/html5reset-1.6.1.css">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="/ec_site/b_css/bootstrap.css">
    <link rel="stylesheet" href="./css/common.css">   
    <link rel="stylesheet" href="./css/about.css">    
    <meta charset="UTF-8">
    <title>ABOUT</title>
</head>
<header>
    <a href ="top.php"><h1>アフリカ布 オンラインショップ</h1></a>
    <div class ="container">
        <div class="menu">
            <ul>
                <li><a href="top.php">HOME</a></li>
                <li><a href="about.php">ABOUT</a></li>
                <li><a href="contact.php">CONTACT</a></li>
            </ul>
            
        </div>
        
        <div class ="header-user">
    
            <li><?php print 'ようこそ'. h($_SESSION['user_name']). 'さん'; ?></li>
      
            <li>
                <a href="cart.php"><i class="fas fa-shopping-cart fa-2x"></i></a>
            </li>
            
            <li>
                <form method ="POST">
                    <label><input type="submit" value="ログアウト"></label>
                    <input type="hidden" name="action" value="logout">            
                </form>
            </li>
        </div>
        

    </div>    
</header>
<body>
    
    <h2>チテンゲとは？</h2>
    <p>アフリカの伝統布。アフリカ女性にはなくてはならない存在。現地の文化に深く根付いています。</p>
    <p>日本では見ることの内容な独創的でカラフルな物が多数あります。</p>

    <h2>使い方</h2>
    <p>現地では、アフリカのお母さんが赤ちゃんをおぶるのに使ったり、荷物を背負うのに使います。アフリカの女性たちにとってなくてはならない存在です。</p>
    <p>他にも、地面に敷いて使ったり、腰に巻いてエプロンのように使います。</p>
    <p>また、布を加工して、ドレスやシャツにしておしゃれをすることもあります。</p>
    <h2>お手入れ方法</h2>
    <p>初回のお洗濯では、事前にぬるま湯につけておくことをお勧めします。もし、心配であれば、単品でお洗濯してみてください。</p>
</body>
</html>