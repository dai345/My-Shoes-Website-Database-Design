<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} 
if (isset($_SESSION["username"]) ) {
  session_unset();
}
if (isset($_POST["username"])) {
  $_SESSION["password"] = $_POST["password"];
  try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
    $sth= $pdo->query("SELECT password FROM customer WHERE username='{$_POST["username"]}'");
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (empty($row)) {
      $pdo = null;
      echo "<script type=\"text/javascript\">"."alert(\"Username doesn't exist!\");"."</script>";
      echo "<script type=\"text/javascript\">"."window.location = \"index.php\";"."</script>";
    } else if ($row['password'] !== $_SESSION["password"]) {
      $pdo = null;
      echo "<script type=\"text/javascript\">"."alert(\"Password doesn't match!\");"."</script>";
      echo "<script type=\"text/javascript\">"."window.location = \"index.php\";"."</script>";
    } else {
      $_SESSION["username"] = $_POST["username"];
      $pdo = null;
      header("Location: store.php");
    }
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
  return;
}
?>
<html lang="en">
<head>
  <title>Sign In</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="E:\Program Files (x86)\XAMPP\htdocs\onlineshop\css\myCSS.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
  <h2>Sign In Here</h2>
  <form class="form-horizontal" role="form" method="post" action="<?php $_PHP_SELF ?>">
    <div class="form-group">
      <label class="control-label col-sm-2" for="username">Username:</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
      </div>
    </div>
    <div class="form-group">
      <label class="control-label col-sm-2" for="pwd">Password:</label>
      <div class="col-sm-10">          
        <input type="password" class="form-control" id="pwd" name="password" placeholder="Enter password">
      </div>
    </div>
    
    <div class="form-group"> 
      <div class="row">       
        <div class="col-sm-offset-2 col-sm-3">
          <button type="submit" class="btn btn-default">Sign in</button>
        </div>
        <div class="col-sm-3">
          <button type="reset" onclick="location.href='signup.php'">Sign up</button>
        </div>
        <div class="col-sm-3">
          <button type="reset" onclick="location.href='store.php'">Guest</button>
        </div>
      </div>
    </div>

  </form>
</div>

</body>
</html>
