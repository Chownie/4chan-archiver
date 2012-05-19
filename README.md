4chan-archiver
==============
GNU public license 3 blah blah blah, can't be bothered to add the text and stuff in here. If you use/modify this just give credit to the github.


Lets create your own little 4chan archive, without needing to use crappy advert ridden websites! (or overly worked on perl scripts, this is 4 hours work)


Requires:

PHP 4+

MySQL

Server that supports cronjobs (or some other kind of scheduling device)


Installation:

Import chanarchive.sql into some database

Setup config.php with your paths and mysql info

Add a cronjob to /usr/bin/php -f /path/to/cron.php (might not be /usr/bin/php, check with your server admin)

Have fun!


Todo:

AJAX everything up

Parse messages/users/emails and have them in the DB (instead of just copying the raw html from 4chan)


Any bugs? Post on the github!


https://github.com/emoose/4chan-archiver