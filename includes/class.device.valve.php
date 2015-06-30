<?php

class HomeMaticValve {
	private $XMLRPC;
	private $address;
	private $channels;
	private $peerId;
	private $name;
	private $tempSensor;
	private $valveState;
	private $controlMode;
	private $targetTemp;
	private $batteryState;
	private $tempFallMode;
	private $tempFallValue;
	private $tempFallTemp;
	private $tempFallWindow;

	private $controlModes = array("auto", "manual", "party", "boost");
	private $tempFallModes = array("inactive","auto","auto_manual","auto_party","active");

	private $lastParamsetUpdate = 0;

	function HomeMaticValve ($address, $channels, $xmlrpc) {
		$this->XMLRPC = $xmlrpc;
		$this->address = $address;
		$this->channels = $channels;
		$peerId = $this->XMLRPC->send("getPeerId",array(1,$address));
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
		$this->tempSensor = $this->XMLRPC->send("getValue", array(intval($this->peerId), 4, "ACTUAL_TEMPERATURE", false));
		return $this->tempSensor;
	}

	function getValveState() {
		$this->valveState = $this->XMLRPC->send("getValue", array(intval($this->peerId), 4, "VALVE_STATE", false));
		return $this->valveState;
	}

	function getControlMode() {
		$this->controlMode = $this->XMLRPC->send("getValue", array(intval($this->peerId), 4, "CONTROL_MODE", false));
		return $this->controlModes[$this->controlMode];
	}

	function setControlMode($mode) {
		switch($mode) {
		case 'auto':
			print_r($this->XMLRPC->send("setValue", array(intval($this->peerId), 4, "AUTO_MODE", true)));
			sleep(1);
			break;
		case 'manual':
			print_r($this->XMLRPC->send("setValue", array(intval($this->peerId), 4, "MANU_MODE", true)));
			sleep(1);
			break;
		case 'boost':
			print_r($this->XMLRPC->send("setValue", array(intval($this->peerId), 4, "BOOST_MODE", true)));
			sleep(1);
			break;
		default:
			echo "setControlMode(): unknown value for \$mode. Use 'auto', 'manual' or 'boost'\n";
		}
	}

	function getTempFallMode() {
		$this->updateParameters();
		return $this->tempFallModes[$this->tempFallMode];
	}

	function getTempFallValue() {
		$this->updateParameters();
		return $this->tempFallValue;
	}

	function getTempFallTemp() {
		$this->updateParameters();
		return $this->tempFallTemp;
	}

	function getTempFallWindow() {
		$this->updateParameters();
		return $this->tempFallWindow;
	}

	function getTargetTemp() {
		$this->targetTemp = $this->XMLRPC->send("getValue", array(intval($this->peerId), 4, "SET_TEMPERATURE", false));
		return $this->targetTemp;
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

	function updateParameters() {
		if(time() - $this->lastParamsetUpdate > 4) {
			$params = $this->getParamset(0,"MASTER");
			$this->lastParamsetUpdate = time();
			$this->tempFallMode = $params["TEMPERATUREFALL_MODUS"];
			$this->tempFallValue = $params["TEMPERATUREFALL_VALUE"];
			$this->tempFallTemp = $params["TEMPERATUREFALL_WINDOW_OPEN"];
			$this->tempFallWindow = $params["TEMPERATUREFALL_WINDOW_OPEN_TIME_PERIOD"];
		}
	}
}

?>
