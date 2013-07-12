<?php


add_action('admin_init', 'wikiembed_options_init' );
add_action('admin_menu', 'wikiembed_options_add_page');

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
	add_object_page( "Wiki Embed", "Wiki Embed", "publish_pages", "wiki-embed", "wikiembed_list_page", plugins_url('/wiki-embed/resources/img/icons_menu.gif'), 28 );
	$list_page = add_submenu_page( "wiki-embed", 'Wiki Embed List', 'Wiki Embed List ', "publish_pages", "wiki-embed", "wikiembed_list_page" );
	
	$settings_page = add_submenu_page( "wiki-embed", "Settings", "Settings", "publish_pages", "wikiembed_settings_page", "wikiembed_settings_page" );
	add_action( 'admin_print_styles-'.$list_page, 'wikiembed_admin_styles_list_page' );
	add_action( 'admin_print_styles-'.$settings_page, 'wikiembed_admin_styles_list_page' );
}

/**
 * wikiembed_admin_styles_list_page function.
 * 
 * @access public
 * @return void
 */
function wikiembed_admin_styles_list_page(){
	wp_register_style( 'wiki-embed-list-page', plugins_url( '/wiki-embed/resources/css/wiki-list-page.css' ) );
	wp_register_script( 'wiki-embed-list-page', plugins_url( '/wiki-embed/resources/js/wiki-embed-list-page.js' ) );
    wp_enqueue_style( 'wiki-embed-list-page' );
    wp_enqueue_script( 'wiki-embed-list-page' );
}

require_once("settings-page.php");
require_once("list-page.php");