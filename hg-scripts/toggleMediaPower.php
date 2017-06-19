<?php

$BASE_PATH = realpath(dirname(__FILE__));
include_once($BASE_PATH . '/../includes/homematic.php');
include_once($BASE_PATH . '/../includes/nad.php');
include_once($BASE_PATH . '/../includes/volumio.php');


$home = new HomeMaticInstance;
if($media = $home->getPwrSensorByName("Pwr-Media")) {
    if($media->isEnabled()) {
        $nad = new nadClient("http://volumio.local:3333/");
        $nad->setPower("Off");
        sleep(1);
        $media->disable();
    }
    else {
        $media->enable();
        $nad = new nadClient("http://volumio.local:3333/", 30, "/tmp/curl.log");
        $nad->setPower("On");
        $nad->setSource("4");
        $nad->setVolume("-40");

        $vol = new volumioClient("http://volumio.local/", 30, "/tmp/curl.log");
        $retries = 0;
        $vol->startWebRadio();
        # the volumio API is somewhat slow on picking up status updates
        sleep(5);
        while(!$vol->isWebRadioPlaying() && $retries < 10) {
            $vol->startWebRadio();
            sleep(5);
            $retries++;
        }
    }
}

?>
