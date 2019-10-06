<?php
$BASE_PATH = realpath(dirname(__FILE__));
include_once($BASE_PATH . '/../includes/homematic.php');
include_once($BASE_PATH . '/../includes/class.graphing.php');
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

$hm->events->delEvent("all_valves_13");
$hm->events->addEvent(1, 6, "all_valves_13", "PRESS_SHORT", 8, "setGlobalTemp.php", "13");

$hm->events->delEvent("all_valves_19");
$hm->events->addEvent(1, 4, "all_valves_19", "PRESS_SHORT", 8, "setGlobalTemp.php", "19");

$hm->events->delEvent("all_valves_21");
$hm->events->addEvent(1, 2, "all_valves_21", "PRESS_SHORT", 8, "setGlobalTemp.php", "21");

$hm->events->delEvent("sleep_valve_22");
$hm->events->addEvent(1, 5, "sleep_valve_22", "PRESS_SHORT", 8, "setPeerNamesTemp.php", "Heizung-Schlafzimmer 22");

$hm->events->delEvent("living_valve_22");
$hm->events->addEvent(1, 3, "living_valve_22", "PRESS_SHORT", 8, "setPeerNamesTemp.php", "Heizung-Wohnzimmer Heizung-Esszimmer 22");

$hm->events->delEvent("sleep_dim50_remote");
$hm->events->addEvent(12, 1, "sleep_dim50_remote", "PRESS_SHORT", 8, "toggleDimLight.php", "Dimmer-Schlafzimmer 50");

$hm->events->delEvent("sleep_dim100_remote");
$hm->events->addEvent(12, 2, "sleep_dim100_remote", "PRESS_SHORT", 8, "toggleDimLight.php", "Dimmer-Schlafzimmer 100");

$hm->events->delEvent("all_valves_13_remote");
$hm->events->addEvent(12, 3, "all_valves_13_remote", "PRESS_SHORT", 8, "setGlobalTemp.php", "13");

$hm->events->delEvent("all_valves_19_remote");
$hm->events->addEvent(12, 4, "all_valves_19_remote", "PRESS_SHORT", 8, "setGlobalTemp.php", "19");

$hm->events->delEvent("all_valves_21_remote");
$hm->events->addEvent(12, 5, "all_valves_21_remote", "PRESS_SHORT", 8, "setGlobalTemp.php", "21");

$hm->events->delEvent("sleep_valve_19_remote");
$hm->events->addEvent(12, 7, "sleep_valve_19_remote", "PRESS_SHORT", 8, "setPeerNamesTemp.php", "Heizung-Schlafzimmer 19");

$hm->events->delEvent("sleep_valve_22_remote");
$hm->events->addEvent(12, 8, "sleep_valve_22_remote", "PRESS_SHORT", 8, "setPeerNamesTemp.php", "Heizung-Schlafzimmer 22");

?>
