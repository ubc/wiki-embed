<?php
/*
Plugin Name: Wiki Embed
Plugin URI: 
Description: Enables the inclusion of mediawiki pages into your own blog page or post. Though the use of shortcodes. 
Version: 1.2.3
Author: Enej Bajgoric - CTLT - UBC
Author URI: http://blogs.ubc.ca/beta/
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

// load the necessery scripts for the site 
add_action('init','wikiembed_init');

// ajax stuff needed for the overlay
add_action("init","wikiemebed_add_ajax");

// removed the wiki emebed custom field on save. 
add_action('save_post','wikiembed_save_post');

// display a page when you are clicked from a wiki page
add_action('template_redirect','wikiembed_load_page');




require_once("admin/admin-overlay.php");
require_once("admin/admin.php");

/********************************************************************
 * Settings Page Magic  
 *
 *
 ********************************************************************/


// GLOBAL variables 
$wikiembed_options 	= get_option('wikiembed_options'); // wikiemebed options

// set the default wiki embed value if the once from the Options are not set
$wikiembed_options = shortcode_atts( wikiembed_settings(), $wikiembed_options);

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
		add_filter('page_link','wikiembed_page_link');
		
		// wiki embed shortcode
		if(!isset($_GET['wikiembed-url'])):
			add_shortcode('wiki-embed', 'wikiembed_shortcode');
		endif;
		
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
		  		'wiki_embed_ajaxurl' => admin_url('admin-ajax.php'),
				));
			break;
			case "new-page":
				wp_enqueue_script( 'wiki-embed-new-page', plugins_url( '/wiki-embed/resources/js/new-page.js'),array("jquery"), $wikiembed_version );
				if(current_user_can('pulish_pages') || current_user_can('unfiltered_html')):
					wp_enqueue_script( 'wiki-embed-admin-js', plugins_url( '/wiki-embed/resources/js/site-admin.js'),array("jquery"), $wikiembed_version );
					
					add_action("wp_head",'wikiembed_add_adminajax');
					// add link 
					add_action('wp_ajax_wiki_embed_add_link', 'wikiembed_list_page_add_link');
					add_action('wp_ajax__nopriv_wiki_embed_add_link', 'wikiembed_list_page_add_link');
					// edit link
					add_action('wp_ajax_wiki_embed_remove_link', 'wikiembed_list_page_remove_link');
					add_action('wp_ajax__nopriv_wiki_embed_remove_link', 'wikiembed_list_page_remove_link');
					
				endif;
				wp_localize_script( 'wiki-embed-new-page', 'WikiEmbedSettings', array(
		  		'siteurl' => get_site_url(),
				));
			break;
			default:
		
		}
		// if($wikiembed_options['overlay'] ): endif;
		
		if($wikiembed_options['style']):
		// add some great wiki styling 
			wp_enqueue_style( 'wiki-embed-style',plugins_url( '/wiki-embed/resources/css/wiki-embed.css'),false, $wikiembed_version, 'screen');
			
		endif; 
	
	endif; // end is_admin
	

}
/**
 * wikiembed_add_adminajax function.
 * 
 * @access public
 * @return void
 */
function wikiembed_add_adminajax(){

	?>
	<script type="text/javascript">
	var wiki_embed_ajaxurl = '<?php echo admin_url('admin-ajax.php');?>';
	</script>
	<?php

}
/**
 * wikiembed_load_page function.
 * 
 * @access public
 * @return void
 */
