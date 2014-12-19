<?php

include_once('includes/homematic.php');



switch($_POST["method"]) {
	case "updateValve":
		if(is_numeric($_POST["peerId"])) {
			$home = new HomeMaticInstance;
			if($valve = $home->getValveByPeerId($_POST["peerId"])) {
				switch($_POST["mode"]) {
				case "auto":
					if($valve->getControlMode() != "auto") {
						$valve->setControlMode("auto");
					}

					break;
				case "manual":
					if($valve->getControlMode() != "manual") {
						$valve->setControlMode("manual");
					}

					if($valve->getTargetTemp() <> floatval($_POST["targetTemp"])) {
						$valve->setTargetTemp(floatval($_POST["targetTemp"]));
					}
					break;
				}

				Header("Location: index.php#peer" . $_POST["peerId"]);
				exit;
			}
			else {
				echo "Error: Valve not found!\n";
			}
		}
		else {
			echo "Error: invalid Peer ID\n";
		}
		break;
	case "do_nothing":
		break;
	default:
		Header("Location: index.php");
}

?>
