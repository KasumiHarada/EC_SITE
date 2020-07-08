<?php
// 安全に表示する
function h($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'utf-8');
}

//入力されたデータの前後の空白を取り除く
function trim_space($str) {
    return preg_replace('/^[　\s]*|[　\s]*$/u', '', $str);
} 

// POSTデータから任意データ取得
function get_post_data ($key){
    $str ='';
    if (isset ($_POST[$key])===TRUE){
        $str = $_POST[$key];
    }
    return $str;
}

// DB接続
function get_db_connect(){
    //mysqlの文字列
    $dsn = 'mysql:dbname=' .$dbname.';host='.$host.';charset='.$charset;
    
    try {
        // DB接続処理
        $dbh =new PDO ($dsn, $username, $password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //エラーがあるときにcatchに飛ぶ
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //エミュレーションを無効にすると、mysql側で実行の準備をする
    
    } catch (PDOException $e){
        print 'DBに接続できません。理由：'. $e->getMessage();
    }
    return $dbh;
}


?>
