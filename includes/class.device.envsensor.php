<?php

class HomeMaticEnvSensors extends HomeMaticGenericDevice {
	private $tempSensor;
	private $humidSensors;

	function __construct($address, $id, $type, $name, $xmlrpc) {
		parent::__construct($address, $id, $type, $name, $xmlrpc);
		$this->hasBatteryState = true;
	}

	function getTempSensor() {
		$this->tempSensor = $this->XMLRPC->send("getValue", array(intval($this->peerId), 1, "TEMPERATURE", false));
		return $this->tempSensor;
	}

	function getHumidSensor() {
		$this->humidSensor = $this->XMLRPC->send("getValue", array(intval($this->peerId), 1, "HUMIDITY", false));
		return $this->humidSensor;
	}

}

?>
