<?php

class HomeMaticGraphing
{
	private $HM;
	private $devices;
	private $valveRrdOptions = array(
		"--step", "60",
		"DS:temp:GAUGE:120:-20:40",
		"DS:valve:GAUGE:120:0:100",
		"RRA:AVERAGE:0.5:1:1440",
		"RRA:AVERAGE:0.5:12:168",
		"RRA:AVERAGE:0.5:228:365",
	);
	private $envSensorRrdOptions = array(
		"--step", "60",
		"DS:temp:GAUGE:120:-20:40",
		"DS:humidity:GAUGE:120:0:100",
		"RRA:AVERAGE:0.5:1:1440",
		"RRA:AVERAGE:0.5:12:168",
		"RRA:AVERAGE:0.5:228:365",
	);
	private $tempGraphOptions = array(
		"--slope-mode",
		"--lower=0",
		"--width=480",
		"--height=160"
	);
	private $colors = array('#30FC30',
		'#3096FC',
		'#FFC930',
		'#FF3030',
		'#30CC30',
		'#3030CC',
		'#FFFF00',
		'#CC3030');
	private $colorIndex = 0;
	private $rrdBasePath = "/var/www/graphs/";
	private $graphPeriods = array('hourly' => '-1h',
		'daily' => '-1d',
		'weekly' => '-1w',
		'monthly' => '-4w',
		'yearly' => '-1y');

	function HomeMaticGraphing($hmInstance) {
		$this->HM = $hmInstance;
		$this->devices = $this->HM->getAllDevices(true);
	}

	function checkAndCreateSources() {
		foreach($this->devices AS $device) {
			switch($device["type"]) {
			case "valve":
				$rrdPath = $this->rrdBasePath . "valves/peer_" . $device["peerId"] . ".rrd";
				if(!file_exists($rrdPath)) {
					rrd_create($rrdPath,$this->valveRrdOptions);
				}
				break;
			case "envsensor":
				$rrdPath = $this->rrdBasePath . "sensors/peer_" . $device["peerId"] . ".rrd";
				if(!file_exists($rrdPath)) {
					rrd_create($rrdPath,$this->envSensorRrdOptions);
				}
				break;
			}
		}
	}

	function updateRrds() {
		$timeStampNow = time();
		foreach($this->devices AS $device) {
			switch($device["type"]) {
			case "valve":
				$rrdPath = $this->rrdBasePath . "valves/peer_" . $device["peerId"] . ".rrd";
				if(file_exists($rrdPath)) {
					$options = array( sprintf("%d:%d:%d",$timeStampNow,$device["tempSensor"],$device["valveState"]));
					if(!rrd_update($rrdPath,$options)) {
						echo "RRD ERROR:" . rrd_error() . "\n";
					}
				}
				break;
			case "envsensor":
				$rrdPath = $this->rrdBasePath . "sensors/peer_" . $device["peerId"] . ".rrd";
				if(file_exists($rrdPath)) {
					$options = array( sprintf("%d:%d:%d",$timeStampNow,$device["tempSensor"],$device["humidSensor"]));
					if(!rrd_update($rrdPath,$options)) {
						echo "RRD ERROR:" . rrd_error() . "\n";
					}
				}
				break;
			}
		}
	}

	function drawTempGraph($period,$deviceList = array()) {
		$options = $this->tempGraphOptions;
		$options[] = "--title=Temperatures";
		$options[] = "--vertical-label=Celsius";
		$options[] = "--start";
		$options[] = $this->graphPeriods[$period];
		$i = 0;
		$this->colorIndex = 0;
		foreach($this->devices AS $device) {
			if(empty($deviceList) || in_array($device["peerId"],$deviceList)) {
				switch($device["type"]) {
				case "valve":
					$rrdPath = "graphs/valves/peer_" . $device["peerId"] . ".rrd";
					break;
				case "envsensor":
					$rrdPath = "graphs/sensors/peer_" . $device["peerId"] . ".rrd";
					break;
				}
				if(isset($device["tempSensor"]) && file_exists($rrdPath)) {
					$options[] = "DEF:temp" . $i . "=" . $rrdPath . ":temp:AVERAGE";
					$options[] = "LINE1:temp" . $i . $this->getColor() . ":" . $device["name"];
					$options[] = "GPRINT:temp" . $i . ":AVERAGE:%2.1lf°C";
					$options[] = "COMMENT:\\n";
					$i++;
				}
			}
		}
		if(!rrd_graph($this->rrdBasePath . "output/all_temp_" . $period . ".gif",$options)) {
			echo "RRD ERROR: " . rrd_error() . "\n";
			print_r($options);
		}
	}

	function drawValveGraph($period,$deviceList = array()) {
		$options = $this->tempGraphOptions;
		$options[] = "--title=Valve States";
		$options[] = "--vertical-label=%";
		$options[] = "--start";
		$options[] = $this->graphPeriods[$period];
		$options[] = "--upper-limit=100";
		$i = 0;
		$this->colorIndex = 0;
		foreach($this->devices AS $device) {
			if($device["type"] == "valve" && (empty($deviceList) || in_array($device["peerId"],$deviceList))) {
				$rrdPath = "graphs/valves/peer_" . $device["peerId"] . ".rrd";
				if(isset($device["valveState"]) && file_exists($rrdPath)) {
					$options[] = "DEF:valve" . $i . "=" . $rrdPath . ":valve:AVERAGE";
					$options[] = "LINE1:valve" . $i . $this->getColor() . ":" . $device["name"];
					$options[] = "GPRINT:valve" . $i . ":AVERAGE:%2.1lf%%";
					$options[] = "COMMENT:\\n";
					$i++;
				}
			}
		}
		if(!rrd_graph($this->rrdBasePath . "output/all_valve_" . $period . ".gif",$options)) {
			echo "RRD ERROR: " . rrd_error() . "\n";
			print_r($options);
		}
	}

	function getColor() {
		$color = $this->colors[$this->colorIndex];
		if($this->colorIndex <= (count($this->colors)-1)) {
			$this->colorIndex++;
		}
		else {
			$this->colorIndex = 0;
		}
		return $color;
	}
}


?>
