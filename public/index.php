<?php

# homegear-web
# Copyright (C) 2015  Rudolph Bott
#

require_once('../includes/Twig/Autoloader.php');
require_once('../includes/Slim/Slim.php');

require_once('../config/config.inc.php');
require_once('../includes/homematic.php');
require_once('../includes/nad.php');

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
	'templates.path' => '../tpl'
));

$app->view(new \Slim\Views\Twig());
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

$app->get('/', function() use ($app) {
	Header("Location: /overview");
	exit;
});

$app->get('/metrics', function() use ($app) {
    $app->response->headers->set("Content-Type", "text/plain; version=0.0.4");
    $hm = new homeMaticInstance();
    $stats = $hm->getPrometheusStats();
    echo $stats;
});

$app->get('/overview', function() use ($app) {
	$hm = new homeMaticInstance();
	$app->render('overview.html', array("pageTitle" => "Dashboard", "hm" => $hm));
});

$app->get('/control', function() use ($app) {
    global $config;
	$hm = new homeMaticInstance();
    $devices = $hm->getAllDevices(true);
    $customActions = $config["actions"];
	$app->render('control.html', array("pageTitle" => "Control", "customActions" => $customActions, "hm" => $hm, "devices" => $devices));
});

$app->get('/customAction/:h', function($id) use ($app) {
    global $config;
    if(is_numeric($id) && isset($config["actions"][$id])) {
        $action = $config["actions"][$id];
        switch($action["method"]) {
        case "hgscript":
	        $hm = new homeMaticInstance();
            $hm->runscript($action["path"], join(" ", $action["params"]));
            break;
        }

    }
    Header("Location: /control");
    exit;
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

$app->post('/enableAllPower', function() use ($app) {
	$hm = new homeMaticInstance();
	if(is_array($_POST["pwrsensors"])) {
        foreach($_POST["pwrsensors"] AS $peerId) {
            if($sensor = $hm->getPwrSensorByPeerId($peerId)) {
                $sensor->enable();
            }
		}
	}
	Header("Location: /control");
	exit;
});

$app->post('/disableAllPower', function() use ($app) {
	$hm = new homeMaticInstance();
	if(is_array($_POST["pwrsensors"])) {
        foreach($_POST["pwrsensors"] AS $peerId) {
            if($sensor = $hm->getPwrSensorByPeerId($peerId)) {
                $sensor->disable();
            }
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
	$app->render('valveDetails.html', array("device" => $device));
});


$app->get('/showGraphs', function() use ($app) {
    $hm = new homeMaticInstance();
	$app->render('showgraphs.html', array("hm" => $hm));
});

$app->get('/showPeers', function() use ($app) {
	$hm = new homeMaticInstance();
	$devices = $hm->getAllDevices();
	$peeringStatus = $hm->isPeering();
	$peeringTimeout = $hm->getPeeringTimeout();
	$app->render('showpeers.html', array("pageTitle" => "Peers", "hm" => $hm, "devices" => $devices, "peeringStatus" => $peeringStatus, "peeringTimeout" => $peeringTimeout));
});

$app->post('/enablePeering', function() use ($app) {
	$hm = new homeMaticInstance();
	if(isset($_POST["enablePeering"])) {
		$hm->setPeeringMode();
	}
	Header("Location: /showPeers");
	exit;
});

$app->get('/showEvents', function() use ($app) {
    $hm = new homeMaticInstance();
    $events = $hm->getEvents();
	$app->render('showevents.html', array("pageTitle" => "Events", "hm" => $hm, "events" => $events));
});

$app->get('/triggerEvent/:h', function($eventId) use ($app) {
    $hm = new homeMaticInstance();
    $events = $hm->triggerEvent($eventId);
    Header("Location: /showEvents");
    exit;
});

$app->get('/timeSchedules', function() use ($app) {
	$hm = new homeMaticInstance();
	$app->render('timeschedules.html', array("hm" => $hm));
});

$app->get('/nad/:h', function($action) use ($app) {
    $nad = new nadClient("http://volumio.local:3333/", 10);
    switch($action) {
    case "model":
        $data["model"] = $nad->getModel();
        break;
    case "power":
        $data["power"] = $nad->getPower();
        break;
    case "source":
        $data["source"] = $nad->getSource();
        break;
    case "volume":
        $data["volume"] = $nad->getVolume();
        break;
    default:
        $data[$action] = null;
    }
    echo json_encode($data);
});

$app->run();

?>
