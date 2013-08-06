<?php
/* required for some wiki embed ajax stuff */
add_action('wp_ajax_wiki_embed_add_link', 'wikiembed_list_page_add_link');
add_action('wp_ajax_wiki_embed_remove_link', 'wikiembed_list_page_remove_link');
// add link 
add_action( 'wp_ajax_wiki_embed_add_link', 'wikiembed_list_page_add_link');
add_action( 'wp_ajax__nopriv_wiki_embed_add_link', 'wikiembed_list_page_add_link');
// edit link
add_action( 'wp_ajax_wiki_embed_remove_link', 'wikiembed_list_page_remove_link');
add_action( 'wp_ajax__nopriv_wiki_embed_remove_link', 'wikiembed_list_page_remove_link');

/**
 * wikiembed_list_page function.
 * use to render the wiki-embed page for listing the avalable embeds
 * @access public
 * @return void
 */
function wikiembed_list_page() {
	global $wikiembed_object;
	
	$wikiembeds = $wikiembed_object->wikiembeds;
	$wikiembed_options = $wikiembed_object->options;
	
	if ( ! empty($_POST) && wp_verify_nonce( $_POST['wikiembed-list'], 'wikiembed-list' ) && isset( $_POST['wikiembed'] ) ) {
		foreach( $_POST['wikiembed'] as $post_item ):
  			$post_wikiembed[] = esc_attr( $post_item );
  		endforeach;
		
  		unset($post_item, $_POST['wikiembed']);
		
  		switch( $_POST['action'] ) {
  			case 'trash':
  				if ( is_array( $post_wikiembed ) ):
	  				foreach ( $wikiembeds as $wikiembeds_id => $wikiembeds_item ):
	  					$bits = explode( ",", $wikiembeds_id );
						
	  					if ( in_array( esc_attr( $bits[0] ) ,$post_wikiembed ) || in_array( esc_attr( $wikiembeds_id ), $post_wikiembed ) ) {
	  						$wikiembed_object->delete_cache( $wikiembeds_id );
	  					}
	  				endforeach;
					
	  				unset( $bits );
  				endif;
	  			break;
  			case 'clear-cache':
  				if ( is_array( $post_wikiembed ) ):
	  				foreach( $wikiembeds as $wikiembeds_id => $wikiembeds_item ):
	  					$bits = explode( ",", $wikiembeds_id );
						
	  					if ( in_array( esc_attr( $bits[0] ), $post_wikiembed ) ) {
	  						$wikiembed_object->clear_cache( $wikiembeds_id );
	  					}
	  				endforeach;
					
	  				unset($bits);
  				endif;
				break;
  		}	
	}
	
	// sort $wikiembeds by page parent and 
	if ( is_array( $wikiembeds ) ):
		ksort( $wikiembeds );
		$wikiembeds_parents = array();
		$previous_url = null;
		$parent_count = 0;
		$count_non_url_items = 0;
		$total_parent_count = 0;
		
		foreach ( $wikiembeds as $hash => $item ): // group wiki embeds with the same url together. so they can have the same url 
			$bits = explode( ",", $hash );
			if ( $previous_url != $bits[0] ): // only group the parent url
				if ( isset( $_GET['non_url_items'] ) && ! isset( $item['url'] ) ):
					$wikiembeds_parents[$parent_count][$hash] = $item;
					$count_non_url_items++;
					$parent_count++;
				elseif ( isset( $_GET['url'] ) ):
					if ( esc_attr( $_GET['url'] ) == esc_attr( $bits[0] ) ):
						$wikiembeds_parents[$parent_count][$hash] = $item;
						$count_non_url_items++;
						$parent_count++;
					endif;
				else:
					if ( ! isset( $_GET['non_url_items'] ) ):
						$wikiembeds_parents[$parent_count][$hash] = $item;
						$parent_count++;
					endif;
					
					if ( ! isset( $item['url'] ) ):
						$count_non_url_items++;
					endif;
				endif;
				
				$total_parent_count++;
				$previous_url = $bits[0];
			else:
				
			endif;
		endforeach;
	endif;
	
	?>
	<div class="wrap">
	<div id="icon-wiki-embed" class="icon32"><br /></div>
	<h2>Wiki Embed List</h2>
	<p>Here is a list of all the wiki content that is being embedded</p>
	
	<form method="post" acction=""> 
	<ul class="subsubsub">
		<li><a href="?page=wiki-embed" <?php if ( ! isset( $_GET['non_url_items'] ) ) { ?>class="current"<?php } ?> >All <span class="count">(<?php echo $total_parent_count; ?>)</span></a> |</li>
		<li><a href="?page=wiki-embed&non_url_items=true" <?php if(isset($_GET['non_url_items'])) { ?>class="current"<?php } ?>>No Target URL <span class="count">(<?php echo $count_non_url_items;?>)</span></a></li>
	</ul>
	<div class="tablenav">
		<div class="alignleft actions">
		<select name="action">
			<option selected="selected" value="-1">Bulk Actions</option>
			<option value="clear-cache">Clear Cache</option>
			<option value="trash">Delete Entry</option>
		</select>
		
		<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="Apply" />
		</div>
				
		<div class="clear"></div>
	</div>
	
	<table cellspacing="0" class="widefat post fixed">
		<thead>
			<tr>
				<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
				<th class="manage-column column-title" id="title" scope="col">URL</th>
				<th class="manage-column column-url" id="url" scope="col">Target URL <?php echo ($wikiembed_options['wiki-links'] == 'new-page'? "<span class='active' >active</span>": "<span class='non-active'>not applicable</span>"); ?> </th>
				<th style="" class="manage-column column-date" id="date" scope="col">Cache Expires In</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
				<th class="manage-column column-title" id="title" scope="col">URL</th>
				<th class="manage-column column-url" id="url" scope="col">Target URL <?php echo ($wikiembed_options['wiki-links'] == 'new-page'? "<span class='active' >active</span>": "<span class='non-active'>not applicable</span>"); ?></th>
				<th class="manage-column column-date" id="date" scope="col">Cache Expires In</th>
			</tr>
		</tfoot>
		<tbody>
			<?php if ( $wikiembeds_parents ):
				$total_size = sizeof( $wikiembeds_parents );
				
				$items_per_page = 20;
				
				if ( isset( $_GET['p'] ) && is_int( intval( $_GET['p'] ) ) ):
					$page = intval($_GET['p']);
				else:
					$page = 1;
				endif;
				
				$count_till = $page * $items_per_page;
				
				if ( $count_till > $total_size ):
					$count_till = $total_size;
				endif;
				
				for ( $i = ( $page - 1 ) * $items_per_page; $i < $count_till; $i++ ) {
					$hash = key( $wikiembeds_parents[$i] );
					$item = $wikiembeds_parents[$i][$hash];		
					$bits = explode( ",", $hash );
					$url = parse_url( $bits[0], PHP_URL_PATH );
				?>
				<tr valign="top" class="<?php echo ( $i % 2 ? 'alternate': ''); ?> parent" >
					<th class="check-column" scope="row">
						<input type="checkbox" value="<?php echo $hash; ?>" name="wikiembed[]">
					</th>
					<td>
						<a href="<?php echo esc_url( $bits[0] ); ?>">
							<?php echo $url; ?>
							<br />
							<span>source: <?php echo esc_url( $bits[0] );?></span>
						</a>
					</td>
					<td>
						<?php if ( ! isset( $item['url'] ) ): ?>
							<p>
								<span class="spacer">none</span>
								<a href="#" class="add-target-url" id="<?php echo urlencode( $hash ); ?>">Add Target URL</a>
							</p>
							<p style="display:none;">
								<input type="text" name="<?php echo urlencode( $hash ); ?>" value="http://" size="80" />
								<input type="button" value="Add Target URL" class="button submit-target-url button-primary" /> 
								<a href="#" class="cancel-tagert-url button-secondary">cancel</a>
							</p>
						<?php else: //REMOVE ?>
							<p>
								<span class="spacer">
									<a href="<?php echo esc_url( $item['url'] ); ?>">
										<?php echo $item['url']; ?>
									</a>
								</span> 
								<a href="#" class="add-target-url" id="<?php echo urlencode( $hash ); ?>">Edit</a>
								<span class="divider">|</span>
								<span class="trash">
									<a class="remove-link" rel="<?php echo urlencode( $hash ); ?>" href="#remove">Remove</a>
								</span>
							</p>
							<p style="display:none;">
								<input type="text" name="<?php echo urlencode( $hash ); ?>" class="" value="<?php echo $item['url']; ?>" size="80" />
								<input type="button" value="Edit Target URL" class="button submit-target-url button-primary" /> 
								<a href="#" class="cancel-tagert-url button-secondary">cancel</a>
							</p>
						<?php endif; ?>
					</td>
					<td>
						<?php 
							if ( ! isset( $item['expires_on'] ) )
								$item['expires_on'] = 0;
							if ( $item['expires_on'] > time() )
								echo human_time_diff( date( 'U', $item['expires_on'] ) );
							else
								echo "expired";
						?>
					</td>
				</tr>
			<?php } else: ?>
				<tr valign="top" class="alternate">
					<td></td>
					<td>
						You don't have any Wiki Embeds Stored
						<br />
						Try embeding a wiki using a shortcode. 
					</td>
					<td></td>
					<td></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<!-- current time: <?php echo date("Y/m/d h:i:s A",time()); ?> -->
	<?php 
		if ( $wikiembeds_parents ):
			?>
			<div class="tablenav">
				<div class="tablenav-pages">
					<span class="displaying-num">
						Displaying <?php echo (($page - 1) * $items_per_page) + 1; ?> &ndash;<?php echo $count_till; ?> of <?php echo $total_size; ?>
					</span>
					<?php
					for ( $i = 1; $i <= ceil( $total_size / $items_per_page ); $i++ ) {
						if ( $i == $page ) {
							?>
							<span class="page-numbers current"><?php echo $i; ?></span>
							<?php
						} else {
							?>
							<a href="admin.php?page=wiki-embed&p=<?php echo $i; ?>" class="page-numbers"> <?php echo $i; ?></a>
							<?php
						}
					} 
					?>
				</div>
			</div>
			<?php
		endif;
		
		wp_nonce_field( 'wikiembed-list','wikiembed-list' ); ?>
	</form>
	<?php 
}

