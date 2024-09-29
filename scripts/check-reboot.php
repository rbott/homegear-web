<?php

# basic variables
$BASE_PATH = realpath(dirname(__FILE__));

# homegear stuff
include_once($BASE_PATH . "/../includes/homematic.php");
$home = new HomeMaticInstance;
$messages = $home->getServiceMessages();

$offline = 0;
foreach($messages as $message) {
	if($message["message"] == "Device offline.") {
		$offline++;
	}
}

if($offline > 5) {
	$uptime_in_seconds = strtok( exec( "cat /proc/uptime" ), "." );
	if($uptime_in_secods < 600) {
		echo("Detected > 5 offline devices... rebooting system.\n");
		exec("/sbin/reboot");
	}
	else {
		echo("Not rebooting system - uptime is below 600 seconds (" . $uptime_in_seconds . ")\n");
	}
}
