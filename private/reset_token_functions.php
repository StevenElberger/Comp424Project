<?php

	require_once("/var/www/html/Comp424Project/private/PHPMailer/class.phpmailer.php");
	require_once("/var/www/html/Comp424Project/private/PHPMailer/class.smtp.php");
	require_once("/var/www/html/Comp424Project/private/definitions.php");
// Reset token functions

// Note: This implementation is based on a Tutorial
// that one of the developers used to learn about web security.
// Tutorial Name: Creating Secure PHP Websites
// Author: Kevin Skoglund
// Website of Tutorial: Lynda.com

// Function that generates a string that can be used as a reset token. 
// The function generates this unique token by generating a random number,
// generating a unique id from that number and then hashing it.
function reset_token() {
	return md5(uniqid(rand()));
}

// Looks up a user and sets their reset_token to
// the given value. Can be used both to create and
// to delete the token.
function set_user_reset_token($username, $token_value) {
	
	// Attempt to connect to the database
   $db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
   if (mysqli_connect_errno()) {
      die("Database connection failed: " . mysqli_connect_error() .
         " (" . mysqli_connect_errno() . ")");
      $log_info = "Connection to DB Failed for Reset Token";
      log_error("DB Connection Error", $log_info);
   }
   
   // SQL statement to retrieve rows that have the username column equal to the given username      
    $sql_statement = "SELECT * FROM users WHERE username='".$username."'";

   // execute query
   $users = $db->query($sql_statement);

   // check if anything was returned by database
   if ($users->num_rows > 0) {

      // fetch the first row of the results of the query
      $row = $users->fetch_assoc();

      // Set reset token for the found user
      $sql_statement = "UPDATE users SET reset_token='".$token_value."' WHERE username='".$username."'";

      // execute query
      $db->query($sql_statement);
      $db->close();
      return true;
	} else {
		return false;
	}
}

// Function that generates a new reset token,
// and sets the reset token for the given user.
function create_reset_token($username) {
	$token = reset_token();
	return set_user_reset_token($username, $token);
}

// Function that removes the token for the given
// user by setting the value to null.
function delete_reset_token($username) {
	$token = null;
	return set_user_reset_token($username, $token);
}

// Returns the user record for a given reset token.
// If token is not found, returns null.
function find_user_with_token($token) {
	if(empty($token)) {
		// We were expecting a token and didn't get one.
		return null;
	} else {
		
	   // Attempt to connect to the database
      $db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
      if (mysqli_connect_errno()) {
         die("Database connection failed: " . mysqli_connect_error() .
           " (" . mysqli_connect_errno() . ")");
         $log_info = "Connection to DB Failed for Reset Token";
         log_error("DB Connection Error", $log_info);
      }
   
      // SQL statement to retrieve rows that have the username column equal to the given username      
      $sql_statement = "SELECT * FROM users WHERE reset_token='".$token."'";

      // execute query
      $users = $db->query($sql_statement);
      
      // check if anything was returned by database
      if ($users->num_rows > 0) {

         // fetch the first row of the results of the query
         $row = $users->fetch_assoc();
		
		   // return the username associated with the reset_token if found
		   // otherwise return null
		   return $row["username"];
	   } else {
		   return null;
	   }
	}
}

// A function to email the reset token to the email
// address on file for this user.
function email_reset_token($username) {
	
	// Attempt to connect to the database
   $db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
   if (mysqli_connect_errno()) {
      die("Database connection failed: " . mysqli_connect_error() .
         " (" . mysqli_connect_errno() . ")");
      $log_info = "Connection to DB Failed for Reset Token";
      log_error("DB Connection Error", $log_info);
   }
   
   // SQL statement to retrieve rows that have the username column equal to the given username      
    $sql_statement = "SELECT * FROM users WHERE username='".$username."'";

   // execute query
   $users = $db->query($sql_statement);

   // check if anything was returned by database
   if ($users->num_rows > 0) {

      // fetch the first row of the results of the query
      $row = $users->fetch_assoc();
      
      $ip_address = "https://108.77.76.162";
      
      $to_name = $row["username"];
      $to = $row["email"];
      $subject = "Comp 424 Security Site Reset Password";
      $body = file_get_contents('forgot_password_email_template.php');
      $body = str_replace("[[token]]", $row["reset_token"], $body);
      //$body = str_replace("[[ip_address]]", "108.77.76.162", $body);
      $body = str_replace("[[ip_address]]", $ip_address, $body);
      
      $from_name = "Comp 424 Security Site";
      $from = EMAIL_USERNAME;
      
      // Set mailer options and content
      $mail = new PHPMailer();
      $mail->Port = 465;
      $mail->IsSMTP();
      $mail->Host = "ssl://smtp.mail.yahoo.com";
      $mail->SMTPSecure = "tls";
      $mail->SMTPAuth = true;
      $mail->Username = $from;
      $mail->Password = EMAIL_PASSWORD;
      $mail->From = $from;
      $mail->FromName = $from_name;
      $mail->AddAddress($to, $to_name);
      $mail->Subject = $subject;
      $mail->AltBody = "To view this message, please use an HTML compatible email viewer";
      $mail->IsHTML(true);
      $mail->MsgHTML($body);
      $mail->WordWrap = 70;
      
      // Uncomment For Testing
      if ($mail->Send()) {
			//echo "Success";
		} else {
			$log_info = "Email failed to be sent for Reset Password Request";
         log_error("Emailer Failed", $log_info);
		}

		// close database connection
      $db->close();
	} 
}

