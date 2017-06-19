<?php

$BASE_PATH = realpath(dirname(__FILE__));
include_once($BASE_PATH . '/../includes/homematic.php');

if(isset($argv[1]) && is_numeric($argv[1]) && ($argv[1] > 12 && $argv[1] < 26)) {
    $home = new HomeMaticInstance;
    $home->setTargetTemperature($argv[1]);
}

?>
