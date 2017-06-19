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

function storeNotification($type, $deviceId = 0, $timeout = 21600) {
    global $config;
    global $redis;
    switch($type) {
    case "tempTooHighReminder":
        $keyName = "tempTooHighReminder";
        break;
    default:
        $keyName = $type . "_" . $deviceId;
        break;
    }   
    $redis->cmd('SET',$keyName, time(), "EX", $timeout)->set();
}

function notifyAllowed($type, $deviceId = 0) {
    global $config;
    global $redis;
    switch($type) {
    case "tempTooHighReminder":
        $keyName = "tempTooHighReminder";
        break;
    default:
        $keyName = $type . "_" . $deviceId;
        break;
    }   
    if($value = $redis->cmd('GET', $keyName)->get()) {
        return false;
    }   
    else {
        return true;
    }   
}


# basic variables
$BASE_PATH = realpath(dirname(__FILE__));
include($BASE_PATH . "/../config/pushover-config.php");
$lastAvgValueFile = "/tmp/average-temp.txt";

# homegear stuff
include_once($BASE_PATH . "/../includes/homematic.php");
$home = new HomeMaticInstance;
$devices = $home->getAllDevices(true);

# redis stuff
include_once($BASE_PATH . "/../includes/redis/redis.php");
$redis = new redis_cli($config["presence"]["redis_host"], $config["presence"]["redis_port"]);

##########################################################
#  Compare current Avg Temp against last known Avg Temp  #
##########################################################
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

##########################################################
#         Check online status and temperature            #
##########################################################

# get temperatures again, this time only the target temperatures of valves
$temperatures = array();
foreach($devices AS $device) {
	if($device["type"] == "valve") {
		$temperatures[] = $device["targetTemp"];
	}
}

# fetch all online status keys from redis
$onlineStatusKeys = $redis->cmd("KEYS", "online-*")->get();

$someoneHome = false;
if(is_array($onlineStatusKeys)) {
    foreach($onlineStatusKeys AS $key) {
        $value = $redis->cmd("GET", $key)->get();
        if($value == "1") {
            # stop on the first positive match (no need to look further)
            $someoneHome = true;
            break;
        }
    }
}

# if nobody seems to be home, check valve temperatures and notify
if(!$someoneHome) {
    $tempOk = true;
    foreach($temperatures AS $temperature) {
        if($temperature >= 21) {
            # stop on the first positive match (no need to look further)
            $tempOk = false;
            break;
        }
    }
    if(!$tempOk) {
<<<<<<< HEAD
        $lastNoOneHomeMsg = $redis->cmd("GET", "lastNoOneHomeMsg")->get();
        if(empty($lastNoOneHomeMsg) || (time() - $lastNoOneHomeMsg > 1800)) {
            $pushMessage = "wtf, nobody is home but the temperature is set > 20C";
            sendPushMessage($pushMessage,$apiUrl,$apiToken,$apiUser);
            $redis->cmd("SET", "lastNoOneHomeMsg", time())->set();
        }
    }
}
=======
        if(notifyAllowed("tempTooHighReminder")) {
            $pushMessage = "wtf, nobody is home but the temperature is set > 20C?";
            sendPushMessage($pushMessage,$apiUrl,$apiToken,$apiUser);
            storeNotification("tempTooHighReminder");
        }
    }
}

##########################################################
#            Check homegear Service Messages             #
##########################################################

$events = $home->getServiceMessages();

foreach($events AS $event) {
    switch($event["type"]) {
    case "UNREACH":
        if(notifyAllowed($event["type"], $event["id"])) {
            $pushMessage = $event["deviceName"] . ": " . $event["message"];
            sendPushMessage($pushMessage,$apiUrl,$apiToken,$apiUser);
            storeNotification($event["type"], $event["id"]);
        }
        break;
    case "LOWBAT":
        if(notifyAllowed($event["type"], $event["id"])) {
            $pushMessage = $event["deviceName"] . ": " . $event["message"];
            sendPushMessage($pushMessage,$apiUrl,$apiToken,$apiUser);
            storeNotification($event["type"], $event["id"]);
        }
        break;
    }
}

>>>>>>> origin/slim-devel

