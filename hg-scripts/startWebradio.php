<?php

function getApi($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://volumio.local/" . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch); 
    curl_close($ch); 
    return json_decode($output);
}


$state = getApi("api/v1/getstate");
if(is_object($state)) {
    if($state->status == "play" && $state->service == "webradio") {
        getApi("api/v1/commands/?cmd=stop");
    }
    else {
        getApi("api/v1/commands/?cmd=playplaylist&name=Webradio");
    }
}

?>
