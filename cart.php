<?php
  session_start();
  $username = $_SESSION['username'];
  /*
      Add comments to item
    */
  if (isset($_POST['pay'])) {
    $dateToday = date("d-m-Y");
    $date = date("Y-m-d", strtotime($dateToday));
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
    /*
      transaction begin
    */
    $pdo->query("START TRANSACTION;");
    $sth = $pdo->query("SELECT customer_id FROM customer WHERE username = '{$username}';");
    $customer_id = $sth->fetch(PDO::FETCH_ASSOC)['customer_id'];
    $sth = $pdo->query("SELECT credit_card_id FROM credit_card WHERE customer_id = {$customer_id};");
    $card_id = $sth->fetch(PDO::FETCH_ASSOC)['credit_card_id'];
    $pdo->query("INSERT INTO customer_order (customer_id, paid_credit_card_id, order_date, total_cost)
               VALUES ({$customer_id}, {$card_id}, '{$date}', 0);");
    $sth = $pdo->query("SELECT MAX(order_id) AS latest_order_id FROM customer_order WHERE customer_id = {$customer_id};");
    $latest_order_id = $sth->fetch(PDO::FETCH_ASSOC)['latest_order_id']; 

    $sth = $pdo->query("SELECT shopping_cart_id FROM customer, shopping_cart WHERE customer.username = '{$username}' AND 
                          customer.customer_id = shopping_cart.customer_id;");
    $shop_cart_id = $sth->fetch(PDO::FETCH_ASSOC)['shopping_cart_id'];
    $sth = $pdo->query("SELECT cart_item_id, product_id, product_amount, total_price FROM cart_item WHERE shopping_cart_id = {$shop_cart_id};");
    $result = $sth->fetchAll();
    $total_price = 0;
    for ($i = 0; $i < count($result); $i++) {
      $cart_item_id = $result[$i]['cart_item_id'];
      $product_id = $result[$i]['product_id'];
      $product_amount = $result[$i]['product_amount'];
      $cur_price = $result[$i]['total_price'];
      $total_price += (double)$cur_price;
      $sth = $pdo->query("DELETE FROM cart_item WHERE cart_item_id = {$cart_item_id};");
      $pdo->query("INSERT INTO order_item (product_id, order_id, product_amount, total_price)
               VALUES ({$product_id}, {$latest_order_id}, {$product_amount}, {$cur_price});");
    }
    $sth = $pdo->query("UPDATE customer_order SET total_cost = {$total_price} WHERE order_id = {$latest_order_id};");
    $pdo->query("COMMIT;");
    /*
      transaction end
    */
    unset($_POST['pay']);
    $pdo = null;
    echo "<script type=\"text/javascript\">"."alert(\"Paid Successfully!\");"."</script>";
  }
?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop Cart</title>
 
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/myCSS.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container">
        <nav class="navbar navbar-default">
          <div class="container-fluid">
            <div class="navbar-header">
              <a class="navbar-brand" href="#">Control Panel</a>
            </div>
            <div>
              <ul class="nav navbar-nav">
                <li><a href="store.php">Store</a></li>
                <?php
                  echo "<li><a href=\"card.php\">My Cards</a></li><li><a href=\"cart.php\">My Cart</a></li>
                  <li><a href=\"index.php\">Log Out</a></li>";
                ?>
              </ul>
            </div>
          </div>
        </nav>
    </div>
    <?php
      $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
      $sth = $pdo->query("SELECT shopping_cart_id FROM customer, shopping_cart WHERE customer.username = '{$username}' AND 
                          customer.customer_id = shopping_cart.customer_id;");
      $shop_cart_id = $sth->fetch(PDO::FETCH_ASSOC)['shopping_cart_id'];
      $sth = $pdo->query("SELECT product_id, product_amount, total_price FROM cart_item WHERE shopping_cart_id = {$shop_cart_id};");
      $result = $sth->fetchAll();
      if(count($result) === 0) {
        $pdo = null;
        echo '<h2 class="text-center">There are no items in your cart.</h2>';
        exit;
      }
      $total_price = 0;
      echo '<div class="row text-center">';
      for ($i = 0; $i < count($result); $i++) {
        $product_id = $result[$i]['product_id'];
        $product_amount = $result[$i]['product_amount'];
        $cur_price = $result[$i]['total_price'];
        $total_price += (double)$cur_price;
        $sth = $pdo->query("SELECT product_name, brand, gender, product_pic, price FROM product WHERE product_id = {$product_id};");
        $product_info = $sth->fetch(PDO::FETCH_ASSOC);
        $num = (string)($i+1);
        $thumbnail_image = 'image_description/' . $product_info["product_pic"];
        echo '<br><br>';
        echo "<h3> Item NO.{$num} </h3>";
        echo '<img src="',$thumbnail_image,'"/>';
        echo "<p >Product Name : {$product_info['product_name']}</p>";
        echo "<p >Product Brand : {$product_info['brand']}</p>";
        echo "<p >Product Gender : {$product_info['gender']}</p>";
        echo "<p >Product ID : {$product_id}</p>";
        echo "<p >Product Single Price : {$product_info['price']}</p>";
        echo "<p >Product Amount : {$product_amount}</p>";
        echo "<p >Sum Amount : {$cur_price}</p>";
      }
      $pdo = null;
      echo "<br><p >------------------------------------------------------</p>";
      echo "<h2>Total Cost : {$total_price}</h2>";
      echo "<form action='cart.php' method='post' >
                <button name='pay' id='pay_button' type='submit' value='pay_for']}'>Pay money</button>
            </form><br>";
      echo '</div>';
    ?>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>