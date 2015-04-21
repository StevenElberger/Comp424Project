<?php

$error_log_file = '/var/www/html/Comp424Project/private/errors.log';
$activity_log_file = '/var/www/html/Comp424Project/private/activity.log';

function log_activity($level="ACTIVITY", $msg="") {
	global $activity_log_file;
	$utimestamp = microtime(true);
	$timestamp = floor(microtime(true));
	$milliseconds = round(($utimestamp - $timestamp) * 1000000);

	$log_msg = $level . ": " . date(preg_replace('`(?<!\\\\)u`', $milliseconds, "Y/m/d H:i:s.u"), $timestamp) . " - " . $msg . PHP_EOL;
	
	file_put_contents($activity_log_file, $log_msg, FILE_APPEND | LOCK_EX); 
}

function log_error($level="ERROR", $msg="") {
	global $error_log_file;
	$utimestamp = microtime(true);
	$timestamp = floor(microtime(true));
	$milliseconds = round(($utimestamp - $timestamp) * 1000000);
	
	$log_msg = $level . ": " . date(preg_replace('`(?<!\\\\)u`', $milliseconds, "Y/m/d H:i:s.u"), $timestamp) . " - " . $msg . PHP_EOL;
	
	file_put_contents($error_log_file, $log_msg, FILE_APPEND | LOCK_EX); 
}

?>
