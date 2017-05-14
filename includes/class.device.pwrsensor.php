<?php

class HomeMaticPwrSensor extends HomeMaticGenericDevice {
	private $enabled;
    private $voltage;
    private $frequency;
    private $power;
    private $current;
    private $energyCounter;

    function __construct($address, $channels, $xmlrpc) {
        parent::__construct($address, $channels, $xmlrpc);
        $this->hasBattery = false;
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

}

?>
