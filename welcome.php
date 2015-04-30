<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Company Website</title>

        <!-- Bootstrap core CSS -->
        <link href="newcss/bootstrap.css" type="text/css" rel="stylesheet">

        <!-- Custom CSS for welcome page -->
        <link href="newcss/welcome.css" type="text/css" rel="stylesheet">

        <?php
            // Grab security functions
            require_once("/var/www/html/Comp424Project/private/initialize.php");
            session_start();
            // Make sure the session is still active
            validate_user_before_displaying();
	    $username = $_SESSION["username"];

	    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

	    $last_login_sql = "UPDATE users SET last_login = '" . time() . "' WHERE username = '" . $username . "'";
	    $last_login = $conn->query($last_login_sql);
	    $time = date('r', $_SESSION["last_login"]);

	    // get times logged in
	    $times_logged_in = $_SESSION["times_logged_in"];
	?>
	</head>
    <body>
        <!-- begin navigation bar -->
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/Comp424Project/welcome.php">Company Website</a>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-2">
                    <ul class="nav navbar-nav">
                        <li class="active"><a id="home-link" href="/Comp424Project/welcome.php">Home<span class="sr-only">(current)</span></a></li>

                        <li><a href="/Comp424Project/account_settings.php">Settings</a></li>
                        <li><a href="/Comp424Project/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- end navigation bar -->

        <div class="progress progress-striped active hidden" id="progdiv" style="margin-top: -20px;">
            <div class="progress-bar" id="progbar" style="width: 0%"></div>
        </div>

        <div class="jumbotron welcome-jumbo hidden" id="welcome-jumbo">
            <!-- Contains the welcome information -->
            <div class="container" id="welcome-container">

                <h1>Welcome, <span id="username"><?php echo $username; ?></span>!</h1>

                <div class="panel panel-default">
                    <div class="panel-body">
                        Your last login was on <?php echo $time; ?>. You have logged in previously <span id="result"><?php echo $times_logged_in; ?></span> times.
			Download the confidential file <a href="/Comp424Project/private/company_confidential_file.txt" download>here</a>.
                    </div>
                </div>

            </div>
            <!-- End of welcome -->
        </div>

        <!-- Bootstrap core JavaScript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <!-- Form validation from Parsley -->
        <script src="js/parsley.min.js"></script>
        <script type="text/javascript">
            // used for pulling info from database
            function testAJAX() {
		$.ajax({
			url: "update_login.php",
			type: "POST",
			success: function (data) {
				alert(data);
			},
			complete: function() {
				alert("Complete");
			},
			error: function (e) {
				console.log("Error:", e);
			}
		});
	    }

            $(document).ready(function(){

                // show the welcome screen
                $("#welcome-jumbo").fadeIn(800).removeClass('hidden');

		//testAJAX();
                
            });
        </script>
    </body>
</html>
