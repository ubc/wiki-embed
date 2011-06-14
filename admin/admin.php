<?php


add_action('admin_init', 'wikiembed_options_init' );
add_action('admin_menu', 'wikiembed_options_add_page');

// Init plugin options to white list our options
function wikiembed_options_init(){
	register_setting( 'wikiembed_options', 'wikiembed_options', 'wikiembed_options_validate' ); // the settings for wiki embed options
}

// Add menu page
function wikiembed_options_add_page() {
	
	$awaiting_mod = 4;
	add_object_page( "Wiki Embed", "Wiki Embed","publish_pages", "wiki-embed", "wikiembed_list_page", plugins_url('/wiki-embed/resources/img/icons_menu.gif'), 28 );
	add_submenu_page("wiki-embed", 'Wiki Embed List','Wiki Embed List ',"publish_pages", "wiki-embed", "wikiembed_list_page");
	add_submenu_page( "wiki-embed", "Settings", "Settings", "publish_pages", "wikiembed_settings_page", "wikiembed_settings_page");
}



require_once("settings-page.php");
require_once("list-page.php");