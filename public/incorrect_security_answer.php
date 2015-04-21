<?php require_once("../private/initialize.php"); ?>

<?php

	// Message returned is the same whether the user 
	// was found or not, so that we don't reveal which 
	// usernames exist and which do not.
	$message = "Sorry, the Answer you provided is not correct";
	
	echo '<p class="btn-primary" align = "center">' . sanitize_html($message) . '</p>';
	echo '<a class="text-center" style="display: block;" href="../index.php">Back to Login</a>';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Forgot Password Security Question</title>
    <!-- Bootstrap core CSS-->
    <link href="../newcss/bootstrap.css" type="text/css" rel="stylesheet">
    
    <!-- Custom CSS for Login -->
    <link href="../newcss/login.css" type="text/css" rel="stylesheet">
    
  </head>
  <body>
  </body>
</html>
