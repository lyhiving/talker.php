<?php
/*
 * This is a real-time PHP client for the Talker group chat application (talkerapp.com).
 * 
 * Make sure you have PEAR installed (http://pear.php.net), in your include_path, and that
 * the Net_Socket package is installed (http://pear.php.net/net_socket).
 *
 * @package Talker.php
 * @author Joseph Szobody <jszobody@gmail.com>
 */
require_once("Net/Socket.php");

class Talker {
	private $socket;
	private $listener;

	private $host = "talkerapp.com";
	private $port = 8500;
	private $timeout = 10;
	
	private $connected = false;
	private $user;
	private $users = array();
	
	function __construct() {
		$this->socket = new Net_Socket();
	}
	
	public function connect($room = "Main", $token) {
		if(!is_object($this)) {
			$talker = new Talker();
			return $talker->connect($room, $token);
		}
		
		// Connect to server
		if(PEAR::isError($this->socket->connect($this->host, $this->port, false, $this->timeout))) {
			Throw new Exception("Unable to connect");
			return false;
		}
		
		$this->socket->enableCrypto(true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
		$this->socket->setBlocking(false);
		
		// Authenticate
		$auth = array("type" => "connect", "room" => $room, "token" => $token);
		if(PEAR::isError($this->send($auth))) {
			throw new Exception("Unable to send authentication request");
			return false;
		}
		
		$result = json_decode($this->socket->readLine(), true);
		
		if($result['type'] == 'error') {
			// Oh yay. Hope someone is catching these exceptions.
			throw new Exception("Error authenticating: " . $result['message']);
		} else if($result['type'] == 'connected') {
			$this->connected = true;
			$this->user = $result['user'];
			
			// Awesome, we're in. Now get the list of users.
			$result = json_decode($this->socket->readLine(), true);
			if($result['type'] == 'users') {
				foreach($result['users'] AS $user) {
					$this->users[$user['name']] = $user['id'];
				}
			}
		} else {
			throw new Exception("Unexpected response from server: " . print_r($result,true));
		}
	}
	
	public function send_message($message) {
		if(!$this->connected) throw new Exception("Not connected");
		
		$message = array("type" => "message", "content" => $message);
		if(PEAR::isError($result = $this->send($message))) {
			throw new Exception("Unable to send message: " . $result["message"]);
			return false;
		}
		return true;
	}
	
	public function send_private_message($user_name, $message) {
		if(!$this->connected) throw new Exception("Not connected");
		if(!$this->users[$user_name]) throw new Exception("Invalid user");
		
		$message = array("type" => "message", "content" => $message, "to" => $this->users[$user_name]['id']);
		if(PEAR::isError($result = $this->send($message))) {
			throw new Exception("Unable to send message: " . $result["message"]);
			return false;
		}
		return true;
	}
	
	public function close() {
		$this->socket->disconnect();
	}
	
	public function leave() {
		$this->send(array("type" => "close"));
		$this->close();
	}
	
	public function user() {
		return $this->user;
	}
	
	public function users() {
		return $this->users;
	}

	public function listen($listener) {
		if(!($listener instanceof BaseListener)) throw new Exception("Invalid listener");
		$listener->setTalker($this);
		
		$this->listener = $listener;
		$this->socket->setTimeout($this->timeout, 0);
		
		while($this->connected) {
			$this->socket->setBlocking(false);
			$result = json_decode($this->socket->readLine(), true);
			
			if($this->socket->eof()) {
				$this->connected = false;
				return false;
			}
			
			if(is_array($result)) $this->incoming($result);
			
			$this->ping();
		}
	}
	
	private function ping() {
		if(!$this->connected) return false;
		$this->send(array("type" => "ping"));
	}
	
	
	private function send($data) {
		return $this->socket->writeLine(json_encode($data));
	}
	
	private function incoming($result) {
		$this->listener->on_event($result);
		switch($result['type']) {
			case 'message':
				if($result['private']) $this->listener->on_private_message($result['user'],$result['content']);
				else $this->listener->on_message($result['user'],$result['content']);
				break;
			case 'users':
				foreach($results['users'] AS $user) {
					$this->users[$user['name']] = $user['id'];
				}
				@$this->listener->on_presence($result['users']);
				break;
			case 'join':
				@$this->listener->on_join($result['user']);
				break;
			case 'idle':
				@$this->listener->on_idle($result['user']);
				break;
			case 'back':
				@$this->listener->on_back($result['back']);
				break;
			case 'leave':
				@$this->listener->on_leave($result['user']);
				break;
		
			return true;
		}
	}
}
?>