function wikiembed_load_page()
{
	if(!isset($_GET['wikiembed-url']) && !isset($_GET['wikiembed-title'])) 
		return true; // do nothing 
	
	// call global variables 
	global $wp_query,$wikiembeds,$wikiembed_options;
		
	// do we need to redirect the page ?
	$wiki_page_url = esc_url($_GET['wikiembed-url']); 
	$wiki_page_id = wikiembed_get_page_id( $wiki_page_url, $wikiembed_options['default']['tabs'], $wikiembed_options['default']['no-contents'], $wikiembed_options['default']['no-edit'] );
	$wiki_embed_shortcode = "[wiki-embed url=".$wiki_page_url;
	
	
	if($wikiembed_options['default']['tabs']):
		$wiki_embed_shortcode .= " tabs";
	endif;
	
	if($wikiembed_options['default']['no-contents']):
		$wiki_embed_shortcode .= " no-contents";
	endif;
	
	if($wikiembed_options['default']['no-edit']):
		$wiki_embed_shortcode .= " no-edit";
		
	endif;
	
	$wiki_embed_shortcode .= "]";
	
	if(isset($wikiembeds[$wiki_page_url]['url'])):
	
		wp_redirect(esc_url($wikiembeds[$wiki_page_url]['url']));
		
	endif;

// no we have no where to redirect the page to just stay here
	if(!isset($has_source))
		$has_source = $wikiembed_options['default']['source'];
		
	if(!isset($remove))
		$remove =""; // nothing to remove 
		
	$url = wikiembed_esc_url($_GET['wikiembed-url']);
	$title = esc_html($_GET['wikiembed-title']);
	
	// explode url - so that the title doesn't contain into 
	$title_array = explode('#',$title);
	if(isset($title_array[1])):
		$title = $title_array[0]; 
	endif;
	$content = wikiembed_get_wiki_content(	
		$url,
		$wikiembed_options['default']['tabs'],
		$wikiembed_options['default']['no-contents'],
		$wikiembed_options['default']['no-edit'],
		$wikiembed_options['wiki-links'],
		$has_source,
		$remove);
	
	if(current_user_can( 'publish_pages' )):
		
		$content.= '<div class="wiki-admin" style="position:relative; border:1px solid #CCC; margin-top:20px;padding:10px;"> <span style="background:#EEE; padding:0 5px; position:absolute; top:-1em; left:10px;">Only visible to admins</span> <a href="'.admin_url('admin.php').'?page=wiki-embed&url='.urlencode($url).'">in Wiki Embed List</a> | <a href="'.admin_url('post-new.php?post_type=page&content=').$wiki_embed_shortcode.'&post_title='.esc_attr($title).'">Create a New Page</a></div>';

	endif;
	
	$admin_email = get_bloginfo('admin_email');
	$user =  get_user_by('email', $admin_email);
	
	$wp_query->is_home = false;
	$wp_query->is_page = true;
	
	$wp_query->post_count = 1;
	$post = (object) null;
	$post->ID = 0; // wiki-embed is set to 0
	$post->post_title = $title;
	$post->guid = get_site_url()."?wikiembed-url=".urlencode($url)."&wikiembed-title=".urlencode($title);
	$post->post_content = $content;
	$post->post_status = "published";
	$post->comment_status = "closed";
	$post->post_modified = date('Y-m-d H:i:s');
	$post->post_excerpt = "excerpt nothing goes here";
	$post->post_parent = 0;
	$post->post_type = "page";
	$post->post_date = date('Y-m-d H:i:s');
	$post->post_author = $user->ID; // newly created posts are set as if they are created by the admin user
	
	$wp_query->posts = array($post);
	$wp_query->queried_object = $post; // this helps remove some errors 
	$flat_url = str_replace(".","_",$url);
	
	// email the telling the admin to do something about the newly visited link. 
	if(is_email($wikiembed_options['wiki-links-new-page-email']) && !isset($_COOKIE["wiki_embed_urls_emailed:".$flat_url]) &&  !current_user_can( 'publish_pages' )):
		
		
		
		$current_url  =	get_site_url()."?wikiembed-url=".urlencode($url)."&wikiembed-title=".urlencode($title);
		$settings_url = get_site_url()."/wp-admin/admin.php?page=wikiembed_settings_page";
		$list_url     = get_site_url()."/wp-admin/admin.php?page=wiki-embed";
		$new_page     = get_site_url()."/wp-admin/post-new.php?post_type=page&post_title=".$title."&content=".$wiki_embed_shortcode;
		
		$list_url_item = get_site_url()."/wp-admin/admin.php?page=wiki-embed&url={$url}";
		
	
		
		$subject = "Wiki Embed Action Required!";
		
		$message = "
		A User stumbled apon a page that is currently not a part of the site.
		This is the url that they visited - {$current_url}
		
		You have a few options:
		
		Fix the problem by:
		Creating a new page - and adding the shortcode 
		Go to {$new_page} 
		
		Here is the shorcode that you might find useful:
		{$wiki_embed_shortcode}
		
		Then go to the Wiki-Embed list and add a Target URL to point to the site
		{$list_url_item}
		
		and place the link that is suppoed to take you to the page that you just created.
		
		
		
		Or you should:
		Do Nothing - remove your email from the wiki embed settings page - {$settings_url}			
	
		";
		
		$sent = wp_mail($wikiembed_options['wiki-links-new-page-email'], $subject, $message); 
		// set the cookie do we don't send the email again
		$expire=time()+60*60*24*30;
		$set_the_cookie = setcookie("wiki_embed_urls_emailed:".$flat_url, "set", $expire);
		
	endif;
	
}


