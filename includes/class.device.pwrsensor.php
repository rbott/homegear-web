<?php

class HomeMaticPwrSensor {
	private $XMLRPC;
	private $address;
	private $channels;
	private $peerId;
	private $typeString;
	private $name;
	private $batteryState;
	private $enabled;
    private $voltage;
    private $frequency;
    private $power;
    private $current;
    private $energyCounter;

	function HomeMaticPwrSensor ($address, $channels, $xmlrpc) {
		$this->XMLRPC = $xmlrpc;
		$this->address = $address;
		$this->channels = $channels;
		$peerId = $this->XMLRPC->send("getPeerId",array(1,$address));
		$this->peerId = $peerId[0];
		$peerData = $this->XMLRPC->send("getDeviceDescription", array(intval($this->peerId),0));
		$this->typeString = $peerData["PARENT_TYPE"];
		$name = $this->XMLRPC->send("getDeviceInfo", array(intval($this->peerId),array('NAME')));
		$this->name = $name["NAME"];
	}

	function getName() {
		return $this->name;
	}

	function getAddress() {
		return $this->address;
	}

	function getPeerId() {
		return $this->peerId;
	}

	function getTypeString() {
		return $this->typeString;
	}

	function isEnabled() {
		$this->enabled = boolval($this->XMLRPC->send("getValue", array(intval($this->peerId), 1, "STATE", false)));
		return $this->enabled;
	}

	function getVoltage() {
		$this->voltage = $this->XMLRPC->send("getValue", array(intval($this->peerId), 2, "VOLTAGE", false));
		return $this->voltage;
    }

	function getFrequency() {
		$this->frequency = $this->XMLRPC->send("getValue", array(intval($this->peerId), 2, "FREQUENCY", false));
		return $this->frequency;
	}

	function getCurrent() {
		$this->current = $this->XMLRPC->send("getValue", array(intval($this->peerId), 2, "CURRENT", false));
		return $this->current;
	}

    function getPower() {
        $this->isEnabled() ? $this->power = $this->XMLRPC->send("getValue", array(intval($this->peerId), 2, "POWER", false)) : $this->power = 0;
		return $this->power;
	}

	function getEnergyCounter() {
		$this->energyCounter = $this->XMLRPC->send("getValue", array(intval($this->peerId), 2, "ENERGY_COUNTER", false));
		return $this->energyCounter;
    }

    function enable() {
		$this->XMLRPC->send("setValue", array(intval($this->peerId), 1, "STATE", true));
    }

    function disable() {
        $this->XMLRPC->send("setValue", array(intval($this->peerId), 1, "STATE", false));
    }

    function togglePower() {
        if($this->isEnabled()) {
            $this->disable();
        }
        else {
            $this->enable();
        }
    }

	function setTargetTemp($temp) {
		print_r($this->XMLRPC->send("setValue", array(intval($this->peerId), 4, "SET_TEMPERATURE", $temp)));
		sleep(1);
	}

	function getBatteryState() {
		$this->batteryState = $this->XMLRPC->send("getValue", array(intval($this->peerId), 4, "BATTERY_STATE", false));
		return $this->batteryState;
	}

	function getParamset($channel = 0, $type = "VALUES") {
		return $this->XMLRPC->send("getParamset", array(intval($this->peerId), $channel, $type));
	}

	function setParamset($paramset, $channel = 0, $type = "VALUES") {
		$this->XMLRPC->send("putParamset", array(intval($this->peerId), $channel, $type, $paramset));
		sleep(1);
	}

}

?>
