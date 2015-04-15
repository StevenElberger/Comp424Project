<?php require_once("../private/initialize.php"); ?>

<?php
session_start();

// initialize variables to default values
$username = $_SESSION["username"];

$securityQuestion = "";
$securityAnswer = "";

$message = "";
$error = false;

// Search our database to retrieve the user data
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
	$securityQuestion = $row['security_question'];
}
else {
	$error = true;
	$securityQuestion = "What is the name of your first love?";
}

if(request_is_post() && request_is_same_domain()) {
	
  if(!csrf_token_is_valid() || !csrf_token_is_recent()) {
  	$message = "Sorry, request was not valid.";
  } else {
    // CSRF tests passed--form was created by us recently.
    $securityAnswer = $_POST["security_answer"];
    
		if(!empty($securityAnswer)) {
   
         // SQL statement to retrieve rows that have the username column equal to the given username      
         $sql_statement = "SELECT * FROM users WHERE username='".$username."'";
         
         // execute query
         $users = $db->query($sql_statement);
         
      
         // check if anything was returned by database
         if ($users->num_rows > 0) {
            // fetch the first row of the results of the query
            $row = $users->fetch_assoc();

	         if($securityAnswer == $row['security_answer']) {
				   // security question answered correctly
				   create_reset_token($username);
				   echo "Answer correctly";
				   
				   $sql_statement = "SELECT * FROM users WHERE username='".$username."'";
         
					// execute query
					$users = $db->query($sql_statement);
					$row = $users->fetch_assoc();
					$token = $row["reset_token"];
				   $_SESSION["token"] = $token;
				   echo header("Location: /Comp424Project/public/reset_password.php");
				   
	          } else {
	            echo header("Location: /Comp424Project/public/incorrect_security_answer.php");
	          }
			 }
		} 
		if ($error === true) {
			echo header("Location: /Comp424Project/public/incorrect_security_answer.php");
		}
  }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Forgot Password</title>
    <!-- Bootstrap core CSS-->
    <link href="../newcss/bootstrap.css" type="text/css" rel="stylesheet">
    
    <!-- Custom CSS for Login -->
    <link href="../newcss/login.css" type="text/css" rel="stylesheet">
    
  </head>
  <body>
    <div class="well login-well" style="padding-top: 15px;">
		 <fieldset>
		 <p>Answer Security Question.</p>
		 <form action="security_question_authentication.php" method="POST" accept-charset="utf-8" class="form-horizontal login-form">
			<?php echo csrf_token_tag(); ?>
			<div class="col-md-12">
				<label><?php echo sanitize_html($securityQuestion); ?>:</label>
				<div class="input-group"> 
					<input type="text" name="security_answer" class="form-control" value="<?php echo sanitize_html($securityAnswer); ?>" /><br />
				</div><br />
			</div>
			<div class="col-md-12">
				<input type="submit" name="submit" value="Submit" class="btn btn-lg btn-block btn-primary"/>
				<a class="text-center" style="display: block;" href="../index.php">Back to Login</a>
			</div>
		 </form>
		 </fieldset>
    </div>
  </body>
</html>