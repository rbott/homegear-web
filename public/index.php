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
	Header("Location: /overview");
	exit;
});

$app->get('/overview', function() use ($app) {
	$hm = new homeMaticInstance();
    $devices = $hm->getAllDevices(true);
    $homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$app->render('overview.html', array("devices" => $devices, "homeStatus" => $homeStatus));
});

$app->get('/control', function() use ($app) {
	$hm = new homeMaticInstance();
	$devices = $hm->getAllDevices(true);
    $homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$app->render('control.html', array("devices" => $devices, "homeStatus" => $homeStatus));
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

$app->get('/valveDetails/:h', function($peerId) use ($app) {
	$hm = new homeMaticInstance();
	$device = $hm->getValveByPeerId($peerId);
    $homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$app->render('valveDetails.html', array("device" => $device, "homeStatus" => $homeStatus));
});


$app->get('/showGraphs', function() use ($app) {
    $hm = new homeMaticInstance();
    $homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$app->render('showgraphs.html', array("homeStatus" => $homeStatus));
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

$app->get('/timeSchedules', function() use ($app) {
	$hm = new homeMaticInstance();
    $homeStatus = ($hm->presenceEnabled() ? ($hm->isHome() ? "homeStatus_home" : "homeStatus_nothome") : "");
	$app->render('timeschedules.html', array("homeStatus" => $homeStatus));
});


$app->run();

?>
