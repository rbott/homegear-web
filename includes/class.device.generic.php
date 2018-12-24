<?php

class HomeMaticGenericDevice {
	protected $XMLRPC;
	protected $address;
	protected $channels;
	protected $peerId;
	protected $typeString;
	protected $name;
	protected $hasBattery = true;
	protected $lowBattery = false;
	protected $links = array();

	function __construct($address, $channels, $xmlrpc) {
		$this->XMLRPC = $xmlrpc;
		$this->address = $address;
		$this->channels = $channels;
		$peerId = $this->XMLRPC->send("getPeerId",array(1,$address));
		$this->peerId = $peerId[0];
		$peerData = $this->XMLRPC->send("getDeviceDescription", array(intval($this->peerId),0));
		$this->typeString = $peerData["PARENT_TYPE"];
		$this->name = $this->XMLRPC->send("getName", array(intval($this->peerId)));

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

	function getLinks() {
		return $this->links;
	}

	function hasLinks() {
		if(count($this->links) > 0) return true;
		else return false;
	}

	function isBatteryLow() {
		if($this->hasBattery) {
			$this->lowBattery = $this->XMLRPC->send("getValue", array(intval($this->peerId), 0, "LOWBAT", false));
		}
		return $this->lowBattery;
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
