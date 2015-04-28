<?php require_once("../private/initialize.php"); ?>

<?php
session_start();

// If the username is not set in the Session, redirect them to the 
// start of the reset password process
if (!isset($_SESSION["username"])) {
	echo header("Location: /Comp424Project/public/forgot_password.php");
}

// Only process request if the request is from the same domain as the 
// machine that generated the form from, the request is a post, and if the form is valid

if(request_is_post()) {
if(request_is_same_domain()) {
	
  if(!csrf_token_is_valid() || !csrf_token_is_recent()) {
	 
	// Form not valid, notify the user and log this activity
  	$message = "Sorry, request was not valid.";
  	$log_info = "A User attempted to submit an invalid form in Reset Password Options. IP Address: " . $_SERVER['REMOTE_ADDR'];
   log_error("Form Forgery", $log_info);
   
  } else {
	  
    // CSRF tests passed--form was created by us recently.
    if ($_POST["auth_method"] == "email") {
		 
		 // If email option selected, redirect to email authentication process
		 echo header("Location: /Comp424Project/public/send_email_authentication.php");
		 
    } else {
		 
		 // Otherwise security question option selected, redirect to security question process
	    echo header("Location: /Comp424Project/public/birthday_verification.php");
	    
    }
  }
} else {
	// Request Forgery, log acivity
	$log_info = "A User attempted to give a request from a different domain in Password Reset Options. IP Address: " . $_SERVER['REMOTE_ADDR'];
   log_error("Request Forgery", $log_info);
}
}

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
    <div class="well login-well" style="padding-top: 15px;">
		 <fieldset>
		 <p>Select Authentication Method.</p>
		 <form action="password_reset_option.php" method="POST" accept-charset="utf-8" class="form-horizontal login-form">
			<?php echo csrf_token_tag(); ?>
			<div class="col-md-12">
				<div class="radio">
					<label><input type="radio" name="auth_method" value = "email" checked>Email Authentication</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="auth_method" value = "question">Answer Security Question</label>
				</div>
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
