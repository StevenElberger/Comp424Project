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

            // Check if logout button was pressed
            if (isset($_POST['logout'])) {
                after_successful_logout();
                echo header("Location: /Comp424Project/index.php");
            }
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

                <h1>Welcome, <span id="doctor_id"><?php echo $username; ?></span>!</h1>

                <div class="panel panel-default">
                    <div class="panel-body">
                        You have logged in X times... yadda yadda.
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
                var xmlhttp;
                if (window.XMLHttpRequest) {
                    xmlhttp = new XMLHttpRequest();
                }
                xmlhttp.onreadystatechange = function () {
                    $("#progdiv").fadeIn(400).removeClass('hidden');
                    if (xmlhttp.readyState == 1) {
                        $("#progbar").css("width", "25%");
                    } else if (xmlhttp.readyState == 2) {
                        $("#progbar").css("width", "50%");
                    } else if (xmlhttp.readyState == 3) {
                        $("#progbar").css("width", "75%");
                    }
                    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                        $("#progbar").css("width", "100%");
                        setTimeout(function() {
                            // display data here
                        }, 1000);
                    }
                };
                var username = $("#username").html();
                xmlhttp.open("POST","loginstuff.php",true);
                // HTTP header required for POST
                xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xmlhttp.send("username=" + username);
            }

            $(document).ready(function(){

                // show the welcome screen
                $("#welcome-jumbo").fadeIn(800).removeClass('hidden');
                
            });
        </script>
    </body>
</html>
