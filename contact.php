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

    // html表示のためのに$modeを定義する処理
    // modeの初期値をinputにしておく
    $mode='input';
    if (isset($_POST['back']) && $_POST['back']){
        // 何もしない
    } else if (isset($_POST['confirm']) && $_POST['confirm']){
        $_SESSION['name']    = $_POST['name'];
        $_SESSION['email']   = $_POST['email'];
        $_SESSION['message'] = $_POST['message'];
        
        $mode='comfirm';
    } else if (isset($_POST['send']) && $_POST['send']){
        $mode='send';
    } else {
        // それぞれ空文字を入れてセッションを初期化しておく
        $_SESSION['name']    = "";
        $_SESSION['email']   = "";
        $_SESSION['message'] = "";
    }
    
    
    // $post 送信された場合の処理
    if ($_SERVER['REQUEST_METHOD']==='POST'){
    
    $action = get_post_data('action');
    
    
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

    } else if($action==='comfirm'){
        
        // それぞれ変数に格納する
        $name = '';
        if (isset ($_SESSION['name']) && $_SESSION['name'] ){
            $name = $_SESSION['name'];
        }
        
        $email = '';
        if (isset ($_SESSION['email']) && $_SESSION['email'] ){
            $email = $_SESSION['email'];
        }
        
        $message = '';
        if (isset ($_SESSION['message']) && $_SESSION['message'] ){
            $message = $_SESSION['message'];
        }
        
        // 名前チェック
        // nameの前後の空白を除去
        trim_space($name);
        
        // nameが空or20文字以上だったらエラー表示
        if (mb_strlen($name)===0){
            $err_msg[]='名前を入力してください';
        } else if (mb_strlen($name)>20){
            $err_msg[]='名前は20文字以内で入力してください';
        }
        
        // emaliの前後の空白を除去
        trim_space($email);
        
        // メールアドレス正規表現チェック
        if(!preg_match($email_regex, $email)){
            $err_msg[]='メールアドレスを正しく入力してください';
        }
        
        // お問い合わせ内容の文字数チェック
        if (mb_strlen($message)===0){
            $err_msg[]='お問い合わせ内容を入力してください';
        } else if (mb_strlen($message)>300){
            $err_msg[]='お問い合わせ内容は300文字以内で入力してください';
        }
        
    } else if ($action ==='send'){
        if (count ($err_msg)===0){
            
            // それぞれ変数に格納する
            $name = '';
            if (isset ($_SESSION['name']) && $_SESSION['name'] ){
                $name = $_SESSION['name'];
            }
            
            $email = '';
            if (isset ($_SESSION['email']) && $_SESSION['email'] ){
                $email = $_SESSION['email'];
            }
            
            $message = '';
            if (isset ($_SESSION['message']) && $_SESSION['message'] ){
                $message = $_SESSION['message'];
            }

            // DBに登録する
            try {
                // sql文
                $sql='INSERT INTO ec_contact (user_id, name, email, message, createdate)VALUES 
                        (:user_id, :name, :email, :message, :createdate)';
                
                // 準備
                $stmt=$dbh->prepare($sql);
                
                // 値をバインド
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $stmt->bindValue(':message', $message, PDO::PARAM_STR);
                $stmt->bindValue(':createdate', $datetime, PDO::PARAM_STR);
                
                // 実行
                $stmt->execute();
                
            } catch(PDOException $e){
                print 'DBにinsertできない'. $e->getMessage();
            }
            
        } // count err終わり 
        
    } //$action終わり    
        
        
    } //$server終わり
    
    
} catch (PDOException $e){
    print 'DBに接続できない'. $e->getMessage();
} 

?>         


<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous"> 
    <link rel="stylesheet" href="./css/common.css">   
    <link rel="stylesheet" href="./css/contact.css">    
    <meta charset="UTF-8">
    <title>CONTACT</title>
</head>
<header>
    <a href ="top.php"><h1>アフリカ布 オンラインショップ</h1></a>
    <div class ="container">
        <div class="menu">
            <ul>
                <li><a href ="top.php">HOME</a></li>
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
    
    <?php if ( count($err_msg) >0){ ?>
        <div class ="alert alert-danger" role="alert">
            <?php foreach($err_msg as $value){ ?>
                <ul><li><?php print $value; ?></li></ul>
            <?php } // foreach終わり?>
        </div>   
    <?php } // ifおわり?>
    
    <?php if ($mode === 'input' || count ($err_msg)>0){ ?>
    
        <!---入力画面のページ-->
        <div class="contact_form">
        <h2>お問い合わせフォーム</h2>    
        <form method="POST" action ="./contact.php">
            <div class="form_name">
                <div class="form_title"><lavel for ="name">お名前</lavel></div>
                <div class="form_cell"><input type="text" size="35" name="name" class="form-control" placeholder="山田　花子" value="<?php print $_SESSION['name'];?>"></div>
            </div>
            
            <div class="form_name">
                <div class ="form_title"><label for="email">メールアドレス</div>
                <div class="form_cell"><input type="email" size="35" name="email" class="form-control" placeholder="yamada@code.com" value="<?php print $_SESSION['email'];?>"></label></div>
            </div>
            
            <div class="message">
                <div class="message_title"><label for="message">お問い合わせ内容</div>
                <div class="message_cell"><textarea name="message" maxlength="300"cols="57" rows="10" class="form-control" placeholder="ここにお問い合わせ内容を入力してください" ><?php print $_SESSION['message'];?></textarea></label></div>
            </div>
            
            <div class="button">
                <input type="submit"  name="confirm" value="確認" class="btn btn-primary">
                <input type="hidden" name="action" value="comfirm">
            </div>
        </form>
        </div>
        
    <?php } else if ($mode ==='comfirm' && count ($err_msg)===0){ ?>
                                        
    
        <!---確認画面-->
        <div class="contact_confirm">
        <h2>お問い合わせフォーム</h2>    
            <form method="post" action ="./contact.php">
                <p>名前：<?php print $_SESSION['name']; ?></p>
                <p>Eメール：<?php print $_SESSION['email']; ?></p>
                <p>お問い合わせ内容：</p>
                <p><?php print nl2br($_SESSION['message']);?></p>
                <p>
                    <input type="submit" name="back" value="戻る" class="btn btn-secondary">
                    <input type="hidden" name="action" value="back">
                    <input type="submit" name="send" value="送信" class="btn btn-primary">
                    <input type="hidden" name="action" value="send">
                </p>
            </form>
        </div>
    
    <?php } else {?>
        
        <p class="send_message">送信しました。お問い合わせありがとうございました。</p>
    <?php } ?>
</body>
</html>