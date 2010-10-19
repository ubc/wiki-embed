<?php
/*
Plugin Name: Wiki Embed
Plugin URI: 
Description: Enables the inclusion of mediawiki pages into your own blog page or post. Though the use of shortcodes. 
Version: 0.9.1
Author: OLT UBC
Author URI: http://blogs.ubc.ca/oltdev
*/

/*
== Installation ==
 
1. Upload WikiEmbed.zip to the /wp-content/plugins/WikiEmbed/WikiEmbed.php directory
2. Unzip into its own folder /wp-content/plugins/
3. Activate the plugin through the 'Plugins' menu in WordPress by clicking "WikiEmbed"
4. Go to your Options Panel and open the "WikiEmbed" submenu. /wp-admin/options-general.php?page=WikiEmbed.php
*/
 
/*
/--------------------------------------------------------------------\
|                                                                    |
| License: GPL                                                       |
|                                                                    |
| WikiEmbed - embed multiple mediawiki page into your post or page   |
| Copyright (C) 2008, OLT, www.olt.ubc.com                   	     |
| All rights reserved.                                               |
|                                                                    |
| This program is free software; you can redistribute it and/or      |
| modify it under the terms of the GNU General Public License        |
| as published by the Free Software Foundation; either version 2     |
| of the License, or (at your option) any later version.             |
|                                                                    |
| This program is distributed in the hope that it will be useful,    |
| but WITHOUT ANY WARRANTY; without even the implied warranty of     |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
| GNU General Public License for more details.                       |
|                                                                    |
| You should have received a copy of the GNU General Public License  |
| along with this program; if not, write to the                      |
| Free Software Foundation, Inc.                                     |
| 51 Franklin Street, Fifth Floor                                    |
| Boston, MA  02110-1301, USA                                        |   
|                                                                    |
\--------------------------------------------------------------------/
*/


// Add pre as a valid element to TinyMCE with lang and line arguments
// add_filter('tiny_mce_before_init', 'wikiembed_mce_valid_elements', 0);

// Modify the version when tinyMCE plugins are changed.
// add_filter('tiny_mce_version', 'wikiembed_change_tinymce_version');


// load the necessery scripts for the site 
add_action('init','wikiembed_init');

// ajax stuff needed for the overlay
add_action("init","wikiemebed_add_ajax");

// removed the wiki emebed custom field on save. 
add_action('save_post','wikiembed_save_post');

// wiki embed shortcode
add_shortcode('wiki-embed', 'wikiembed_shortcode');
add_action('template_redirect','wikiembed_load_page');


add_filter('page_link','wikiembed_page_link');
require_once("admin/admin-overlay.php");
require_once("admin/admin.php");

/********************************************************************
 * Settings Page Magic  
 *
 *
 ********************************************************************/


// GLOBAL variables 
$wikiembed_options 	= get_option('wikiembed_options'); // wikiemebed options

if(!$wikiembed_options)
	$wikiembed_options = wikiembed_settings();

$wikiembeds 		= get_option('wikiembeds');
$wikiembed_content_count; // wiki content count needed by the shortcode 
$wikiembed_version = 0.8;

/**
 * wikiembed_init function.
 * 
 * load the neccesery styles 
 * @access public
 * @return void
 */
