<?php

class HomeMaticValve extends HomeMaticGenericDevice {
	private $tempSensor;
	private $valveState;
	private $controlMode;
	private $targetTemp;
	private $tempFallMode;
	private $tempFallValue;
	private $tempFallTemp;
	private $tempFallWindow;
	private $batteryVoltage;

	private $controlModes = array("auto", "manual", "party", "boost");
	private $tempFallModes = array("inactive","auto","auto_manual","auto_party","active");

	private $lastParamsetUpdate = 0;

	function __construct($address, $channels, $xmlrpc) {
		parent::__construct($address, $channels, $xmlrpc);
	}

	function getBatteryVoltage() {
		$this->batteryVoltage = $this->XMLRPC->send("getValue", array(intval($this->peerId), 4, "BATTERY_STATE", false));
		return $this->batteryVoltage;
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
			print_r($this->XMLRPC->send("setValue", array(intval($this->peerId), 4, "AUTO_MODE", true, true)));
			usleep(500);
			break;
		case 'manual':
			print_r($this->XMLRPC->send("setValue", array(intval($this->peerId), 4, "MANU_MODE", true, true)));
			usleep(500);
			break;
		case 'boost':
			print_r($this->XMLRPC->send("setValue", array(intval($this->peerId), 4, "BOOST_MODE", true, true)));
			usleep(500);
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
		$this->targetTemp = $this->XMLRPC->send("getValue", array(intval($this->peerId), 4, "SET_TEMPERATURE", false, true));
		return $this->targetTemp;
	}

	function setTargetTemp($temp) {
		print_r($this->XMLRPC->send("setValue", array(intval($this->peerId), 4, "SET_TEMPERATURE", $temp, true)));
		usleep(500);
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
