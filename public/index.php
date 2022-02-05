<?php

# homegear-web
# Copyright (C) 2015  Rudolph Bott
#

require_once('../includes/Twig/Autoloader.php');
require_once('../includes/Slim/Slim.php');

require_once('../config/config.inc.php');
require_once('../includes/homematic.php');

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
	'templates.path' => '../tpl'
));

$app->view(new \Slim\Views\Twig());
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

$app->get('/', function() use ($app) {
	$app->render('index.html');
});

$app->get('/metrics', function() use ($app) {
	$app->response->headers->set("Content-Type", "text/plain; version=0.0.4");
	$hm = new homeMaticInstance();
	$stats = $hm->getPrometheusStats();
	echo $stats;
});

$app->get('/transportation', function() use ($app) {
	$app->response->headers->set("Content-Type", "application/json");
	$stops = [ "20020064", "20020195", "20020105" ];
	$interesting_lines = [ 
		"843" => ["R"],
		"844" => ["R"],
		"848" => ["H","R"],
		"709" => ["R"],
		"S11" => ["H","R"]
	];
	$types = [
		0 => "Zug",
		1 => "S-Bahn",
		2 => "U-Bahn",
		3 => "Stadtbahn",
		4 => "Straßenbahn",
		5 => "Bus",
		6 => "Bus",
		7 => "Bus",
		13 => "Regionalbahn",
		14 => "Zug",
		15 => "Zug",
		16 => "Zug",
		17 => "Bus"
	];

	$return_data = [];
	foreach($stops as $stop) {
		$params = [
			"sessionID=0",
			"requestID=0",
			"language=DE",
			"type_dm=stopID",
			"name_dm=" . $stop,
			"useProxFootSearch=0",
			"mode=direct",
			"limit=15",
			"useRealtime=1"
		];
		$xmlstr=file_get_contents('https://openservice-test.vrr.de/static02/XML_DM_REQUEST?' . join('&', $params));
		libxml_use_internal_errors(true);
		$data = new SimpleXMLElement($xmlstr);
		if ($data === false) {
			echo "failed";
			foreach(libxml_get_errors() as $error) {
				echo $error->message;
			}
		}
		else {
			$departures = $data->xpath("//itdDeparture");
			foreach($departures as $dep) {
				$line = (string)$dep->itdServingLine->attributes()->number;
				if(array_key_exists($line, $interesting_lines)) {
					if(isset($dep->itdServingLine->motDivaParams)) {
						$direction = (string)$dep->itdServingLine->motDivaParams->attributes()->direction;
						if(!in_array($direction, $interesting_lines[$line])) {
							continue;
						}
					}

					$has_realtime = (int)$dep->itdServingLine->attributes()->realtime;
					if($has_realtime == 1 && isset($dep->itdRTDateTime)) {
						$has_realtime = true;
						$time_base = $dep->itdRTDateTime;
					} else {
						$has_realtime = false;
						$time_base = $dep->itdDateTime;
					}
					if($has_realtime) {
						$delay = (int)$dep->itdServingLine->itdNoTrain->attributes()->delay;
						if($delay == -9999) {
							continue;
						}
					}


					$day = (int)$time_base->itdDate->attributes()->day;
					$month = (int)$time_base->itdDate->attributes()->month;
					$year = (int)$time_base->itdDate->attributes()->year;
					$hour = (int)$time_base->itdTime->attributes()->hour;
					$minute = (int)$time_base->itdTime->attributes()->minute;
					$timeString = sprintf("%d/%d/%d %02d:%02d:00", $month, $day, $year, $hour, $minute);
					$time = strtotime($timeString);
					if($time < (time() + 150) || $time > (time() + 3600)) {
						continue;
					}
	
					$direction = (string)$dep->itdServingLine->attributes()->direction;
					$type = (int)$dep->itdServingLine->attributes()->motType;
	
					$return_data[] = [
						"time" => $time,
						"timeString" => $timeString,
						"line" => $line,
						"direction" => $direction,
						"type" => $types[$type],
						"realtime" => $has_realtime
					];
				}
			}
		}
	}
	function cmp($a, $b) {
		return $a["time"] > $b["time"];
	}

	usort($return_data, "cmp");
	echo json_encode([ "elements" => $return_data ]);
});

$app->get('/heaters', function() use ($app) {
	$app->response->headers->set("Content-Type", "application/json");
	$hm = new homeMaticInstance();
	$devices = $hm->getAllValves();
	$return_data = [];
	foreach($devices as $device) {
		$return_data[] = [
			"name" => str_replace("Heizung-", "", $device->getName()),
			"temperature" => (float)$device->getTempSensor(),
			"valve" => (int)$device->getValveState(),
			"target" => (float)$device->getTargetTemp()
		];
	}

	function cmp($a, $b) {
		return strcmp($a["name"], $b["name"]);
	}
	usort($return_data, "cmp");
	echo json_encode([ "elements" => $return_data]);
});


