<?php

# wilfried-kuefen.de
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

/*
$app->add(new \Slim\Middleware\HttpBasicAuthentication([
	"path" => "/admin",
	"realm" => "Protected",
	"secure" => false,
	"users" => [
		"rudi" => "hgadmin",
	]
]));
*/

$app->view(new \Slim\Views\Twig());
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

$app->get('/', function() use ($app) {
	Header("Location: /overview");
	exit;
});

$app->get('/overview', function() use ($app) {
	$site = new homeMaticInstance();
	$devices = $site->getAllDevices(true);
	$app->render('overview.html', array("devices" => $devices,));
});

$app->get('/control', function() use ($app) {
	$site = new homeMaticInstance();
	$devices = $site->getAllDevices(true);
	$app->render('control.html', array("devices" => $devices,));
});

$app->post('/setTemp', function() use ($app) {
	$site = new homeMaticInstance();
	if($valve = $site->getValveByPeerId($_POST["peerId"])) {
		$valve->setTargetTemp(floatval($_POST["targetTemp"]));
	}
	Header("Location: /control");
	exit;
});

$app->post('/setAllTemp', function() use ($app) {
	$site = new homeMaticInstance();
	$site->setTargetTemperature(floatval($_POST["targetTemp"]));
	Header("Location: /control");
	exit;
});

$app->get('/valveDetails/:h', function($peerId) use ($app) {
	$site = new homeMaticInstance();
	$device = $site->getValveByPeerId($peerId);
	$app->render('valveDetails.html', array("device" => $device,));
});


$app->run();

?>
