<?php
require_once("lib/class.baselistener.php");

class Listener extends BaseListener {
	function on_message($user, $message) {
		if($user['name'] != $this->talker->user['name']) $this->talker->send_message("We got a message from {$user['name']}: $message");
	}
	
	function on_join($user) {
		$this->talker->send_message("Heya {$user['name']}, so nice of you to grace us with your presence");
	}
}
?>