function wikiembed_init()
{
	global $wikiembed_options, $wikiembed_version;
	

	if(!is_admin()): // never display this stuff in the admin 
	
		if($wikiembed_options['tabs']):
		// embed this if tabs enabled 
			wp_enqueue_script( 'wiki-embed-tabs', plugins_url( '/wiki-embed/resources/js/tabs.js'),array("jquery","jquery-ui-tabs"), $wikiembed_version );
			
		endif;
		if($wikiembed_options['tabs-style']):
		// embed this if tabs enabled style
			wp_enqueue_style('wiki-embed-tabs', plugins_url('/wiki-embed/resources/css/tabs.css'),false, $wikiembed_version, 'screen' );
			
		endif;
		switch ($wikiembed_options['wiki-links']){
			case "overlay":
				// embed this if tabs enabled
				wp_enqueue_script( 'colorbox', plugins_url( '/wiki-embed/resources/js/jquery.colorbox-min.js'),array("jquery"), "1.3.6" );
				wp_enqueue_script( 'wiki-embed-overlay', plugins_url( '/wiki-embed/resources/js/overlay.js'),array("colorbox","jquery"), $wikiembed_version );
				wp_enqueue_style( 'wiki-embed-overlay',plugins_url( '/wiki-embed/resources/css/colorbox.css'),false, $wikiembed_version, 'screen');
				wp_localize_script( 'wiki-embed-overlay', 'WikiEmbedSettings', array(
		  		'ajaxurl' => admin_url('admin-ajax.php'),
				));
			break;
			case "new-page":
				wp_enqueue_script( 'wiki-embed-new-page', plugins_url( '/wiki-embed/resources/js/new-page.js'),array("jquery"), $wikiembed_version );
				wp_localize_script( 'wiki-embed-new-page', 'WikiEmbedSettings', array(
		  		'siteurl' => get_bloginfo('siteurl'),
				));
			break;
			default:
		
		}
		if($wikiembed_options['overlay'] ):
		
		endif;
		
		if($wikiembed_options['style']):
		// add some great wiki styling 
			wp_enqueue_style( 'wiki-embed-style',plugins_url( '/wiki-embed/resources/css/wiki-embed.css'),false, $wikiembed_version, 'screen');
			
		endif; 
	
	endif; // end is_admin
	

}

function wikiembed_load_page()
{
	global $wp_query,$wikiembeds,$wikiembed_options ;
	
	if(isset($_GET['wikiembed-url']) && isset($_GET['wikiembed-title'])) :
		
	// do we need to redirect the page ?
	
	$wiki_page_id = esc_url($_GET['wikiembed-url']).",";
	$wiki_page_url = esc_url($_GET['wikiembed-url']);
	if($wikiembed_options['default']['tabs'])
		$wiki_page_id .= "tabs,";
	
	if($wikiembed_options['default']['no-contents'])
		$wiki_page_id .= "no-contents,";
	
	if($wikiembed_options['default']['no-edit'])
		$wiki_page_id .= "no-edit,";
		
	$wiki_page_id = substr($wiki_page_id,0,-1);

	
	if(isset($wikiembeds[$wiki_page_url]['url']))
		wp_redirect(esc_url($wikiembeds[$wiki_page_url]['url']));
	
	// no we have no where to redirect the page to just stay here
		
		$url = esc_url($_GET['wikiembed-url']);
		$title = esc_html($_GET['wikiembed-title']);
		$content = $content = wikiembed_get_wiki_content(	
			$url,
			$wikiembed_options['default']['tabs'],
			$wikiembed_options['default']['no-contents'],
			$wikiembed_options['default']['no-edit'],
			$wikiembed_options['wiki-links'],
			$has_source,
			$remove);
		$wp_query->is_home = false;
		$wp_query->is_page = true;
		
		$wp_query->post_count = 1;
		$post = (object) null;
		$post->ID = 'wiki-embed';
		$post->post_title = $title;
		$post->guid = get_bloginfo('siteurl')."?wikiembed-url=".urlencode($url)."&wikiembed-title=".urlencode($title);
		$post->post_content = $content;
		$post->post_status = "published";
		$post->comment_status = "closed";
		$post->post_modified = date('Y-m-d H:i:s');
		$post->post_excerpt = "excerpt nothing goes here";
		$post->post_parent = 0;
		$post->post_type = "page";
		$post->post_date = date('Y-m-d H:i:s');
		
		$wp_query->posts = array($post);
		
	endif;
	
}


function wikiembed_page_link($url){
	global $post;
		if($post->ID == "wiki-embed")
			$url = $post->guid;
	return $url; 

}