$app->get('/env-sensors', function() use ($app) {
	$app->response->headers->set("Content-Type", "application/json");
	$hm = new homeMaticInstance();
	$devices = $hm->getAllEnvSensors();
	$return_data = [];
	foreach($devices as $device) {
		$return_data[] = [
			"name" => str_replace("Temp-", "", $device->getName()),
			"temperature" => (float)$device->getTempSensor(),
			"humidity" => (int)$device->getHumidSensor()
		];
	}
	function cmp($a, $b) {
		return strcmp($a["name"], $b["name"]);
	}
	usort($return_data, "cmp");
	echo json_encode([ "elements" => $return_data]);
});

$app->get('/power-sensors', function() use ($app) {
	$app->response->headers->set("Content-Type", "application/json");
	$hm = new homeMaticInstance();
	$devices = $hm->getAllPwrSensors();
	$return_data = [];
	foreach($devices as $device) {
		$return_data[] = [
			"name" => str_replace("Power-", "", $device->getName()),
			"usage" => (int)$device->getPower(),
			"enabled" => $device->isEnabled()
		];
	}
	function cmp($a, $b) {
		return strcmp($a["name"], $b["name"]);
	}
	usort($return_data, "cmp");
	echo json_encode([ "elements" => $return_data]);
});

$app->get('/overview', function() use ($app) {
	$hm = new homeMaticInstance();
	$devices = $hm->getAllDevices();
	$homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$serviceMessages = $hm->getServiceMessages();
	$app->render('overview.html', array("devices" => $devices, "serviceMessages" => $serviceMessages, "homeStatus" => $homeStatus));
});

$app->get('/control', function() use ($app) {
	$hm = new homeMaticInstance();
	$devices = $hm->getAllDevices();
	$homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$serviceMessages = $hm->getServiceMessages();
	$app->render('control.html', array("devices" => $devices, "serviceMessages" => $serviceMessages, "homeStatus" => $homeStatus));
});

$app->post('/setTemp', function() use ($app) {
	$hm = new homeMaticInstance();
	if($valve = $hm->getValveByPeerId($_POST["peerId"])) {
		$valve->setTargetTemp(floatval($_POST["targetTemp"]));
	}
	Header("Location: /control");
	exit;
});

$app->post('/setAllTemp', function() use ($app) {
	$hm = new homeMaticInstance();
	if(is_array($_POST["valves"])) {
		foreach($_POST["valves"] AS $peerId) {
			$hm->setTargetTemperature(floatval($_POST["targetTemp"]), intval($peerId));
		}
	}
	Header("Location: /control");
	exit;
});

$app->post('/togglePwr', function() use ($app) {
	$hm = new homeMaticInstance();
	if($sensor = $hm->getPwrSensorByPeerId($_POST["peerId"])) {
		$sensor->togglePower();
	}
	Header("Location: /control");
	exit;
});

$app->get('/valveDetails/:h', function($peerId) use ($app) {
	$hm = new homeMaticInstance();
	$device = $hm->getValveByPeerId($peerId);
	$homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$app->render('valveDetails.html', array("device" => $device, "homeStatus" => $homeStatus));
});

$app->get('/showPeers', function() use ($app) {
	$hm = new homeMaticInstance();
	$devices = $hm->getAllDevices();
	$peeringStatus = $hm->isPeering();
	$peeringTimeout = $hm->getPeeringTimeout();
	$homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$app->render('showpeers.html', array("devices" => $devices, "peeringStatus" => $peeringStatus, "peeringTimeout" => $peeringTimeout, "homeStatus" => $homeStatus));
});

$app->post('/enablePeering', function() use ($app) {
	$hm = new homeMaticInstance();
	if(isset($_POST["enablePeering"])) {
		$hm->setPeeringMode();
	}
	Header("Location: /showPeers");
	exit;
});

$app->post('/linkPeers', function() use ($app) {
	$hm = new homeMaticInstance();
	if(is_numeric($_POST["masterPeer"]) && is_numeric($_POST["slavePeer"])) {
		$hm->linkPeers($_POST["masterPeer"], $_POST["slavePeer"]);
	}
	Header("Location: /showPeers");
	exit;
});

$app->get('/showEvents', function() use ($app) {
	$hm = new homeMaticInstance();
	$events = $hm->getEvents();
	$homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$app->render('showevents.html', array("homeStatus" => $homeStatus, "events" => $events));
});

$app->get('/triggerEvent/:h', function($eventId) use ($app) {
	$hm = new homeMaticInstance();
	$events = $hm->triggerEvent($eventId);
	Header("Location: /showEvents");
	exit;
});

$app->get('/timeSchedules', function() use ($app) {
	$hm = new homeMaticInstance();
	$homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$app->render('timeschedules.html', array("homeStatus" => $homeStatus));
});


$app->run();

?>
