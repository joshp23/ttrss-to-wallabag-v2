Wallabag v2: A TT-Rss to Wallabag v 2.x plugin - v 1.0
=====================

Wallabag v 2.x plugin for Tiny-Tiny-RSS

This plugin is designed to login via Oauth work with the Wallabag v 2.x api.

# Installing the plugin:

1. Copy this repo into the plugins.local folder of your ttrss:

     cp /path/to/ttrss-wallabag-plugin /path/to/tinyrss/plugins.local/wallabag_v2

2. You'll also need to restart the application for changes to take place. 
If you're running something like php-fpm:

     sudo service php5-fpm restart

Optionally just restart your webserver. In apache:

    	sudo service apache2 restart

3. Getting the Oauth Token

Generate an Oath client id and client secret in Wallabag to use with TT-Rss, and simply fill in the details in the pref's dialogue in TTRSS for this plugin.



# Helpfull Links:

* [Official TT-RSS Plugin Documentation](https://tt-rss.org/gitlab/fox/tt-rss/wikis/Plugins)
* [official Wallabag Documentation](http://doc.wallabag.org/en/v2/)
* [Wallabag on GitHub](https://github.com/wallabag/wallabag)
* [Wallabag Home Page](https://www.wallabag.org/)