function wikiembed_page_link($url){
	global $post;
		
		if($post->ID == 0)
			$url = $post->guid;
	return $url; 

}


/********************************************************************
 * Shortcode Magic  
 *
 *
 ********************************************************************/
/* for backwards compatibility */
function wikiembed_save_post($post_id) {	
	
	if(wp_is_post_revision($post_id)):
		$post = get_post( wp_is_post_revision($post_id) );
	
		// start fresh each time you save the post or page
		delete_post_meta($post->ID, "wiki_embed");
	endif;
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
	global $id,$post, $wikiembed_content_count, $wikiembed_options, $wikiembeds;
	
	if(is_null($wikiembed_content_count))
		$wikiembed_content_count = 0;
	
	$wikiembed_content_count++; 
	
	/* url is the unique identifier */
	extract(shortcode_atts(array(
		'url' => NULL,
		'update' => NULL, /* 30 minutes */
		'remove'=>NULL,
		'get'	=>NULL,
		'default_get' =>NULL,
		'has_source'  =>NULL,

	), $atts));
	
	if(!$url) // checks to see if url is defined 
		return "you need to set a url";
		
	$url = wikiembed_esc_url($url); // I am not sure how usefull this is at all 
	
	// other possbile attributes
	$has_no_edit 	 = ( in_array("no-edit", 	$atts)? true: false );	
	$has_no_contents = ( in_array("no-contents",$atts)? true: false );
	$has_tabs 		 = ( in_array("tabs", 		$atts)? true: false );
	
	
	if(!isset($has_source)) // this can be overwritten on per page basis
		$has_source = $wikiembed_options['default']['source'];

	
	if( !is_numeric($update) || $update < 5)
		$update = $wikiembed_options['wiki-update'];  // this can be overwritten on per page basis
		
	/**
	 * code here lets you add the get and default_get parameter to your wiki-emebed
	 */
	if($get)
	{
		$gets = explode(",",$get);

		$default_gets = explode(",",$default_get);
		$count_get = 0;
		foreach($gets as $get_parameter):
			$gets_replace[] = ( isset($_GET[trim($get_parameter)]) && esc_html($_GET[trim($get_parameter)]) != "" ? esc_html($_GET[trim($get_parameter)]): $default_gets[$count_get]) ;
			$gets_search[]	= "%".trim($get_parameter)."%";
			$count_get++;

		endforeach;
		
		$url = str_replace($gets_search, $gets_replace, $url);
	}
	
	
	$wiki_page_id = wikiembed_get_page_id( $url, $has_tabs, $has_no_contents, $has_no_edit, $remove );
	
	// check to see if we need a refresh was forced 
	if(current_user_can( 'publish_pages' ) && isset($_GET['refresh']) && wp_verify_nonce($_GET['refresh'], $wiki_page_id)):		
			
			// we store stuff 
			foreach($wikiembeds  as $wikiembeds_id => $wikiembeds_item):
				$bits = explode(",",$wikiembeds_id);
				if(esc_attr($bits[0]) == esc_attr($url)): // delete 
					unset($wikiembeds[$wikiembeds_id]['expires_on']);
					delete_transient( md5($wikiembeds_id) );
					
				endif;
	  		endforeach;
			unset($wikiembeds_id); 
	endif;

	
	
	// this function retuns the wiki content the way it is suppoed to come 
	$content = wikiembed_get_wiki_content( $url, $has_tabs, $has_no_contents, $has_no_edit, $update, $has_source, $remove );
	
	// if the user is admin 
	
	if(current_user_can( 'publish_pages' )):
		
		$admin = "<div class='wiki-admin' style='position:relative; border:1px solid #CCC; margin-top:20px;padding:10px;'> <span style='background:#EEE; padding:0 5px; position:absolute; top:-1em; left:10px;'>Only visible to admins</span> Wiki content expires in: ".human_time_diff( date('U', $wikiembeds[$wiki_page_id]["expires_on"] ) ). " <a href='?refresh=".wp_create_nonce($wiki_page_id)."'>Refresh Wiki Content</a> | <a href='".admin_url('admin.php')."?page=wiki-embed&url=".urlencode($url)."'>in Wiki Embed List</a>";
	
 	
		if($wikiembed_options['wiki-links'] == "new-page"):
			if(!isset($wikiembeds[$url]['url'])):
			
				$admin .= " <br /> <a href='' alt='".urlencode($url)."' title='Set this {$post->post_type} as Target URL' class='wiki-embed-set-target-url' rel='".get_permalink($post->ID)."'>Set this {$post->post_type} as Target URL</a>";
			else:
				$admin .= " <br /> <span>Target URL set: ".esc_url($wikiembeds[$url]['url'])."</span>";
			endif;
		endif;

		$admin .= "</div>";
	
		return $content.$admin; 
	endif;
	
	return $content; 
}
/**
 * wikiembed_get_page_id function.
 * 
 * @access public
 * @param mixed $url
 * @param mixed $has_tabs
 * @param mixed $has_no_contents
 * @param mixed $has_no_edi
 * @return void
 */
