<?php

class HomeMaticEnvSensors extends HomeMaticGenericDevice {
	private $tempSensor;
	private $humidSensors;

	function __construct($address, $channels, $xmlrpc) {
		parent::__construct($address, $channels, $xmlrpc);
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
