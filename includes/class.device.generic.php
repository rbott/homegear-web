<?php

class HomeMaticGenericDevice {
	protected $XMLRPC;
	protected $address;
	protected $peerId;
	protected $typeString;
	protected $name;
	protected $rssi;
	protected $hasBatteryState = false;
	protected $lowBattery = false;
	protected $batteryVoltage = 0.0;
	protected $links = array();

	function __construct($address, $id, $type, $name, $xmlrpc) {
		$this->log("Constructing new class for " . $address);
		$startTime = microtime(true);
		$this->XMLRPC = $xmlrpc;
		$this->address = $address;
		$this->peerId = $id;
		$this->typeString = $type;
		$this->name = $name;

		$elapsedTime = microtime(true) - $startTime;
		$this->log(sprintf("Finished constructing new class for %s (took: %fs)", $address, $elapsedTime));
	}

	function log($line) {
		$time = sprintf("%f", microtime(true));
		$fp = @fopen("/tmp/homematic.log","a");
		if($fp) {
			fputs($fp, $time . " " . $line . "\n");
			fclose($fp);
		}
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

	function getRssi() {
		$this->rssi = $this->lowBattery = $this->XMLRPC->send("getValue", array(intval($this->peerId), 0, "RSSI_DEVICE", false));
		return $this->rssi;
	}

	function getLinks() {
		$links = $this->XMLRPC->send("getLinks",array($this->peerId));
		if(!empty($links)) {
			foreach($links AS $link) {
				$this->links[] = array(
					"receiverName" => $this->XMLRPC->send("getName", array($link["RECEIVER_ID"])),
					"receiverId" => $link["RECEIVER_ID"],
					"receiverChannel" => $link["RECEIVER_ID"],
					"senderName" => $this->XMLRPC->send("getName", array($link["SENDER_ID"])),
					"senderId" => $link["SENDER_ID"],
					"senderChannel" => $link["SENDER_ID"]
				);
			}
		}
		return $this->links;
	}

	function hasLinks() {
		if(count($this->links) > 0) return true;
		else return false;
	}

	function hasBattery() {
		return $this->hasBatteryState;
	}

	function isBatteryLow() {
		if($this->hasBattery()) {
			$this->lowBattery = $this->XMLRPC->send("getValue", array(intval($this->peerId), 0, "LOWBAT", false));
		}
		return $this->lowBattery;
	}

	function getBatteryVoltage() {
		if($this->hasBattery()) {
			$rpcReturn = $this->XMLRPC->send("getValue", array(intval($this->peerId), 4, "BATTERY_STATE", false));
			if(is_float($rpcReturn)) {
				$this->batteryVoltage = floatval($rpcReturn);
			}
		}
		return $this->batteryVoltage;
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
