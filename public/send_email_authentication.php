<?php require_once("../private/initialize.php"); ?>

<?php
session_start();

if (!isset($_SESSION["username"])) {
	echo header("Location: /Comp424Project/public/forgot_password.php");
}

$username = $_SESSION["username"];
$username = sanitize_sql($username);

// Search our fake database to retrieve the user data
// Attempt to connect to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_errno()) {
	die("Database connection failed: " . mysqli_connect_error() .
	  " (" . mysqli_connect_errno() . ")");
}

// SQL statement to retrieve rows that have the username column equal to the given username      
$sql_statement = "SELECT * FROM users WHERE username='".$username."'";

// execute query
$users = $db->query($sql_statement);

// check if anything was returned by database
if ($users->num_rows > 0) {
	// fetch the first row of the results of the query
	$row = $users->fetch_assoc();
	$user = $row['username'];
	$valid = $row['valid'];

	if($user && $valid != 0) {
	   // Username was found; okay to reset
	   create_reset_token($username);
	   email_reset_token($username);
	   
	   $log_info = "A User requested to reset password through email for username, " . $username . ". Request successful.";
		log_activity("Password Reset Request", $log_info);
	   
	} else {
		// Username account not valid
		$log_info = "A User requested to reset password through email for username, " . $username . ". Request failed, account not valid.";
		log_activity("Password Reset Request", $log_info);
	}
} else {
	// Username was not found; don't do anything
	$log_info = "A User requested to reset password through email for username, " . $username . ". Request failed, username does not exist.";
	log_activity("Password Reset Request", $log_info);
}

// Message returned is the same whether the user 
// was found or not, so that we don't reveal which 
// usernames exist and which do not.
$message = "A link to reset your password has been sent to the email address on file.";

echo '<p class="btn-primary" align = "center">' . sanitize_html($message) . '</p>';
echo '<a class="text-center" style="display: block;" href="../index.php">Back to Login</a>';

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Forgot Password Option</title>
    <!-- Bootstrap core CSS-->
    <link href="../newcss/bootstrap.css" type="text/css" rel="stylesheet">
    
    <!-- Custom CSS for Login -->
    <link href="../newcss/login.css" type="text/css" rel="stylesheet">
    
  </head>
  <body>
  </body>
</html>