function wikiembed_get_page_id( $url, $has_tabs, $has_no_contents, $has_no_edit, $remove=false ) {
	
	$wiki_page_id = esc_url($url).",";
		if($has_tabs)
			$wiki_page_id .= "tabs,";
		
		if($has_no_contents)
			$wiki_page_id .= "no-contents,";
		
		if($has_no_edit)
			$wiki_page_id .= "no-edit,";
		
		if($remove)
			$wiki_page_id .= $remove.",";
		$wiki_page_id =	substr($wiki_page_id,0,-1);
	return $wiki_page_id;
}
/**
 * wikiembed_get_wiki_content function.
 * 
 * @access public
 * @param mixed $url
 * @param mixed $has_tabs
 * @param mixed $has_no_contents
 * @param mixed $has_no_edit
 * @param mixed $update
 * @param bool $has_source. (default: false)
 * @param mixed $remove. (default: null)
 * @return void
 */
function wikiembed_get_wiki_content( $url, $has_tabs, $has_no_contents, $has_no_edit, $update, $has_source, $remove=null ) {
	global $wikiembeds,$wikiembed_options,$wikiembed_content_count;
	
	
	$wiki_page_id = wikiembed_get_page_id( $url, $has_tabs, $has_no_contents, $has_no_edit, $remove );
	$wiki_page_id_hash  = md5($wiki_page_id); // if we don't md5 the hash we can't really 
	
	
	// Get any existing copy of our transient data
	if (false === ( $wiki_page_body = get_transient( $wiki_page_id_hash ) ) ): 
	
		// lets try to get the  
    	$wiki_page_body  = wp_remote_request_wikipage($url,$update);
    	
    	if(!$wiki_page_body): // we couldn't get the wiki content
    		return '<span class="alert">
						We were not able to Retrieve the content of this page, at this time.<br />
						You can: <br />
						1. Try refreshing the page. Press Ctrl + R (windows) or ⌘ Cmd + R (mac)<br />
						2. Go to the <a href="'.esc_url($url).'" >source</a><br />
					</span>';
		endif; 
		
		$wiki_page_body = wikiembed_render( $wiki_page_body, $has_no_edit , $has_no_contents , $has_tabs, $remove );
    
    	// place the rendered content in to db
     	$worked = set_transient($wiki_page_id_hash, $wiki_page_body, $update*60);
     	
     	// keep a track of what how long it is going to be in there
     	if( is_array($wikiembeds) ):
     		$wikiembeds[$wiki_page_id]['expires_on'] =  time() + ($update * 60);
			update_option( 'wikiembeds', $wikiembeds );
  		else:
  			$wikiembeds[$wiki_page_id]['expires_on'] =  time() + ($update * 60);
    		add_option( 'wikiembeds', $wikiembeds );
    	endif;
    endif;
    
    
	// display the source 
	$wiki_embed_end = '';
	if($has_source):
		$source_text = ( isset( $wikiembed_options['default']['pre-source'] ) ? $wikiembed_options['default']['pre-source'] : "source:" ); 
		
		$wiki_embed_end .= '<span class="wiki-embed-source">'.$source_text.' <a href="'.$url.'">'.$url.'</a></span>';
	endif;
	// add special wiki embed classed depending on what should be happening
	$wiki_embed_class = '';
	switch($wikiembed_options['wiki-links']){
		case "overlay":
			$wiki_embed_class .= " wiki-embed-overlay ";
		break;
		
		case "new-page":
			$wiki_embed_class .= " wiki-embed-new-page ";
		break; 
	
	}
	
	$wiki_target_url = ' wiki-target-url-not-set';
	
	if(isset($wikiembeds[$wiki_page_id]['url']) && $wikiembeds[$wiki_page_id]['url'])
		$wiki_target_url = " wiki-target-url-set";
	
	$wiki_embed_class .= $wiki_target_url; 
	
	return "<div class='wiki-embed ".$wiki_embed_class."' rel='{$url}'>".$wiki_page_body."</div>".$wiki_embed_end;
	
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
	global $wikiembeds,$wikiembed_options;
	
	$wikiembed_id = wikiembed_get_page_id( $url, false, false, false ); // just the url gets converted to the id 
	$wiki_page_id_hash = md5($url);
	
	if($wikiembed_options['security']['whitelist']):
		$white_list_pass = false;
		$white_list_urls = preg_split( '/\r\n|\r|\n/', $wikiembed_options['security']['whitelist'] ); // http://blog.motane.lu/2009/02/16/exploding-new-lines-in-php/
			
			foreach($white_list_urls as $check_url):
			
			if( substr($url, 0 , strlen( $check_url ) )  == $check_url):
				$white_list_pass = true;
				break;
			endif;
		endforeach;
		
		if(!$white_list_pass)
			return "This url does not meet the site security guidelines.";
	endif;
	
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
				
		$wikiembeds[$wikiembed_id]['expires_on'] =  time() + ($update * 60);
		update_option( 'wikiembeds', $wikiembeds ); // but the new wiki embed hasn't been set yet
		
     return $wiki_page_body;
     	
}
/**
 * wikiembed_render function.
 * 
 * @access public
 * @param mixed $wiki_page_body
 * @param mixed $has_no_edit
 * @param mixed $has_no_contents
 * @param mixed $has_tabs
 * @return void
 */
