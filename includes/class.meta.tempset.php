<?php


class tempsetInstance {
	public $presetWorkday = array("MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY");
	public $presetWeekend = array("SATURDAY", "SUNDAY");

	private $tempset = array();
	private $currentTimesetId = 1;

	function addTemp($startTime,$temperature,$days = array("MONDAY","TUESDAY","WEDNESDAY","THURSDAY","FRIDAY","SATURDAY","SUNDAY")) {
		if(preg_match("/^(\d+):(\d+)$/",$startTime,$timeParts)) {
			$startTimeSeconds = (floatval($timeParts[0] . "." . $timeParts[1]) * 60);
			foreach($days AS $day) {
				$this->tempset["ENDTIME_" . $day . "_" . $this->currentTimesetId] = $startTimeSeconds;
				$this->tempset["TEMPERATURE_" . $day . "_" . $this->currentTimesetId] = $temperature;
			}
			$this->currentTimesetId++;
			return true;
		}
		else {
			return false;
		}
	}

	function tempsetInstance() {
	}

	function prepareTempset() {
		$countStart = 1;
		$countEnd = 13;
		$days = array("MONDAY","TUESDAY","WEDNESDAY","THURSDAY","FRIDAY","SATURDAY","SUNDAY");
		$sets = array("ENDTIME","TEMPERATURE");
		$defaults = array("ENDTIME" => 1440, "TEMPERATURE" => 17);
		foreach($sets AS $set) {
			foreach($days AS $day) {
				for($i = $countStart; $i <= $countEnd; $i++) {
					$key = $set . "_" . $day . "_". $i;
					if(!isset($this->tempset[$key])) {
						$this->tempset[$key] = $defaults[$set];
						echo "Added " . $key . " => " . $defaults[$set] . "\n";
					}
				}
			}
		}
	}
	
	function getTempset() {
		$this->prepareTempset();
		return $this->tempset;
	}
}

?>
