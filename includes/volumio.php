<?php


class volumioClient {
    private $retries = 0;
    private $retrylog = false;
    private $url = "http://volumio.local/";

    function __construct($url = "http://volumio.local/", $retries = 0, $retrylog = false) {
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

    function getState() {
        $state = $this->apiCall($this->url . "api/v1/getstate");
        if(is_object($state)) return $state;
        else return false;
    }

    function isWebRadioPlaying() {
        if($state = $this->getState()) {
            if($state->status == "play" && $state->service == "webradio") {
                return true;
            }
            else return false;
        }
        else return false;
    }

    function stopPlayback() {
        $data = $this->apiCall($this->url . "api/v1/commands/?cmd=stop");
        if(is_object($data)) return $data;
        else return false;
    }

    function startWebRadio() {
        $data = $this->apiCall($this->url . "api/v1/commands/?cmd=playplaylist&name=Webradio");
        if(is_object($data)) return $data;
        else return false;
    }

}
?>