// A function to email the username to the email
// address on file for this user.
function email_username_token($email) {
	
	// Attempt to connect to the database
   $db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
   if (mysqli_connect_errno()) {
      die("Database connection failed: " . mysqli_connect_error() .
         " (" . mysqli_connect_errno() . ")");
      $log_info = "Connection to DB Failed for Reset Token";
      log_error("DB Connection Error", $log_info);
   }
   
   // SQL statement to retrieve rows that have the username column equal to the given username      
    $sql_statement = "SELECT * FROM users WHERE email='".$email."'";

   // execute query
   $users = $db->query($sql_statement);

   // check if anything was returned by database
   if ($users->num_rows > 0) {

      // fetch the first row of the results of the query
      $row = $users->fetch_assoc();
      
      $ip_address = "https://108.77.76.162";
      
      $to_name = $row["username"];
      $to = $row["email"];
      $subject = "Comp 424 Security Site Retrieve Username";
      $body = file_get_contents('forgot_username_email_template.php');
      $body = str_replace("[[username]]", $row["username"], $body);
      $body = str_replace("[[ip_address]]", $ip_address, $body);
      $from_name = "Comp 424 Security Site";
      $from = EMAIL_USERNAME;
      
      // Set mailer options and content
      $mail = new PHPMailer();
      $mail->Port = 465;
      $mail->IsSMTP();
      $mail->Host = "ssl://smtp.mail.yahoo.com";
      $mail->SMTPSecure = "tls";
      $mail->SMTPAuth = true;
      $mail->Username = $from;
      $mail->Password = EMAIL_PASSWORD;
      $mail->From = $from;
      $mail->FromName = $from_name;
      $mail->AddAddress($to, $to_name);
      $mail->Subject = $subject;
      $mail->AltBody = "To view this message, please use an HTML compatible email viewer";
      $mail->IsHTML(true);
      $mail->MsgHTML($body);
      $mail->WordWrap = 70;
      
      // Uncomment For Testing
      if ($mail->Send()) {
			//echo "Success";
		} else {
			
			$log_info = "Email failed to be sent for Retrieval of Username";
         log_error("Emailer Failed", $log_info);
		}

		// close database connection
      $db->close();
	} 
}

// A function to email a link with a token, that will
// validate the users account.
function email_validation_token($username) {
	
	// Attempt to connect to the database
   $db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
   if (mysqli_connect_errno()) {
      die("Database connection failed: " . mysqli_connect_error() .
         " (" . mysqli_connect_errno() . ")");
      $log_info = "Connection to DB Failed for Reset Token";
      log_error("DB Connection Error", $log_info);
   }
   
   // SQL statement to retrieve rows that have the username column equal to the given username      
    $sql_statement = "SELECT * FROM users WHERE username='".$username."'";

   // execute query
   $users = $db->query($sql_statement);

   // check if anything was returned by database
   if ($users->num_rows > 0) {

      // fetch the first row of the results of the query
      $row = $users->fetch_assoc();
      
      $ip_address = "https://108.77.76.162";
      
      $to_name = $row["username"];
      $to = $row["email"];
      $subject = "Comp 424 Security Site Validation Email";
      $body = file_get_contents('public/validation_email_template.php');
      $body = str_replace("[[token]]", $row["reset_token"], $body);
      //$body = str_replace("[[ip_address]]", "108.77.79.66", $body);
      $body = str_replace("[[ip_address]]", $ip_address, $body);
      
      $from_name = "Comp 424 Security Site";
      $from = EMAIL_USERNAME;
      
      // Set mailer options and content
      $mail = new PHPMailer();
      $mail->Port = 465;
      $mail->IsSMTP();
      $mail->Host = "ssl://smtp.mail.yahoo.com";
      $mail->SMTPSecure = "tls";
      $mail->SMTPAuth = true;
      $mail->Username = $from;
      $mail->Password = EMAIL_PASSWORD;
      $mail->From = $from;
      $mail->FromName = $from_name;
      $mail->AddAddress($to, $to_name);
      $mail->Subject = $subject;
      $mail->AltBody = "To view this message, please use an HTML compatible email viewer";
      $mail->IsHTML(true);
      $mail->MsgHTML($body);
      $mail->WordWrap = 70;
      
      // Uncomment For Testing
      if ($mail->Send()) {
			//echo "Success";
		} else {
			$log_info = "Email failed to be sent for Validation of Account";
         log_error("Emailer Failed", $log_info);
		}

		// close database connection
      $db->close();
	} 
}



?>
