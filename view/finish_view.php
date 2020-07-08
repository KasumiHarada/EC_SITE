<!DOCTYPE html>
<html lang="ja">
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/common.css">
    <meta charset="UTF-8">
    <title>購入完了画面</title>
</head>
<header>
     
    <a href ="top.php"><h1>アフリカ布 オンラインショップ</h1></a>
    <div class ="header-user">
        <li><?php print 'ようこそ'. h($_SESSION['user_name']). 'さん'; ?></li>
  
        <li>
            <form method ="POST">
                <label><input type="submit" value="ログアウト"></label>
                <input type="hidden" name="action" value="logout">            
            </form>
        </li>
    </div>
        
</header>
<body>
    
    <?php 
    // 在庫が足りない、公開商品じゃない場合のエラーメッセージ表示
    if (count($err_msg>0)){
        foreach($err_msg as $value){
            print $value;
        }
    }
    ?>
    <a href ="top.php"><p>買い物を続ける</p></a>
    
    <?php $total = 0; ?>
    <?php if (count($err_msg)===0){ ?>
    <h2>ご購入ありがとうございました。</h2>
    <table>
        <tr>
            <th></th>
            <th></th>
            <th>価格</th>
            <th>数量</th>
        </tr>
        <?php foreach ($results as $result){ ?>
    
    
            <?php $_SESSION['item_id'] = $result['item_id'];?>
            <?php $_SESSION['amount'] = $result['amount'];?>
            
            <?php $money = $result['price'] * $result['amount']; ?>
            <?php $total = $total + $money; ?>
            
            <tr>
                <td>
                    <img src ="<?php print $img_dir. $result['img'] ;?>">
                </td>
                
                <td>
                    <?php print $result['name']; ?>
                </td>
                
                <td>
                    <?php  print number_format($result['price']); ?>円
                </td>
                
                <td>
                    <?php print $result['amount']; ?>個
                </td>
            </tr>

        <?php } //foreachおわり ?>
    </table>
    
    <h2>合計：<?php print number_format($total) ;?>円</h2>
    <?php } else if (count ($results)===0 ){ ?>
        <p>商品がありません</p>
    <?php } ?>
    
  
</body>
</html>