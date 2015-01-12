<?php

include_once('/var/www/includes/homematic.php');
include_once('/var/www/includes/class.graphing.php');
$hm = new HomeMaticInstance;
$hg = new HomeMaticGraphing($hm);

$periods = array ("hourly", "daily", "weekly", "monthly", "yearly");
foreach($periods AS $period) {
	$hg->drawTempGraph($period);
	$hg->drawValveGraph($period);
	$hg->drawHumidityGraph($period);
}

?>
