<?php

class HomeMaticEvents {
    private $XMLRPC;
    private $events = array();

    public $types = array ( "Triggered" => 0,
        "Timed" => 1);
    public $triggers = array( "Unchanged" => 1,
        "Changed" => 2,
        "Greater" => 3,
        "Less" => 4,
        "GreaterOrUnchanged" => 5,
        "LessOrUnchanged" => 6,
        "Updated" => 7,
        "Value" => 8,
        "NotValue" => 9,
        "GreaterThanValue" => 10,
        "LessThanValue" => 11,
        "GreaterOrEqualValue" => 12,
        "LessOrEqualValue" => 13);



	function HomeMaticEvents ($xmlrpc) {
		$this->XMLRPC = $xmlrpc;
	}

	function getEvents() {
		return $this->XMLRPC->send("listEvents", array());
    }

    function addEvent($peer, $channel, $id, $variable, $trigger, $script, $scriptParams) {
        $params = array( "TYPE" => 0,
            "ID" => $id,
            "PEERID" => $peer,
            "PEERCHANNEL" => $channel,
            "VARIABLE" => $variable,
            "TRIGGER" => $trigger,
            "TRIGGERVALUE" => true,
            "EVENTMETHOD" => "runScript",
            "EVENTMETHODPARAMS" => array($script, $scriptParams));
        print_r($this->XMLRPC->send("addEvent",$params));
    }

    function delEvent($id) {
        print_r($this->XMLRPC->send("removeEvent",$id));
    }

    function lookupType($type) {
        return array_search($type, $this->types);
    }

    function lookupTrigger($trigger) {
        return array_search($trigger, $this->triggers);
    }

}

?>
