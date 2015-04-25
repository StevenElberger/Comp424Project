<?php

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		require_once("/var/www/html/Comp424Project/private/initialize.php");
		//$username = $_POST["username"];

		// create connection
	    	$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$times_logged_in_sql = "SELECT * FROM users";
		$times_logged_in = $conn->query($times_logged_in_sql);

		echo $times_logged_in;
	}
?>