function  wikiembed_render( $wiki_page_body, $has_no_edit , $has_no_contents , $has_tabs, $remove ) {
	global $wikiembed_content_count;
	// Do we need to modify the content? 
	if( $has_no_edit || $has_no_contents || $has_tabs || $remove ): 
		require_once("resources/simple_html_dom.php");
			
			
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
					
		
			
		$index = 0;
		$list = '';
		$headlines = $html->find("h2 span.mw-headline");
		$count = count($headlines)-1;
		foreach($headlines as $headline):
		
				if( $has_tabs ): // create tabs 
					if($wikiembed_content_count <= 1):
						$list .= '<li><a href="#fragment-'.$wikiembed_content_count.'-'.$index.'" ><span>'.$headline->innertext.'</span></a></li>';
					else:
						$list .= '<li><a href="#fragment-'.$wikiembed_content_count.'-'.$index.'" >'.$headline->outertext.'</a></li>';
					endif;
				endif; // end of creating tabs 
				
				if($index !=0):
					$class = "wikiembed-fragment wikiembed-fragment-counter-".$index;
					
					if($count == $index)
						$class .= " wikiembed-fragment-last";
					
					if($wikiembed_content_count <= 1):
						$headline->parent()->outertext = 
						'</div><div id="fragment-'.$wikiembed_content_count.'-'.$index.'" class="'.$class.'"><h2>'.$headline->outertext.'</h2>';
					else:
						$headline->parent()->outertext = 
						'</div><div id="fragment-'.$wikiembed_content_count.'-'.$index.'" class="'.$class.'"><h2><span class="mw-headline">'.$headline->innertext.'</span></h2>';
					endif;
				endif;
				$index++;
		endforeach;

		$wiki_embed_end_tabs = '';
		if( $has_tabs ):	// create tabs 
			$tabs = '<div class="wiki-embed-tabs wiki-embed-fragment-count-'.$count.'">'; // shell div
			if( $list !='' ):
				$tabs .= '<ul class="wiki-embed-tabs-nav">'.$list.'</ul>';
			endif;
		else:
			$tabs = '<div class="wiki-embed-shell wiki-embed-fragment-count-'.$count.'">'; // shell div 
		endif;
		// the first div inside for the first tab
		$tabs .= '<div id="fragment-'.$wikiembed_content_count.'-0" class="wikiembed-fragment wikiembed-fragment-counter-0">';
	
		
		if(isset($headlines[0])):
			if($wikiembed_content_count <= 1):
				$headlines[0]->parent()->outertext = $tabs.'<h2>'.$headlines[0]->outertext.'</h2>';				
			else:
				$headlines[0]->parent()->outertext = $tabs.'<h2><span class="mw-headline">'.$headlines[0]->innertext.'</span></h2>';
			endif;
		endif;
		if(isset($headlines[0]))
			$wiki_embed_end_tabs   .="</div></div>";
				
				
				// endif;							
		$wiki_page_body = $html->save();
		$wiki_page_body .= $wiki_embed_end_tabs;
			
		
	endif; // end of content modifications 
			
	if(!empty($removed_elements))
		$remove_att = 	'remove="'.implode(",",$removed_elements).'"';
     			     	

	return $wiki_page_body;
}

