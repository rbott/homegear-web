<?php

$BASE_PATH = realpath(dirname(__FILE__));
include_once($BASE_PATH . '/../includes/homematic.php');
include_once($BASE_PATH . '/../includes/nad.php');
include_once($BASE_PATH . '/../includes/volumio.php');

$longopts = array(
    "enable-power",
    "disable-power",
    "source:",
    "start-webradio",
    "stop-volumio-playback"
);

$opts = getopt("", $longopts);

$home = new HomeMaticInstance;
$nad = new nadClient("http://volumio.local:3333/", 30);

if($media = $home->getPwrSensorByName("Pwr-Media")) {
    if(array_key_exists("enable-power", $opts)) {
        $media->enable();
        $nad->setPower("On");
        if(array_key_exists("source", $opts)) {
            switch($opts["source"]) {
            case "tv":
                $nad->setSource("1");
                $nad->setVolume("-40");
                break;
            case "volumio":
                $nad->setSource("4");
                $nad->setVolume("-40");
                break;
            case "phono":
                $nad->setSource("5");
                $nad->setVolume("-30");
                break;
            case "bluetooth":
                $nad->setSource("8");
                $nad->setVolume("-40");
                break;
            }
        }
        if(array_key_exists("start-webradio", $opts)) {
            $vol = new volumioClient("http://volumio.local/", 30);
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
        if(array_key_exists("stop-volumio-playback", $opts)) {
            $vol = new volumioClient("http://volumio.local/", 30);
            $vol->startWebRadio();
        }

    }
    elseif(array_key_exists("disable-power", $opts)) {
        $nad->setPower("Off");
        sleep(1);
        $media->disable();
    }
}
?>
