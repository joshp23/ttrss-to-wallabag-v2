Wallabag v2: A TT-Rss to Wallabag v 2.x plugin
=====================
A Wallabag v 2.x plugin for Tiny-Tiny-RSS designed to login via Oauth and work with the Wallabag v 2.x api.

### Installing the plugin:
1. Clone this repo or just grab the [latest release](https://github.com/joshp23/ttrss-to-wallabag-v2/releases/latest) and extract the wallabag_v2 folder into the `plugins.local` folder of ttrss:  
2. Install PHP Curl
	```
	sudo apt-get install php-curl
	```
3. Get the Oauth Token/Configuration
	* In Wallabag: Create a new Oath client in the Developer tab, take note of the client id and client secret.
	* In TT-RSS: Enable the plugin and simply fill in the details in the Wallabag V2 Preferences dialogue.

	Special Note: Do not add trailing slashes to any URLs in either the Wallabag or TT-RSS settings or you will get nothing but 404 responses!
4. Enjoy 1-click posting to Wallabag!

### TODO ... which may or not actually ever get done...
1. Fine tune error messages (currently somewhat possible by uncommenting debug lines in `init.php`)
2. Add tag support
3. Add hotkey support
4. Add colour changing button

### Helpfull Links:
* [Official TT-RSS Plugin Documentation](https://tt-rss.org/gitlab/fox/tt-rss/wikis/Plugins)
* [Official Wallabag Documentation](http://doc.wallabag.org/en/v2/)
* [Wallabag on GitHub](https://github.com/wallabag/wallabag)
* [Wallabag Home Page](https://www.wallabag.org/)

### Credits
Code for this project exists because of:

* [fxneumann's OneClickPocket plugin for TTRSS](https://github.com/fxneumann/oneclickpocket)
* [xppppp's Wallabag v1 plugin for TTRSS](https://github.com/xppppp/ttrss-wallabag-plugin)
