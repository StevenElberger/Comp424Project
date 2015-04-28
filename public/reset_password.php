<?php require_once("/var/www/html/Comp424Project/private/initialize.php"); ?>
<?php

session_start();

$message = "";

// Token should be stored in the Session
$token = $_SESSION['token'];


// Confirm that user requested to reset password
$username = find_user_with_token($token);

if(!isset($username)) {
	
	// Token wasn't set in Session or didn't match a user.
	redirect_to('forgot_password.php');
}

// Only process request if the request is from the same domain as the 
// machine that generated the form from, the request is a post, and if the form is valid

if(request_is_post()) {
if(request_is_same_domain()) {
	
  if(!csrf_token_is_valid() || !csrf_token_is_recent()) {
	  
	// If the form is invalid, notify the user and log this activity
  	$message = "Sorry, request was not valid.";
  	$log_info = "A User attempted to submit an invalid form in Reset Password. IP Address: " . $_SERVER['REMOTE_ADDR'];
   log_error("Form Forgery", $log_info);
   
  } else {
	   // CSRF tests passed--form was created by us recently.
	
		// retrieve the values submitted via the form
	   $password = $_POST['password'];
	   $password_confirm = $_POST['confirm'];
	   
	   $password = test_input($password);
			
		// password and password_confirm are valid
		// Hash the password and save it to the fake database
		$hashed_password = password_hash($password, PASSWORD_BCRYPT);
		
		// Update Password in Database and Remove Token
		
		// Attempt to connect to the database
		$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		if (mysqli_connect_errno()) {
			die("Database connection failed: " . mysqli_connect_error() .
			  " (" . mysqli_connect_errno() . ")");
			$log_info = "Connection to DB Failed in Reset Password";
			log_error("DB Connection Error", $log_info);
		}

		// SQL statement to retrieve rows that have the username column equal to the given username      
		$sql_statement = "SELECT * FROM users WHERE username='" .$username. "'";

		// execute query
		$users = $db->query($sql_statement);
	
		// check if anything was returned by database
		if ($users->num_rows > 0) {
		   $sql_statement = "UPDATE users SET password='" .$hashed_password. "' WHERE username ='" .$username."'";
		   $db->query($sql_statement);
		   
		   $log_info = "A User has successfully reset the password for username, " . $username . ".";
			log_activity("Password Reset Successful", $log_info);
		   
		   // fetch the first row of the results of the query
			$row = $users->fetch_assoc();
		   delete_reset_token($row['username']);
		   $db->close();
		   
		   // Once the Password has been updated, redirect user to login
		   redirect_to('../index.php');
		   
	   }
	}
} else {
	// Request Forgery, log acivity
	$log_info = "A User attempted to give a request from a different domain in Reset Password. IP Address: " . $_SERVER['REMOTE_ADDR'];
   log_error("Request Forgery", $log_info);
}
}

// Removes unwanted and potentially malicious characters
// from the form data to prevent XSS hacks / exploits
function test_input($data) {
	 $data = trim($data);
	 $data = sanitize_sql($data);
	 $data = htmlspecialchars($data);
	 return $data;
}

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Reset Password</title>
    
     <!-- Bootstrap core CSS-->
    <link href="../newcss/bootstrap.css" type="text/css" rel="stylesheet">
    
    <!-- Custom CSS for Login -->
    <link href="../newcss/login.css" type="text/css" rel="stylesheet">
  </head>
  <body style="padding-top: 0;">

    <?php
      if($message != "") {
        echo '<p>' . sanitize_html($message) . '</p>';
      }
    ?>
		<div class="well login-well">
			<fieldset>
				<p>Set your new password.</p>
	    
					<?php $url = "reset_password.php?token=" . sanitize_url($token); ?>
				<form role="form" id="reset-password-form" action="<?php echo $url; ?>" method="POST" accept-charset="utf-8" class="form-horizontal login-form">
					<?php echo csrf_token_tag(); ?>
				  <div class="form-group" id="password-input">
						<div class="col-md-12">
							 <label>Password:</label>
							 <div class="input-group">
								  <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
								  <input type="password" id="password" maxlength="16" name="password" class="form-control" data-container="body" data-toggle="popover" data-trigger="focus" data-content="8 - 16 characters long" data-parsley-required="true" data-parsley-length="[8, 16]" data-parsley-group="block1" data-parsley-ui-enabled="false">
							 </div>
						</div>
				  </div>
		    <!-- begin progress bar -->
		    <div class="progress progress-striped active" id="strength-bar-div">
			<div class="progress-bar" id="strength-bar" style="width: 0%"></div>
		    </div>
				  <div class="form-group" id="confirm-input">
						<div class="col-md-12">
							 <label>Confirm Password:</label>
							 <div class="input-group">
								  <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
								  <input type="password" id="confirm" maxlength="16" name="confirm" class="form-control" data-container="body" data-toggle="popover" data-trigger="focus" data-content="must match password" data-parsley-required="true" data-parsley-equalto="#password" data-parsley-length="[8, 16]" data-parsley-group="block2" data-parsley-ui-enabled="false">
							 </div>
						</div>
				  </div>
					<div class="col-md-12">
						<input type="submit" name="submit" value="Set password" class="btn btn-lg btn-block btn-primary" />
						<a class="text-center" style="display: block;" href="../index.php">Go to Login</a>
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
	function passwordStrength() {
		var strength = ["", "Weak", "Okay", "Good", "Strong", "Very strong"];
		var score = 0;
		var password = $("#password").val();

		// increase score for pass length of at least 8
		if (password.length > 0) score++;

		// increase score if pass contains both a lowercase and uppercase letter
		if ( (password.match(/[a-z]/)) && (password.match(/[A-Z]/)) ) score++;

		// increase score if pass contains a number
		if (password.match(/\d+/)) score++;

		// increase score if pass contains at least 1 symbol
		if (password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/)) score++;

		// increase score if pass length of at least 12
		if (password.length > 12) score++;

		// provide feedback to user
		var percent = "" + score*20 + "%";
		$("#strength-bar").css("width", percent).html(strength[score]);
	}

        $(document).ready(function () {

	   $("#password").keyup(function() {
		passwordStrength();
	   });

	    // activate all popovers
            $(function () {
                $('[data-toggle="popover"]').popover();
            });

                $('#reset-password-form').parsley().subscribe('parsley:form:validate', function (formInstance) {

                    var password = formInstance.isValid('block1', true);
                    var confirm = formInstance.isValid('block2', true);

                    if (password && confirm) {
                        return;
                    }

                    // otherwise, stop form submission and mark
                    // required fields with bootstrap
                    formInstance.submitEvent.preventDefault();

                    // show error alert
                    $('#error-alert').removeClass("hidden");

                    /*
                        Input validation rules:
                        - All forms required
                        - Password must be 8 to 16 characters long
                        - Confirm password must match password, 8 to 16 characters long
                     */

                    if (!password) {
                        $('#password-input').addClass("has-error");
                        $('#password').popover('show');
                    } else {
                        $('#password-input').removeClass("has-error");
                    }

                    if (!confirm) {
                        $('#confirm-input').addClass("has-error");
                        $('#confirm').popover('show');
                    } else {
                        $('#confirm-input').removeClass("has-error");
                    }
                });
            });
        </script>
		
  </body>
</html>
