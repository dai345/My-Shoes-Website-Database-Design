<?php
    function make_thumb($src,$dest,$desired_width) {
      /* read the source image */
      $source_image = imagecreatefromjpeg($src);
      $width = imagesx($source_image);
      $height = imagesy($source_image);
      /* find the "desired height" of this thumbnail, relative to the desired width  */
      $desired_height = floor($height*($desired_width/$width));
      /* create a new, "virtual" image */
      $virtual_image = imagecreatetruecolor($desired_width,$desired_height);
      /* copy source image at a resized size */
      imagecopyresized($virtual_image,$source_image,0,0,0,0,$desired_width,$desired_height,$width,$height);
      /* create the physical thumbnail image to its destination */
      imagejpeg($virtual_image,$dest);
    }

    /* function:  returns files from dir */
    function get_files($images_dir,$exts = array('jpg')) {
      $files = array();
      if($handle = opendir($images_dir)) {
        while(false !== ($file = readdir($handle))) {
          $extension = strtolower(get_file_extension($file));
          if($extension && in_array($extension,$exts)) {
            $files[] = $file;
          }
        }
        closedir($handle);
      }
      return $files;
    }

    /* function:  returns a file's extension */
    function get_file_extension($file_name) {
      return substr(strrchr($file_name,'.'),1);
    }

    session_start();
    /*
      database manager goes to the mysql admin page
    */
    if (isset($_SESSION['username']) && $_SESSION['username'] === "db_manager") {
      header("Location: http://localhost/phpmyadmin/");
      exit;
    }

    /*
      company manager goes to the monthly sales report page
    */
    if (isset($_SESSION['username']) && $_SESSION['username'] === "company_manager") {
      header("Location: report.php");
      exit;
    }
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
    $customer_id = -1;
    $is_guest = false;
    if (!isset($_SESSION['username']) || $_SESSION['username'] === "temp_guest") {
      /*
        current user is a guest, insert temperary customer infomation into customer table
      */
      $is_guest = true;
      $pdo->query("INSERT INTO customer (temp_user) VALUES ('Y');");
      $_SESSION['username'] = "temp_guest";
      $sth = $pdo->query("SELECT MAX(customer_id) AS cid FROM customer");
      $row = $sth->fetch(PDO::FETCH_ASSOC);
      $customer_id = $row["cid"];
      $pdo = null;
    } else {
      $sth = $pdo->query("SELECT customer_id FROM customer WHERE username = '{$_SESSION['username']}';");
      $customer_id = $sth->fetch(PDO::FETCH_ASSOC)['customer_id'];
      $pdo = null;
    }
    if (isset($_POST["item_id"])) {
      $_SESSION['item_id'] = $_POST["item_id"];
      header('Location: detail.php');
    }
