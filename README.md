# Installation

1. To install the server `git clone` this repo to any private directory.
2. Navigate to the directory and run `composer install`. Make sure you meet all requirements requested by the process.
3. Double check that you have the [Sockets](http://php.net/manual/bg/book.sockets.php) extension enabled in your PHP configuration and that your machine supports PHP 7+ in CLI mode.
4. Make sure PHP has write permissions for the `cache` directory.

# Configuring

The server can be configured by editing the values in `config.php`. Just follow the instructions in the comments.

# Running

To run the server open up your terminal and run `php index.php`. By default the server will try to bind on `127.0.0.1:4000`, you can change the values in `config.php`.

# Demonstration

Here's a short video demonstration on installing and running the server.

The first time the server is run it takes about 3 seconds in the video, but in reality it took a minute and a half. The second time the server is run it's in real speed.

[![Telegram Server Demonstration](http://i3.ytimg.com/vi/UJEQy7Ugcso/maxresdefault.jpg)](https://youtu.be/UJEQy7Ugcso)