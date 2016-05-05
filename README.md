Wallabag v2: A TT-Rss to Wallabag v 2.x plugin - v .01
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

3. Getting the Oauth Token

     Generate an Oath client id and client secret in Wallabag to use with TT-Rss, and simply fill in the details in the pref's dialogue in TTRSS for this plugin.



# Helpfull Links:

* [Official TT-RSS Plugin Documentation](https://tt-rss.org/gitlab/fox/tt-rss/wikis/Plugins)
* [Official Wallabag Documentation](http://doc.wallabag.org/en/v2/)
* [Wallabag on GitHub](https://github.com/wallabag/wallabag)
* [Wallabag Home Page](https://www.wallabag.org/)
