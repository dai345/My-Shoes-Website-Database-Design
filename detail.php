<?php
  session_start();
  $is_guest = false;
  if (!isset($_SESSION['username']) || $_SESSION['username'] === "temp_guest") {
    /*
      current user is a guest, insert temperary customer infomation into customer table
    */
    $is_guest = true;
    $_SESSION['username'] = "temp_guest";
    //print ("It is a guest");
  }
  $username = $_SESSION['username'];
  $product_id = $_SESSION['item_id'];
  /*
    Add Browser History
  */
  /*
    transaction begin
  */
  $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
  $pdo->query("START TRANSACTION;");
  $sth = $pdo->query("SELECT customer_id FROM customer WHERE username = '{$username}';");
  $customer_id = $sth->fetch(PDO::FETCH_ASSOC)['customer_id'];
  $pdo->query("INSERT INTO browser_history (customer_id, product_id)
               VALUES ({$customer_id}, {$product_id});");
  $pdo->query("COMMIT;");
  $pdo = null;
  /*
    transaction end
  */

  /*
    Put item into cart
  */
  if (isset($_POST['add_to_cart'])) {
    if ($is_guest) {
      echo "<script type=\"text/javascript\">"."alert(\"Your are guest, you need to log in in order to buy our product!\");"."</script>";
      // echo "<script type=\"text/javascript\">"."window.location = \"index.php\";"."</script>";
    } else {
      $item_amount = (string)$_POST['item_num'];
      $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
      /*
        transaction begin
      */
      $pdo->query("START TRANSACTION;");
      $sth = $pdo->query("SELECT price FROM product WHERE product_id = {$product_id};");
      $product_price = (double)$sth->fetch(PDO::FETCH_ASSOC)['price'];
      $total_price = (string)($product_price * (int)$item_amount);
      $sth = $pdo->query("SELECT customer_id FROM customer WHERE username = '{$username}';");
      $customer_id = $sth->fetch(PDO::FETCH_ASSOC)['customer_id'];
      $sth = $pdo->query("SELECT shopping_cart_id FROM shopping_cart WHERE customer_id = {$customer_id};");
      $shop_cart_id = $sth->fetch(PDO::FETCH_ASSOC)['shopping_cart_id'];
      $pdo->query("INSERT INTO cart_item (shopping_cart_id, product_id, product_amount, total_price)
               VALUES ({$shop_cart_id}, {$product_id}, {$item_amount}, {$total_price});");
      $pdo->query("COMMIT;");
      /*
        transaction end
      */
      $pdo = null;
      echo "<script type=\"text/javascript\">"."alert(\"Add Successfully!\");"."</script>";
      echo "<script type=\"text/javascript\">"."window.location = \"detail.php\";"."</script>";
    }
  }

  /*
      Add comments to item
    */
  if (isset($_POST['talk_sth'])) {
    if ($is_guest) {
      echo "<script type=\"text/javascript\">"."alert(\"Your are guest, you need to log in in order to add comments to product!\");"."</script>";
      // echo "<script type=\"text/javascript\">"."window.location = \"index.php\";"."</script>";
    } else {
      $rate = $_POST['rating'];
      $description = $_POST['comments'];
      $dateToday = date("d-m-Y");
      $date = date("Y-m-d", strtotime($dateToday));
      $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
      /*
        transaction begin
      */
      $pdo->query("START TRANSACTION;");
      $sth = $pdo->query("SELECT customer_id FROM customer WHERE username = '{$username}';");
      $customer_id = $sth->fetch(PDO::FETCH_ASSOC)['customer_id'];
      $pdo->query("INSERT INTO customer_review (customer_id, product_id, review_date, rate, description)
               VALUES ({$customer_id}, {$product_id}, '{$date}', {$rate}, '$description');");
      print("INSERT INTO customer_review (customer_id, product_id, review_date, rate, description)
               VALUES ({$customer_id}, {$product_id}, '{$date}', {$rate}, '$description');");
      $pdo->query("COMMIT;");
      /*
        transaction end
      */
      $pdo = null;
      echo "<script type=\"text/javascript\">"."alert(\"Add Comments Successfully!\");"."</script>";
      echo "<script type=\"text/javascript\">"."window.location = \"detail.php\";"."</script>";
    }
  }
?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Information</title>
 
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
                  if (!$is_guest) {
                    echo "<li><a href=\"card.php\">My Cards</a></li><li><a href=\"cart.php\">My Cart</a></li>
                    <li><a href=\"index.php\">Log Out</a></li>";
                  } else {
                    echo "<li><a href=\"index.php\">Sign In</a></li>";
                  }
                ?>
              </ul>
            </div>
          </div>
        </nav>
    </div>
    <?php
      $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
      $sth = $pdo->query("SELECT * FROM product WHERE product_id = {$_SESSION['item_id']};");
      $result = $sth->fetch(PDO::FETCH_ASSOC);
      $sth = $pdo->query("SELECT COALESCE(SUM(rate), -99999) AS rate_sum FROM customer_review WHERE product_id = {$_SESSION['item_id']};");
      $sum_result = $sth->fetch(PDO::FETCH_ASSOC);
      $rate_sum = (double)$sum_result['rate_sum'];
      $sth = $pdo->query("SELECT COUNT(rate) as rate_count FROM customer_review WHERE product_id = {$_SESSION['item_id']};");
      $count_result = $sth->fetch(PDO::FETCH_ASSOC);
      $rate_num = empty($count_result) ? 0 : (double)$count_result['rate_count'];
      $average_rate = $rate_num < 0.1 ? "Nobody rated this product." : (string)($rate_sum / $rate_num);
      $thumbnail_image = 'image_description/' . $result["product_pic"];
      $pdo = null;
      echo '<div class="row text-center"><img class="iii" src="',$thumbnail_image,'" /></a>';
      echo "<p >Product Name : {$result['product_name']}</p>";
      echo "<p >Product Brand : {$result['brand']}</p>";
      echo "<p >Product Gender : {$result['gender']}</p>";
      echo "<p >Product ID : {$result['product_id']}</p>";
      echo "<p >Product Price : {$result['price']}</p>";
      echo "<p >Product Rating : {$average_rate}</p>";
      echo "<p >Product Description : {$result['product_description']}</p>";
      echo "<form action='detail.php' method='post' >
              <label>How many : <input type=\"number\" name=\"item_num\" placeholder=\"Amount\" size=\"10\" required></label>
              <button name='add_to_cart' type='submit' value='{$result['product_id']}'>Add To Cart</button>
            </form><br>";
      echo "<h3>Talk about this one: </h3>";
      echo "<form action='detail.php' method='post' >
              <label>Give this product a score: <input type=\"text\" name=\"rating\" placeholder=\"  Range from 0 to 100\" size=\"25\" required></label>
              <br>
              <label>Give this product a comment: <input type=\"text\" name=\"comments\" placeholder=\"      Your Comments\" size=\"100\" required></label>
              <br>
              <button name='talk_sth' type='submit' value='{$result['product_id']}'>Submit</button>
            </form></div>";
    ?>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>