<?php require_once("/var/www/html/Comp424Project/private/initialize.php"); ?>

<?php
session_start();

// initialize variables to default values
$message = "";

// Only process request if the request is from the same domain as the 
// machine that generated the form from, the request is a post, and if the form is valid
if(request_is_post()) {
	
if(request_is_same_domain()) {
	
  if(!csrf_token_is_valid() || !csrf_token_is_recent()) {
	  
	  // form is not valid, notify the user and log the information
  	  $message = "Sorry, request was not valid.";
  	  $log_info = "A User attempted to submit an invalid form in Forgot Password. IP Address: " . $_SERVER['REMOTE_ADDR'];
     log_error("Form Forgery", $log_info);
     
  } else {
    // CSRF tests passed--form was created by us recently.
    
    // Store the username and move forward on the reset password process
    $_SESSION["username"] = test_input($_POST["username"]);
    echo header("Location: /Comp424Project/public/password_reset_option.php");
  }
} else {
	// Request Forgery, log acivity
	$log_info = "A User attempted to give a request from a different domain in Forgot Password. IP Address: " . $_SERVER['REMOTE_ADDR'];
   log_error("Request Forgery", $log_info);
}
}

// Removes unwanted and potentially malicious characters
// from the form data to prevent XSS hacks / exploits
function test_input($data) {
	$data = trim($data);
	$data = sanitize_sql($data);
	$data = htmlspecialchars($data);
	//$data = json_encode($data);
	return $data;
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
    <?php
		// Message to notify the user of important information
      if($message != "") {
        echo '<p class="btn-primary" align = "center">' . sanitize_html($message) . '</p>';
      }
    ?>
    <div class="well login-well" style="padding-top: 15px;">
		 <fieldset>
		 <p>Enter your username to reset your password.</p>
		 <form role="form" id="reset-password-form" class="form-horizontal login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
			<?php echo csrf_token_tag(); ?>
			<div class="form-group" id="username-input">
				<div class="col-md-12">
					 <label>Username:</label>
					 <div class="input-group">
						  <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
						  <input type="text" name="username" maxlength="32" id="username" class="form-control" data-container="body" data-toggle="popover" data-trigger="focus" data-content="Please Enter a Username" data-parsley-required="true" data-parsley-type="alphanum" data-parsley-group="block1" data-parsley-ui-enabled="false">
					 </div>
				</div>
		  </div>
			<div class="col-md-12">
				<input type="submit" name="submit" value="Submit" class="btn btn-lg btn-block btn-primary"/>
				<a class="text-center" style="display: block;" href="forgot_username.php">Forgot your username?</a>
				<a class="text-center" style="display: block;" href="../index.php">Back to Login</a>
			</div>
		 </form>
		 </fieldset>
    </div>
    
            <!-- Bootstrap core JavaScript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <!-- Form validation from Parsley -->
        <script src="../js/parsley.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                // activate all popovers
                $(function () {
                    $('[data-toggle="popover"]').popover();
                });

					 // Check the validity of thr form that is being submitted
                $('#reset-password-form').parsley().subscribe('parsley:form:validate', function (formInstance) {
						  
						  // Check for valid input for all entered information
                    var username = formInstance.isValid('block1', true);

						  // If input is valid, not more actions required
                    if (username) {
                        return;
                    }

                    // otherwise, stop form submission and mark
                    // required fields with bootstrap
                    formInstance.submitEvent.preventDefault();

                    // show error alert
                    $('#error-alert').removeClass("hidden");

                    /*
                        Input validation rules:
                        - Username Required
                     */
                    if (!username) {
                        $('#username-input').addClass("has-error");
                        $('#username').popover('show');
                    } else {
                        $('#username-input').removeClass("has-error");
                    }
                });
            });
        </script>
  </body>
</html>
