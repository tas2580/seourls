# phpBB SEO URLs

This is an extension for the phpBB forums software. You need an instalation of <a href="https://github.com/phpbb/phpbb">phpBB</a> 3.1.x or 3.2.x to use this extension.

[![Download](https://raw.githubusercontent.com/tas2580/privacyprotection/master/.github/button_download.png)](https://tas2580.net/downloads/phpbb-seo-url/) [![Donate](https://raw.githubusercontent.com/tas2580/privacyprotection/master/.github/button_donate.png)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=S8PXGAJZP9GWN)


## DESCRIPTION
Changes the URLs of forums and topics too more SEO friendly URLs with the title of the forums and topics in it. This
extension is held as simple as possible, it contains no ACP, you can just install it and use the SEO friendly URLs.

* `/viewforum.php?f=1 becomes /forum-title-f1/`
* `/viewtopic.php?f=1&t=2 becomes /forum-title-f1/topic-title-t2.html`


## INSTALLATION
To install this extension download it from here and upload the files in your forum under <b>/ext/tas2580/seourls</b>.
After that go to the Admin panel of your forum and navigate in to Customise -> Extension Management -> Extensions. Search this extension in the list of extensions and click on Enable.

### URL Rewriting
See: https://github.com/tas2580/seourls/wiki/Webserver-configuration

## SUPPORT
You can get support for this extension on <a href="https://www.phpbb.com/community/viewtopic.php?f=456&t=2288486">phpbb.com</a>
or in german on <a href="https://www.phpbb.de/community/viewtopic.php?f=149&t=233380">phpbb.de</a>. For more informations look at
<a href="https://tas2580.net/downloads/phpbb-seo-url/">my Website</a>.

## Old phpBB Versions
### phpBB 2.0.x
<a href="https://github.com/tas2580/seourls/archive/phpBB-2.0.x.zip">Download ZIP</a> | 
<a href="https://github.com/tas2580/seourls/tree/phpBB-2.0.x">View Branch</a>

### phpBB 3.0.x
<a href="https://github.com/tas2580/seourls/archive/phpBB-3.0.x.zip">Download ZIP</a> | 
<a href="https://github.com/tas2580/seourls/tree/phpBB-3.0.x">View Branch</a>

## LICENSE
<a href="http://opensource.org/licenses/gpl-2.0.php">GNU General Public License v2</a>

## Automated Testing
We use automated unit tests to prevent regressions. Check out our travis build below:

[![Build Status](https://travis-ci.org/tas2580/seourls.svg?branch=master)](https://travis-ci.org/tas2580/seourls)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tas2580/seourls/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tas2580/seourls/?branch=master)