/********************************************************************
 * Shortcode Magic  
 *
 *
 ********************************************************************/

function wikiembed_save_post($post_id) {	
	
	$post = get_post(wp_is_post_revision($post_id));
	
	// start fresh each time you save the post or page
	delete_post_meta($post->ID, "wiki_embed");
	
	return $post_id;
} 

// example usage of the shortcode
// [wiki-embed url="http://" overlay tabs no-edit no-contents ]
/**
 * wikiembed_shortcode function.
 * 
 * @access public
 * @return void
 */
function wikiembed_shortcode($atts)
{
	global $id, $wikiembed_content_count, $wikiembed_options, $wikiembeds;
	
	if(is_null($wikiembed_content_count))
		$wikiembed_content_count = 0;
	
	$wikiembed_content_count++; 
	
	/* url is the unique identifier */
	extract(shortcode_atts(array(
		'url' => NULL,
		'update' => NULL, /* 30 minutes */
		'remove'=>NULL,
	), $atts));
	
	// other possbile attributes
	
	
	
	
	$has_no_edit 	 = ( in_array("no-edit", 	$atts)? true: false );	
	$has_no_contents = ( in_array("no-contents",$atts)? true: false );
	$has_tabs 		 = ( in_array("tabs", 		$atts)? true: false );
	
	// $has_overlay 	 = ( in_array("overlay", 	$atts)? true: false ); // this is just for backwards compatibility 
	// $has_source 	 = ( in_array("source",		$atts)? true: false ); 
	
	$content .= wikiembed_get_wiki_content($url,$has_tabs,$has_no_contents,$has_no_edit,$update,$has_source,$remove);
		
	return $content; 


}

