<?php

include_once('includes/homematic.php');
include_once('includes/rain.tpl.class.php');

$home = new HomeMaticInstance;

$tempset["regular"] = new tempsetInstance;
$tempset["regular"]->addTemp("07:00",19,$tempset["regular"]->presetWorkday);
$tempset["regular"]->addTemp("09:00",17,$tempset["regular"]->presetWorkday);
$tempset["regular"]->addTemp("18:00",19,$tempset["regular"]->presetWorkday);
$tempset["regular"]->addTemp("23:00",17,$tempset["regular"]->presetWorkday);

$tempset["regular"]->addTemp("08:00",20,$tempset["regular"]->presetWeekend);
$tempset["regular"]->addTemp("11:00",17,$tempset["regular"]->presetWorkday);

$tempset["nothome"] = new tempsetInstance;
$tempset["nothome"]->addTemp("01:00",17);

raintpl::$tpl_dir = "tpl/";
raintpl::$cache_dir = "/tmp/";
raintpl::$base_url = "/";
raintpl::$path_replace = true;

$tpl = new raintpl();

$tpl->assign("deviceList",$home->getAllDevices(true));

$tpl->draw('index');

?>