function wikiembed_esc_url($url){
	// remove unwanted 
	$url = wikiembed_remove_action_render($url);
	$url = str_replace("&#038;","&",$url);
	$url = str_replace("&amp;","&",$url);	
	// remove any unwanted anchor tags 
	$url_array = explode("#",$url);
	
	return $url_array[0];
}
/**
 * wikiembed_remove_action_render function.
 * removed any add action from the url 
 * @access public
 * @param mixed $url
 * @return void
 */
function wikiembed_remove_action_render($url) {
	if(substr($url,-14) == "?action=render")
		return substr($url,0,-14);
	else
		return $url;	

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
	global $wikiembeds, $wikiembed_options;
	$url = wikiembed_action_url($_GET['url']);
	$source_url = esc_url(urldecode($_GET['url']));
	$remove = esc_attr(urldecode($_GET['remove']));
	$title = esc_html(urldecode($_GET['title']));
	$plain_html = ( isset($_GET['plain_html']) ? true: false );
	$source_url = wikiembed_remove_action_render($source_url);
	
	
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
	if(!$plain_html):
	?>
<!doctype html>

<!--[if lt IE 7 ]> <html class="ie6" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>    <html class="ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<title><?php echo urldecode(esc_attr($_GET['title'])); ?></title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js"></script>
<link media="screen" href="<?php bloginfo('stylesheet_url')?>" type="text/css" rel="stylesheet" >
<link media="screen" href="/<?php echo PLUGINDIR ; ?>/wiki-embed/resources/css/wiki-embed.css" type="text/css" rel="stylesheet" >
<link media="screen" href="/<?php echo PLUGINDIR ; ?>/wiki-embed/resources/css/wiki-overlay.css" type="text/css" rel="stylesheet" >
<script src="/<?php echo PLUGINDIR ; ?>/wiki-embed/resources/js/wiki-embed-overlay.js" ></script>

</head>
<body>

	<div id="wiki-embed-iframe">
	
	<div class="wiki-embed-content">
	<h1 class="wiki-embed-title" ><?php echo $title; ?></h1>
	<?php 
	endif;
	echo $content;
	if(!$plain_html):
	?>
	</div>
	</div>
	</body></html>
	<?php
	endif;
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
	$wikiembed_options['tabs-style'] = 0;
	$wikiembed_options['wiki-update'] = "30";
	
	$wikiembed_options['wiki-links'] = "default";
	$wikiembed_options['wiki-links-new-page-email'] = '';
	$wikiembed_options['default']['source'] = 1;
	$wikiembed_options['default']['pre-source'] = "source:";
	
	
	$wikiembed_options['default']['no-contents'] = 1;
	$wikiembed_options['default']['no-edit'] = 1;
	$wikiembed_options['default']['tabs'] = 1;
	$wikiembed_options['security']['whitelist'] = null;


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
	return http_build_url(urldecode($url), array("query" => "action=render"), HTTP_URL_JOIN_QUERY);
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
		// $url = str_replace("&#038;","&",$url);
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