function wikiembed_get_wiki_content($url,$has_tabs,$has_no_contents,$has_no_edit,$update,$has_source=false,$remove=null)
{
	global $wikiembeds,$wikiembed_options,$wikiembed_content_count;
	
	if( !is_numeric($update) || $update < 5)
	$update = $wikiembed_options['wiki-update']; 
	
	// create the unique id 
	$wiki_page_id = esc_url($url).",";
		
	if($has_tabs)
		$wiki_page_id .= "tabs,";
	
	if($has_no_contents)
		$wiki_page_id .= "no-contents,";
	
	if($has_no_edit)
		$wiki_page_id .= "no-edit,";
		
	$wiki_page_id = substr($wiki_page_id,0,-1);
	
	$wiki_page_id_hash  = md5($wiki_page_id); // if we don't md5 the hash we can't really 
	
	// Get any existing copy of our transient data
	if (false === ( $wiki_page_body = get_transient($wiki_page_id_hash ) ) ): 
		
		// lets try to get the  
    	$wiki_page_body  = wp_remote_request_wikipage($url,$update);
    	if(!$wiki_page_body)
    		return '<span class="alert">
						We were not able to Retrieve the content of this page, at this time.<br />
						You can: <br />
						1. Try refreshing the page.<br />
						2. Go to the <a href="'.esc_url($url).'" >source</a><br />
						</span>';
    	

     	// Do we need to modify the content? 
		if( $has_no_edit || $has_no_contents || $has_tabs ): 
			require_once("resources/simple_html_dom.php");
			// $wiki_page_body = apply_filters('the_content', $wiki_page_body);
			
			$html = str_get_html($wiki_page_body);
		
			$remove_elements = explode(",",$remove);
			
			// remove edit links 
			if( $has_no_edit ):
				$remove_elements[] = '.editsection';
			endif; // end of removing links
		
			// remove table of contents 
			if( $has_no_contents ):
				$remove_elements[] = '#toc';
			endif;

			// bonus you can remove any element by passing in a css selected and seperating them by commas
			if(!empty($remove_elements)):
				foreach($remove_elements as $element):
					
					if($element):
						foreach($html->find($element) as $e):
							$e->outertext ='';
						endforeach;	
					$removed_elements[] = $element;
					endif;
					
				endforeach;
			endif; // end of removing of the elements 
						
			// create tabs 
			if( $has_tabs ):
				$index = 0;
			
				$headlines = $html->find("h2 span.mw-headline");
				foreach($headlines as $headline):
					
					$list .= '<li><a href="#fragment-'.$wikiembed_content_count.'-'.$index.'"><span>'.$headline->innertext.'</span></a></li>';
							
					if($index !=0)
						$headline->parent()->outertext = '</div><div id="fragment-'.$wikiembed_content_count.'-'.$index.'"><h2><span class="mw-headline">'.$headline->innertext.'</span></h2>';
						
					$index++;
				endforeach;
					
				$tabs = '<div class="wiki-embed-tabs"><ul class="wiki-embed-tabs-nav">'.$list.'</ul><div id="fragment-'.$wikiembed_content_count.'-0">';
			
				if(isset($list)):
					$headlines[0]->parent()->outertext = $tabs.'<h2><span class="mw-headline">'.$headlines[0]->innertext.'</span></h2>';				
			
					if($headlines)		
					$wiki_embed_end_tabs   .="</div></div>";
				endif;							
			endif; // end of creating tabs 
		
			$wiki_page_body = $html->save();
			$wiki_page_body .= $wiki_embed_end_tabs;
			
		
		endif; // end of content modifications 
			
		if(!empty($removed_elements))
			$remove_att = 	'remove="'.implode(",",$removed_elements).'"';
     	
     	// set the cache
     	$worked = set_transient($wiki_page_id_hash, $wiki_page_body, $update*60);
     	
     	// keep a track of what needed 
     	
     	if( isset($wikiembeds) ):
     		$wikiembeds[$wiki_page_id]['expires_on'] =  time() + ($update * 60);
			update_option( 'wikiembeds', $wikiembeds );
  		else:
    		add_option( 'wikiembeds', $wikiembeds );
    	endif;
		     	
	endif; // end of updating 
	
	// display the source 
	if($has_source || $wikiembed_options['default']['source'] ):
		$source_text = ( isset( $wikiembed_options['default']['pre-source'] ) ? $wikiembed_options['default']['pre-source'] : "source:" ); 
		
			$wiki_embed_end .= '<span class="wiki-embed-source">'.$source_text.' <a href="'.esc_url($url).'">'.esc_url($url).'</a></span>';
	endif;
	
	switch($wikiembed_options['wiki-links']){
		case "overlay":
			$wiki_embed_class .= " wiki-embed-overlay ";
		break;
		
		case "new-page":
			$wiki_embed_class .= " wiki-embed-new-page ";
		break; 
	
	
	}
	// add the overlay	
	if($has_overlay):	
		$wiki_embed_class .= " wiki-embed-overlay ";
	endif;
	

	
	return "<div class='wiki-embed ".$wiki_embed_class."'>".$wiki_page_body."</div>".$wiki_embed_end; 
}
/**
 * wp_remote_request_wikipage function.
 * This function get the content from the url and stores in an transient. 
 * @access public
 * @param mixed $url
 * @param mixed $update
 * @return void
 */
function wp_remote_request_wikipage($url,$update)
{
	global $wikiembeds;
	$wiki_page_id_hash = md5($url);
	// grab the content from the cache
	if (false === ( $wiki_page_body = get_transient( $wiki_page_id_hash ) ) ): 
	
		// else return the 
		$wiki_page = wp_remote_request(wikiembed_action_url($url));
		
		if( !is_wp_error($wiki_page) ):
	    	
	     	$wiki_page_body = $wiki_page['body'];
	     		
	    else:
	     	// an error occured try getting the content again
	     	$wiki_page = wp_remote_request(wikiembed_action_url($url));
	     	
	     	// error occured while fetching content 
	     	if( is_wp_error($wiki_page) ) return false;
	     	$wiki_page_body = $wiki_page['body']; 
	     endif;
     		
     endif;
				
		$wikiembeds[$url]['expires_on'] =  time() + ($update * 60);
		update_option( 'wikiembeds', $wikiembeds );
		
     return $wiki_page_body;
     	

}



