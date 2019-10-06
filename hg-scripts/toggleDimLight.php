<?php

$BASE_PATH = realpath(dirname(__FILE__));
include_once($BASE_PATH . '/../includes/homematic.php');

if(count($argv) == 3) {
	$level = $argv[2];
	$level = $level / 100;
	$peer = $argv[1];
	$home = new HomeMaticInstance;
	$home->getDimmerByName($peer)->toggleLevel($level);
}

?>
