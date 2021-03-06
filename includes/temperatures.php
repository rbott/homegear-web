<?php

$_temperatures = array(
	"regular" => array(
			"ENDTIME_MONDAY_1" => 1020,
			"ENDTIME_TUESDAY_1" => 1020,
			"ENDTIME_WEDNESDAY_1" => 1020,
			"ENDTIME_THURSDAY_1" => 1020,
			"ENDTIME_FRIDAY_1" => 1020,
			"ENDTIME_SATURDAY_1" => 540,
			"ENDTIME_SUNDAY_1" => 540,
			"ENDTIME_MONDAY_2" => 1380,
			"ENDTIME_TUESDAY_2" => 1380,
			"ENDTIME_WEDNESDAY_2" => 1380,
			"ENDTIME_THURSDAY_2" => 1380,
			"ENDTIME_FRIDAY_2" => 1380,
			"ENDTIME_SATURDAY_2" => 1380,
			"ENDTIME_SUNDAY_2" => 1380,
			"TEMPERATURE_FRIDAY_1" => 17,
			"TEMPERATURE_TUESDAY_1" => 17,
			"TEMPERATURE_WEDNESDAY_1" => 17,
			"TEMPERATURE_THURSDAY_1" => 17,
			"TEMPERATURE_FRIDAY_1" => 17,
			"TEMPERATURE_SATURDAY_1" => 17,
			"TEMPERATURE_SUNDAY_1" => 17,
			"TEMPERATURE_FRIDAY_2" => 20,
			"TEMPERATURE_TUESDAY_2" => 20,
			"TEMPERATURE_WEDNESDAY_2" => 20,
			"TEMPERATURE_THURSDAY_2" => 20,
			"TEMPERATURE_FRIDAY_2" => 20,
			"TEMPERATURE_SATURDAY_2" => 20,
			"TEMPERATURE_SUNDAY_2" => 20,
		),
	"regular_colder" => array(
			"ENDTIME_MONDAY_1" => 1020,
			"ENDTIME_TUESDAY_1" => 1020,
			"ENDTIME_WEDNESDAY_1" => 1020,
			"ENDTIME_THURSDAY_1" => 1020,
			"ENDTIME_FRIDAY_1" => 1020,
			"ENDTIME_SATURDAY_1" => 540,
			"ENDTIME_SUNDAY_1" => 540,
			"ENDTIME_MONDAY_2" => 1380,
			"ENDTIME_TUESDAY_2" => 1380,
			"ENDTIME_WEDNESDAY_2" => 1380,
			"ENDTIME_THURSDAY_2" => 1380,
			"ENDTIME_FRIDAY_2" => 1380,
			"ENDTIME_SATURDAY_2" => 1380,
			"ENDTIME_SUNDAY_2" => 1380,
			"TEMPERATURE_FRIDAY_1" => 17,
			"TEMPERATURE_TUESDAY_1" => 17,
			"TEMPERATURE_WEDNESDAY_1" => 17,
			"TEMPERATURE_THURSDAY_1" => 17,
			"TEMPERATURE_FRIDAY_1" => 17,
			"TEMPERATURE_SATURDAY_1" => 17,
			"TEMPERATURE_SUNDAY_1" => 17,
			"TEMPERATURE_FRIDAY_2" => 19,
			"TEMPERATURE_TUESDAY_2" => 19,
			"TEMPERATURE_WEDNESDAY_2" => 19,
			"TEMPERATURE_THURSDAY_2" => 19,
			"TEMPERATURE_FRIDAY_2" => 19,
			"TEMPERATURE_SATURDAY_2" => 19,
			"TEMPERATURE_SUNDAY_2" => 19,
		),
);

function prepare_tempset($tempParams) {
	$countStart = 1;
	$countEnd = 13;
	$days = array("MONDAY","TUESDAY","WEDNESDAY","THURSDAY","FRIDAY","SATURDAY","SUNDAY");
	$sets = array("ENDTIME","TEMPERATURE");
	$defaults = array("ENDTIME" => 1440, "TEMPERATURE" => 17);
	foreach($sets AS $set) {
		foreach($days AS $day) {
			for($i = $countStart; $i <= $countEnd; $i++) {
				$key = $set . "_" . $day . "_". $i;
				if(!isset($tempParams[$key])) {
					$tempParams[$key] = $defaults[$set];
					echo "Added " . $key . " => " . $defaults[$set] . "\n";
				}
			}
		}
	}
}

?>
