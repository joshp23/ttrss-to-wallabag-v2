Wallabag v2: A TT-Rss to Wallabag v 2.x plugin - v 1.0
=====================

Wallabag v 2.x plugin for Tiny-Tiny-RSS

This plugin is designed to login via Oauth work with the Wallabag v 2.x api.

# Installing the plugin:

Copy this repo into the plugins.local folder of your ttrss:

1.     cp /path/to/ttrss-wallabag-plugin /path/to/tinyrss/plugins.local/wallabag

You'll also need to restart the application for changes to take place. 
If you're running something like php-fpm:

2.a     sudo service php5-fpm restart

Optionally just restart your webserver. In apache:

2.b	sudo service apache2 restart

# Getting the Oauth Token

First we need to generate an Oath token in Wallabag to use with TT-Rss



# Helpfull Links:

* [Official TT-RSS Plugin Documentation](https://tt-rss.org/gitlab/fox/tt-rss/wikis/Plugins)
* [official Wallabag Documentation](http://doc.wallabag.org/en/v2/)
* [Wallabag on GitHub](https://github.com/wallabag/wallabag)
* [Wallabag Home Page](https://www.wallabag.org/)
