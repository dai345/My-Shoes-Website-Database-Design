<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} 
if (isset($_SESSION["username"]) && $_SESSION['username'] !== 'temp_user') {
  session_unset();
}
if (isset($_POST["username"])) {
  try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=Shop', 'root', '');
    $sth= $pdo->query("SELECT password FROM customer WHERE username='{$_POST["username"]}'");
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (empty($row)) {
      /*
		transaction begin
      */
	  $pdo->query("START TRANSACTION;");
	  $pdo->query("INSERT INTO address (street, city, state, zip)
      			   VALUES ('{$_POST["street"]}', '{$_POST["city"]}', '{$_POST["state"]}', '{$_POST["zipcode"]}');");
	  $sth = $pdo->query("SELECT MAX(address_id) AS aid FROM address");
	  $row = $sth->fetch(PDO::FETCH_ASSOC);
	  $address_id = $row["aid"];
      $pdo->query("INSERT INTO customer (first_nm, last_nm, middle_nm, phone_num, username, password, temp_user, address_id)
      			   VALUES ('{$_POST["first_nm"]}', '{$_POST["last_nm"]}', '{$_POST["middle_nm"]}', '{$_POST["phone"]}', 
      			   '{$_POST["username"]}', '{$_POST["password1"]}', 'N', $address_id);");
      $sth = $pdo->query("SELECT MAX(customer_id) AS cid FROM customer");
      $row = $sth->fetch(PDO::FETCH_ASSOC);
      $customer_id = $row["cid"];
      $full_name = $_POST["first_nm"] . " " . $_POST["middle_nm"] . " " . $_POST["last_nm"];
      $exp_month = $_POST["exp_month"];
      $exp_year = $_POST["exp_year"];
	  $pdo->query("INSERT INTO credit_card (customer_id, bill_address_id, holder_nm, card_num, exp_month, exp_year, sec_code)
      			   VALUES ($customer_id, $address_id, '{$full_name}', '{$_POST["card_num"]}', $exp_month, $exp_year, '{$_POST["sec_code"]}');");
	  $pdo->query("INSERT INTO shopping_cart (customer_id) VALUES ($customer_id);");
      $pdo->query("COMMIT;");
      /*
		transaction end
      */
      $pdo = null;
      echo "<script type=\"text/javascript\">"."alert(\"Signed up completed\");"."</script>";
      echo "<script type=\"text/javascript\">"."window.location = \"index.php\";"."</script>";
    } else {
      $pdo = null;
      echo "<script type=\"text/javascript\">"."alert(\"Username occupied pleae try others.\");"."</script>";
      echo "<script type=\"text/javascript\">"."window.location = \"signup.php\";"."</script>";
    } 
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
  return;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Sing Up</title>
	<script type="text/javascript">
		 function check(){
		 	if (pwd1.value != pwd2.value){
				alert("Ops! Your password don't match.");
				return false;
			}
		}
	</script>
</head>
<body>
<div id="container">
<div>
	<h2>Sign Up for your account!</h2>
	<form action="<?php $_PHP_SELF ?>" method="post" onsubmit="return check();">
		<fieldset class="contact-us">
			<legend>Personal Information</legend>
			<h3>Account Information</h3>
			<div>
				<label>Userame:  <input type="text" name="username" placeholder="Chose a username" required></label>
			</div>

			<div>
				<label>Password: <input type="password" id="pwd1" name="password1" placeholder="Your password" required></label>
			</div>

			<div>
				<label>Confirm Password: <input type="password" id="pwd2" name="password2" placeholder="Repeat password"  required></label>
			</div>
			
			<h3>Name</h3>
			<div>
				<label>First name:  <input type="text" name="first_nm" placeholder="Your first name" required></label>
			</div>

			<div>
				<label>Middle name:  <input type="text" name="middle_nm" placeholder="Your middle name (optional)"></label>
			</div>

			<div>
				<label>Last name:  <input type="text" name="last_nm" placeholder="Your last name" required></label>
			</div>

			<h3>Contacts</h3>

			<div>
				<label>Street: <input type="text" name="street" placeholder="Your Street" required></label>
			</div>

			<div>
				<label>City: <input type="text" name="city" placeholder="Your City" required></label>
			</div>
			<div>
				<label>State: <input type="text" name="state" placeholder="Your State" required></label>
			</div>
			<div>
				<label>Zip Code: <input type="text" name="zipcode" placeholder="Your Zipcode" pattern="[0-9]{5}"  required></label>
			</div>

			<div>
				<label>Phone: <input type="text" name="phone" placeholder="Your Phone" pattern="[0-9]{10}"  required></label>
			</div>

			<h3>Credit Card</h3>
			<div>
				<label>Card Number: <input type="text" name="card_num" placeholder="Your Card Number" pattern="[0-9]{16}"  required></label>
			</div>

			<div>
				<label>Expire Month: <input type="text" name="exp_month" placeholder="Your Expire Month" required></label>
			</div>

			<div>
				<label>Expire Year: <input type="text" name="exp_year" placeholder="Your Expire Year" required></label>
			</div>

			<div>
				<label>Secure Code: <input type="text" name="sec_code" placeholder="Your Secure Code" required></label>
			</div>
			<br>
			<input id="submit" type="submit" value="Submit">
          	<button type="reset" >Clear All</button>

		</fieldset>
		
	</form>
</div>
</div>
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</body>
</html>