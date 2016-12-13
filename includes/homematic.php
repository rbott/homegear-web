<?php

$BASEPATH = realpath(dirname(__FILE__));

include_once("/var/lib/homegear/scripts/HM-XMLRPC-Client/Client.php");
include_once($BASEPATH . "/class.device.valve.php");
include_once($BASEPATH . "/class.device.envsensor.php");
include_once($BASEPATH . "/class.meta.tempset.php");
include_once($BASEPATH . "/../includes/redis/redis.php");
include_once($BASEPATH . "/../config/config.inc.php");

class HomeMaticInstance
{
	private $XMLRPC;
	private $valves = array();
	private $envSensors = array();
    private $peeringTimeout = -1;
    private $config = array();

    function HomeMaticInstance() {
        global $config;
		$host = "localhost";
		$port = 2001;
        $ssl = false;
        $this->XMLRPC = new \XMLRPC\Client($host, $port, $ssl);
        $this->config = $config;

		$devices = $this->XMLRPC->send("listDevices", array());
		foreach($devices AS $device) {
			if(empty($device["PARENT"])) {
				switch($device["TYPE"]) {
				case "HM-WDS40-TH-I-2":
					$this->envSensors[] = new HomeMaticEnvSensors($device["ADDRESS"], $device["CHANNELS"], $this->XMLRPC);
					break;
				case "HM-CC-RT-DN":
					$this->valves[] = new HomeMaticValve($device["ADDRESS"], $device["CHANNELS"], $this->XMLRPC);
					break;
				}
			}
		}
    }

    function presenceEnabled() {
        if(isset($this->config["presence"]) && isset($this->config["presence"]["enabled"])) {
            return $this->config["presence"]["enabled"];
        }
        else {
            return false;
        }
    }

    function isHome() {
        if($this->presenceEnabled()) {
            $redis = new redis_cli($this->config["presence"]["redis_host"], $this->config["presence"]["redis_port"]);
			$onlineStatusKeys = $redis->cmd("KEYS", "online-*")->get();

			$someoneHome = false;
			if(is_array($onlineStatusKeys)) {
				foreach($onlineStatusKeys AS $key) {
					$value = $redis->cmd("GET", $key)->get();
					if($value == "1") {
						# stop on the first positive match (no need to look further)
						$someoneHome = true;
						break;
					}
				}
			}
            return $someoneHome;
		}
        else {
            return false;
        }
    }

	function isPeering() {
		$peeringMode = $this->XMLRPC->send("getInstallMode", array());
		if($peeringMode == 0) {
			$this->peeringTimeout = 0;
			return false;
		}
		else {
			$this->peeringTimeout = $peeringMode;
			return true;
		}
	}

	function getPeeringTimeout() {
		$this->isPeering();
		return $this->peeringTimeout;
	}

	function setPeeringMode() {
		$this->XMLRPC->send("setInstallMode", array(true));
	}

	function getValveNames() {
		$names = array();
		foreach($this->valves AS $valve) {
			$names[] = $valve->getName();
		}
		return $names;
	}

	function getValveByName($name) {
		foreach($this->valves AS $valve) {
			if($valve->getName() == $name) {
				return $valve;
			}
		}
		return false;
	}

	function getValveByPeerId($peerId) {
		foreach($this->valves AS $valve) {
			if($valve->getPeerId() == $peerId) {
				return $valve;
			}
		}
		return false;
	}

	function getEnvSensorNames() {
		$names = array();
		foreach($this->envSensors AS $sensor) {
			$names[] = $sensor->getName();
		}
		return $names;
	}

	function getAllDevices($withState = false) {
		$devices = array();
		foreach($this->valves AS $valve) {
			if(!$withState) {
				$devices[] = array( "name" => $valve->getName(),
						"peerId" => $valve->getPeerId(),
						"address" => $valve->getAddress(),
						"typeString" => $valve->getTypeString(),
						"type" => "valve");
			}
			else {
				$devices[] = array( "name" => $valve->getName(),
						"peerId" => $valve->getPeerId(),
						"address" => $valve->getAddress(),
						"typeString" => $valve->getTypeString(),
						"type" => "valve",
						"valveState" => $valve->getValveState(),
						"tempSensor" => $valve->getTempSensor(),
						"targetTemp" => $valve->getTargetTemp(),
						"controlMode" => $valve->getControlMode(),
						"tempFallMode" => $valve->getTempFallMode(),
						"tempFallTemp" => $valve->getTempFallTemp(),
						"tempFallWindow" => $valve->getTempFallWindow());
			}
		}
		foreach($this->envSensors AS $sensor) {
			if(!$withState) {
				$devices[] = array( "name" => $sensor->getName(),
						"peerId" => $sensor->getPeerId(),
						"address" => $sensor->getAddress(),
						"typeString" => $sensor->getTypeString(),
						"type" => "envsensor");
			}
			else {
				$devices[] = array( "name" => $sensor->getName(),
						"peerId" => $sensor->getPeerId(),
						"address" => $sensor->getAddress(),
						"typeString" => $sensor->getTypeString(),
						"type" => "envsensor",
						"tempSensor" => $sensor->getTempSensor(),
						"humidSensor" => $sensor->getHumidSensor());
			}
		}
		return $devices;
	}

	function getAllTemperatures() {
		$temps = array();
		foreach($this->valves AS $valve) {
			$temps[$valve->getName()] = $valve->getTempSensor();
		}
		foreach($this->envSensors AS $sensor) {
			$temps[$sensor->getName()] = $sensor->getTempSensor();
		}
		return $temps;
	}

	function getAllHumidity() {
		$humidity = array();
		foreach($this->envSensors AS $sensor) {
			$humidity[$sensor->getName()] = $sensor->getHumidSensor();
		}
		return $humidity;
	}

	function getAllControlModes() {
		$modes = array();
		foreach($this->valves AS $valve) {
			$modes[$valve->getName()] = $valve->getControlMode();
		}
		return $modes;
	}

	function getAllTempFallModes() {
		$modes = array();
		foreach($this->valves AS $valve) {
			$modes[$valve->getName()] = $valve->getTempFallMode();
		}
		return $modes;
	}

	function setControlMode($mode) {
		if($peerId > 0) {
			# just adjust one valve
			$valve = $this->getValveByPeerId($peerId);
			if($valve->getTargetTemp() != $mode) {
				$valve->setControlMode($mode);
			}
		}
		else {
			# adjust ALL the valves
			foreach($this->valves AS $valve) {
				if($valve->getControlMode() != $mode) {
					$valve->setControlMode($mode);
				}
			}
		}
	}

	function setTargetTemperature($temp,$peerId = -1) {
		if($peerId > 0) {
			# just adjust one valve
			$valve = $this->getValveByPeerId($peerId);
			if($valve->getTargetTemp() <> $temp) {
				$valve->setTargetTemp($temp);
			}
		}
		else {
			# adjust ALL the valves!
			foreach($this->valves AS $valve) {
				$valve->setTargetTemp($temp);
			}
		}
	}

}


?>
