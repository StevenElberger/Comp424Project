<?php require_once("../private/initialize.php"); ?>

<?php

// Make sure session has started
session_start();

// If the username is not set in the Session Variables, do not continue.
// Redirect to the start of reset password process.
if (!isset($_SESSION["username"])) {
	echo header("Location: /Comp424Project/public/forgot_password.php");
}

// initialize variables to default values
$username = $_SESSION["username"];
$username = sanitize_sql($username);

$birthday = "";
$message = "";
$error = false;

// Search our database to retrieve the user data
// Attempt to connect to the database
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_errno()) {
	die("Database connection failed: " . mysqli_connect_error() .
	  " (" . mysqli_connect_errno() . ")");
	$log_info = "Connection to DB Failed in Birthday Verification";
   log_error("DB Connection Error", $log_info);
}

// SQL statement to retrieve rows that have the username column equal to the given username      
$sql_statement = "SELECT * FROM users WHERE username='".$username."'";

// execute query
$users = $db->query($sql_statement);

// check if anything was returned by database
if ($users->num_rows > 0) {
	$error = false;
}
else {
	$error = true;
}

// Only process request if the request is from the same domain as the 
// machine that generated the form from, the request is a post, and if the form is valid
if(!request_is_same_domain()) {
	
	// Request Forgery, log acivity
	$log_info = "A User attempted to give a request from a different domain in Birthday Verification. IP Address: " . $_SERVER['REMOTE_ADDR'];
   log_error("Request Forgery", $log_info);
   return;

}
if(request_is_post()) {
	
  if(!csrf_token_is_valid() || !csrf_token_is_recent()) {
	  
	// if not valid, notify the user and log information about event
  	$message = "Sorry, request was not valid.";
  	$log_info = "A User attempted to submit an invalid form in Birthday Verification. IP Address: " . $_SERVER['REMOTE_ADDR'];
   log_error("Form Forgery", $log_info);
   
  } else {
    // CSRF tests passed--form was created by us recently.
    
    // Retrieve response
    $birthdayResponse = $_POST["birthday"];
    
		if(!empty($birthdayResponse)) {
   
         // SQL statement to retrieve rows that have the username column equal to the given username      
         $sql_statement = "SELECT * FROM users WHERE username='".$username."'";
         
         // execute query
         $users = $db->query($sql_statement);
      
         // check if anything was returned by database
         if ($users->num_rows > 0 ) {
				
            // fetch the first row of the results of the query
            $row = $users->fetch_assoc();
            $valid = $row["valid"];

				// Check whether the response was correct or not
	         if($birthdayResponse === $row['birthday'] && $valid != 0) {
				   
				   // Correct response, send the user to security question form
				   echo header("Location: /Comp424Project/public/security_question_authentication.php");
				   
	          } else {
					
					// Incorrect response, send the user to feedback page
	            echo header("Location: /Comp424Project/public/incorrect_security_answer.php");
	            
	          }
			 } else {
				 
				 // The user does not exist in the database, set that there was an error
				 $error = true;
				 
			 }
		} 
		if ($error === true) {
			
			// If there was an error, either the birthday was incorrect or the username does not exist
			// Send the user to feedback page for incorrect input
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
		 <p>Birthday Question.</p>
		 <form role="form" id="birthday-verification-form" class="form-horizontal login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
			<?php echo csrf_token_tag(); ?>
			<div class="form-group" id="birthday-input">
				<div class="col-md-12">
					 <label>Birthday:</label>
					 <div class="input-group">
						  <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
						  <input type="date" id="birthday" name = "birthday" style="margin-top: 15px; margin-left: 15px;" data-parsley-required="true" data-parsley-group="block1" data-container="body" data-toggle="popover" data-trigger="focus" data-content="Please Enter a Birthday" data-parsley-ui-enabled="false">
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

					 // Check form for valid input
                $('#birthday-verification-form').parsley().subscribe('parsley:form:validate', function (formInstance) {

						  // Check the validity of form input for birthday
                    var birthday = formInstance.isValid('block1', true);

						  // If form data correct, then no further action required
                    if (birthday) {
                        return;
                    }

                    // otherwise, stop form submission and mark
                    // required fields with bootstrap
                    formInstance.submitEvent.preventDefault();

                    // show error alert
                    $('#error-alert').removeClass("hidden");

                    /*
                        Input validation rules:
                        - birthday Required
                     */
                    if (!birthday) {
                        $('#birthday-input').addClass("has-error");
                        $('#birthday').popover('show');
                        
                    } else {
                        $('#birthday-input').removeClass("has-error");
                    }
                });
            });
        </script>
    
  </body>
</html>
