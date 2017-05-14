<?php

class HomeMaticSwitch extends HomeMaticGenericDevice {
	private $enabled;
    private $voltage;
    private $frequency;
    private $power;
    private $current;
    private $energyCounter;

    function __construct($address, $channels, $xmlrpc) {
        parent::__construct($address, $channels, $xmlrpc);
	}

}

?>
