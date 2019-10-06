<?php

class HomeMaticSwitch extends HomeMaticGenericDevice {

	function __construct($address, $id, $type, $name, $xmlrpc) {
		parent::__construct($address, $id, $type, $name, $xmlrpc);
		$this->hasBatteryState = true;
	}
}

?>
