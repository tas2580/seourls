# phpBB SEO URLs

This is an extension for the phpBB forums software. You need an instalation of <a href="https://github.com/phpbb/phpbb">phpBB 3.1.x</a> to use this extension.

## DESCRIPTION
Changes the URLs of forums and topics too more SEO friendly URLs with the title of the forums and topics in it. This
extension is held as simple as possible, it contains no ACP, you can just install it and use the SEO friendly URLs.

* `/viewforum.php?f=1 becomes /forum-title-f1/`
* `/viewtopic.php?f=1&t=2 becomes /forum-title-f1/topic-title-t2.html`


## INSTALLATION
To install this extension download it from here and upload the files in your forum under <b>/ext/tas2580/seourls</b>.
After that go to the Admin panel of your forum and navigate in to Customise -> Extension Management -> Extensions. Search this extension in the list of extensions and click on Enable.

### URL Rewriting
The extension modifies the links that are outputed by the forum. So you need to rewrite the new links to working URLs.

#### Apache
Open your .htacces and find <code>RewriteEngine on</code> right after this add the following code:
```
RewriteBase /

RewriteRule ^(.*)-f([0-9]*)/(.*)-t([0-9]*)-s([0-9]*).html viewtopic.php?f=$2&t=$4&start=$5&%{QUERY_STRING} [L]
RewriteRule ^(.*)-f([0-9]*)/(.*)-t([0-9]*).html viewtopic.php?f=$2&t=$4&%{QUERY_STRING} [L]
RewriteRule ^(.*)-f([0-9]*)/index-s([0-9]*).html viewforum.php?f=$2&start=$3&%{QUERY_STRING} [L]
RewriteRule ^(.*)-f([0-9]*)/ viewforum.php?f=$2&%{QUERY_STRING} [L]
RewriteRule ^(.*)-f([0-9]*) viewforum.php?f=$2&%{QUERY_STRING} [L]
```
If your forum is under domain.tld/forum you also need to change <code>RewriteBase /</code> to <code>RewriteBase /forum</code>

#### ngnix
Open your `/etc/nginx/nginx.conf` and add the following code to your VHost configuration.
```
location / {
	rewrite ^/(.*)-f([0-9]*)/(.*)-t([0-9]*)-s([0-9]*).html /viewtopic.php?f=$2&t=$4&start=$5&$query_string last;
	rewrite ^/(.*)-f([0-9]*)/(.*)-t([0-9]*).html /viewtopic.php?f=$2&t=$4&$query_string last;
	rewrite ^/(.*)-f([0-9]*)/index-s([0-9]*).html /viewforum.php?f=$2&start=$3&$query_string last;
	rewrite ^/(.*)-f([0-9]*)/ /viewforum.php?f=$2&$query_string last;
	rewrite ^/(.*)-f([0-9]*) /viewforum.php?f=$2&$query_string last;
}
```

#### Lighttpd
Open your `/etc/lighttpd/lighttpd.conf` and add the following code to your VHost configuration.
```
url.rewrite-once = (
	"/(.*)-f([0-9]*)/(.*)-t([0-9]*)-s([0-9]*).html(\?(.*))?"	=> "/viewtopic.php?f=$2&t=$4&start=$5&$7",
	"/(.*)-f([0-9]*)/(.*)-t([0-9]*).html(\?(.*))?"				=> "/viewtopic.php?f=$2&t=$4&$6",
	"/(.*)-f([0-9]*)/index-s([0-9]*).html(\?(.*))?"				=> "/viewforum.php?f=$2&start=$3&$5",
	"/(.*)-f([0-9]*)/(\?(.*))?"									=> "/viewforum.php?f=$2&$4",
)
```

#### Caddy Webserver
Open your `Caddyfile` and add the following code to your VHost configuration.
```
rewrite {
		regexp /(.*)-f([0-9]*)/(.*)-t([0-9]*)-s([0-9]*).html(\?(.*))?
		to /viewtopic.php?f={2}&t={4}&start={5}&{7}
}
rewrite {
		regexp /(.*)-f([0-9]*)/(.*)-t([0-9]*).html(\?(.*))?
		to /viewtopic.php?f={2}&t={4}&{6}
}
rewrite {
		regexp /(.*)-f([0-9]*)/index-s([0-9]*).html(\?(.*))?
		to /viewforum.php?f={2}&start=${3}&{5}
}
rewrite {
		regexp /(.*)-f([0-9]*)/(\?(.*))?
		to /viewforum.php?f={2}&{4}
}
```
## SUPPORT
You can get support for this extension on <a href="https://www.phpbb.com/community/viewtopic.php?f=456&t=2288486">phpbb.com</a>
or in german on <a href="https://www.phpbb.de/community/viewtopic.php?f=149&t=233380">phpbb.de</a>. For more informations look at
<a href="https://tas2580.net/downloads/phpbb-seo-url/">my Website</a>.

## LICENSE
<a href="http://opensource.org/licenses/gpl-2.0.php">GNU General Public License v2</a>

## Automated Testing
We use automated unit tests to prevent regressions. Check out our travis build below:

[![Build Status](https://travis-ci.org/tas2580/seourls.svg?branch=master)](https://travis-ci.org/seourls)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tas2580/seourls/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tas2580/seourls/?branch=master)