?>

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
      <h1>Shoe's Store</h1>
      <p>Feel free to chose your favourite shoes here! Lowest prices promise!</p> 
    </div>
    <body>
      <nav class="navbar navbar-default">
        <div class="container-fluid">
          <div class="navbar-header">
            <a class="navbar-brand" href="#">Control Panel</a>
          </div>
          <div>
            <ul class="nav navbar-nav">
              <li class="active"><a href="store.php">Store</a></li>
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
  <form role="form" method="get">
    <div class="container">
      <h2>Chose Brand:</h2>
      <label class="checkbox-inline">
        <input type="checkbox" name="brand[]" value="Nike">Nike
      </label>
      <label class="checkbox-inline">
        <input type="checkbox" name="brand[]" value="Adidas">Adidas
      </label>
      <label class="checkbox-inline">
        <input type="checkbox" name="brand[]" value="Puma">Puma
      </label>
      <label class="checkbox-inline">
        <input type="checkbox" name="brand[]" value="New Balance">New Balance
      </label>
      <label class="checkbox-inline">
        <input type="checkbox" name="brand[]" value="Danner">Danner
      </label>
      <label class="checkbox-inline">
        <input type="checkbox" name="brand[]" value="Lacoste">Lacoste
      </label>
      <label class="checkbox-inline">
        <input type="checkbox" name="brand[]" value="Steve Madden">Steve Madden
      </label>
      <label class="checkbox-inline">
        <input type="checkbox" name="brand[]" value="Calvin Klein">Calvin Klein
      </label>
      <label class="checkbox-inline">
        <input type="checkbox" name="brand[]" value="Skechers">Skechers
      </label>
    </div>
    <div class="container">
      <h2>Chose Style:</h2>
      <label class="checkbox-inline">
        <input type="checkbox" name="style[]" value="athletic">Athletic
      </label>
      <label class="checkbox-inline">
        <input type="checkbox" name="style[]" value="casual">Casual
      </label>
      <label class="checkbox-inline">
        <input type="checkbox" name="style[]" value="business">Buisiness
      </label>
    </div>
    <div class="container">
      <div>
        <h2>Chose Gender:</h2>
        <select name="gender" class="form-control">
          <option value="">-- Please Select --</option>
          <option value="female">Female</option>
          <option value="male">Male</option>
        </select>
      </div>
    </div>

    <div class="container">
      <div>
        <h2>Chose Price Range:</h2>
        <div class="row">
          <input type="number" step="any" class="col-md-2" name="min_price" placeholder="min price"/>
          <input type="number" step="any" class="col-md-2" name="max_price" placeholder="max price"/>
        </div>
      </div>
    </div>
    <div class="container">
      <div>
        <h2>Chose Sort Method:</h2>
          <select name="sort">
            <option value=""  selected>-- Please Select --</option>
            <option value="price_low2high">Prices Low to High</option>
            <option value="price_high2low">Prices High to Low</option>
          </select>
        </p>
      </div>
    </div>
    <br>
    <input id="submit" class="col-sm-offset-2 col-sm-1" type="submit" name="submit" value="Submit">
    <button type="reset" class="col-sm-1">Clear All</button>
  </form>
  <br>
  <br>
    <?php
      if (isset($_GET["submit"])) {
        /*
          check brands
        */
        $brand_requirement = " WHERE brand IN (";
        if (isset($_GET["brand"])) {
          $brands = $_GET["brand"];
          for ($i = 0; $i < count($brands); $i++) {
            if ($brands[$i] !== "") {
              $brand_requirement .= " '" . $brands[$i] . "',";
            }
          }
          $brand_requirement = rtrim($brand_requirement, ",");
          $brand_requirement .= ") ";
        } else {
          $brand_requirement = "";
        }
        
        
        /*
          check style
        */
        $style_requirement = $brand_requirement === "" ? " WHREE" : " AND";
        $style_requirement .= " style IN (";
        if (isset($_GET["style"])) {
          $style = $_GET["style"];
          for ($i = 0; $i < count($style); $i++) {
            if ($style[$i] !== "") {
              $style_requirement .= " '" . $style[$i] . "',";
            }
          }
          $style_requirement = rtrim($style_requirement, ",");
          $style_requirement .= ") ";
        } else {
          $style_requirement = "";
        }
        // print ($style_requirement);
        /*
          check gender
        */
        $gender = $_GET['gender'];
        $gender_requirement = "";
        if ($_GET["gender"] !== "") {
          $gender_requirement = ($brand_requirement === "" && $style_requirement === "") ? " WHERE" : "";
          $gender_requirement .= ($brand_requirement !== "" || $style_requirement !== "") ? " AND" : "";
          $gender_requirement .= " gender='{$_GET['gender']}'";
        }

        /*
          check price range
        */
        $low_price = (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) ? (float)$_GET['min_price'] : -1; 
        $high_price = (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) ? (float)$_GET['max_price'] : 999999999;
        $price_requirement = ($brand_requirement === "" && $style_requirement === "" && $gender_requirement === "") ? " WHERE" : "";
        $price_requirement .= ($brand_requirement !== "" || $style_requirement !== "" || $gender_requirement !== "") ? " AND" : "";
        $price_requirement .= " (price BETWEEN $low_price AND $high_price)";
        $order_requirement = "";
        if ($_GET["sort"] !== "") {
          $order_requirement = " ORDER BY price ";
          if ($_GET["sort"] === "price_low2high") {
            $order_requirement .= "ASC";
          } else {
            $order_requirement .= "DESC";
          }
        } else {
          $order_requirement = " ORDER BY brand" ;
        }
        $whole_query = "SELECT * FROM product " . $brand_requirement . $style_requirement . $gender_requirement . $price_requirement 
                        . $order_requirement . ";";
       // print ($whole_query);
        //print ($whole_query . "\n");
        $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
        $sth= $pdo->query($whole_query);
        $result = $sth->fetchAll();
        /** settings **/
        $images_dir = 'images/';
        $thumbs_dir = 'image_description/';
        $thumbs_width = 200;
        $images_per_row = 3;


        /** generate photo gallery **/
        if(count($result)) {
          $index = 0;
          for ($i = 0; $i < count($result); $i++) {
            $thumbnail_image = $thumbs_dir.$result[$i]["product_pic"];
            if(!file_exists($thumbnail_image)) {
              $extension = get_file_extension($thumbnail_image);
              if($extension) {
                make_thumb($images_dir.$result[$i]["product_pic"],$thumbnail_image,$thumbs_width);
              }
            }
            echo '<a href="',$images_dir.$result[$i]["product_pic"],'" class="photo-link smoothbox" rel="gallery"><img src="',$thumbnail_image,'" /></a>';
            echo "<p>Product Name : {$result[$i]['product_name']}</p>";
            echo "<p>Product Brand : {$result[$i]['brand']}</p>";
            echo "<p >Product Gender : {$result[$i]['gender']}</p>";
            echo "<p>Product ID : {$result[$i]['product_id']}</p>";
            echo "<p>Product Price : {$result[$i]['price']}</p>";
            echo "<form action='store.php' method='post'>
                    <button name='item_id' type='submit' value='{$result[$i]['product_id']}'>See Details</button>
                  </form>";
            if($i % $images_per_row == 0) { echo '<div class="clear"></div>'; }
          }
          echo '<div class="clear"></div>';
        } else {
          echo '<h2>There are no results in this search.</h2>';
        }
      } else {
        /*
          automatical recommendation
        */
        $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
        $pdo->query("START TRANSACTION;");
        $sth = $pdo->query("SELECT product.brand, browser_history.browser_history_id FROM browser_history, product WHERE 
                    product.product_id = browser_history.product_id AND browser_history.customer_id = {$customer_id} 
                    ORDER BY browser_history_id DESC LIMIT 1;");
        print ("<br>");
        $history = $sth->fetch(PDO::FETCH_ASSOC);
        $result = null;
        if (empty($history)) {
          /*
            No browser history record, give this user random suggestions.
          */
          echo '<h2>What you may like (no browser history, randome suggestions):</h2>';
          $sth = $pdo->query("SELECT * FROM product ORDER BY RAND() LIMIT 3");
          $result = $sth->fetchAll();
        } else {
          /* 
            Give suggestions by using favourite brand.
          */
          echo '<h2>What you may like (according to latest browser history):</h2>';
          $favourite_brand = $history['brand'];
          $sth = $pdo->query("SELECT * FROM product WHERE brand = '{$favourite_brand}' LIMIT 3");
          $result = $sth->fetchAll();
        }
        $pdo->query("COMMIT;");
        $pdo = null;
        
        $images_dir = 'images/';
        $thumbs_dir = 'image_description/';
        $thumbs_width = 200;
        $images_per_row = 3;
        $index = 0;
        for ($i = 0; $i < count($result); $i++) {
          $thumbnail_image = $thumbs_dir.$result[$i]["product_pic"];
          if(!file_exists($thumbnail_image)) {
            $extension = get_file_extension($thumbnail_image);
            if($extension) {
              make_thumb($images_dir.$result[$i]["product_pic"],$thumbnail_image,$thumbs_width);
            }
          }
          echo '<a href="',$images_dir.$result[$i]["product_pic"],'" class="photo-link smoothbox" rel="gallery"><img src="',$thumbnail_image,'" /></a>';
          echo "<p>Product Name : {$result[$i]['product_name']}</p>";
          echo "<p>Product Brand : {$result[$i]['brand']}</p>";
          echo "<p >Product Gender : {$result[$i]['gender']}</p>";
          echo "<p>Product ID : {$result[$i]['product_id']}</p>";
          echo "<p>Product Price : {$result[$i]['price']}</p>";
          echo "<form action='store.php' method='post'>
                  <button name='item_id' type='submit' value='{$result[$i]['product_id']}'>See Details</button>
                </form>";
          if($i % $images_per_row == 0) { echo '<div class="clear"></div>'; }
        }
      }
    ?>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>



