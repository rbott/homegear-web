<?php

$BASE_PATH = realpath(dirname(__FILE__));
include_once($BASE_PATH . '/../includes/homematic.php');
include_once($BASE_PATH . '/../includes/class.graphing.php');
$hm = new HomeMaticInstance;
$hg = new HomeMaticGraphing($hm);

$periods = array ("hourly", "daily", "weekly", "monthly", "yearly");
foreach($periods AS $period) {
	$hg->drawTempGraph($period);
	$hg->drawValveGraph($period);
	$hg->drawHumidityGraph($period);
	foreach($hm->getAllDevices() AS $device) {
		if($device["type"] == "valve") {
			$hg->drawCurrentVsActualTempGraph($period,$device["peerId"]);
		}
	}
}

?>
