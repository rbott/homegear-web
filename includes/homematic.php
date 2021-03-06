<?php

$BASEPATH = realpath(dirname(__FILE__));

include_once("/var/lib/homegear/scripts/HM-XMLRPC-Client/Client.php");
include_once($BASEPATH . "/class.device.generic.php");
include_once($BASEPATH . "/class.device.valve.php");
include_once($BASEPATH . "/class.device.envsensor.php");
include_once($BASEPATH . "/class.device.pwrsensor.php");
include_once($BASEPATH . "/class.device.switch.php");
include_once($BASEPATH . "/class.meta.tempset.php");
include_once($BASEPATH . "/class.meta.events.php");
include_once($BASEPATH . "/../includes/redis/redis.php");
include_once($BASEPATH . "/../config/config.inc.php");

class HomeMaticInstance
{
	private $XMLRPC;
	private $valves = array();
    private $envSensors = array();
    private $pwrSensors = array();
    private $peeringTimeout = -1;
    private $config = array();

    public $events;

    function HomeMaticInstance() {
        global $config;
		$host = "localhost";
		$port = 2001;
        $ssl = false;
        $this->XMLRPC = new \XMLRPC\Client($host, $port, $ssl);
        $this->config = $config;

        $this->events = new HomeMaticEvents($this->XMLRPC);

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
                case "HM-ES-PMSw1-Pl":
                    $this->pwrSensors[] = new HomeMaticPwrSensor($device["ADDRESS"], $device["CHANNELS"], $this->XMLRPC);
                    break;
                case "HM-PB-6-WM55":
                    $this->switches[] = new HomeMaticSwitch($device["ADDRESS"], $device["CHANNELS"], $this->XMLRPC);
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

    function getServiceMessages() {
        $messages = $this->XMLRPC->send("getServiceMessages", array(true));
        $return = array();
        foreach($messages AS $message) {
            $entry = array( "id" => $message[0],
                            "deviceName" => $this->getDeviceByPeerId($message[0])->getName(),
                            "type" => $message[2],
                            "value" => $message[3]);
            switch ($message[2]) {
            case "STICKY_UNREACH":
                # we ignore these for now
                break;
            case "LOWBAT":
                $entry["message"] = "Battery low.";
                $return[] = $entry;
                break;
            case "UNREACH":
                $entry["message"] = "Device offline.";
                $return[] = $entry;
                break;
            case "CONFIG_PENDING":
                $entry["message"] = "There is still data to be submitted to this device.";
                $return[] = $entry;
                break;
            case "ERROR":
                $entry["message"] = "An undefined error has occured with this device (payload: '" . $message[3] . "').";
                $return[] = $entry;
                break;
            case "FAULT_REPORTING":
                if($message[3] != 6) {
                    $entry["message"] = "Unknown message type '" . $message[2] . "' with payload '" . $message[3] . "' occured.";
                    $return[] = $entry;
                }
                break;
            default:
                $entry["message"] = "Unknown message type '" . $message[2] . "' with payload '" . $message[3] . "' occured.";
                $return[] = $entry;
            }
        }
        return $return;
    }

    function getDeviceByName($name) {
		foreach($this->valves AS $valve) {
			if($valve->getName() == $name) {
				return $valve;
			}
		}
		foreach($this->envSensors AS $sensor) {
			if($sensor->getName() == $name) {
				return $sensor;
			}
		}
		foreach($this->pwrSensors AS $sensor) {
			if($sensor->getName() == $name) {
				return $sensor;
			}
		}
		foreach($this->switches AS $switch) {
			if($switch->getName() == $name) {
				return $switch;
			}
		}
		return false;
	}

    function getDeviceByPeerId($peerId) {
		foreach($this->valves AS $valve) {
			if($valve->getPeerId() == $peerId) {
				return $valve;
			}
		}
		foreach($this->envSensors AS $sensor) {
			if($sensor->getPeerId() == $peerId) {
				return $sensor;
			}
		}
		foreach($this->pwrSensors AS $sensor) {
			if($sensor->getPeerId() == $peerId) {
				return $sensor;
			}
		}
		foreach($this->switches AS $switch) {
			if($switch->getPeerId() == $peerId) {
				return $switch;
			}
		}
		return false;
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

    function getPwrSensorByName($name) {
		foreach($this->pwrSensors AS $sensor) {
			if($sensor->getName() == $name) {
				return $sensor;
			}
		}
		return false;
	}

	function getPwrSensorByPeerId($peerId) {
		foreach($this->pwrSensors AS $sensor) {
			if($sensor->getPeerId() == $peerId) {
				return $sensor;
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
                        "type" => "valve",
                        "batteryLow" => $valve->isBatteryLow(),
                        "batteryVoltage" => $valve->getBatteryVoltage());
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
                        "tempFallWindow" => $valve->getTempFallWindow(),
                        "batteryLow" => $valve->isBatteryLow(),
                        "batteryVoltage" => $valve->getBatteryVoltage());
			}
		}
		foreach($this->envSensors AS $sensor) {
			if(!$withState) {
				$devices[] = array( "name" => $sensor->getName(),
						"peerId" => $sensor->getPeerId(),
						"address" => $sensor->getAddress(),
						"typeString" => $sensor->getTypeString(),
                        "type" => "envsensor",
                        "batteryLow" => $sensor->isBatteryLow());
			}
			else {
				$devices[] = array( "name" => $sensor->getName(),
						"peerId" => $sensor->getPeerId(),
						"address" => $sensor->getAddress(),
						"typeString" => $sensor->getTypeString(),
						"type" => "envsensor",
						"tempSensor" => $sensor->getTempSensor(),
                        "humidSensor" => $sensor->getHumidSensor(),
                        "batteryLow" => $sensor->isBatteryLow());
			}
        }
        foreach($this->pwrSensors AS $sensor) {
   			if(!$withState) {
				$devices[] = array( "name" => $sensor->getName(),
						"peerId" => $sensor->getPeerId(),
						"address" => $sensor->getAddress(),
						"typeString" => $sensor->getTypeString(),
                        "type" => "pwrsensor",
                        "batteryLow" => $sensor->isBatteryLow());
			}
			else {
				$devices[] = array( "name" => $sensor->getName(),
						"peerId" => $sensor->getPeerId(),
						"address" => $sensor->getAddress(),
						"typeString" => $sensor->getTypeString(),
                        "type" => "pwrsensor",
                        "enabled" => $sensor->isEnabled(),
                        "power" => $sensor->getPower(),
                        "batteryLow" => $sensor->isBatteryLow());
			}
        }
        foreach($this->switches AS $switch) {
            $devices[] = array( "name" => $switch->getName(),
				"peerId" => $switch->getPeerId(),
				"address" => $switch->getAddress(),
				"typeString" => $switch->getTypeString(),
                "type" => "switch",
                "batteryLow" => $switch->isBatteryLow());
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

    function getEvents() {
        $rpcEvents = $this->events->getEvents();
        $events = array();
        foreach($rpcEvents as $event) {
            $peerName = $this->getDeviceByPeerId($event["PEERID"])->getName();
            $enabled = ($event["ENABLED"] == 1 ? true : false);
            $events[] = array( "peerName" => $peerName,
                "eventId" => $event["ID"],
                "eventType" => $this->events->lookupType($event["TYPE"]),
                "eventTrigger" => $this->events->lookupTrigger($event["TRIGGER"]),
                "eventMethod" => $event["EVENTMETHOD"],
                "peerChannel" => $event["PEERCHANNEL"],
                "peerVariable" => $event["VARIABLE"],
                "eventMethodParams" => join($event["EVENTMETHODPARAMS"],", "),
                "eventEnabled" => $enabled,
            );
        }

        return $events;
    }

    function triggerEvent($eventId) {
        return $this->events->triggerEvent($eventId);
    }

    function runscript($script, $params) {
		return $this->XMLRPC->send("runScript", array($script, $params, false));
    }

    function getPrometheusStats(){
        $devices = $this->getAllDevices(true);
        $data = array();
        $result = "";
        foreach($devices AS $device) {
            switch($device["type"]) {
            case "valve":
                $data["valve"]["temp"][$device["name"]] = $device["tempSensor"];
                $data["valve"]["targettemp"][$device["name"]] = $device["targetTemp"];
                $data["valve"]["valve"][$device["name"]] = $device["valveState"];
                $data["valve"]["battery"][$device["name"]] = $device["batteryVoltage"];
                break;
            case "envsensor":
                $data["envsensor"]["temp"][$device["name"]] = $device["tempSensor"];
                $data["envsensor"]["humidity"][$device["name"]] = $device["humidSensor"];
                break;
            case "pwrsensor":
                $data["pwrsensor"]["power"][$device["name"]] = $device["power"];
                $data["pwrsensor"]["enabled"][$device["name"]] = $device["enabled"];
                $sensor = $this->getPwrSensorByName($device["name"]);
                $data["pwrsensor"]["voltage"][$device["name"]] = $sensor->getVoltage();
                $data["pwrsensor"]["current"][$device["name"]] = $sensor->getCurrent();
                $data["pwrsensor"]["energyCounter"][$device["name"]] = $sensor->getEnergyCounter();
                break;
            }
        }
        foreach($data AS $type => $device) {
            if($type == "envsensor" || $type == "valve") {
                $result .= sprintf("# TYPE homematic_%s_temp gauge\n", $type);
                foreach($device["temp"] AS $name => $temp) {
                    $result .= sprintf("homematic_%s_temp{name=\"%s\"} %.2f\n", $type, $name, $temp);
                }
            }
            if($type == "envsensor") {
                $result .= sprintf("# TYPE homematic_%s_humidity gauge\n", $type);
                foreach($device["humidity"] AS $name => $humidity) {
                   $result .= sprintf("homematic_%s_humidity{name=\"%s\"} %d\n", $type, $name, $humidity);
                }
            }
            if($type == "valve") {
                $result .= sprintf("# TYPE homematic_%s_state gauge\n", $type);
                foreach($device["valve"] AS $name => $state) {
                   $result .= sprintf("homematic_%s_state{name=\"%s\"} %s\n", $type, $name, $state);
                }
                $result .= sprintf("# TYPE homematic_%s_target gauge\n", $type);
                foreach($device["targettemp"] AS $name => $target) {
                   $result .= sprintf("homematic_%s_target{name=\"%s\"} %d\n", $type, $name, $target);
                }
                $result .= sprintf("# TYPE homematic_%s_battery gauge\n", $type);
                foreach($device["battery"] AS $name => $state) {
                   $result .= sprintf("homematic_%s_battery{name=\"%s\"} %s\n", $type, $name, $state);
                }
            }
            if($type == "pwrsensor") {
                $result .= sprintf("# TYPE homematic_%s_enabled gauge\n", $type);
                foreach($device["enabled"] AS $name => $enabled) {
                    $result .= sprintf("homematic_%s_enabled{name=\"%s\"} %b\n", $type, $name, $enabled);
                }
                $result .= sprintf("# TYPE homematic_%s_power gauge\n", $type);
                foreach($device["power"] AS $name => $power) {
                    $result .= sprintf("homematic_%s_power{name=\"%s\"} %s\n", $type, $name, $power);
                }
                $result .= sprintf("# TYPE homematic_%s_current gauge\n", $type);
                foreach($device["current"] AS $name => $current) {
                    $result .= sprintf("homematic_%s_current{name=\"%s\"} %s\n", $type, $name, $current);
                }
                $result .= sprintf("# TYPE homematic_%s_voltage gauge\n", $type);
                foreach($device["voltage"] AS $name => $voltage) {
                    $result .= sprintf("homematic_%s_voltage{name=\"%s\"} %s\n", $type, $name, $voltage);
                }
                $result .= sprintf("# TYPE homematic_%s_energycounter counter\n", $type);
                foreach($device["energyCounter"] AS $name => $energyCounter) {
                    $result .= sprintf("homematic_%s_energycounter{name=\"%s\"} %s\n", $type, $name, $energyCounter);
                }
            }

        }
        return $result;
    }

}


?>
