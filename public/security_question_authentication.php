<?php require_once("../private/initialize.php"); ?>

<?php
session_start();

if (!isset($_SESSION["username"])) {
	echo header("Location: /Comp424Project/public/forgot_password.php");
}

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
				   
				   $log_info = "A User attempted to reset password through security question for username, " . $username . ". Request successful.";
               log_activity("Password Reset Request", $log_info);
				   
				   $sql_statement = "SELECT * FROM users WHERE username='".$username."'";
         
					// execute query
					$users = $db->query($sql_statement);
					$row = $users->fetch_assoc();
					$token = $row["reset_token"];
				   $_SESSION["token"] = $token;
				   echo header("Location: /Comp424Project/public/reset_password.php");
				   
	          } else {
					$log_info = "A User attmepted to reset password through security question for username, " . $username . ". Request failed.";
               log_activity("Password Reset Request", $log_info);
	            echo header("Location: /Comp424Project/public/incorrect_security_answer.php");
	          }
			 } else {
				$log_info = "A User attmepted to reset password through security question for username, " . $username . ". Request failed, username does not exist.";
            log_activity("Password Reset Request", $log_info);
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
		 <form role="form" id="security-question-form" class="form-horizontal login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
			<?php echo csrf_token_tag(); ?>
			<div class="form-group" id="security-answer-input">
				<div class="col-md-12">
					<label><?php echo sanitize_html($securityQuestion); ?>:</label>
					<div class="input-group"> 
						<input type="text" name="security_answer" class="form-control" value="<?php echo sanitize_html($securityAnswer); ?>" data-container="body" data-toggle="popover" data-trigger="focus" data-content="Please Answer Security Question" data-parsley-required="true" data-parsley-group="block1" data-parsley-ui-enabled="false">
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<input type="submit" name="submit" value="Submit" class="btn btn-lg btn-block btn-primary"/>
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

                $('#security-question-form').parsley().subscribe('parsley:form:validate', function (formInstance) {

                    var securityAnswer = formInstance.isValid('block1', true);

                    if (securityAnswer) {
                        return;
                    }

                    // otherwise, stop form submission and mark
                    // required fields with bootstrap
                    formInstance.submitEvent.preventDefault();

                    // show error alert
                    $('#error-alert').removeClass("hidden");

                    /*
                        Input validation rules:
                        - Security Answer Required
                     */
                    if (!securityAnswer) {
                        $('#security-answer-input').addClass("has-error");
                        $('#security_answer').popover('show');
                    } else {
                        $('#security-answer-input').removeClass("has-error");
                    }
                });
            });
        </script>
        
  </body>
</html>
