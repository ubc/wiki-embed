<?php
/**
 * Plugin Name: Wiki Embed
 * Plugin URI: http://wordpress.org/extend/plugins/wiki-embed/
 * Description: Enables the inclusion of mediawiki pages into your own blog page or post through the use of shortcodes. 
 * Version: 1.4.6
 * Author: Enej Bajgoric, Devindra Payment, CTLT, UBC
 * Author URI: http://cms.ubc.ca
 *
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License as published by the Free Software Foundation; either version 2 of the License, 
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write 
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA * This program is free software; you can redistribute it and/or modify it under the terms of  the GNU 
 * General Public License as published by the Free Software Foundation; either version 2 of the License, 
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write 
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * 
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

// admin side 
require( 'admin/admin-overlay.php' );
require( 'admin/admin.php' );

// update
// require( 'wiki-embed-update.php' );

Class Wiki_Embed {
	static $instance;
	public $options; // GLOBAL Options 
	public $version; 
	public $content_count; // wiki content count needed by the shortcode 
	public $wikiembeds; 
	
	public $pre_load_scripts;
	public $load_scripts;
	
	public $tabs_support;
	public $accordion_support;
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
		
		// set the default wiki embed value if the once from the Options are not set
		$this->options       = shortcode_atts( $this->default_settings(), get_option( 'wikiembed_options' ) );
		$this->wikiembeds    = get_option( 'wikiembeds' ); // we might not need to load this here at all...
		$this->content_count = 0; 
		$this->version       = 0.9;
		
		// display a page when you are clicked from a wiki page
		add_action( 'template_redirect', array( $this, 'load_page' ) );
		add_filter( 'posts_join', array( $this, 'search_metadata_join' ) );
		add_filter( 'posts_where', array( $this, 'search_metadata_where' ) );
		add_filter( 'sf_posts_query', array( $this, 'search_metadata_ajaxy' ) );
	}
	
	/**
	 * register_scripts function.
	 * 
	 * @access public
	 * @return void
	 */
	function register_scripts() {
		wp_register_script( 'wiki-embed-tabs', plugins_url( '/wiki-embed/resources/js/tabs.js' ), array( "jquery", "jquery-ui-tabs" ), $this->version, true );
		wp_register_script( 'wiki-embed-accordion', plugins_url( '/wiki-embed/resources/js/accordion.js' ), array( "jquery", "jquery-ui-accordion" ), $this->version, true );
		
		$this->tabs_support = get_theme_support('tabs');
		$this->accordion_support = get_theme_support( 'accordions' );
		
		if ( $this->tabs_support[0] == 'twitter-bootstrap' || $this->accordion_support[0] == 'twitter-bootstrap' ) {
			require_once( 'support/twitter-bootstrap/action.php' );
		}
		
		if ( $this->tabs_support[0] == 'twitter-bootstrap' ) {
			wp_register_script( 'twitter-tab-shortcode' , plugins_url('support/twitter-bootstrap/twitter.bootstrap.tabs.js', __FILE__), array( 'jquery' ), '1.0', true );
		}
		
		// ADD styling 
		$this->options['tabs-style'] = ( empty( $this->tabs_support ) ? $this->options['tabs-style'] : 0 );
		$this->options['accordion-style'] = ( empty( $this->accordion_support ) ? $this->options['accordion-style'] : 0 );
		
		// embed this if tabs enabled style
		if ( $this->options['tabs-style'] ) {
			wp_enqueue_style( 'wiki-embed-tabs', plugins_url( '/wiki-embed/resources/css/tabs.css' ), false, $this->version ); 		
		}
		
		if ( $this->options['accordion-style'] ) {
			wp_enqueue_style( 'wiki-embed-accordion', plugins_url( '/wiki-embed/resources/css/accordion.css' ), false, $this->version ); 
		}
		
		// add some great wiki styling 
		if ( $this->options['style'] ) {
			wp_enqueue_style( 'wiki-embed-style', plugins_url( '/wiki-embed/resources/css/wiki-embed.css' ), false, $this->version, 'screen' );
		}
		
		switch ( $this->options['wiki-links'] ) {
			case "overlay":
				// embed this if tabs enabled
				wp_register_script( 'colorbox', plugins_url( '/wiki-embed/resources/js/jquery.colorbox.min.js'),array("jquery"), "1.3.20.2", true );
				wp_register_script( 'wiki-embed-overlay', plugins_url( '/wiki-embed/resources/js/overlay.js'),array( "colorbox", "jquery" ), $this->version, true );
				wp_localize_script( 'wiki-embed-overlay', 'WikiEmbedSettings', array( 'ajaxurl' => admin_url('admin-ajax.php') ) );
				wp_enqueue_style( 'wiki-embed-overlay', plugins_url( '/wiki-embed/resources/css/colorbox.css'),false, $this->version, 'screen');
				
				$this->pre_load_scripts[] = 'wiki-embed-overlay';
				break;
			case "new-page":
				wp_register_script( 'wiki-embed-new-page', plugins_url( '/wiki-embed/resources/js/new-page.js' ), array( "jquery" ), $this->version, true );
				$this->pre_load_scripts[] = 'wiki-embed-new-page';
				wp_localize_script( 'wiki-embed-new-page', 'WikiEmbedSettings', array( 'siteurl' => get_site_url(), 'ajaxurl' => admin_url('admin-ajax.php') ) );
				
				if ( current_user_can( 'pulish_pages' ) || current_user_can('unfiltered_html') ) {
					wp_register_script( 'wiki-embed-site-admin', plugins_url( '/wiki-embed/resources/js/site-admin.js'),array( "jquery", 'wiki-embed-new-page' ), $this->version, true );
					$this->pre_load_scripts[] = 'wiki-embed-site-admin';
				}
				break;
			default:
		}
	}
	
	/**
	 * print_scripts function.
	 * 
	 * @access public
	 * @return void
	 */
	function print_scripts() {
		if ( ! is_array( $this->load_scripts ) ) {
			return;
		}
		
		foreach ( $this->load_scripts as $script ) {
			wp_print_scripts( $script );
		}
	}
	
	/**
	 * init function.
	 * 
	 * @access public
	 * @return void
	 */
	function init() {
		// load the necessery scripts for the site 
		// add_action('init','wikiembed_init');
		if ( ! is_admin() ) {
			$this->register_scripts();
		}
		
		if ( ! is_admin() ) { // never display this stuff in the admin 
			add_filter( 'page_link', array( $this, 'page_link' ) );
		}
		
		// wiki embed shortcode
		add_shortcode( 'wiki-embed', array( $this, 'shortcode' ) );
		
		add_action( 'wp_footer', array( $this, 'print_scripts' ) );
		
		// ajax stuff needed for the overlay	
		if ( defined( 'DOING_AJAX' ) ) {
			if ( is_user_logged_in() ) {
				add_action( 'wp_ajax_wiki_embed', array( $this, 'overlay_ajax' ) );
			} else {
				add_action( 'wp_ajax_nopriv_wiki_embed', array( $this, 'overlay_ajax' ) );
			}
		}
	}
	
	/**
	 * settings function.
	 * default settings
	 * @access public
	 * @return void
	 */
	function default_settings() {
		return array(
			'tabs'            => 1,
			'accordians'      => 1,
			'style'           => 1,
			'tabs-style'      => 0,
			'accordion-style' => 0,
			'wiki-update'     => "30",
			'wiki-links'      => "default",
			'wiki-links-new-page-email' => "",
			'default' => array(
				'source'      => 1,
				'pre-source'  => "source: ",
				'no-contents' => 1,
				'no-edit'     => 1,
				'no-infobox'  => 0,
				'tabs'        => 1,
			),
			'security' => array(
				'whitelist' => null,
			),
		);
	}
	
	/**
	 * shortcode function.
	 * 
	 * @access public
	 * @return void
	 */
	function shortcode( $atts ) {
		global $post;
		
		$this->content_count++; 
		
		// url is the unique identifier
		$atts = apply_filters( 'wikiembed_override_atts', $atts );
		
		extract( shortcode_atts( array(
			'url'         => NULL,
			'update'      => NULL, // 30 minutes
			'remove'      => NULL,
			'get'	      => NULL,
			'default_get' => NULL,
			'has_source'  => NULL,
		), $atts ) );
		
		if ( ! $url && current_user_can( 'manage_options' ) ) { // checks to see if url is defined 
			ob_start();
			?>
			<hr />
			<div class="wiki-embed-warning">
				<div style="color: darkred;">
					You need to specify a url for your Wiki-Embed Shortcode
				</div>
				<small>
					This message is only displayed to administrators.
					<br />
					Please <a href=" <?php echo get_edit_post_link( $post->ID ); ?> ">edit this page</a>, and remove the [wiki-embed] shortcode, or specify a url parameter.
				</small>
			</div>
			<hr />
			<?php
			return ob_get_clean();
		}
		
		$url = $this->get_page_url( $url ); // escape the url 
		
		// other possible attributes
		$has_no_edit 	 = in_array( "no-edit",     $atts );	
		$has_no_contents = in_array( "no-contents", $atts );
		$has_no_infobox  = in_array( "no-infobox",  $atts );
		$has_tabs 		 = in_array( "tabs",        $atts );
		$has_accordion 	 = in_array( "accordion",   $atts );
		
		if ( ! isset( $has_source ) ) { // this can be overwritten on per page basis
			$has_source = $this->options['default']['source'];
		}
		
		if ( ! is_numeric( $update ) || $update < 5 ) {
			$update = $this->options['wiki-update'];  // this can be overwritten on per page basis
		}
		
		$this->load_scripts( $has_tabs, $has_accordion );
		
		/**
		 * code here lets you add the get and default_get parameter to your wiki-emebed
		 */
		if ( $get ) {
			$gets = explode( ",", $get );
			
			$default_gets = explode( ",", $default_get );
			$count_get = 0;
			foreach ( $gets as $get_parameter ) {
				$gets_replace[] = ( isset( $_GET[trim( $get_parameter )] ) && esc_html( $_GET[trim( $get_parameter )] ) != "" ? esc_html( $_GET[trim( $get_parameter )] ) : $default_gets[$count_get] );
				$gets_search[]	= "%".trim( $get_parameter )."%";
				$count_get++;
			}
			
			$url = str_replace( $gets_search, $gets_replace, $url );
		}
		
		$wiki_page_id = $this->get_page_id( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox, $remove );
		
		// check to see if we need a refresh or was forced 
		if ( current_user_can( 'publish_pages' ) && isset( $_GET['refresh'] ) && wp_verify_nonce( $_GET['refresh'], $wiki_page_id ) ) {
			// we store stuff 
			foreach ( $this->wikiembeds as $wikiembeds_id => $wikiembeds_item ) {
				$bits = explode( ",", $wikiembeds_id );
				
				if ( esc_attr( $bits[0] ) == esc_attr( $url ) ) {
					// Rather than deleting the data, set it to expire a long time ago so if the refresh fails it can be ignored.
					$this->wikiembeds[$wikiembeds_id]['expires_on'] = 1;
					update_option( 'wikiembeds', $this->wikiembeds );
				}
			}
			
			unset( $wikiembeds_id ); 
		}
		
		// this function retuns the wiki content the way it is suppoed to come 
		$content = $this->get_wiki_content( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox,  $update, $has_source, $remove );
		
		$this->update_wikiembed_postmeta( $post->ID, $url, $content );
		
		// if the user is admin 
		if ( current_user_can( 'publish_pages' ) ) {
			if ( time() > $this->wikiembeds[$wiki_page_id]["expires_on"] ) {
				$admin = "<div class='wiki-admin' style='position:relative; border:1px solid #CCC; margin-top:20px;padding:10px;'> <span style='background:#EEE; padding:0 5px; position:absolute; top:-1em; left:10px;'>Only visible to admins</span> Wiki content is expired and will be refreshed as soon as the source page can be reached. <a href='?refresh=".wp_create_nonce($wiki_page_id)."'>Retry now</a> | <a href='".admin_url('admin.php')."?page=wiki-embed&url=".urlencode($url)."'>in Wiki Embed List</a>";
			} else {
				$admin = "<div class='wiki-admin' style='position:relative; border:1px solid #CCC; margin-top:20px;padding:10px;'> <span style='background:#EEE; padding:0 5px; position:absolute; top:-1em; left:10px;'>Only visible to admins</span> Wiki content expires in: ".human_time_diff( date('U', $this->wikiembeds[$wiki_page_id]["expires_on"] ) ). " <a href='".esc_url('?refresh='.wp_create_nonce($wiki_page_id))."'>Refresh Wiki Content</a> | <a href='".admin_url('admin.php')."?page=wiki-embed&url=".urlencode($url)."'>in Wiki Embed List</a>";
			}
			
			if ( $this->options['wiki-links'] == "new-page" ) {
				if ( ! isset( $this->wikiembeds[$url]['url'] ) ) {
					$admin .= " <br /> <a href='' alt='".urlencode( $url )."' title='Set this {$post->post_type} as Target URL' class='wiki-embed-set-target-url' rel='".get_permalink( $post->ID )."'>Set this {$post->post_type} as Target URL</a>";
				} else {
					$admin .= " <br /> <span>Target URL set: ".esc_url( $this->wikiembeds[$url]['url'] )."</span>";
				}
			}
			
			$admin .= "</div>";
			return $content.$admin; 
		}
		
		return $content;
	}
	
	/**
	 * load_page function.
	 * 
	 * @access public
	 * @return void
	 */
	function load_page() {
		if ( ! isset( $_GET['wikiembed-url'] ) && ! isset( $_GET['wikiembed-title'] ) ) {
			return true; // do nothing 
		}
		
		// call global variables 
		global $wp_query;
		
		// do we need to redirect the page ? 
		$wiki_page_url = esc_url( $_GET['wikiembed-url'] ); 
		
		// we could try to load it 
		if ( isset( $this->wikiembeds[$wiki_page_url]['url'] ) ):
			wp_redirect( esc_url( $this->wikiembeds[$wiki_page_url]['url'] ) );
			die();
		endif;
		
		$tabs      = ( $this->options['default']['tabs'] == 1 ? true : false); 
		$accordion = ( $this->options['default']['tabs'] == 2 ? true : false); 
		$wiki_page_id = $this->get_page_id( $wiki_page_url, $accordion, $tabs, $this->options['default']['no-contents'], $this->options['default']['no-edit'], $this->options['default']['no-infobox'] );
		
		// make sure to load scripts
		$this->load_scripts( $has_tabs, $has_accordion );
		
		/* Generate the shortcode ? */
		$wiki_embed_shortcode = $this->get_page_shortcode( $wiki_page_url, $accordion, $tabs, $this->options['default']['no-contents'], $this->options['default']['no-edit'], $this->options['default']['no-infobox'] );
		
	    // no we have no where to redirect the page to just stay here 
		if ( ! isset( $has_source ) ) {
			$has_source = $this->options['default']['source'];
		}
		
		if ( ! isset( $remove ) ) {
			$remove = ""; // nothing to remove 
		}
		
		$url   = $this->get_page_url( $_GET['wikiembed-url'] );
		$title = $this->get_page_title( $_GET['wikiembed-title'] );
		
		$content = $this->get_wiki_content(	
			$url,
			$accordion,
			$tabs,
			$this->options['default']['no-contents'],
			$this->options['default']['no-edit'],
			$this->options['default']['no-infobox'],
			$this->options['wiki-links'],
			$this->options['default']['source'],
			$remove
		);
		
		if ( current_user_can( 'publish_pages' ) ) {
			$content.= '<div class="wiki-admin" style="position:relative; border:1px solid #CCC; margin-top:20px;padding:10px;"> <span style="background:#EEE; padding:0 5px; position:absolute; top:-1em; left:10px;">Only visible to admins</span> <a href="'.admin_url('admin.php').'?page=wiki-embed&url='.urlencode($url).'">in Wiki Embed List</a> | <a href="'.admin_url('post-new.php?post_type=page&content=').urlencode($wiki_embed_shortcode).'&post_title='.esc_attr($title).'">Create a New Page</a></div>';
		}
		
		$admin_email = get_bloginfo( 'admin_email' );
		$user = get_user_by( 'email', $admin_email );
		
		$wp_query->is_home = false;
		$wp_query->is_page = true;
		
		$wp_query->post_count = 1;
		$post = (object) null;
		$post->ID = 0; // wiki-embed is set to 0
		$post->post_title = $title;
		$post->guid = get_site_url()."?wikiembed-url=".urlencode($url)."&wikiembed-title=".urlencode( $title );
		$post->post_content = $content;
		$post->post_status = "published";
		$post->comment_status = "closed";
		$post->post_modified = date( 'Y-m-d H:i:s' );
		$post->post_excerpt = "excerpt nothing goes here";
		$post->post_parent = 0;
		$post->post_type = "page";
		$post->post_date = date( 'Y-m-d H:i:s' );
		$post->post_author = $user->ID; // newly created posts are set as if they are created by the admin user
		
		$wp_query->posts = array( $post );
		$wp_query->queried_object = $post; // this helps remove some errors 
		$flat_url = str_replace( ".", "_", $url);
		
		// email the telling the admin to do something about the newly visited link. 
		if ( is_email( $this->options['wiki-links-new-page-email'] ) && ! isset( $_COOKIE["wiki_embed_urls_emailed:".$flat_url] ) && ! current_user_can( 'publish_pages' ) ) {
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
			
			$sent = wp_mail( $this->options['wiki-links-new-page-email'], $subject, $message ); 
			
			// set the cookie do we don't send the email again
			$expire = time() + 60*60*24*30;
			$set_the_cookie = setcookie( "wiki_embed_urls_emailed:".$flat_url, "set", $expire );
		}
	}
	
	/**
	 * load_scripts function.
	 * 
	 * @access public
	 * @param mixed $has_tabs
	 * @param mixed $has_accordion
	 * @return void
	 */
	function load_scripts( $has_tabs, $has_accordion ) {
		if ( ! empty( $this->pre_load_scripts ) ) {
			$this->load_scripts = $this->pre_load_scripts;
		}
		
		if ( is_array( $this->tabs_support ) ) {
			switch( $this->tabs_support[0] ) {
				case 'twitter-bootstrap';
					$this->load_scripts[] = 'twitter-tab-shortcode';
					break; 
				// add support for something else here 
				default:
					$this->load_scripts[] = 'wiki-embed-tabs';
					break;
			}
		} elseif ( $has_tabs ) {
			$this->load_scripts[] = 'wiki-embed-tabs';
		}
		
		if ( is_array( $this->accordion_support ) ) {
			switch( $this->accordion_support[0] ) {
				case 'twitter-bootstrap';
					// Do Nothing
					break; 
				// add support for something else here 
				default:
					$this->load_scripts[] = 'wiki-embed-accordion';	
				break;
			}
		} elseif ( $has_accordion ) {
			$this->load_scripts[] = 'wiki-embed-accordion';	
		}
	}
	
	/**
	 * get_page_id function.
	 * 
	 * @access public
	 * @param mixed $url
	 * @param mixed $has_accordion
	 * @param mixed $has_tabs
	 * @param mixed $has_no_contents
	 * @param mixed $has_no_edit
	 * @param mixed $has_no_infobox
	 * @param bool $remove (default: false)
	 * @return string $page_id;
	 */
	function get_page_id( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox, $remove = false ) {
		$wiki_page_id = esc_url( $url ).",";
		
		if ( $has_tabs ) {
			$wiki_page_id .= "tabs,";
		}
		
		if ( $has_accordion ) {
			$wiki_page_id .= "accordion,";
		}
		
		if ( $has_no_contents ) {
			$wiki_page_id .= "no-contents,";
		}
		
		if ( $has_no_edit ) {
			$wiki_page_id .= "no-edit,";
		}
		
		if ( $has_no_infobox ) {
			$wiki_page_id .= "no-infobox,";
		}
		
		if ( $remove ) {
			$wiki_page_id .= $remove.",";
		}
		
		$wiki_page_id =	substr( $wiki_page_id, 0, -1 );
		
		return $wiki_page_id;
	}
	
	/**
	 * get_page_shortcode function.
	 * 
	 * @access public
	 * @param mixed $url
	 * @param mixed $has_accordion
	 * @param mixed $has_tabs
	 * @param mixed $has_no_contents
	 * @param mixed $has_no_edit
	 * @param mixed $has_no_infobox
	 * @return string $wiki_embed_shortcode
	 */
	function get_page_shortcode( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox ) {
		$atts = "";
		$atts .= " url=" . $url;
		if ( $has_tabs )        $atts .= " tabs";
		if ( $has_accordion )   $atts .= " accordion";
		if ( $has_no_contents ) $atts .= " no-contents";
		if ( $has_no_edit )     $atts .= " no-edit";
		if ( $has_no_infobox )  $atts .= " no-infobox";
		
		return "[wiki-embed".$atts."]";
	}
	
	/**
	 * get_page_url function.
	 * 
	 * @access public
	 * @param mixed $get_url
	 * @return void
	 */
	function get_page_url( $get_url ) {
		// Remove unwanted parts
		$url = $this->remove_action_render( $get_url );
		$url = str_replace( "&#038;","&", $url );
		$url = str_replace( "&amp;","&", $url );	
		$url_array = explode( "#", $url );
		
		return $url_array[0];
	}
	
	/* TODO: his function is identical to the one above it. Remove one of them. */
	function esc_url( $url ) {
		// remove unwanted parts
		$url = $this->remove_action_render( $url );
		$url = str_replace( "&#038;", "&", $url );
		$url = str_replace( "&amp;", "&", $url );	
		$url_array = explode( "#", $url );
		
		return $url_array[0];
	}
	
	/**
	 * remove_action_render function.
	 * removed any add action from the end of the url 
	 * @access public
	 * @param mixed $url
	 * @return void
	 */
	function remove_action_render( $url ) {
		if ( substr( $url, -14 ) == "?action=render" ) {
			return substr( $url, 0, -14 );
		} else {
			return $url;	
		}
	}
	
	/**
	 * get_page_title function.
	 * 
	 * @access public
	 * @param mixed $title
	 * @return void
	 */
	function get_page_title( $title ) {
		$title =  esc_html( $title );
		
		// explode url - so that the title doesn't hash marks contain into 
		$title_array = explode( '#', $title );
		
		$title = ( isset( $title_array[1] )  ? $title_array[0] : $title );
		return $title ;
	}
	
	/**
	 * get_wiki_content function.
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
	function get_wiki_content( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox, $update, $has_source, $remove = null ) {
		$wiki_page_id = $this->get_page_id( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox,  $remove );
		
		// get the cached version 
		$wiki_page_body = $this->get_cache( $wiki_page_id );
		
		if ( $wiki_page_body && $this->wikiembeds[$wiki_page_id]['expires_on'] < time() && ! ( isset( $_GET['refresh'] ) && wp_verify_nonce( $_GET['refresh'], $wiki_page_id ) ) ) {
			//If the cache exists but is expired (and an immediate refresh has not been forced:
			// Refresh it at the end!
			register_shutdown_function( array( $this, 'refresh_after_load'), $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox, $update, $has_source, $remove );
		} elseif ( $wiki_page_body && $this->wikiembeds[$wiki_page_id]['expires_on'] >= time() ) {
			//If cache exists and is fresh
			// we don't do anything
			//then we don't really need to do anything special here!
		} elseif ( ! $wiki_page_body || ( current_user_can( 'publish_pages' ) && isset( $_GET['refresh'] ) && wp_verify_nonce( $_GET['refresh'], $wiki_page_id ) ) ) {	
			//Get page from remote site
			$wiki_page_body  = $this->remote_request_wikipage( $url, $update );
			
			if ( $wiki_page_body ) { // Successfully grabbed remote contnet
				//render page content
				$wiki_page_body = $this->render( $wiki_page_id, $wiki_page_body, $has_no_edit, $has_no_contents , $has_no_infobox, $has_accordion, $has_tabs, $remove );
				$worked = $this->update_cache( $wiki_page_id, $wiki_page_body, $update );
			} else { //Failed, (and there's no cache available) so show an error
				$update = 0;	//Set the expiry offset to 0 (now) to try again next time the page is loaded
				return '<span class="alert">
						We were not able to Retrieve the content of this page, at this time.<br />
						You can: <br />
						1. Try refreshing the page. Press Ctrl + R (windows) or ⌘ Cmd + R (mac)<br />
					2. Go to the <a href="'.esc_url($url).'" >source</a><br />
					</span>';
			}
		}
	    
		// display the source 
		$wiki_embed_end = '';
		if ( $has_source ) {
			$source_text = ( isset( $this->options['default']['pre-source'] ) ? $this->options['default']['pre-source'] : "source:" ); 
			$wiki_embed_end .= '<span class="wiki-embed-source">'.$source_text.' <a href="'.esc_url( urldecode($url)) .'">'.urldecode($url).'</a></span>';
		}
		
		// add special wiki embed classed depending on what should be happening
		$wiki_embed_class = '';
		switch ( $this->options['wiki-links'] ) {
			case "overlay":
				$wiki_embed_class .= " wiki-embed-overlay ";
				break;
			case "new-page":
				$wiki_embed_class .= " wiki-embed-new-page ";
				break;
		}
		
		$wiki_target_url = ' wiki-target-url-not-set';
		
		if ( isset( $this->wikiembeds[$wiki_page_id]['url'] ) && $this->wikiembeds[$wiki_page_id]['url'] ) {
			$wiki_target_url = " wiki-target-url-set";
		}
		
		$wiki_embed_class .= $wiki_target_url; 
		return "<div class='wiki-embed ".$wiki_embed_class."' rel='{$url}'>".$wiki_page_body."</div>".$wiki_embed_end;
	}
	
	/**
	 * remote_request_wikipage function.
	 * This function get the content from the url and stores in an transient. 
	 * @access public
	 * @param mixed $url
	 * @param mixed $update
	 * @return void
	 */
	function remote_request_wikipage( $url, $update ) {
		$wikiembed_id = $this->get_page_id( $url, false, false, false, false, false ); // just the url gets converted to the id 
		
		if ( ! $this->pass_url_check( $url ) ) {
			return "This url does not meet the site security guidelines.";
		}
		
		// grab the content from the cache
		if ( false === ( $wiki_page_body = $this->get_cache( $wikiembed_id ) ) || $this->wikiembeds[$wikiembed_id]['expires_on'] < time() ) {
			// else return the 
			$wiki_page = wp_remote_request( $this->action_url( $url ) );
			
			if ( ! is_wp_error( $wiki_page ) ) {
				$wiki_page_body = $this->rudermentory_check( $wiki_page );
				
				if ( ! $wiki_page_body ) {
					return false;
				}
			} else {
		     	// an error occured try getting the content again
		     	$wiki_page = wp_remote_request( $this->action_url($url) );
		     	if ( ! is_wp_error( $wiki_page ) ) {
		     		$wiki_page_body = $this->rudermentory_check( $wiki_page );
		     		
		     		if ( ! $wiki_page_body ) {
						return false;
		     		}
				} else {
		     		return false;// error occured while fetching content 
				}
			}
		    
		    // make sure that we are UTF-8
		    if ( function_exists('mb_convert_encoding') ) {
		    	$wiki_page_body = mb_convert_encoding( $wiki_page_body, 'HTML-ENTITIES', "UTF-8" ); 
		    }
			
		    // cache the result
			$wiki_page_body = $this->make_safe( $wiki_page_body );
			$this->update_cache( $wikiembed_id, $wiki_page_body, $update );
		}
		
	    return $wiki_page_body;
	}
	
	/**
	 * rudermentory_check function.
	 * 
	 * @access public
	 * @param mixed $wiki_page
	 * @return void
	 */
	function rudermentory_check( $wiki_page ) {
		//rudimentary error check - if the wiki content contains one of the error strings below
		//or the http status code is an error than it should not be saved.
		$error_strings = array( "Can't contact the database server" );
		
		foreach ( $error_strings as $error ) {
			if ( strpos( $wiki_page['body'], $error ) !== false ) {
				$errors = true;
				break;
			}
		}
		
		if ( ! $errors && $wiki_page['response']['code'] == 200 ): 
	 		return $wiki_page['body'];
	 	else:
	 		return false;
	 	endif;	
	}
	
	/**
	 * pass_url_check function.
	 * 
	 * @access public
	 * @param mixed $url
	 * @return void
	 */
	function pass_url_check( $url ) {
		$white_list = trim( $this->options['security']['whitelist'] );
		
		if ( ! empty( $white_list ) ) {
			$white_list_pass = false;
			$white_list_urls = preg_split( '/\r\n|\r|\n/', $this->options['security']['whitelist'] ); 
			// http://blog.motane.lu/2009/02/16/exploding-new-lines-in-php/
			
			foreach ( $white_list_urls as $check_url ) {
				if ( substr( $url, 0, strlen( $check_url ) ) == $check_url ) {
					$white_list_pass = true;
					break;
				}
			}
			
			if ( ! $white_list_pass ) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * action_url function.
	 * 
	 * @access public
	 * @param mixed $url
	 * @return void
	 */
	function action_url( $url ) {
		if ( ! function_exists( 'http_build_url' ) ) {
			require( 'http_build_url.php' );
		}
		
		return http_build_url( $url, array( "query" => "action=render" ), HTTP_URL_JOIN_QUERY );
	}

	/**
	 * wiki_embed_make_safe function.
	 * strip out any unwanted tags - the same way wordpress does
	 * @access public
	 * @param mixed $body
	 * @return void
	 */
	function make_safe( $body ) {
		global $allowedposttags;
		$new_tags = $allowedposttags;
	   
		foreach ( $allowedposttags as $tag => $array ) {
		   $new_tags[$tag]['id'] = array();
		   $new_tags[$tag]['class'] = array();
		   $new_tags[$tag]['style'] = array();
		}
		
		// param
		$new_tags['param']['name'] = array();
		$new_tags['param']['value'] = array();
		
		// object
		$new_tags['object']['type'] = array();
		$new_tags['object']['allowscriptaccess'] = array();
		$new_tags['object']['allownetworking'] = array();
		$new_tags['object']['allowfullscreen'] = array();
		$new_tags['object']['width'] = array();
		$new_tags['object']['height'] = array();
		$new_tags['object']['data'] = array();
		
		// embed
		$new_tags['embed']['width'] = array();
		$new_tags['embed']['height'] = array();
		$new_tags['embed']['type'] = array();
		$new_tags['embed']['wmode'] = array();
		$new_tags['embed']['src'] = array();
		$new_tags['embed']['type'] = array();
		
		// <iframe width="480" height="360" src="http://www.youtube.com/embed/CoAv6yIVkSQ" frameborder="0" allowfullscreen></iframe>
		// is there a better way of allowing trusted sources like youtube? 
		$new_tags['iframe']['allowfullscreen'] = array();
		$new_tags['iframe']['width'] = array();
		$new_tags['iframe']['height'] = array();
		$new_tags['iframe']['src'] = array();
		$new_tags['iframe']['frameborder'] = array();
		
		// lets sanitize this 
	    $body = wp_kses( $body, $new_tags );
		return $body;
	}

	/**
	 * render function.
	 * 
	 * @access public
	 * @param mixed $wiki_page_body
	 * @param mixed $has_no_edit
	 * @param mixed $has_no_contents
	 * @param mixed $has_no_infobox
	 * @param mixed $has_accordion
	 * @param mixed $has_tabs
	 * @return void
	 */
	function render( $wiki_page_id, $wiki_page_body, $has_no_edit, $has_no_contents, $has_no_infobox, $has_accordion, $has_tabs, $remove ) {
		
		if ( $has_no_edit || $has_no_contents || $has_no_infobox || $has_accordion || $has_tabs || $remove ) {
			require_once( "resources/css_selector.php" );	//for using CSS selectors to query the DOM (instead of xpath)
			
			$wiki_page_id = md5( $wiki_page_id );	
			//Prevent the parser from throwing PHP warnings if it receives malformed HTML
			libxml_use_internal_errors(true);
			
			//For some reason any other method of specifying the encoding doesn't seem to work and special characters get broken
			$html = DOMDocument::loadHTML( '<?xml version="1.0" encoding="UTF-8"?>' . $wiki_page_body );	
			
			//Remove specified elements
			$remove_elements = explode( ",", $remove );
			
			// remove edit links 
			if ( $has_no_edit ):
				$remove_elements[] = '.editsection';
			endif; // end of removing links
			
			// remove table of contents 
			if ( $has_no_contents ):
				$remove_elements[] = '#toc';
			endif;
			
			// remove infobox 
			if ( $has_no_infobox ):
				$remove_elements[] = '.infobox';
			endif;
			
			$finder = new DomCSS($html);
			
			// bonus you can remove any element by passing in a css selector and seperating them by commas
			if ( ! empty( $remove_elements ) ) {
				foreach ( $remove_elements as $element ) {
					if ( $element ) {
						foreach ( $finder->query( $element ) as $e ) {
							$e->parentNode->removeChild($e);
						}
						
						$removed_elements[] = $element;
					}
				}
			} // end of removing of the elements 
			
			//Strip out undesired tags that DOMDocument automaticaly adds
			$wiki_page_body = preg_replace( array( '/^<!DOCTYPE.+?>/u','/<\?.+?\?>/' ), array( '', '' ), str_replace( array( '<html>', '</html>', '<body>', '</body>' ), array( '', '', '', '' ), $html->saveHTML() ) );
			
			//Seperate article content into an array of headers and an array of content (for tabs/accordions/styling)
			$start_offset = 0;
			$headlines = array();
			$content = array();
			$first_header_position = strpos( $wiki_page_body, '<h2>' );
			
			//Check if the first header is from a table of contents. if so, need to move up and find the next header.
			if ( ! $this->extract_headline_text( substr( $wiki_page_body, $first_header_position, strpos( $wiki_page_body, '</h2>' ) + 5 - $first_header_position ) ) ) {
				$first_header_position = strpos( $wiki_page_body, '<h2>', $first_header_position + 1 );
			}
			
			$article_intro = substr( $wiki_page_body, 0, $first_header_position ); //contains everything up to (but excluding) the first subsection of the article
			$article_content = substr( $wiki_page_body, $first_header_position ); //contains the rest of the article 
			
			//Go through the wiki body, find all the h2s and content between h2s and put them into arrays.
			while ( true ) {
				$start_header = strpos( $article_content, '<h2>', $start_offset );
				
				if ( $start_header === false ) { //The article doesn't have any headers
					$article_intro = $article_content;
					break;
				}
				
				//find out where the end of this header and the end of the corresponding section are
				$end_header  = strpos( $article_content, '</h2>', $start_offset );
				$end_section = strpos( $article_content, '<h2>', $end_header );
				$headlines[] = substr( $article_content, $start_header + 4, $end_header - $start_header - 4 );
				
				if ( $end_section !== false ) { //success, we've hit another header
					$content[] = substr( $article_content, $end_header + 5, $end_section-$end_header - 5 );
					$start_offset = $end_section;
				} else { //we've hit the end of the article without finding anything else
					$content[] = substr( $article_content, $end_header + 5 );
					break;
				}
			}
			//Now $content[] and $headers[] each are populated for the purposes of tabs/accordions etc
			
			//Build the main page content, with tabs & accordion if necessary
			$article_sections = array();
			$tab_list = "";
			$index = 0;
			$count = count( $headlines ) - 1 ;
			
			foreach ( $headlines as $headline ) {
				//add headline to the tabs list if we're using tabs
				if ( $has_tabs ) {
					$tab_list .= '<li><a href="#fragment-'.$wiki_page_id.'-'.$index.'" >'.$this->extract_headline_text( $headline ).'</a></li>';				
				}
				
				$headline_class = "wikiembed-fragment wikiembed-fragment-counter-".$index;
				
				if ( $count == $index ) {
					$headline_class .= " wikiembed-fragment-last";
				}
				
				if ( $has_accordion ) { //jquery UI's accordions use <h2> and <div> pairs to organize accordion content
					$headline_class .=" wikiembed-fragment-accordion ";
					$headline_class = apply_filters( 'wiki-embed-article-content-class', $headline_class, $index, 'accordion' );
					
					$article_content_raw = '
						<h2><!-- start of headline wiki-embed --><a href="#">' . $this->extract_headline_text( $headline )  . '</a><!--end of headline wiki-embed --></h2>
						<!-- start of content headline --><div class="' . $headline_class . '">
							<!-- start of content wiki-embed -->' . $content[$index] . '<!-- end of content wiki-embed -->
						</div>
					';
					
					$article_sections[] = apply_filters( 'wiki-embed-article-content', $article_content_raw, $index, 'accordion', $wiki_page_id );
				} else { //And this alternative structure for tabs. (or if there's neither tabs nor accordion)
					$headline_class = apply_filters('wiki-embed-article-content-class', $headline_class, $index, 'tabs' );
					$article_content_raw = '
						<div id="fragment-'.$wiki_page_id.'-'.$index.'" class="'.$headline_class.'">
							<h2>'.$headline.'</h2>
							<!-- start of content wiki-embed -->' . $content[$index] . '<!-- end of content wiki-embed -->
						</div>
					';
					if ( $has_tabs ) {
						$article_sections[] = apply_filters( 'wiki-embed-article-content', $article_content_raw, $index, 'tabs', $wiki_page_id );
					} else {
						$article_sections[] = apply_filters( 'wiki-embed-article-content', $article_content_raw, $index, 'none', $wiki_page_id );
					}
				}
				
				$index++;
			}
			
			if ( $has_tabs ) { // Accordians
				$tab_list = apply_filters( 'wiki-embed-tab_list', $tab_list );
				$start = '<div class="wiki-embed-tabs wiki-embed-fragment-count-'.$count.'">'; // shell div
				
				$tabs_shell_class = apply_filters( 'wiki-embed-tabs-shell-class', 'wiki-embed-tabs-nav');
				
				if ( ! empty( $tab_list ) ) {
					$start .= '<ul class="'.$tabs_shell_class.'">'.$tab_list.'</ul>';
				}
				
				$articles_content = apply_filters( 'wiki-embed-articles', implode( " ", $article_sections ), 'tabs' );
			} elseif ( $has_accordion ) { // Tabs
				$start = '<div id="accordion-wiki-'.$this->content_count.'" class="wiki-embed-shell wiki-embed-accordion wiki-embed-fragment-count-'.$count.'">'; // shell div
				$articles_content = apply_filters( 'wiki-embed-articles', implode( " ", $article_sections ), 'accordion' );
			} else { // None
				$start = '<div class="wiki-embed-shell wiki-embed-fragment-count-'.$count.'">'; // shell div
				$articles_content = apply_filters( 'wiki-embed-articles', implode( " ", $article_sections ), 'none' );
			}
			
			$wiki_page_body = $article_intro . $start . $articles_content . '</div>';
		} // end of content modifications 
		
		//clear the error buffer since we're not interested in handling minor HTML errors here
		libxml_clear_errors();
		
		return $wiki_page_body;
	}

	/**
	 * extract_headline_text function.
	 * given an <h2> tag, returns the content of the inner mw-headline span, or return false on failure.
	 * @access public
	 * @param mixed $element
	 * @return string
	 */
	function extract_headline_text($element){
		$match = preg_match( '/id=".+?">(.+?)<\/span>/', $element, $headline );
		
		if ( $match ) {
			return $headline[1];
		} else {
			return false;
		}
	}

	/* FILTERS */
	/**
	 * page_link function.
	 * filter for the page link … 
	 * @access public
	 * @param mixed $url
	 * @return void
	 */
	function page_link( $url ) {
		global $post;
		
		if ( $post->ID === 0 ) {
			return $post->guid;
		}
		
		return $url;
	}
	/* END OF FILTERS */
	
	/* AJAX STUFF HAPPENED HERE */
	/**
	 * wikiembed_overlay_ajax function.
	 * 
	 * This function is what gets dislayed in the overlay
	 * @access public
	 * @return void
	 */
	function overlay_ajax() {
		$url = $this->action_url( $_GET['url'] );
		$source_url = esc_url( urldecode( $_GET['url'] ) );
		$remove = esc_attr( urldecode( $_GET['remove'] ) );
		$title = esc_html( urldecode( $_GET['title'] ) );
		$plain_html = isset( $_GET['plain_html'] );
		$source_url = $this->remove_action_render( $source_url );
		
		// constuct 
		$wiki_page_id = esc_url( $_GET['wikiembed-url'] ).",";
		
		if ( $this->options['default']['tabs'] == 2   ) $wiki_page_id .= "accordion,";
		if ( $this->options['default']['tabs'] == 1   ) $wiki_page_id .= "tabs,";
		if ( $this->options['default']['no-contents'] ) $wiki_page_id .= "no-contents,";
		if ( $this->options['default']['no-infobox']  ) $wiki_page_id .= "no-infobox,";
		if ( $this->options['default']['no-edit']     ) $wiki_page_id .= "no-edit,";
		
		$wiki_page_id = substr( $wiki_page_id, 0, -1 );
	
		$content = $this->get_wiki_content(
			$url,
			$this->options['default']['accordion']=='2',
			$this->options['default']['tabs']=='1',
			$this->options['default']['no-contents'],
			$this->options['default']['no-edit'],
			$this->options['default']['no-infobox'],
			$this->options['wiki-links'],
			$has_source,
			$remove
		);
		
		if ( $plain_html ):
			echo $content;
		else:
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
							<?php echo $content; ?>
						</div>
					</div>
				</body>
			</html>
			<?php
		endif;
		die(); // don't need any more help 
	}
	
	function search_metadata_join( $join ) {
		global $wpdb, $wp_query;
		
		if ( ! is_admin() && $wp_query->is_search ) {
			$join .= " LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id AND ( ".$wpdb->postmeta.".meta_key = 'wikiembed_content' ) ";
		}
		
		return $join;
	}
	
	function search_metadata_where( $where ) {
		global $wpdb, $wp, $wp_query;
		
		if ( ! is_admin() && $wp_query->is_search ) {
			$where .= " OR ( ".$wpdb->postmeta.".meta_value LIKE '%".$wp->query_vars['s']."%' ) ";
		}
		
		return $where;
	}
	
	/**
	 * Makes the plugin searchable by Ajaxy Live Search.
	 * http://wordpress.org/plugins/ajaxy-search-form/
	 *
	 * This is a specific fix for integration with Ajaxy, and only for Ajaxy.
	 * It hooks into a custom filter created by the Ajaxy plugin,
	 * and makes assumptions about how the query is formatted.
	 * If Ajaxy changes how they query, this function will very easily break.
	 */
	function search_metadata_ajaxy( $query ) {
		global $wpdb;
		
		$result = true;
		if ( preg_match( '/%(.*?)%/', $query, $result ) ) {
			$search = $result[1];
			
			$query = explode( "where", $query, 2 );
			$where = $query[1];
			$query = $query[0];
			
			$where = explode( "limit", $where, 2 );
			$limit = $where[1];
			$where = $where[0];
			
			$where = explode( ")", $where, 2 );
			$where = $where[0] . " OR ".$wpdb->postmeta.".meta_value LIKE '%".$search."%' ) " . $where[1];
			
			$join = " LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id AND ( ".$wpdb->postmeta.".meta_key = 'wikiembed_content' ) ";
			$query = $query . $join . "WHERE" . $where . "LIMIT" . $limit;
		}
		
		return $query;
	}

	
	/* CACHING */
	/**
	 * get_cache function.
	 * 
	 * @access public
	 * @param mixed $wiki_page_id
	 * @return void
	 */
	function get_cache( $wiki_page_id ) {
		return get_option( $this->get_hash( $wiki_page_id ) );
	}
	
	/**
	 * update_cache function.
	 * 
	 * @access public
	 * @param mixed $wiki_page_id
	 * @param mixed $body
	 * @param mixed $update
	 * @return void
	 */
	function update_cache( $wiki_page_id, $body, $update ) {
		/**
		 * check to see if we have a site already 
		 **/
		$hash = $this->get_hash( $wiki_page_id);
		
		if ( false === get_option( $hash ) ) {
			$worked = add_option( $hash, $body, '', 'no' ); // this make sure that we don't have autoload turned on
		} else {
			$worked = update_option( $hash, $body );
		}
		
		// save it under the wikiembed
		// keep a track of what how long it is going to be in there
		if ( is_array( $this->wikiembeds ) ) {
			$this->wikiembeds[$wiki_page_id]['expires_on'] = time() + ($update * 60);
			update_option( 'wikiembeds', $this->wikiembeds );
		} else {
			$this->wikiembeds[$wiki_page_id]['expires_on'] = time() + ($update * 60);
			add_option( 'wikiembeds', $this->wikiembeds, '', 'no' );
		}
		
		return $worked;
	}
	
	/**
	 * delete_cache function.
	 * 
	 * @access public
	 * @param mixed $wiki_page_id
	 * @return void
	 */
	function delete_cache( $wiki_page_id ) {
		$hash = $this->get_hash( $wiki_page_id );
		
		delete_option( $hash );
		
		if ( is_array( $this->wikiembeds ) ) {
			unset( $this->wikiembeds[$wiki_page_id] );
			update_option( 'wikiembeds', $this->wikiembeds );
		}
	}

	/**
	 * clear_cache function.
	 * 
	 * @access public
	 * @param mixed $wiki_page_id
	 * @return void
	 */
	function clear_cache( $wiki_page_id ) {
		$hash = $this->get_hash( $wiki_page_id );
		
		delete_option( $hash );
		
		if ( is_array( $this->wikiembeds ) ) {
			$this->wikiembeds[$wiki_page_id]['expires_on'] = 1;
			update_option( 'wikiembeds', $this->wikiembeds );
		}
	}
	
	/**
	 * get_hash function.
	 * 
	 * @access public
	 * @param mixed $wiki_page_id
	 * @return void
	 */
	function get_hash( $wiki_page_id ) {
		return "wikiemebed_".md5( $wiki_page_id );
	}
	
	/**
	 * refresh_after_load function.
	 * Refresh the content after the page has loaded
	 * @access public
	 * @param mixed $url
	 * @param mixed $has_accordion
	 * @param mixed $has_tabs
	 * @param mixed $has_no_contents
	 * @param mixed $has_no_edit
	 * @param mixed $has_no_infobox
	 * @param mixed $update
	 * @param mixed $has_source
	 * @param mixed $remove (default: null)
	 * @return void
	 */
	function refresh_after_load($url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox, $update, $has_source, $remove = null ) {
		//Get page from remote site
		global $wikiembeds,$wikiembed_options;
		$wiki_page_id = $this->get_page_id( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox,  $remove );
		$wiki_page_body = $this->remote_request_wikipage( $url, $update );
		
		if ( $wiki_page_body ) { // Successfully grabbed remote content
			//render page content
			$wiki_page_body = $this->render( $wiki_page_id, $wiki_page_body, $has_no_edit, $has_no_contents , $has_no_infobox, $has_accordion, $has_tabs, $remove );
			$this->update_cache( $wiki_page_id,  $wiki_page_body, $update );
		}
	}
	/* for backwards compatibility */
	
	/**
	 * wikiembed_save_post function.
	 * 
	 * @access public
	 * @param mixed $post_id
	 * @return void
	 */
	function save_post( $post_id ) {	
		if ( wp_is_post_revision( $post_id ) ) {
			$post = get_post( wp_is_post_revision( $post_id ) );
			
			// start fresh each time you save the post or page
			delete_post_meta( $post->ID, "wiki_embed" );
		}
		
		return $post_id;
	}
	
	function update_wikiembed_postmeta( $post_id, $url, $content ) {
		if ( $this->wikiembeds[$url] != get_post_meta( $post_id, "wikiembed_expiration" ) ) {
			$content = strip_tags( $content );
			
			// If this is not the first piece of content to be embeded, then include the content that we got from previous shortcodes.
			if ( $this->content_count > 1 ) {
				$old_content = get_post_meta( $post_id, "wikiembed_content" );
				$old_content = $old_content[0];
				$content = $old_content . $content;
			}
			
			update_post_meta( $post_id, "wikiembed_content", $content );
			update_post_meta( $post_id, "wikiembed_expiration", $this->wikiembeds[$url] );
		}
	}
	/* END OF CACHING */
}

$wikiembed_object = new Wiki_Embed();