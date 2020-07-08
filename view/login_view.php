<!DOCTYPE html>
<html lang="ja">
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/login.css">
    <meta charset="UTF-8">
    <title>ログイン画面</title>
</head>
<body>
    <h1>ログインページ</h1>
        <div class ="login">
            <form method="POST">
                <p>ユーザー名：<input type="text" name="user_name" ></p>
                <p>パスワード：<input type="password" name="password"></p>
                <p><input type="submit" class ="btn btn-primary" value="ログイン"></p>
                <p><a href ="register.php">新規登録はこちら</a></p>
            </form>
        </div>
        <?php if (count ($err_msg)>0){?>
            <div class="alert alert-danger" role="alert">
                <?php foreach ($err_msg as $value){ ?>
                    <?php print $value; ?>
                <?php }?>
            </div>
        <?php }?>
</body>
</html>