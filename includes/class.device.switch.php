<?php

class HomeMaticSwitch {
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

	function HomeMaticSwitch ($address, $channels, $xmlrpc) {
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

	function getBatteryState() {
		$this->batteryState = $this->XMLRPC->send("getValue", array(intval($this->peerId), 4, "BATTERY_STATE", false));
		return $this->batteryState;
	}

	function getParamset($channel = 0, $type = "VALUES") {
		return $this->XMLRPC->send("getParamset", array(intval($this->peerId), $channel, $type));
	}

	function setParamset($paramset, $channel = 0, $type = "VALUES") {
		$this->XMLRPC->send("putParamset", array(intval($this->peerId), $channel, $type, $paramset));
		usleep(500);
	}

}

?>
