<?php require_once("/var/www/html/Comp424Project/private/initialize.php"); ?>
<?php

session_start();

$message = "";

$token = $_GET['token'];

// Confirm that the token sent is valid
$username = find_user_with_token($token);
if(!isset($username)) {
	// Token wasn't sent or didn't match a user.
	redirect_to('../index.php');
}
		
// Update Password in Database and Remove Token
// Attempt to connect to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_errno()) {
	die("Database connection failed: " . mysqli_connect_error() .
	  " (" . mysqli_connect_errno() . ")");
	$log_info = "Connection to DB Failed in Validate Account";
   log_error("DB Connection Error", $log_info);
}

$username = sanitize_sql($username);

// SQL statement to retrieve rows that have the username column equal to the given username      
$sql_statement = "UPDATE users SET valid='1' WHERE username='" .$username. "'";

// execute query
$users = $db->query($sql_statement);

// check if anything was returned by database
if ($db->affected_rows > 0) {
	
	// Message returned is the same whether the user 
	// was found or not, so that we don't reveal which 
	// usernames exist and which do not.
	$message = "Your account has been authenticated";
	
	echo '<p class="btn-primary" align = "center">' . sanitize_html($message) . '</p>';
	echo '<a class="text-center" style="display: block;" href="../index.php">Back to Login</a>';
} else {
	
	$message = "Error in Authenticating";
	
	echo '<p class="btn-primary" align = "center">' . sanitize_html($message) . '</p>';
	echo '<a class="text-center" style="display: block;" href="../index.php">Back to Login</a>';
}

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Account Authentication</title>
    
     <!-- Bootstrap core CSS-->
    <link href="../newcss/bootstrap.css" type="text/css" rel="stylesheet">
    
    <!-- Custom CSS for Login -->
    <link href="../newcss/login.css" type="text/css" rel="stylesheet">
  </head>
  <body style="padding-top: 0;">

  </body>
</html>
