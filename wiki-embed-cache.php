<?php
/**
 * wiki_embed_get_cache function.
 * 
 * @access public
 * @param mixed $wiki_page_id
 * @return void
 */
function wiki_embed_get_cache( $wiki_page_id ) {
	return get_option( wiki_embed_get_hash( $wiki_page_id ) );
}

/**
 * wiki_embed_update_cache function.
 * 
 * @access public
 * @param mixed $wiki_page_id
 * @param mixed $body
 * @param mixed $update
 * @return void
 */
function wiki_embed_update_cache( $wiki_page_id, $body, $update ) {
	global $wikiembeds, $wikiembed_options, $wikiembed_content_count;
	
	/**
	 * check to see if we have a site already 
	 **/
	$hash = wiki_embed_get_hash( $wiki_page_id );
	
	if ( false === get_option( $hash ) ) {
		$worked = add_option( $hash, $body, '', 'no' ); // this make sure that we don't have autoload turned on
	} else {
		$worked = update_option( $hash, $body );
	}

	// save it under the wikiembed
	// keep a track of what how long it is going to be in there
	if ( is_array( $wikiembeds ) ) {
		$wikiembeds[$wiki_page_id]['expires_on'] = time() + ($update * 60);
		update_option( 'wikiembeds', $wikiembeds );
	} else {
		$wikiembeds[$wiki_page_id]['expires_on'] =  time() + ($update * 60);
		add_option( 'wikiembeds', $wikiembeds, '', 'no' );
	}
	
	return $worked;
}

/**
 * wiki_embed_delete_cache function.
 * 
 * @access public
 * @param mixed $wiki_page_id
 * @return void
 */
function wiki_embed_delete_cache( $wiki_page_id ) {
	global $wikiembeds, $wikiembed_options, $wikiembed_content_count;
	
	$hash = wiki_embed_get_hash( $wiki_page_id );
	delete_option( $hash );
	
	if ( is_array( $wikiembeds ) ) {
		unset( $wikiembeds[$wiki_page_id] );
		update_option( 'wikiembeds', $wikiembeds );
	}
}

/**
 * wiki_embed_clear_cache function.
 * 
 * @access public
 * @param mixed $wiki_page_id
 * @return void
 */
function wiki_embed_clear_cache( $wiki_page_id ) {
	global $wikiembeds, $wikiembed_options, $wikiembed_content_count;
	
	$hash = wiki_embed_get_hash( $wiki_page_id );
	delete_option( $hash );
	
	if ( is_array( $wikiembeds ) ) {
		$wikiembeds[$wiki_page_id]['expires_on'] = 1;
		update_option( 'wikiembeds', $wikiembeds );
	}
}

/**
 * wiki_embed_get_hash function.
 * 
 * @access public
 * @param mixed $wiki_page_id
 * @return void
 */
function wiki_embed_get_hash( $wiki_page_id ) {
	return "wikiemebed_".md5( $wiki_page_id );
}
