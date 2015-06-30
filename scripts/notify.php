<?php

function sendPushMessage($pushMessage,$apiUrl,$apiToken,$apiUser) {
	curl_setopt_array($ch = curl_init(), array(
		CURLOPT_URL => $apiUrl,
		CURLOPT_POSTFIELDS => array(
			"token" => $apiToken,
			"user" => $apiUser,
			"message" => $pushMessage,
		),
		CURLOPT_SAFE_UPLOAD => true,
		CURLOPT_RETURNTRANSFER => true,
	));
	curl_exec($ch);
	curl_close($ch);
}

# basic variables
$BASE_PATH = realpath(dirname(__FILE__));
include($BASE_PATH . "/../config/pushover-config.php");
$lastAvgValueFile = "/tmp/average-temp.txt";

# homegear stuff
include_once($BASE_PATH . "/../includes/homematic.php");
$home = new HomeMaticInstance;
$devices = $home->getAllDevices(true);

$temperatures = array();
foreach($devices AS $device) {
	if(!empty($device["tempSensor"])) {
		$temperatures[] = $device["tempSensor"];
	}
}

if(count($temperatures) > 0) {
	$avgTemp = round(array_sum($temperatures) / count($temperatures),2);
}
else {
	$avgTemp = 0.0;
}

# read last value
if(file_exists($lastAvgValueFile)) {
	$fp = fopen($lastAvgValueFile,"r");
	if($fp) {
		$line = fgets($fp,1024);
		$lastAvgValue = round(floatval($line),2);
		if(!is_float($lastAvgValue)) {
			$lastAvgValue = 0.0;
		}
		fclose($fp);
	}
}
else {
	$lastAvgValue = 0.0;
}

# store new value
$fp = fopen($lastAvgValueFile,"w");
if($fp) {
	fputs($fp,$avgTemp);
	fclose($fp);
}

# generate Notifications
if($lastAvgValue > $avgTemp) {
	if($lastAvgValue - $avgTemp > 10) {
		$pushMessage = "AVG temp dropped by more than 10°C! (" . $lastAvgValue . " -> " . $avgTemp . ")";
		sendPushMessage($pushMessage,$apiUrl,$apiToken,$apiUser);
	}
}
else {
	if($avgTemp - $lastAvgValue > 10) {
		$pushMessage = "AVG temp raised by more than 10°C! (" . $lastAvgValue . " -> " . $avgTemp . ")";
		sendPushMessage($pushMessage,$apiUrl,$apiToken,$apiUser);
	}
}
if($avgTemp >= 28 && $lastAvgValue < 28) {
	$pushMessage = "AVG temp now OVER 28°C! (" . $avgTemp . ")";
	sendPushMessage($pushMessage,$apiUrl,$apiToken,$apiUser);
}


?>
