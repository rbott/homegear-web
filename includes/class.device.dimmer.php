<?php

class HomeMaticDimmer extends HomeMaticGenericDevice {
	private $enabled;
	private $level;

	function __construct($address, $channels, $xmlrpc) {
		parent::__construct($address, $channels, $xmlrpc);
		$this->hasBattery = false;
	}

	function isEnabled() {
		$data = floatval($this->XMLRPC->send("getValue", array(intval($this->peerId), 1, "LEVEL", false)));
		if($data > 0) {
			$this->enabled = true;
		}
		else {
			$this->enabled = false;
		}
		return $this->enabled;
	}

	function enable() {
		$this->XMLRPC->send("setValue", array(intval($this->peerId), 1, "LEVEL", 1.0, true));
	}

	function disable() {
		$this->XMLRPC->send("setValue", array(intval($this->peerId), 1, "LEVEL", 0.0 , true));
	}

	function getLevel() {
		$this->level = floatval($this->XMLRPC->send("getValue", array(intval($this->peerId), 1, "LEVEL", false)));
		return $this->level;
	}

	function setLevel($level) {
		$this->XMLRPC->send("setValue", array(intval($this->peerId), 1, "LEVEL", floatval($level), true));
	}

	function togglePower() {
		if($this->isEnabled()) {
			$this->disable();
		}
		else {
			$this->enable();
		}
	}

	function toggleLevel($level) {
		if($this->isEnabled() && $this->getLevel() == $level) {
			$this->disable();
		}
		else {
			$this->setLevel($level);
		}
	}
}

?>
