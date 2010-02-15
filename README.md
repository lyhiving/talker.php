# Talker PHP Client
A real-time [Talker](http://talkerapp.com) PHP client. Wraps the [Talker protocol](https://talker.tenderapp.com/faqs/api/talker-protocol) and provides methods both to talk, and to listen in on a room with event callbacks.

# Requirements

The PEAR [Net_Socket](http://pear.php.net/net_socket) package is used for socket communication. Make sure you have PEAR installed, available in your include_path, and that the Net_Socket package is also installed.

# For simple talking

1) Get a Talker account at https://talkerapp.com/signup

2) Get your Talker Token on https://myaccount.talkerapp.com/settings

3) Connect to a room and talk:

	$talker = new Talker();
	$talker->connect("Room name","Talker token");
	$talker->send_message("Greetings");

# To listen

1) Create a listener class that extends BaseListener.

2) Provide a method for any of the events you want to handle.

3) Tell Talker to start listening, and provide your listener object:

	$listener = new MyListener();
	$talker->listen($listener);

# Examples

See exampleclient.php and examplelistener.php.
	
# License

Released under the MIT license.
	
# Credits

Thanks to http://github.com/macournoyer/ for Talker.rb. Was helpful in getting this written.