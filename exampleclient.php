<?php
require("lib/class.talker.php");
require("examplelistener.php");

$talker = new Talker();
$listener = new Listener();

$talker->connect("Room name","Talker token");
$talker->send_message("Greetings");
$talker->listen($listener);
?>