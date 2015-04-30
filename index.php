<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Company Login</title>

        <!-- Bootstrap core CSS -->
        <link href="newcss/bootstrap.css" type="text/css" rel="stylesheet">

        <!-- Custom CSS for Login -->
        <link href="newcss/login.css" type="text/css" rel="stylesheet">

    </head>

    <body>

    <?php
        // Grab security functions
        require_once("/var/www/html/Comp424Project/private/initialize.php");
        // Error placeholders
        $usernameError = $passwordError = "";
        // Authentication placeholders
        $username = $password = "";
        $bad_authentication = "";
        $message = "";
        session_start();
		
        /*
		*  First, check if the request is POST
		*  Then make sure the domain is not being forged
		*  Finally, check the CSRF token to detect form forgery
		*/
        if (request_is_post()) {
	        if(request_is_same_domain()) {
	            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
	            if(!csrf_token_is_valid() && !csrf_token_is_recent()) {
	                $log_info = "A User attempted to submit an invalid form in Login. IP Address: " . $_SERVER['REMOTE_ADDR'];
	                log_error("Form Forgery", $log_info);
	            } else {
	                // CSRF tests passed--form was created by us recently
					// Check provided credentials and sanitize them
					if (isset($_POST["username"]) && !empty($_POST["username"])) {
						$username = test_input($_POST["username"]);
					}
					
					if (isset($_POST["password"]) && !empty($_POST["password"])) {
						$password = test_input($_POST["password"]);
					}

	                $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	                
	                // Check connection
					if ($conn->connect_error) {
						die("Connection failed: " . $conn->connect_error);
						$log_info = "Connection to DB Failed in Login";
						log_error("DB Connection Error", $log_info);
					}
	                
	                // check to make sure username actually exists
	                if (username_exists($username, $conn)) {
	                    // then check if the user is throttled
	                    $throttle_delay = throttle_failed_logins($username);
	                    if ($throttle_delay > 0) {
							// Throttled at the moment, try again after delay period
							set_error_string($bad_authentication, "throttle");

							$log_info = "A User attempted many times to login using username, " . $username . ", and failed. IP Address: " . $_SERVER['REMOTE_ADDR'];
							log_error("Failed Login", $log_info);
	                    } else {
	                        // not throttled - make connection to db
	                        // and check the credentials
							// Create connection
							$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
							// Check connection
							if ($conn->connect_error) {
								die("Connection failed: " . $conn->connect_error);
							}
							// Grab the password for the given username
							$sql = "SELECT * FROM users WHERE username = '" . $username . "'";
							
							$result = $conn->query($sql);
							// If there's a match, check to make sure authentication was successful
							if ($result->num_rows > 0) {
								$row = $result->fetch_assoc();
								$valid = $row["valid"];
								// We know the username matches so check the password against the hash
								if (password_verify($password, $row["password"]) && $valid != 0) {
									// grab last login and times logged in
									$last_login_sql = "SELECT last_login FROM users WHERE username = '" . $username . "'";
									$last_login = $conn->query($last_login_sql);
									$_SESSION["last_login"] = $last_login;
									$times_logged_in_sql = "SELECT times_logged_in FROM users WHERE username = '" . $username . "'";
									$times_logged_in = $conn->query($times_logged_in_sql);
									if ($times_logged_in->num_rows > 0) {
										$row = $times_logged_in->fetch_assoc();
										$_SESSION["times_logged_in"] = $row["times_logged_in"];
										$times_logged_in_increment = $row["times_logged_in"] + 1;
										$update_times_logged_in_sql = "UPDATE users SET times_logged_in = '" . $times_logged_in_increment . "'  WHERE username = '" . $username . "'";
										$update_times_logged_in = $conn->query($update_times_logged_in_sql);
									}
									// Initialize session data and
									// redirect user to the welcome page
									$_SESSION["username"] = $username;
									clear_failed_login($username);
									after_successful_login();
									$log_info = "A User attempted to login with username, " . $username . " and was successful";
									log_activity("Login", $log_info);
									echo header("Location: /Comp424Project/welcome.php");
								} else {
									record_failed_login($username);
									$log_info = "A User attempted to login with username, " .$username . " has attempted to login to the site and failed";
									log_activity("Login", $log_info);
									// build feedback string for users
									set_error_string($bad_authentication, "authentication");
								}
							}
							$conn->close();
	                    }
	                } else {
	                    // no such username
	                    $log_info = "A User attempted to login with username, " . $username . ", and failed, username does not exists";
	                    log_activity("Login", $log_info);
	                    set_error_string($bad_authentication, "authentication");
	                }
	            }
			} else {
				$log_info = "A User attempted to give a request from a different domain in Login. IP Address: " . $_SERVER['REMOTE_ADDR'];
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
        // Checks to see if given username already exists
        function username_exists($given_username, $existing_conn) {
            $sql = "SELECT username FROM users WHERE username = '".$given_username."'";
            $result = $existing_conn->query($sql);
            return $result->num_rows > 0;
        }
		// Don't let the user know which piece of data was incorrect
		function set_error_string($error_string, $error) {
			if ($error == "authentication") {
				$error_string = "<div class='alert alert-danger' role='alert'>";
				$error_string .= "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
				$error_string .= "<span class='sr-only'>Error:</span>";
				$error_string .= "<span> Incorrect username or password</span>";
				$error_string .= "</div>";
			} else if ($error == "throttle") {
				$error_string = "<div class='alert alert-danger login-error' role='alert'>";
				$error_string .= "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
				$error_string .= "<span class='sr-only'>Error:</span>";
				$error_string .= "<span>Too many failed logins.</span>";
				$error_string .= "<span>You must wait {$throttle_delay} minutes before you can attempt another login.</span>";
				$error_string .= "</div>";
			}
		}
    ?>

        <div class="well login-well">
            <fieldset>
                <h1 class="text-center">Company Login</h1>
                <?php echo $bad_authentication; ?>
                <form role="form" id="login-form" class="form-horizontal login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                    <?php echo csrf_token_tag(); ?>
                    <div class="form-group" id="username-input">
                        <div class="col-md-12">
                            <label>Username:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
                                <input type="username" name="username" class="form-control" data-parsley-required="true" data-parsley-group="block1" data-parsley-length="[3, 32]" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="password-input">
                        <div class="col-md-12">
                            <label>Password:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
                                <input type="password" name="password" class="form-control" data-parsley-required="true" data-parsley-group="block2" data-parsley-length="[3, 32]" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                        <div class="col-md-12" style="margin-top: 5%;">
                            <button type="submit" class="btn btn-lg btn-block btn-primary validate">Login</button>
                </form>
                            <a class="text-center" style="display:block;" href="createaccount.php">New user? Sign up</a>
                            <a class="text-center" style="display: block;" href="public/forgot_username.php">Forgot your username?</a>
                            <a class="text-center" style="display: block;" href="public/forgot_password.php">Forgot your password?</a>
                        </div>
            </fieldset>
        </div><!-- container -->


        <!-- Bootstrap core JavaScript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <!-- Form validation from Parsley -->
        <script src="js/parsley.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $('#login-form').parsley().subscribe('parsley:form:validate', function (formInstance) {
                    // make sure both username and password are provided
                    if (formInstance.isValid('block1', true) && formInstance.isValid('block2', true)) {
                        return;
                    }
                    // otherwise, stop form submission and mark
                    // required fields with bootstrap
                    formInstance.submitEvent.preventDefault();
                    // if one was supplied, but not the other
                    // remove the error class from the valid input
                    if (!formInstance.isValid('block1', true)) {
                        $('#username-input').addClass("has-error");
                    } else {
                        $('#username-input').removeClass("has-error");
                    }
                    if (!formInstance.isValid('block2', true)) {
                        $('#password-input').addClass("has-error");
                    } else {
                        $('#password-input').removeClass("has-error");
                    }
                });
            });
        </script>
    </body>
</html>
