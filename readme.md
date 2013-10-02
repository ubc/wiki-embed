=== Wiki Embed ===
Contributors: enej, ejackisch, devindra, ctlt-dev, ubcdev
Tags: mediawiki, wiki, wiki-embed, embed, content framework, wiki inc, 
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 1.4.7

Wiki Embed lets you embed mediawiki pages in to your site, sites like Wikipedia

== Description ==


This plugin will pull content from any Media Wiki website (Such as wikipedia.org).

It strips and reformats the content, allowing you to supply some arguments to dictate how this works.

**How does it work?**
On your WordPress page or post. You embed a shortcode, something like 
 
`[wiki-embed url='http://en.wikipedia.org/wiki/WordPress' tabs no-contents no-infobox ]`

Once you save the page you will have the content of the wiki embed into you page. Kind of like a youtube video. 
Only the page will look like as if it is part of your site. Any changes that are made on the wiki will be reflected on your site, once the cache has expired and a new version of the page is requested from the wiki. 

**Why would you want to do that?**
You can build a better navigation structure to your site, while crowd sourcing the content of the pages inside the wiki. 
Win Win. 

** Where can I get more info?**
Checkout our Support page http://support.cms.ubc.ca/cms-manual/adding-content/embedding-content-from-the-ubc-wiki/ that is using the wiki-embed plugin to grab content from our [wiki http://wiki.ubc.ca/Documentation:UBC_Content_Management_System/CLF_Theme/How_to_embed_content_from_the_UBC_Wiki



== Frequently Asked Questions ==

= Could you use this to replicate the whole of Wikipedia = 

Maybe, but why would you want to? That is not what the tool was designed to do. 

= Images are now showing up properly =
This might have to do with your MediaWiki Install make sure its set in such a way that allows you to embed images from the media wiki into another site. 

= Dark background WordPress themes =
Sorry, but at the moment this plugin works well with themes that have a white background. 

To make it work with a dark background you need to change your css to add rules that will make it work with your theme.

= How do I import wiki pages into my site =
This plugin is not a wiki importer. It helps mirror wiki pages inside your WordPress site.

= 

== Usage ==

Wiki Embed is implemented using the shortcode [wiki-embed]. It accepts the following arguments:
* url: (required) the web address of the wiki article that you want to embed on this page.
* no-edit: Hide the "edit" links from the wiki.
* no-contents: Hide the page's contents box.
* no-infobox: Hide any infobox that appears on the wiki for this page.
* tabs: Replaces the sections of the wiki article with tabs.
* accordion: Replaces the sections of the wiki article with an accordian. This option cannot be used as the same time as 'tabs'.

Example;
`[wiki-embed url="http://en.wikipedia.org/wiki/Example" no-edit no-contents no-infobox accordion]`


== Configuration ==

Settings for the plugin can be found in 'Wiki Embed' -> 'Settings'.
Here you can enable/disable various features, define shortcode defaults, and configure some global settings for the plugin.


== Installation ==

1. Upload `wiki-embed` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Change the wiki embed settings to your liking


== Screenshots ==

1. A page that list all the wiki content that is embedded on the site. 
2. A look at the wiki embed settings page. 
3. Click on this ICON to get a Model window that will help you embed the site.
4. A way to embed a media wiki page inside your site. 

== Changelog ==
= 1.4.7 =
* Security update. 

= 1.4.6 =
* user bug please update.

= 1.4.5 =
* wordpress search queries will now also search wiki-embedded content.

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
* removed unneeded code 
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
* Default Settings added
* This is the version before that is undergoing verification testing

= 0.9 =
* Is the pre production release, please help us test it.
 
 