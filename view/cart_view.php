
<!DOCTYPE html>
<html lang="ja">
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/cart.css">   
    <meta charset="UTF-8">
    <title>購入画面</title>
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
<div class="container">    
    
    <div class="cart">    
    <?php 
    // 成功メッセージ
    if (isset ($result_msg) !==''){
        foreach ($result_msg as $value){
            print $value;
        }
    }  
    
    // エラーメッセージ
    if (count($err_msg)>0){
        foreach ($err_msg as $value){
            print $value;
        }
    }  
    ?>

        
    <h2>ショッピングカート</h2>
        <table class="table">
            <tr>
                <th>商品画像</th>
                <th>商品名</th>
                <th>価格</th>
                <th>数量</th>
                <th>小計</th>
                <th>操作</th>
            </tr>
            
                
            <?php $total = 0; ?>

            <?php if (count($results) > 0) { ?>
            <tr>    
                <?php foreach ($results as $result){ ?>
                    <?php $_SESSION['item_id'] = $result['item_id'];?>
                    <?php $_SESSION['amount'] = $result['amount'];?>
                    
                    <?php $money = $result['price'] * $result['amount']; ?>
                    <?php $total = $total + $money; ?>

                    <td>    
                        <img src ="<?php print $img_dir. $result['img'] ;?>">
                    </td>
                    
                    <td>
                        <?php print $result['name']; ?>
                    </td>
                    
                    <td>
                        <?php  print number_format($result['price']*1.08) ; ?>円
                    </td>
                    
                    <td>
                        <form method="POST">
                            <input type="number" name="change_amount" size="1" value="<?php print $result['amount'];?>">
                            <input type="hidden" name="item_id" value="<?php print $result['item_id'];?>">
                            <input type="hidden" name="action" value="change_amount">
                            <input type="submit" value="変更する" class="btn btn-secondary">
                        </form>
                                            
                        <?php 
                        // 公開ステータスが１（公開）かどうか、在庫数がゼロでないかチェック
                        if ($result['stock'] ===0 || $result['status'] ===0){
                            print '売り切れです';
                        }
                        
                        // 在庫数が希望必要数を超えてないかどうかチェック
                        if ($result['stock']  < $result['amount'] ){
                            print '在庫がありません';
                        }
                        ?>
                    </td>
                    
                    <td>
                        <?php print number_format($money*1.08); ?>円
                    </td>
                    
                    <td>
                        <form method ='POST'>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="item_id" value="<?php print $result['item_id'];?>">
                            <input type="submit" value="削除" class="btn btn-danger delete">
                        </form>
                    </td> 
            </tr>            
                <?php } // foreachおわり?>
            
            <?php } else{?>
                    <tr>
                        <td class="nodata" colspan="6">商品がありません</td>
                    </tr>
            <?php }?>
        </table>
        <h2 class ="total">合計：<?php print number_format($total*1.08) .'円';?></h2>
        
        <form method="POST" action ="finish.php">
            <input type="submit" value="購入する" class="btn btn-block btn-primary">
            <input type="hidden" name="action" value="purchase">
        </form>
    </div>    
    
</div>
</body>
</html>