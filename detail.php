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

    // 詳細ページでIDを受け取る
    if ($_SERVER['REQUEST_METHOD']==='GET'){
           $item_id =$_GET['item_id'];

        try {
            
            $sql='SELECT 
                    ec_item_master.item_id,
                    ec_item_master.name,
                    ec_item_master.price, 
                    ec_item_master.img, 
                    ec_item_master.detail 
                    FROM ec_item_master
                    WHERE item_id =:item_id';
            
            
            $stmt=$dbh->prepare($sql);
            
            $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            $results= $stmt->fetchAll();
            
        } catch(PDOException $e){
            print 'select文で取得できない';
        }
    } // $SERVER GET終わり
    
    
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
        
    } // $_SERVER POSTおわり
    
} catch (PDOException $e){
    print 'DBに接続できない'. $e->getMessage();
}     
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/common.css">   
    <link rel="stylesheet" href="./css/detail.css">    
    <meta charset="UTF-8">
    <title>商品一覧ページ</title>
</head>
<body>
<header>
    <a href ="top.php"><h1>アフリカ布 オンラインショップ</h1></a>
    <div class ="container">
        <div class="menu">
            <ul>
                <li><a href="top.php">HOME</a></li>
                <li><a href="about.php">ABOUT</a></li>
                <li><a href ="contact.php">CONTACT</a></li>
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
    
<main>
    
    <div class="item">
        
        <?php if(count($results)>0){ ?>
            <?php foreach($results as $result){?>
                
                <div class="img">
                    <img src ="<?php print $img_dir.$result['img'];?>"> 
                </div>
                
                <div class="detail">
                    <p>商品名：<?php print $result['name']; ?></p>
                    <p>値段　：<?php print number_format($result['price']); ?>円</p>
                    <div>詳細情報:
                        <p><?php print $result['detail'];?></p>
                    </div>
                    
                    <form method='POST' action='cart.php'>    
                        <input type="submit" class="btn btn-primary" value="カートにいれる"/>
                        <input type="hidden" name="item_id" value="<?php print $result['item_id'];?>">
                        <input type="hidden" name="action" value='insert_cart'>
                    </form>
                
                </div>
            
            <?php }?>  
        <?php }?>
    </div>
    <h2><a href="top.php">TOPページに戻る</a></h2>
</main>
</body>
</html>
