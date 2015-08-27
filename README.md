<img src="https://tas2580.net/downloads/image-11.png" width="600" height="80" alt="phpBB 3.1 - SEO URLs" />

This is an extension for the phpBB forums software. You need an instalation of phpBB 3.1.x to use this.

DESCRIPTION
-------
Changes the URLs of forums and topics too more SEO friendly URLs with the title of the forums and topics in it. This
extension is held as simple as possible, it contains no ACP, you can just install it and use the SEO friendly URLs.

/viewforum.php?f=1 becomes /forum-title-f1/

/viewtopic.php?f=1&t=2 becomes /forum-title-f1/topic-title-t2.html

You can access the XML sitemap under {YOUR_BOARD_URL}/seositemap.xml

INSTALLATION
----------
To install this extension download it from here and upload the files in your forum under <b>/ext/tas2580/seourls</b>.
After that go to the Admin panel of your forum and navigate in to Customise -> Extension Management -> Extensions. Search this extension in the list of extensions and click on Enable.

Also you need to add the rewrite rules from the htaccess.txt file included in this extension to your .htaccess file.
Open your .htacces and find <code>RewriteEngine on</code> right after this add the complete content of the htaccess.txt

If your forum is under domain.tld/forum you also need to change <code>RewriteBase /</code> to <code>RewriteBase /forum</code>


SUPPORT
-------
You can get support for this extension on <a href="https://www.phpbb.com/community/viewtopic.php?f=456&t=2288486">phpbb.com</a>
or in german on <a href="https://www.phpbb.de/community/viewtopic.php?f=149&t=233380">phpbb.de</a>. For more informations look at
<a href="https://tas2580.net/downloads/download-11.html">my Website</a>.

LICENSE
-------
<a href="http://opensource.org/licenses/gpl-2.0.php">GNU General Public License v2</a>

[![Build Status](https://travis-ci.org/tas2580/seourls.svg?branch=master)](https://travis-ci.org/seourls)
