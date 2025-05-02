<?php


add_action( 'admin_init', 'wikiembed_options_init' );
add_action( 'admin_menu', 'wikiembed_options_add_page' );

add_action( 'wpmu_options', 'wikiembed_network_site_admin_options' );


add_action( 'update_wpmu_options', 'wikiembed_network_site_admin_options_process' );

// Init plugin options to white list our options

/**
 * wikiembed_options_init function.
 *
 * @access public
 * @return void
 */
function wikiembed_options_init() {
	register_setting( 'wikiembed_options', 'wikiembed_options', 'wikiembed_options_validate' ); // the settings for wiki embed options
}

// Add menu page
/**
 * wikiembed_options_add_page function.
 *
 * @access public
 * @return void
 */
function wikiembed_options_add_page() {

	$awaiting_mod = 4;
	add_menu_page( "Wiki Embed", "Wiki Embed", "publish_pages", "wiki-embed", "wikiembed_list_page", plugins_url('/wiki-embed/resources/img/icons_menu.gif'), 28 );
	$list_page 		= add_submenu_page( "wiki-embed", 'Wiki Embed List', 'Wiki Embed List ', "publish_pages", "wiki-embed", "wikiembed_list_page" );

	$settings_page 	= add_submenu_page( "wiki-embed", "Settings", "Settings", "publish_pages", "wikiembed_settings_page", "wikiembed_settings_page" );

	add_action( 'admin_print_styles-'.$list_page, 'wikiembed_admin_styles_list_page' );
	add_action( 'admin_print_styles-'.$settings_page, 'wikiembed_admin_styles_list_page' );
}

/**
 * wikiembed_admin_styles_list_page function.
 *
 * @access public
 * @return void
 */
function wikiembed_admin_styles_list_page() {



	wp_register_style( 'wiki-embed-list-page', plugins_url( '/wiki-embed/resources/css/wiki-list-page.css' ) );

	wp_register_script( 'wiki-embed-list-page', plugins_url( '/wiki-embed/resources/js/wiki-embed-list-page.js' ) );
    wp_enqueue_style( 'wiki-embed-list-page' );
    wp_enqueue_script( 'wiki-embed-list-page' );
    wp_localize_script( 'wiki-embed-list-page', 'WikiEmbedSettings_S', array(  'nonce' => wp_create_nonce("wiki_embed_ajax") ) );

}

/**
 * wikiembed_network_site_admin_options function.
 *
 * @access public
 * @return void
 */
function wikiembed_network_site_admin_options(){
	?>
		<h3><?php esc_html_e('Wiki Embed Settings') ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="wiki_embed_white_list"><?php esc_html_e('White List of Allowed Sites') ?></label></th>
				<td>
	                <textarea type="text" name="wiki_embed_white_list" id="wiki_embed_white_list" class="regular-text" style="width:100%; height:200px;" ><?php echo esc_textarea( get_site_option('wiki_embed_white_list') ); ?> </textarea>
					<br />
					<span>separate urls with new lines </span>
				</td>
			</tr>
		</table>
<?php

}

/**
 * wikiembed_network_site_admin_options_process function.
 *
 * @access public
 * @return void
 */
function wikiembed_network_site_admin_options_process(){


	$whitelist = trim( $_POST['wiki_embed_white_list'] );
	// check that each of the lines is a url;

	$esc_whitelist = wikiembed_text_to_array_of_urls( $whitelist );
	update_site_option( 'wiki_embed_white_list' , implode( "\n",  $esc_whitelist ) );

}

/**
 * wikiembed_text_to_array_of_urls function.
 * Takes in text which should contain url on each new line and return urls in an array
 * @access public
 * @param mixed $text
 * @return void
 */
function wikiembed_text_to_array_of_urls( $text ) {
	$white_list_array = preg_split( '/\r\n|\r|\n/', $text );
	$array_of_urls = array();
	foreach( $white_list_array as $url):
		$url = trim($url);
		if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
	    	// It's a valid URL
	    	$array_of_urls[] = $url;
		}
	endforeach;

	return $array_of_urls;
}

require_once( WIKI_EMBED_ROOT. "/admin/settings-page.php");
require_once( WIKI_EMBED_ROOT. "/admin/list-page.php");