/********************************************************************
 * Ajax Magic 
 *
 *
 ********************************************************************/

/**
 * wikiemebed_add_ajax function.
 * 
 * @access public
 * @return void
 */
function wikiemebed_add_ajax()
{
	if ( defined( 'DOING_AJAX' ) ):
		if( is_user_logged_in() ):
			add_action('wp_ajax_wiki_embed', 'wikiembed_overlay_ajax');
		else:
			add_action('wp_ajax_nopriv_wiki_embed', 'wikiembed_overlay_ajax');
		endif;
	endif;
}

/**
 * wikiembed_overlay_ajax function.
 * 
 * This function is what gets dislayed in the overlay
 * @access public
 * @return void
 */
function wikiembed_overlay_ajax() {
	global $wikiembeds,$wikiembed_options;
	$url = wikiembed_action_url($_GET['url']);
	$source_url = esc_url(urldecode($_GET['url']));
	$remove = esc_attr(urldecode($_GET['remove']));
	$title = esc_html(urldecode($_GET['title']));
	
	// constuct 
	$wiki_page_id = esc_url($_GET['wikiembed-url']).",";
		
	if($wikiembed_options['default']['tabs'])
		$wiki_page_id .= "tabs,";
	
	if($wikiembed_options['default']['no-contents'])
		$wiki_page_id .= "no-contents,";
	
	if($wikiembed_options['default']['no-edit'])
		$wiki_page_id .= "no-edit,";
		
	$wiki_page_id = substr($wiki_page_id,0,-1);

	
	$content = wikiembed_get_wiki_content(
			$url,
			$wikiembed_options['default']['tabs'],
			$wikiembed_options['default']['no-contents'],
			$wikiembed_options['default']['no-edit'],
			$wikiembed_options['wiki-links'],
			$has_source,
			$remove);
	
	
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head profile="http://gmpg.org/xfn/11">
<title><?php echo urldecode(esc_attr($_GET['title'])); ?></title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js"></script>
<link media="screen" href="<?php bloginfo('stylesheet_url')?>" type="text/css" rel="stylesheet" >
<link media="screen" href="/<?php echo PLUGINDIR ; ?>/wiki-embed/resources/css/wiki-embed.css" type="text/css" rel="stylesheet" >
<link media="screen" href="/<?php echo PLUGINDIR ; ?>/wiki-embed/resources/css/wiki-overlay.css" type="text/css" rel="stylesheet" >
<script src="/<?php echo PLUGINDIR ; ?>/wiki-embed/resources/js/wiki-embed-overlay.js" ></script>

</head>
<body>
	<div id="wiki-embed-iframe">
	<div class="wiki-embed-source">source: <a target="_top" href="<?php echo $source_url; ?>"><?php echo $source_url; ?></a></div>
	<div class="wiki-embed-content">
	<h1 class="wiki-embed-title" ><?php echo $title; ?></h1>
	<?php 
	echo $content;
	?>
	</div>
	</div>
	</body></html>
	<?php
	die(); // don't need any more help 
}

/********************************************************************
 * Helper Functions 
 *
 *
 ********************************************************************/
/**
 * wikiembed_settings function.
 * 
 * @access public
 * @return void
 */
function wikiembed_settings()
{
	$wikiembed_options['tabs'] = 1;
	$wikiembed_options['style'] = 1;
	$wikiembed_options['tabs-style'] = 1;
	$wikiembed_options['wiki-update'] = "30";
	
	$wikiembed_options['wiki-links'] = "default";
	$wikiembed_options['default']['source'] = 1;
	$wikiembed_options['default']['pre-source'] = "source:";
	
	
	$wikiembed_options['default']['no-contents'] = 1;
	$wikiembed_options['default']['no-edit'] = 1;
	$wikiembed_options['default']['tabs'] = 1;


	return $wikiembed_options;
}

