<?php

$BASE_PATH = realpath(dirname(__FILE__));
include_once($BASE_PATH . '/../includes/homematic.php');
include_once($BASE_PATH . '/../includes/class.graphing.php');
$hm = new HomeMaticInstance;
$hg = new HomeMaticGraphing($hm);

$hg->checkAndCreateSources();
$hg->updateRrds();

?>
