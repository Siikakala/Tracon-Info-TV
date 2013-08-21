## Tracon Info-Manager (aka. Höylä) running on Kohana PHP Framework, version 3.3 (release)

Tracon Info-Manager is the information system used during the event, which contains several tools for conventions. Mainly developed for [Tracon](http://tracon.fi). Uses lots of ajax and therefore works best with Google Chrome/Chromium (doesn't work at all with IE, some things doesn't work as should or are not performing fast enough with Firefox and Opera).

Some features:
* Info-TV (slideshow)
	* Every frontend (browser which shows the slideshow) is individually controllable
	* Several simultaneous slideshows
	* Integrated video player (can play rtmp-streams)
	* Big scroller with several individual pieces and separate slideshow section
* Log book with regex search and hotkeys
* Production planning (minute schedule)
* SMS sending and receiving (uses [Nexmo](http://nexmo.com) and [Gearman](http://gearman.org/))
* Dashboard for seeing everything with one glance
* Chat, for internal conversations. Uses websockets and server relays messages between chat and irc. The server side piece: https://github.com/Siikakala/Websocket-IRC-relay

System is using [Kohana](http://kohanaframework.org/) PHP framework.


Comments, some variable names and commit messages are in Finnish. Sorry about that.


One little note about SMS sending: you can start the worker with `$ ./minion worker` but **DO NOT** start more than one. Nexmo limits the sending speed to 5 messages per second and the worker tries to be slow enought to not be throttled. With multiple workers some individual messages could be sent multiple times needlessly and Nexmo will certainly throttle the sending.