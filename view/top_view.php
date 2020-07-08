<!DOCTYPE html>
<html lang="ja">
<head>
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/common.css">   
    <link rel="stylesheet" href="./css/top.css">    
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
    <section class="jumbotron text-center">
        
            
            <p class="lead text-muted">アフリカの伝統布：チテンゲ。独創的でカラフルな布を販売します。</p>
            <p class="lead text-muted">部屋のインテリアに、小物作りに、いかがでしょうか。異文化理解のきっかけになれば幸いです。</p>
    </section>
  
    <div class="result_msg">
        <?php if ($flash_msg !== ''){?>
        <ul><li><?php print $flash_msg;?></li></ul>
        <?php } ?>
    
         <?php if (count ($result_msg)>0){?>
        <?php foreach($result_msg as $value){?>
        <?php print $value;}?>
        <?php }?>
    </div>
    
    <div class="item_list">
        
        <table border="1">
            <?php
            $i=0;
            $max = 4;
            foreach($results as $result):
            $i++;
            ?>
            
            <?php if($i == 1):?>
            
            <tr>
                <?php endif;?>
                
                    <td>
                        <table>
                            
                            <div class ="linkbox">
                             
                                <tr>
                                    <td><img src="<?php print h($img_dir. $result['img']);?>"></td>
                                </tr>
                                
                                <tr>
                                    <td><a href ="detail.php?item_id=<?php print $result['item_id']; ?>"><?php print h($result['name']);?></a></td>
                                </tr>
                                
                                <tr>
                                    <td><?php print '￥'.number_format($result['price']*1.08);?></td>
                                </tr>
                            
                                <tr>
                                    <td>
                                        <?php if (($result['stock'])===0){ ?>
                            
                                        <div class="sold_out">
                                            <?php print 'SOLD OUT'; ?>
                                            <?php } else {?>
                                            
                                            <form method="POST" action ="cart.php">
                                                <!--大<input type="radio" name="size" value="1">-->
                                                <!--中<input type="radio" name="size" value="2">-->
                                                <!--小<input type="radio" name="size" value="3">-->
                                                  
                                                <input type="submit" value="カートに入れる" class="btn btn-block btn-secondary">
                                                <input type="hidden" name="item_id" value="<?php print $result['item_id'];?>">
                                                <input type="hidden" name="action" value='insert_cart'>
                                            </form>
                                             
                                        <?php } ?>
                                        </div> 
                                    </td>
                                </tr>
                              
                            </div>
                        
                        </table>
                    </td>
                
                <?php if($i == $max):?>
            </tr>
            <?php $i=0; ?><?php endif;?>
            <?php endforeach;?>
        </table>
        
    </div>

</main>
<footer>
    <div class ="container">
        <p><small>Copyright&copy;shop all Rights Reserved</small></p>
    </div>
   
</footer>
</body>
</html>