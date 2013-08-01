## Tracon Info-Manager (aka. Höylä) running on Kohana PHP Framework, version 3.3 (release)

Tracon Info-Manager is the contime information system, which contains several tools for Cons. Mainly developed for [Tracon](http://tracon.fi). Uses lots of ajax.

Some features:
* Info-TV (slideshow)
	* Every frontend (browser which shows the slideshow) is individually controllable
	* Several simultaneous slideshows
	* Integrated video player (can play rtmp-streams)
	* Big scroller with several individual pieces and separate slideshow section
* Log book with regex search and hotkeys
* Production planning (minute schedule)
* SMS sending and receiving (uses [Nexmo](http://nexmo.com))
* Dashboard for seeing everything with one glance
* Chat, for internal conversations. Uses websockets and server relays messages between chat and irc. The server side piece: https://github.com/Siikakala/Websocket-IRC-relay

System is using [Kohana](http://kohanaframework.org/) PHP framework.
