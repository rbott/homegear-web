<?php
$BASE_PATH = realpath(dirname(__FILE__));
include_once($BASE_PATH . '/../includes/homematic.php');
$hm = new HomeMaticInstance;

# example events to connect a switch device to certain actions
# this example connects the six buttons of a HM-PB-6-WM55 to 
# local scripted actions. the scripts have to reside in the
# 'scriptingPath' configured within homegear
#
# each button has its own channel with the following layout:
# 6 - 5
# 4 - 3
# 2 - 1
#

$dimmer = $hm->getDimmerByPeerId(13);
if ($dimmer->isEnabled()) {
	echo "ist an\n";
}
else echo "ist aus\n";

print_r($dimmer->getRssi());

echo "\n";
#$dimmer->toggleLevel(0.4);


?>
