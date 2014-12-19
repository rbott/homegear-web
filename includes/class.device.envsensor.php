<?php

class HomeMaticEnvSensors {
	private $XMLRPC;
	private $address;
	private $channels;
	private $peerId;
	private $name;
	private $tempSensor;
	private $humidSensors;
	private $batteryState;

	function HomeMaticEnvSensors ($address, $channels, $xmlrpc) {
		$this->XMLRPC = $xmlrpc;
		$this->address = $address;
		$this->channels = $channels;
		$peerId = $this->XMLRPC->send("getPeerId",$address);
		$this->peerId = $peerId[0];
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