/**
 * wikiembed_list_page_add_link function.
 * used to add a target url to the wiki-embed
 * @access public
 * @return void
 */
function wikiembed_list_page_add_link() {
	global $wikiembed_object;
	
	$wikiembeds = $wikiembed_object->wikiembeds;
	
	$decoded_id = urldecode( $_POST['id'] );
	if ( isset( $_POST['id'] ) && isset( $wikiembeds[$decoded_id] ) &&  esc_url( $_POST['url'] ) ) {
		$wikiembeds[$decoded_id]['url'] = esc_url( $_POST['url'] );
		update_option( 'wikiembeds', $wikiembeds );
		echo "success";
	} else { 
		echo "fail";
	}
	
	die();
}

/**
 * wikiembed_list_page_remove_link function.
 * used to remove a target url from the wiki-embed
 * @access public
 * @return void
 */
function wikiembed_list_page_remove_link() {
	global $wikiembed_object;
	$wikiembeds = $wikiembed_object->wikiembeds;
	
	$decoded_id = urldecode( $_POST['id'] );
	if ( isset( $_POST['id'] ) && isset( $wikiembeds[$decoded_id] ) ) {
		unset( $wikiembeds[$decoded_id]['url']);
		echo "success";
		update_option( 'wikiembeds', $wikiembeds );
	} else {
		echo "fail";
	}
	
	die();
}
