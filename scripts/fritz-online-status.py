#!/usr/bin/python
from fritzconnection import FritzConnection 
import argparse
import ConfigParser
import sys
import redis
import time

# output debug information
def debugPrint(message):
    if args.debug:
        print message

# handle arguments
parser = argparse.ArgumentParser(description="Check if given MACs or IPs are online")
parser.add_argument("--config", help="Path the config file", required=True)
parser.add_argument("--mac", nargs="+", help="Give one or multiple MACs to search")
parser.add_argument("--ip", nargs="+", help="Give one or multiple IPs to search")
parser.add_argument("--no-redis", action="store_true")
parser.add_argument("--debug", action="store_true")
args = parser.parse_args()

# read config
config = ConfigParser.RawConfigParser()
config.read(args.config)
fritzHost = config.get("fritzbox", "host")
fritzUser = config.get("fritzbox", "username")
fritzPass = config.get("fritzbox", "password")
redisHost = config.get("redis", "host")
redisPort = config.get("redis", "port")

# init redis
if not args.no_redis:
    debugPrint("Connecting to Redis")
    r = redis.StrictRedis()

# init fritzbox connection
debugPrint("Connecting to FritzBox")
con = FritzConnection(address=fritzHost, user=fritzUser, password=fritzPass)
macs = []
ips = []
for i in range(1,5):
    try:
        wlanInfo = con.call_action('WLANConfiguration:' + str(i), 'GetInfo')
    except KeyError:
        continue
    if wlanInfo['NewStatus'] == "Up":
        wlanSSID = con.call_action('WLANConfiguration:' + str(i), 'GetSSID')
        wlanAssoc = con.call_action('WLANConfiguration:' + str(i), 'GetTotalAssociations')
        debugPrint("Found SSID '" + wlanSSID['NewSSID'] + "' (" + str(wlanAssoc['NewTotalAssociations']) + " Clients)")
        for k in range(0, wlanAssoc['NewTotalAssociations']):
            clientInfo = con.call_action('WLANConfiguration:' + str(i), 'GetGenericAssociatedDeviceInfo', NewAssociatedDeviceIndex=k)
            macs.append(clientInfo['NewAssociatedDeviceMACAddress'])
            ips.append(clientInfo['NewAssociatedDeviceIPAddress'])
            debugPrint(" " + clientInfo['NewAssociatedDeviceIPAddress'] + " (" + clientInfo['NewAssociatedDeviceMACAddress'] + ")")

macMatchCount = 0
ipMatchCount = 0

if isinstance(args.mac, list):
    macMatchCount = len(set(macs).intersection(args.mac))
    debugPrint(str(macMatchCount) + " MACs have matched")
if isinstance(args.ip, list):
    ipMatchCount = len(set(ips).intersection(args.ip))
    debugPrint(str(ipMatchCount) + " IPs have matched")

if macMatchCount > 0 or ipMatchCount > 0:
    if not args.no_redis:
        tstamp = str(int(time.time()))
        debugPrint("Storing status with key 'online-" + tstamp + "' in redis")
        r.set('online-' + tstamp,'1',1800)
    debugPrint("At least one of the given addresses is currently online")
    sys.exit(0)
else:
    if args.debug:
        debugPrint("None of the given addresses are currently online")
    sys.exit(1)

