=== Wiki Embed ===
Contributors: enej, ejackisch, ctlt-dev, ubcdev
Tags: mediawiki, wiki, wiki-embed, embed, content framework, wiki inc, 
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 1.4.4

Wiki Embed lets you embed mediawiki pages in to your site, sites like Wikipedia

== Description ==

The Wiki Embed's intent is to help create a (http://wiki.ubc.ca/Resource_Management_Framework "Resourse Management Framework").

It tries its damn hardest to pull in the content from a MediaWiki page into the WordPress environment. After html scraping the content from the MediaWiki page using a special url. ( note: try adding '?action=render' to the end of to any MediaWiki url) it strips out unwanted content and adds some tabs if you so desire. 


To contribute to this plugin 
Start here:
https://github.com/ubc/wiki-embed


== Frequently Asked Questions ==

= Could you use this to replicate the whole of Wikipedia = 

Maybe, but why would you want to? That is not what the tool was designed to do. 

== Installation ==

1. Upload `wiki-embed` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Change the wiki embed settings to your liking

== Screenshots ==

1. A page that list all the wiki content that is embedded on the site. 
2. A look at the wiki embed settings page. 
3. A way to embed a media wiki page inside your site. 


== Changelog ==
= 1.4.4 =
* allowing to display object and param tags so that we can embed kultura videos

= 1.4.3 =
* wiki_embed cache now not auto loaded.
* better escaped content that is coming from the the wiki
* removed unwanted site_options 
* bug fix: UTF encoding

= 1.4.2 =
* deploy script didn't quite work trying with a different user

= 1.4.1 =
* Bug fix: Prevents the DOMDocument parser from giving PHP Warnings if its given bad HTML from the source page

= 1.4 =
* Enables ability to split article into accordion (via jQuery UI)
* Rewrote function that parses/renders html to no longer use Simple HTML DOM parser as it uses a lot of resources 
* Updated caching: Now if the remote page can't be accessed, cached content will be served if it exists, even if it is past its expiry date.
* More caching: Now if the cache expires the page will show the cached content one last time and refresh it immediately after. This means the user should never really face delays for the wiki content is to be retrieved and parsed.
* Fixed overlay view
* The refresh of the page happens after the page has been set to the browser. 
* Skipping version 1.3 since there are a lot of change in version 1.4

= 1.2.4 =
* Bug fix: Causes errors on 404 pages. Links stop working. Now fixed
* removed un-necessary files

= 1.2.3 =
* Settings page bug fix, wasn't working properly. 
* Always ignore files that link to pdf documents, 
* convert &amp; to & 

= 1.2.2 = 
* removed unnecessary javascript files
* add files for css and js on admin side
* lots of bug fixes 
* better url recognition
* bug fix for table of content anchor linking 

= 1.2.1 = 
* bug fix
* #toc links weren't ignored properly 
* force styles that might not be present


= 1.2 = 
* Added a security feature that only allows certain sites to be wiki embedable in your site.
* Bug fixes, TOC is not hijacked by js any more and is treated as an internal link, update the overlay to use HTML5
* allow default a get parameter to be passed to the url. so you can do stuff like [wiki-embed url='http://en.wikipedia.org/wiki/%page%' get=page default_get=WordPress]
* and now if you go to the page that has that shortcode and pass in a ?page=English in the url
* it will embed the http://en.wikipedia.org/wiki/English instead of the default http://en.wikipedia.org/wiki/WordPress
* allow for plan html view by going to the {siteurl}/wp-admin/admin-ajax.php?url={encoded_url}&action=wiki_embed&plain_html=1
* this make the wiki embed act like a scraper.


= 1.1 = 
* Bug fix will display the admin overlay again, this bug occurs only when wiki embed is network activated 


= 1.0 = 
* removed not needed code 
* improved setting menu navigation 
* improved Wiki Embed List | added the ability to see embeds that don't have a target url easier
* bug fix. embedded images are not conceded as internal links any more 
* bug fix. editor can now save settings. Added email to setting to which notifications are sent to if a visitor stumbles on a link that is not part of the navigation. 
* improvement easier to add target urls to wiki embeds. 
* version bumped up to 1.0 
* ajaxurl changed to wiki_embed_ajaxurl 
* version bumped up to 1.0

= 0.9.1 = 
* Pagination added 
* Icons added 
* How the content gets saved and stored improved. For example if you are quering the same wiki contetent only you will only request the content from the wiki once.
* Default Settings added
* This is the version before that is undergoing verification testing


= 0.9 =
* Is the pre production release, please help us test it.
 

