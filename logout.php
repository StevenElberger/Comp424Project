<?php
// Grab security functions
require_once("/var/www/html/Comp424Project/private/initialize.php");
session_start();
after_successful_logout();
echo header("Location: /Comp424Project/index.php");
?>
