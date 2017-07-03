<?php


class nadClient {
    private $retries = 0;
    private $retrylog = false;
    private $url = "";

    function __construct($url, $retries = 0, $retrylog = false) {
        $this->url = $url;
        $this->retries = $retries;
        $this->retrylog = $retrylog;
    }

    function apiCall($url,$method = "GET") {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

        $output = curl_exec($ch); 

        $retry = 0;
        if($this->retrylog) $log = fopen($this->retrylog, "a");
        else $log = false;

        while((curl_errno($ch) == 7 || curl_errno($ch) == 28 ) && $retry < $this->retries){
            sleep(4);
            if($log) fputs($log, date("Y-m-d H:i:s") . " - Curl to '" . $url . "' failed (Error " . curl_errno($ch) . "), retrying... (attempt " . $retry . ")\n");
            $output = curl_exec($ch);
            $retry++;
        }
        if($log) fclose($log);
        curl_close($ch); 
        return json_decode($output);

    }

    function getValue($command) {
        return $this->apiCall($this->url . "nad/c368/v1.0/Main/" . $command);
    }

    function setValue($command,$value) {
        return $this->apiCall($this->url . "nad/c368/v1.0/Main/" . $command . "/" . $value, "PUT");
    }

    function getModel() {
        $data = $this->getValue("model");
        if($data->error == 0) return $data->value;
    }

    function getPower() {
        $data = $this->getValue("power");
        if($data->error == 0) return $data->value;
    }

    function getSource() {
        $data = $this->getValue("source");
        if($data->error == 0) return $data->value;
    }

    function getVolume() {
        $data = $this->getValue("volume");
        if($data->error == 0) return $data->value;
    }

    function setPower($state) {
        $data = $this->setValue("power", $state);
        sleep(1);
        if($data->error == 0) return $data->value;
    }

    function setSource($source) {
        $data = $this->setValue("source", $source);
        if($data->error == 0) return $data->value;
    }

    function setVolume($volume) {
        $data = $this->setValue("volume", $volume);
        if($data->error == 0) return $data->value;
    }

}
?>
