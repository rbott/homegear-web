<?php

include_once('/var/www/includes/homematic.php');
include_once('/var/www/includes/class.graphing.php');
$hm = new HomeMaticInstance;
$hg = new HomeMaticGraphing($hm);

$hg->checkAndCreateSources();
$hg->updateRrds();

?>
