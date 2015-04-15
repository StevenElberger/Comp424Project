<?php require_once("../private/initialize.php"); ?>

<?php
session_start();

if(request_is_post() && request_is_same_domain()) {
	
  if(!csrf_token_is_valid() || !csrf_token_is_recent()) {
  	$message = "Sorry, request was not valid.";
  } else {
    // CSRF tests passed--form was created by us recently.
    if ($_POST["auth_method"] == "email") {
		 echo header("Location: /Comp424Project/public/send_email_authentication.php");
    } else {
	    echo header("Location: /Comp424Project/public/security_question_authentication.php");
    }
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
