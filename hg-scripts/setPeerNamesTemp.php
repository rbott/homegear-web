<?php

$BASE_PATH = realpath(dirname(__FILE__));
include_once($BASE_PATH . '/../includes/homematic.php');

if(count($argv) >= 3) {
    $temp = $argv[count($argv) - 1 ]; 
    $peers = array();
    for($i = 1; $i < count($argv) - 1; $i++) {
        $peers[] = $argv[$i];
    }
    if(is_numeric($temp) && ($temp > 12 && $temp < 26)) {
        $home = new HomeMaticInstance;
        foreach($peers AS $peer) {
            $home->getValveByName($peer)->setTargetTemp($temp);
        }
    }
}

?>