/**
 * wikiembed_action_url function.
 * 
 * helper functoin that converst the url into a url that is more useful.
 * @access public
 * @param mixed $url
 * @return void
 */
function wikiembed_action_url($url)
{
	return http_build_url(esc_url(urldecode($url)), array("query" => "action=render"), HTTP_URL_JOIN_QUERY);
}

/* See http://www.php.net/manual/en/function.http-build-url.php for more details */
if (!function_exists('http_build_url'))
{
	define('HTTP_URL_REPLACE', 1);				// Replace every part of the first URL when there's one of the second URL
	define('HTTP_URL_JOIN_PATH', 2);			// Join relative paths
	define('HTTP_URL_JOIN_QUERY', 4);			// Join query strings
	define('HTTP_URL_STRIP_USER', 8);			// Strip any user authentication information
	define('HTTP_URL_STRIP_PASS', 16);			// Strip any password authentication information
	define('HTTP_URL_STRIP_AUTH', 32);			// Strip any authentication information
	define('HTTP_URL_STRIP_PORT', 64);			// Strip explicit port numbers
	define('HTTP_URL_STRIP_PATH', 128);			// Strip complete path
	define('HTTP_URL_STRIP_QUERY', 256);		// Strip query string
	define('HTTP_URL_STRIP_FRAGMENT', 512);		// Strip any fragments (#identifier)
	define('HTTP_URL_STRIP_ALL', 1024);			// Strip anything but scheme and host
	
	// Build an URL
	// The parts of the second URL will be merged into the first according to the flags argument. 
	// 
	// @param	mixed			(Part(s) of) an URL in form of a string or associative array like parse_url() returns
	// @param	mixed			Same as the first argument
	// @param	int				A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
	// @param	array			If set, it will be filled with the parts of the composed url like parse_url() would return 
	function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false)
	{
		$keys = array('user','pass','port','path','query','fragment');
		
		// HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
		if ($flags & HTTP_URL_STRIP_ALL)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
			$flags |= HTTP_URL_STRIP_PORT;
			$flags |= HTTP_URL_STRIP_PATH;
			$flags |= HTTP_URL_STRIP_QUERY;
			$flags |= HTTP_URL_STRIP_FRAGMENT;
		}
		// HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
		else if ($flags & HTTP_URL_STRIP_AUTH)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
		}
		
		// Parse the original URL
		if(parse_url($url))
		$parse_url = parse_url($url);
		
		// Scheme and Host are always replaced
		if (isset($parts['scheme']))
			$parse_url['scheme'] = $parts['scheme'];
		if (isset($parts['host']))
			$parse_url['host'] = $parts['host'];
		
		// (If applicable) Replace the original URL with it's new parts
		if ($flags & HTTP_URL_REPLACE)
		{
			foreach ($keys as $key)
			{
				if (isset($parts[$key]))
					$parse_url[$key] = $parts[$key];
			}
		}
		else
		{
			// Join the original URL path with the new path
			if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
			{
				if (isset($parse_url['path']))
					$parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
				else
					$parse_url['path'] = $parts['path'];
			}
			
			// Join the original query string with the new query string
			if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
			{
				if (isset($parse_url['query']))
					$parse_url['query'] .= '&' . $parts['query'];
				else
					$parse_url['query'] = $parts['query'];
			}
		}
			
		// Strips all the applicable sections of the URL
		// Note: Scheme and Host are never stripped
		foreach ($keys as $key)
		{
			if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
				unset($parse_url[$key]);
		}
		
		
		$new_url = $parse_url;
		
		return 
			 ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
			.((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
			.((isset($parse_url['host'])) ? $parse_url['host'] : '')
			.((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
			.((isset($parse_url['path'])) ? $parse_url['path'] : '')
			.((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
			.((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
		;
	}
}
