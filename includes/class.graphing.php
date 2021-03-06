<?php

class HomeMaticGraphing
{
	private $HM;
	private $devices;
	private $valveRrdOptions = array(
		"--step", "60",
		"DS:temp:GAUGE:120:-20:40",
		"DS:configuredTemp:GAUGE:120:-20:40",
		"DS:valve:GAUGE:120:0:100",
		"RRA:LAST:0.5:1:1440",
		"RRA:LAST:0.5:12:168",
		"RRA:LAST:0.5:228:365",
	);
	private $envSensorRrdOptions = array(
		"--step", "60",
		"DS:temp:GAUGE:120:-20:40",
		"DS:humidity:GAUGE:120:0:100",
		"RRA:LAST:0.5:1:1440",
		"RRA:LAST:0.5:12:168",
		"RRA:LAST:0.5:228:365",
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
					$options = array( sprintf("%d:%d:%d:%d",$timeStampNow,$device["tempSensor"],$device["targetTemp"],$device["valveState"]));
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
		$options[] = "--title=Temperatures (" . $period . ")";
		$options[] = "--vertical-label=Celsius";
		$options[] = "--start";
		$options[] = $this->graphPeriods[$period];
		$i = 0;
		$this->colorIndex = 0;
		foreach($this->devices AS $device) {
			if(empty($deviceList) || in_array($device["peerId"],$deviceList)) {
				switch($device["type"]) {
				case "valve":
					$rrdPath = $this->rrdBasePath . "valves/peer_" . $device["peerId"] . ".rrd";
					break;
				case "envsensor":
					$rrdPath = $this->rrdBasePath . "sensors/peer_" . $device["peerId"] . ".rrd";
					break;
				}
				if(isset($device["tempSensor"]) && file_exists($rrdPath)) {
					$options[] = "DEF:temp" . $i . "=" . $rrdPath . ":temp:LAST";
					$options[] = "LINE2:temp" . $i . $this->getColor() . ":" . $device["name"];
					$options[] = "GPRINT:temp" . $i . ":LAST:%2.1lf°C";
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
		$options[] = "--title=Valve States (" . $period . ")";
		$options[] = "--vertical-label=%";
		$options[] = "--start";
		$options[] = $this->graphPeriods[$period];
		$options[] = "--upper-limit=100";
		$i = 0;
		$this->colorIndex = 0;
		foreach($this->devices AS $device) {
			if($device["type"] == "valve" && (empty($deviceList) || in_array($device["peerId"],$deviceList))) {
				$rrdPath = $this->rrdBasePath . "valves/peer_" . $device["peerId"] . ".rrd";
				if(isset($device["valveState"]) && file_exists($rrdPath)) {
					$options[] = "DEF:valve" . $i . "=" . $rrdPath . ":valve:LAST";
					$options[] = "LINE2:valve" . $i . $this->getColor() . ":" . $device["name"];
					$options[] = "GPRINT:valve" . $i . ":LAST:%2.1lf%%";
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

	function drawHumidityGraph($period,$deviceList = array()) {
		$options = $this->tempGraphOptions;
		$options[] = "--title=Humidity (" . $period . ")";
		$options[] = "--vertical-label=%";
		$options[] = "--start";
		$options[] = $this->graphPeriods[$period];
		$options[] = "--upper-limit=100";
		$i = 0;
		$this->colorIndex = 0;
		foreach($this->devices AS $device) {
			if($device["type"] == "envsensor" && (empty($deviceList) || in_array($device["peerId"],$deviceList))) {
				$rrdPath = $this->rrdBasePath . "sensors/peer_" . $device["peerId"] . ".rrd";
				if(isset($device["humidSensor"]) && file_exists($rrdPath)) {
					$options[] = "DEF:humidity" . $i . "=" . $rrdPath . ":humidity:LAST";
					$options[] = "LINE2:humidity" . $i . $this->getColor() . ":" . $device["name"];
					$options[] = "GPRINT:humidity" . $i . ":LAST:%2.1lf%%";
					$options[] = "COMMENT:\\n";
					$i++;
				}
			}
		}
		if(!rrd_graph($this->rrdBasePath . "output/all_humidity_" . $period . ".gif",$options)) {
			echo "RRD ERROR: " . rrd_error() . "\n";
			print_r($options);
		}
	}

	function drawCurrentVsActualTempGraph($period,$peerId) {
		$options = $this->tempGraphOptions;
		$options[] = "--title=Actual Temp vs. Configured Temp on Peer " . $peerId . " (" . $period . ")";
		$options[] = "--vertical-label=°C";
		$options[] = "--start";
		$options[] = $this->graphPeriods[$period];
		$options[] = "--upper-limit=30";
		$i = 0;
		$this->colorIndex = 0;
		foreach($this->devices AS $device) {
			if($device["type"] == "valve" && intval($device["peerId"]) == intval($peerId)) {
				$rrdPath = $this->rrdBasePath . "valves/peer_" . $device["peerId"] . ".rrd";
				if(isset($device["tempSensor"]) && file_exists($rrdPath)) {
					$options[] = "DEF:temp" . $i . "=" . $rrdPath . ":temp:LAST";
					$options[] = "LINE2:temp" . $i . $this->getColor() . ": Actual Temperature";
					$options[] = "GPRINT:temp" . $i . ":LAST:%2.1lf°C";
					$options[] = "DEF:configuredTemp" . $i . "=" . $rrdPath . ":configuredTemp:LAST";
					$options[] = "LINE2:configuredTemp" . $i . $this->getColor() . ": Configured Temperature";
					$options[] = "GPRINT:configuredTemp" . $i . ":LAST:%2.1lf°C";
					$options[] = "COMMENT:\\n";
					$i++;
				}
			}
		}
		if($i == 0) {
			$options[] = "ERROR: Device " . $peerId . " not found or not a valve!";
		}
		if(!rrd_graph($this->rrdBasePath . "output/" . $peerId . "_CurrentVsActual_" . $period . ".gif",$options)) {
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
