<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store</title>
 
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
    <div class="jumbotron">
      <h1>Company manager's workspace</h1>
      <p>Create monthly sales report here.</p> 
    </div>
    <body>
      <nav class="navbar navbar-default">
        <div class="container-fluid">
          <div class="navbar-header">
            <a class="navbar-brand" href="#">Control Panel</a>
          </div>
          <div>
            <ul class="nav navbar-nav">
              <li class="active"><a href="#">Workspace</a></li>
              <?php
                echo "<li><a href=\"index.php\">Log Out</a></li>";
              ?>
            </ul>
          </div>
        </div>
      </nav>
  </div>
  <?php
    if (isset($_GET['create_report'])) {
      $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
      $sth = $pdo->query("SELECT product.brand, SUM(order_item.total_price) AS tp, SUM(order_item.product_amount) AS pa 
                   FROM product, customer_order, order_item 
                   WHERE YEAR(customer_order.order_date) = YEAR(CURDATE()) AND MONTH(customer_order.order_date) = MONTH(CURDATE()) 
                   AND product.product_id = order_item.product_id AND order_item.order_id = customer_order.order_id 
                   GROUP BY brand ORDER BY pa DESC;");
      $result = $sth->fetchAll();
      echo '<div class="row text-center">';
      $total_sales = 0;
      if (empty($result)) {
        echo '<h2>No Savles this month, work hard!</h2>';
        echo "<p>-------------------------------------------</p>";
      } else {
        echo '<h2>Report from most popular to least popular:</h2>';
        for ($i = 0; $i < count($result); $i++) {
          $num = (string)($i+1);
          $total_sales += (double)$result[$i]['tp'];
          echo '<div class="row text-center">';
          echo "<h3> Brand NO.{$num} </h3>";
          echo "<p>Brand Name : {$result[$i]['brand']}</p>";
          echo "<p>Sales Quantity : {$result[$i]['pa']}</p>";
          echo "<p >Sales Volumes: {$result[$i]['tp']}</p>";
          echo "<p>-------------------------------------------</p>";
        }
      }
      echo "<h2>Total Sales : {$total_sales}</h2>";
      echo '</div>';
      $pdo = null;
      unset($_GET['create_report']);
    }
  ?>

    <?php
    echo "<br><br><form action='report.php' class=\"col-sm-offset-4 col-sm-4\" method='get' >
              <button name='create_report' type='submit' value='yes'>Create Monthly Sales Report</button>
            </form><br>";
    ?>
  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>