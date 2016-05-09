Wallabag v2: A TT-Rss to Wallabag v 2.x plugin - v 1.0
=====================

A Wallabag v 2.x plugin for Tiny-Tiny-RSS designed to login via Oauth and work with the Wallabag v 2.x api.

### Installing the plugin:

1. Download this repo and extract the wallabag_v2 folder into the plugins.local folder of your ttrss:  

2. Restart the application for changes to take place.  

	If you're running something like php-fpm:

	```bash
	sudo service php5-fpm restart
	```
     Optionally just restart your webserver. In apache:  
     
	```bash
	sudo service apache2 restart
	```

3. Getting the Oauth Token/Configuration

     In Wallabag: Create a new Oath client in the Developer tab, take note of the client id and client secret.
     In TT-Rss: Enable the plugin and simply fill in the details in the Wallabag V2 Preferences dialogue.
     	
     	Special Note: Do not add a trailing slash to any URL (either Wallabag or TT-RSS) in any setting or you will get nothing but 404 responses!

4. Enjoy posting directly to Wallabag with 1-click

### ToDo ... which may or not actually ever get done...

1. Enable use of the refresh token
2. Fine tune error messages
3. Add tag support
4. Add hotkey support
5. Add colour changing button

### Helpfull Links:

* [Official TT-RSS Plugin Documentation](https://tt-rss.org/gitlab/fox/tt-rss/wikis/Plugins)
* [Official Wallabag Documentation](http://doc.wallabag.org/en/v2/)
* [Wallabag on GitHub](https://github.com/wallabag/wallabag)
* [Wallabag Home Page](https://www.wallabag.org/)

### Credits

Code for this project exists because of:

* [fxneumann's OneClickPocket plugin for TTRSS](https://github.com/fxneumann/oneclickpocket)
* [xppppp's Wallabag v1 plugin for TTRSS](https://github.com/xppppp/ttrss-wallabag-plugin)
