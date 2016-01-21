# homegear-web
simple homegear web interface with additional scripts

Use this simple web interface to display HomeMatic valves (HM-CC-RT-DN) and temperature sensors (HM-WDS40-TH-I-2) paired with a homegear installation. Coming from raintpl and jquery-mobile, I switched to Slim/Twig and Bootstrap/jQuery.
Additionally, it stores data collected from valves and sensors in RRD files and displays graphs. There is also a simple notification option for abnormal events (e.g. high temperature) via Pushover service.

# requirements
* tested on Ubuntu 14.04 LTS (trusty)
* homegear (tested against 0.5.24) - https://www.homegear.eu/index.php/Main_Page
* Webserver (tested against lighttpd)
* PHP (tested against 5.5 from Ubuntu)
 * PHP Modules: curl, json, rrd, xmlrpc, pear

# installation
This is somewhat untested, as I have not done any other setups than my own homebox so far.
* install the requirements as mentioned above - I will not go into detail here about the homegear setup
* since my scripts use the XMLRPC API, homegear does not nessecarily have to be installed on the same system. However, the scripts currently rely on the XML RPC Client shipped with homegear (/var/lib/homegear/scripts/HM-XMLRPC-Client/Client.php)
* clone this repository to /var/www (or any other convenient location) and configure your webserver to point to the public/ sub-directory

 # rrd graphs
 * you need to setup two cronjobs to have this working:
 ```
*/1 * * * * php /path/to/homegear-web/scripts/graphing.php
*/5 * * * * php /path/to/homegear-web/scripts/creategraphs.php
 ```
 # pushover
 * you need a working pushover account for this to work (doh!)
 * create an application and note down the application token as well as your personal user key
 * rename ```config/pushover-config.php.dist``` to ```config/pushover-config.php``` and configure token/user key
 * add a cronjob for the notify script:
 ```
*/15 * * * * php /path/to/homegear-web/scripts/notify.php
 ```
 
# TODO
homegear-web is far away from being complete. Several features are even outright broken (aka have not been finished yet). But for the most part, they are just missing in the frontend. Here is an incomplete list:
* graphs should be drawn 'on demand'
* it is not possible view a custom timespan for a graph (currently only daily, weekly, monthly)
* store tempsets (time schedules for temperature settings) on valves - there is some experimental work here in the backend but that's about it

# Disclaimer
homegear-weg is developed for personal use only. As I am not a professional developer, the code is rather...messy. However, if you have anything to contribute or improve, feel free to submit a pull request :-)
