<?php

include_once('includes/homematic.php');
include_once('includes/temperatures.php');
$home = new HomeMaticInstance;

#echo "Available Valves:\n";
#print_r($home->getValveNames());
#
#echo "Selecting 'Heizung-Kueche'\n";
#$kueche = $home->getValveByName("Heizung-Kueche");
#
##echo "Setting Target Temperature to 20.0°C\n";
##$kueche->setTargetTemp(20.0);
#
#echo "Printing all Temperature Sensors:\n";
#print_r($home->getAllTemperatures());
#
#echo "Printing all Humidity Sensors:\n";
#print_r($home->getAllHumidity());
#
#echo "Printing all Control Mode Settings in Valves:\n";
#print_r($home->getAllControlModes());
#
#echo "Printing all Temperature Fall Mode Settings in Valves:\n";
#print_r($home->getAllTempFallModes());

#echo "Setting Control Mode to 'auto' on all valves:\n";
#$home->setAllControlModes('manual');
#echo "Setting all temperatures to 19°C:\n";
#$home->setAllTargetTemperatures(19.0);

#echo "Config Dump of 'Heizung-Kueche':\n";
#print_r($kueche->getParamset(0,'MASTER'));

#prepare_tempset($_temperatures["regular"]);
?>
