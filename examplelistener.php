<?php
require_once("lib/class.baselistener.php");

class Listener extends BaseListener {
	function on_message($user, $message) {
		echo "We got a message from {$user['name']}: $message\n";
	}
}

?>