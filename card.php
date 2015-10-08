<?php
  session_start();
  $username = $_SESSION['username'];

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
      $sth = $pdo->query("SELECT bill_address_id, holder_nm, card_num, exp_month, exp_year, sec_code FROM customer, credit_card 
        WHERE customer.username = '{$username}' AND customer.customer_id = credit_card.customer_id;");
      $card_info = $sth->fetch(PDO::FETCH_ASSOC);
      $address_id = $card_info['bill_address_id'];
      $holder_nm = $card_info['holder_nm'];
      $card_num = $card_info['card_num'];
      $exp_month = $card_info['exp_month'];
      $exp_year = $card_info['exp_year'];
      $exp_date = $exp_month . "/" . $exp_year;
      $sec_code = $card_info['sec_code'];
      $sth = $pdo->query("SELECT street, city, state, zip FROM address WHERE address_id = {$address_id};");
      $address = $sth->fetch(PDO::FETCH_ASSOC);
      $bill_address = $address['street'] . ", " . $address['city'] . ", " . $address['state'] . ", " . $address['zip'];
      echo '<div class="row text-center">';
      echo '<br><br>';
      echo "<h3> Card Infomarmation: </h3>";
      echo "<p >Card Holder Name : {$holder_nm}</p>";
      echo "<p >Card Number : {$card_num}</p>";
      echo "<p >Card Expires Date : {$exp_date}</p>";
      echo "<p >Card Secury Code : {$sec_code}</p>";
      echo "<p >Card Bill Address : {$bill_address}</p>";
      echo '</div>';
    ?>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>