<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Create an Account</title>

        <!-- Bootstrap core CSS -->
        <link href="newcss/bootstrap.css" type="text/css" rel="stylesheet">

        <!-- Custom CSS for Login -->
        <link href="newcss/login.css" type="text/css" rel="stylesheet">

	<!-- Re-CAPTCHA -->
	<script src='https://www.google.com/recaptcha/api.js'></script>

    </head>

    <body>
        <?php
            // Grab security functions
            require_once("/var/www/html/Comp424Project/private/initialize.php");
				session_start();
            // Flag for first load
            $firstLoad = true;
            // Error placeholders
            $firstNameError = $lastNameError = $usernameError = $mismatchError = "";
            $passwordError = $confirmError = $emailError =  $companyError = $phoneError = $requiredFields = "";
            $securityQuestionError = $securityAnswerError = $securityQuestionError2 = $securityAnswerError2 = $birthdayError = "";
            // Placeholders for variables from form
            $username = $password = $confirm = $first_name = $last_name = $email = $company = $phone = "";
            $security_question = $security_answer = $security_question_2 = $security_answer_2 = $birthday = "";
			   $captcha = $captcha_error = "";

            // in case form was submitted and the username already exists
            if (isset($usernameError)) {
                $usernameError = "";
            }

           // Only process POST requests, not GET
           if (request_is_post()) {
           if(request_is_same_domain()) {
			  if(!csrf_token_is_valid() || !csrf_token_is_recent()) {
			  	$message = "Sorry, request was not valid.";
			  	$log_info = "A User attempted to submit an invalid form in Create Account. IP Address: " . $_SERVER['REMOTE_ADDR'];
			   log_error("Form Forgery", $log_info);
			  } else {
			    // CSRF tests passed--form was created by us recently.
                $firstLoad = false;
                // Check the required fields
                if (empty($_POST["first_name"])) {
                    $firstNameError = "*";
                } else {
                    $first_name = test_input($_POST["first_name"]);
                }

                if (empty($_POST["last_name"])) {
                    $lastNameError = "*";
                } else {
                    $last_name = test_input($_POST["last_name"]);
                }

                if (empty($_POST["username"])) {
                    $usernameError = "*";
                } else {
                    $username = test_input($_POST["username"]);
                }

                if (empty($_POST["password"])) {
                    $passwordError = "*";
                } else {
                    $password = test_input($_POST["password"]);
                }

                if (empty($_POST["confirm"])) {
                    $confirmError = "*";
                } else {
                    $confirm = test_input($_POST["confirm"]);
                }

                if (empty($_POST["email"])) {
                    $emailError = "*";
                } else {
                    $email = test_input($_POST["email"]);
                }
                
                if (empty($_POST["security_question"])) {
                    $securityQuestionError = "*";
                } else {
                    $security_question = test_input($_POST["security_question"]);
                }
                
                if (empty($_POST["security_answer"])) {
                    $securityAnswerError = "*";
                } else {
                    $security_answer = test_input($_POST["security_answer"]);
                }
                
                if (empty($_POST["security_question_2"])) {
                    $securityQuestionError2 = "*";
                } else {
                    $security_question_2 = test_input($_POST["security_question_2"]);
                }
                
                if (empty($_POST["security_answer_2"])) {
                    $securityAnswerError2 = "*";
                } else {
                    $security_answer_2 = test_input($_POST["security_answer_2"]);
                }

                if (empty($_POST["company"])) {
                    $companyError = "*";
                } else {
                    $company = test_input($_POST["company"]);
                }

                if (empty($_POST["phone"])) {
                    $phoneError = "*";
                } else {
                    $phone = test_input($_POST["phone"]);
                }
                if (empty($_POST["birthday"])) {
                    $birthdayError = "*";
                } else {
                    $birthday = test_input($_POST["birthday"]);
                }

                if ($password !== $confirm) {
                    $mismatchError = "Passwords do not match";
                }

				if (empty($_POST["g-recaptcha-response"])) {
					// user didn't complete / pass the captcha!
					$captcha_error = "You must be a robot!";
				} else {
					$captcha = $_POST["g-recaptcha-response"];
				}

            // As long as all variables were initialized, the data is good to go
            if (($first_name !== "") && ($last_name !== "") && ($username !== "") && ($company !== "") && ($email !== "")
                && ($securityAnswer !== "") &&($phone !== "") && ($password !== "") && ($confirm !== "") && 
                ($securityQuestion !== "") && ($mismatchError === "") && ($birthday !== "") && ($securityQuestion2 !== "")
                && ($securityAnswer2 !== "") && ($captcha_error === "")) {
					 
				// validate user's captcha - send POST to Google
				$url = 'https://www.google.com/recaptcha/api/siteverify';
				$data = array('secret' => '6LcqUAUTAAAAAEB6--fszvEOo43k_h9cIDe8kCXe', 'response' => $captcha);

				$options = array(
						'http' => array(
								'header' => "Content-type: application/x-www-form-urlencoded\r\n",
								'method' => 'POST',
								'content' => http_build_query($data),
						),
				);
				$context = stream_context_create($options);
				$result = file_get_contents($url, false, $context);
				// response is a JSON object - check for success
				$object = json_decode($result);
				// user failed captcha
				if ($object->success != 1) {
					echo "Please complete the CAPTCHA.";
					var_dump($object);
					return;
				} else {
	                // Store the hash, not the pass
		            $hash_pass = password_hash($password, PASSWORD_BCRYPT);

		            // Create connection
		            $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

		            // Check connection
		            if ($conn->connect_error) {
		                die("Connection failed: " . $conn->connect_error);
		                $log_info = "Connection to DB Failed in Create Account";
                      log_error("DB Connection Error", $log_info);
		            }

		            // Adds a new user account with form data into the physician table of the database
		            // -- To do: form checking (e.g., username already exists, security, etc.)
		            $sql = "INSERT INTO users (username, password, first_name, last_name, security_question, security_answer, security_question_2, security_answer_2,
		             company, phone, email, birthday, times_logged_in, last_login, valid) VALUES ('".$username."', '".$hash_pass."', '".$first_name."', '".$last_name.
		             "', '".$security_question."', '".$security_answer."', '".$security_question_2."', '".$security_answer_2."', '".$company."', '".$phone."', '".$email."', '".$birthday."', 0, 0, 0)";

		            if (username_exists($username, $conn)) {
		                $usernameError = "<div class='alert alert-danger' id='username-exists' role='alert'>";
		                $usernameError .= "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
		                $usernameError .= "<span class='sr-only'>Error:</span>";
		                $usernameError .= "<span> Username already exists</span>";
		                $usernameError .= "</div>";
		            } else if ($conn->query($sql) === TRUE) {
		                // Redirect upon successful account creation
							 create_reset_token($username);
							 email_validation_token($username);
		                echo header("Location: /Comp424Project/public/email_validation_notification.php");
		            } else {
		                echo "Error: " . $sql . "<br />" . $conn->error;
		            }

		            // Peace out
		            $conn->close();
				}
				
            } else {
                if (!$firstLoad) {
                    $requiredFields = "The following fields are required: ";
                }
            }
			}
		} else {
			// Request Forgery, log acivity
			$log_info = "A User attempted to give a request from a different domain in Create Account. IP Address: " . $_SERVER['REMOTE_ADDR'];
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
        ?>

        <div class="well login-well">
            <fieldset>
                <h2 class="text-center">Create an Account</h2>
                <div class="alert alert-danger hidden login-error" id="error-alert" role="alert">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                    <span class="sr-only">Error:</span>
                    <span id="list-errors">The following fields have errors:</span>
                </div>
                <?php echo $usernameError; ?>
                <form role="form" id="account-form" class="form-horizontal login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
		<?php echo csrf_token_tag(); ?>
                    <div class="form-group" id="first-name-input">
                        <div class="col-md-12">
                            <label>First Name:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon">FN</span></span>
                                <input type="text" name="first_name" id="first-name" class="form-control" data-toggle="tooltip" data-placement="right" title="Wenis" value="<?php echo $first_name; ?>" data-parsley-required="true" data-parsley-length="[3, 32]" data-parsley-group="block1" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="last-name-input">
                        <div class="col-md-12">
                            <label>Last Name:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon">LN</span></span>
                                <input type="text" name="last_name" id="last-name" class="form-control" value="<?php echo $last_name; ?>" data-parsley-required="true" data-parsley-group="block2" data-parsley-length="[3, 32]" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="username-input">
                        <div class="col-md-12">
                            <label>Username:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
                                <input type="text" name="username" id="username" class="form-control" value="<?php echo $username; ?>" data-container="body" data-toggle="popover" data-trigger="focus" data-content="8 - 16 alphanumeric characters" data-parsley-required="true" data-parsley-type="alphanum" data-parsley-length="[8, 16]" data-parsley-group="block3" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="password-input">
                        <div class="col-md-12">
                            <label>Password:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
                                <input type="password" id="password" name="password" class="form-control" data-container="body" data-toggle="popover" data-trigger="focus" data-content="8 - 16 characters long" data-parsley-required="true" data-parsley-length="[8, 16]" data-parsley-group="block4" data-parsley-ui-enabled="false">
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
                                <input type="password" id="confirm" name="confirm" class="form-control" data-container="body" data-toggle="popover" data-trigger="focus" data-content="must match password" data-parsley-required="true" data-parsley-equalto="#password" data-parsley-length="[8, 16]" data-parsley-group="block5" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="email-input">
                        <div class="col-md-12">
                            <label>Email:</label><label class="control-label" id="email-control"></label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>
                                <input type="text" id="email" name="email" class="form-control" value="<?php echo $email; ?>" data-container="body" data-toggle="popover" data-trigger="focus" data-content="must be valid email address" data-parsley-required="true" data-parsley-type="email" data-parsley-length="[3, 32]" data-parsley-group="block6" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="security-question-input">
                        <div class="col-md-12">
                            <label>Security Question:</label><label class="control-label" id="security-question-control"></label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon">Q1</span></span>
                                <input type="text" id="security_question" name="security_question" class="form-control" value="<?php echo $security_question; ?>" data-container="body" data-toggle="popover" data-trigger="focus" data-content="Enter a Question that you can answer" data-parsley-required="true" data-parsley-group="block7" data-parsley-length="[1, 32]" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="security-answer-input">
                        <div class="col-md-12">
                            <label>Security Question Answer:</label><label class="control-label" id="security-answer-control"></label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon">A1</span></span>
                                <input type="text" id="security_answer" name="security_answer" class="form-control" value="<?php echo $security_answer; ?>" data-container="body" data-toggle="popover" data-trigger="focus" data-content="Answer to Security Question" data-parsley-required="true" data-parsley-group="block8" data-parsley-length="[1, 32]" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="security-question-2-input">
                        <div class="col-md-12">
                            <label>Security Question 2:</label><label class="control-label" id="security-question-2-control"></label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon">Q2</span></span>
                                <input type="text" id="security_question_2" name="security_question_2" class="form-control" value="<?php echo $security_question_2; ?>" data-container="body" data-toggle="popover" data-trigger="focus" data-content="Enter a Question that you can answer" data-parsley-required="true" data-parsley-group="block9" data-parsley-length="[1, 32]" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="security-answer-2-input">
                        <div class="col-md-12">
                            <label>Security Question Answer 2:</label><label class="control-label" id="security-answer-2-control"></label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon">A2</span></span>
                                <input type="text" id="security_answer_2" name="security_answer_2" class="form-control" value="<?php echo $security_answer_2; ?>" data-container="body" data-toggle="popover" data-trigger="focus" data-content="Answer to Security Question" data-parsley-required="true" data-parsley-group="block10" data-parsley-length="[1, 32]" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="company-input">
                        <div class="col-md-12">
                            <label>Company:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-globe"></span></span>
                                <input type="text" id="company" name="company" class="form-control" value="<?php echo $company; ?>" data-parsley-required="true" data-parsley-group="block11" data-parsley-length="[3, 32]" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="phone-input">
                        <div class="col-md-12">
                            <label>Phone:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-phone"></span></span>
                                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $phone; ?>" data-container="body" data-toggle="popover" data-trigger="focus" data-content="7 - 10 digits" data-parsley-required="true" data-parsley-type="digits" data-parsley-length="[7, 10]" data-parsley-group="block12" data-parsley-ui-enabled="false">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="birthday-input">
                        <div class="col-md-12">
                            <label>Birthday:</label>
                            <div class="input-group">
										  <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
										  <input type="date" id="birthday" name = "birthday" style="margin-top: 15px; margin-left: 15px;" data-parsley-required="true" data-container="body" data-toggle="popover" data-trigger="focus" data-content="Please Enter a Birthday" data-parsley-group="block13" data-parsley-ui-enabled="false">
                            </div>
							<div class="g-recaptcha" data-sitekey="6LcqUAUTAAAAADohUaXzn21dr-RA-cLz6HODEVGX"></div>
                            <button type="submit" style="margin-top: 5%;" class="btn btn-lg btn-block btn-primary validate">Create Account</button>
                        </div>
                    </div>
                </form>
            </fieldset>
        </div><!-- /.container -->

        <!-- Bootstrap core JavaScript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <!-- Form validation from Parsley -->
        <script src="js/parsley.min.js"></script>
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

                $('#account-form').parsley().subscribe('parsley:form:validate', function (formInstance) {

                    var firstName = formInstance.isValid('block1', true);
                    var lastName = formInstance.isValid('block2', true);
                    var username = formInstance.isValid('block3', true);
                    var password = formInstance.isValid('block4', true);
                    var confirm = formInstance.isValid('block5', true);
                    var email = formInstance.isValid('block6', true);
                    var securityQuestion = formInstance.isValid('block7', true);
                    var securityAnswer = formInstance.isValid('block8', true);
                    var securityQuestion2 = formInstance.isValid('block9', true);
                    var securityAnswer2 = formInstance.isValid('block10', true);
                    var company = formInstance.isValid('block11', true);
                    var phone = formInstance.isValid('block12', true);
                    var birthday = formInstance.isValid('block13', true);

                    if (firstName && lastName && username && password && confirm && email && securityQuestion && 
                    securityAnswer && securityQuestion2 && securityAnswer2 && company && phone && birthday) {
                        return;
                    }

                    // otherwise, stop form submission and mark
                    // required fields with bootstrap
                    formInstance.submitEvent.preventDefault();

                    // show error alert
                    $('#error-alert').removeClass("hidden");
                    // hide username already exists error
                    $('#username-exists').addClass("hidden");

                    /*
                        Input validation rules:
                        - All forms required
                        - Username must be alphanumeric characters, 8 to 16 characters long
                        - Password must be 8 to 16 characters long
                        - Confirm password must match password, 8 to 16 characters long
                        - E-mail must be a valid e-mail address
                        - Phone number must be digits only, length 7 to 10
                     */

                    if (!firstName) {
                        $('#first-name-input').addClass("has-error");
                    } else {
                        $('#first-name-input').removeClass("has-error");
                    }

                    if (!lastName) {
                        $('#last-name-input').addClass("has-error");
                    } else {
                        $('#last-name-input').removeClass("has-error");
                    }

                    if (!username) {
                        $('#username-input').addClass("has-error");
                        $('#username').popover('show');
                    } else {
                        $('#username-input').removeClass("has-error");
                    }

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
                    
                    if (!securityQuestion) {
                        $('#security-question-input').addClass("has-error");
                        $('#security_question').popover('show');
                    } else {
                        $('#security-question-input').removeClass("has-error");
                    }
                    
                    if (!securityAnswer) {
                        $('#security-answer-input').addClass("has-error");
                        $('#security_answer').popover('show');
                    } else {
                        $('#security-answer-input').removeClass("has-error");
                    }
                    
                    if (!securityQuestion2) {
                        $('#security-question-2-input').addClass("has-error");
                        $('#security_question_2').popover('show');
                    } else {
                        $('#security-question-2-input').removeClass("has-error");
                    }
                    
                    if (!securityAnswer2) {
                        $('#security-answer-2-input').addClass("has-error");
                        $('#security_answer_2').popover('show');
                    } else {
                        $('#security-answer-2-input').removeClass("has-error");
                    }

                    if (!email) {
                        $('#email-input').addClass("has-error");
                        $('#email').popover('show');
                    } else {
                        $('#email-input').removeClass("has-error");
                    }

                    if (!company) {
                        $('#company-input').addClass("has-error");
                    } else {
                        $('#company-input').removeClass("has-error");
                    }

                    if (!phone) {
                        $('#phone-input').addClass("has-error");
                        $('#phone').popover('show');
                    } else {
                        $('#phone-input').removeClass("has-error");
                    }
                    
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
