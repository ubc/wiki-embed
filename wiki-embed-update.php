<?php 
// This file is not included any more.
// you might need it if you are updating from an older version of the plugin
function wiki_embed_update_db_check() {
    global $wikiembed_version;
	
    if ( get_option( 'wikiembed_version' ) != $wikiembed_version ) {
        wiki_embed_update_08();
    }
}

add_action( 'plugins_loaded', 'wiki_embed_update_db_check' );

/**
 * wiki_embed_update_08 function.
 * when we switched from _transient to options
 * @access public
 * @return void
 */
function wiki_embed_update_08() {
	global $wikiembed_version;
	$wikiembeds = get_option( 'wikiembeds' );
	
	if ( is_array( $wikiembeds ) ) {
		foreach ( $wikiembeds as $id => $wiki_array ) {
			// lets delete these things
			delete_transient( md5( $id ) );
			delete_option( md5( $id ) );
		}
	}
	
	update_option( 'wikiembed_version', $wikiembed_version );
} 