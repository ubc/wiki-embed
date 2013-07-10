<?php 
function wikiembed_settings_page() {
	global $wikiembed_object;

	$wikiembed_options = $wikiembed_object->options;
	$wikiembed_version = $wikiembed_object->version;

	$updated = false;
	$option = "wikiembed_options";

	if ( isset( $_POST[$option] ) ):
		$value = $_POST[$option];
		
		if ( ! is_array( $value ) ) {
			$value = trim($value);
		}
		
		$value = stripslashes_deep( $value );
		$updated = update_option( $option, $value );
		$wikiembed_options = $value;
	endif; 	
	
	$tabs_support = get_theme_support( 'tabs' );
	$accordion_support = get_theme_support( 'accordions' );
	?>
	<div class="wrap">
	    <div class="icon32" id="icon-options-general"><br /></div>
		
		<h2>Wiki Embed Settings</h2>
		<form method="post" action="admin.php?page=wikiembed_settings_page">
			<?php settings_fields('wikiembed_options'); ?>
			<a href="#" id="show-help" >Explain More</a>
			
			<?php if ( $updated ): ?>
				<div class="updated below-h2" id="message"><p>Wiki Embed Settings Updated</p></div>
			<?php endif; ?>
			<h3>Enable Wiki Embed Functionality </h3>
			<p>If there is functionality that wiki embed has that you don't want &mdash; disable it. This will keep pages lean and mean. </p>
			<table class="form-table">
				<tr>
					<th valign="top" class="label" scope="row"></th>
					<td class="field">
						<input type="checkbox" aria-required="true" value="1" name="wikiembed_options[tabs]" id="wiki-embed-edit" <?php checked( $wikiembed_options['tabs'] ); ?> />
						<span>
							<label for="wiki-embed-edit">Ability to convert a Wiki page headlines into tabs</label>
						</span>
						<br />
						<div class="help-div">Loads the tabs javascript file on each page of the site.</div>
						
						<input type="checkbox" aria-required="true" value="1" name="wikiembed_options[accordions]" id="wiki-embed-edit" <?php checked( $wikiembed_options['accordions'] ); ?>/>
						<span>
							<label for="wiki-embed-edit">Ability to convert a Wiki page headlines into accordion</label>
						</span>
						<br />
						<div class="help-div">Loads the accordions javascript file on each page of the site.</div>
						
						<input type="checkbox" aria-required="true" value="1" name="wikiembed_options[style]" id="wiki-embed-overlay" <?php checked( $wikiembed_options['style'] ); ?> />
						<span>
							<label for="wiki-embed-overlay">Additional styling not commonly found in your theme.</label>
						</span>
						<br />
						<div class="help-div">Loads wiki-embed css files on each page of the site.<br /></div>
						
						<?php $disabled_tabs = ( empty($tabs_support) ? '' : 'disabled="disabled"'); ?>
						<input type="checkbox" aria-required="true" value="1" <?php echo $disabled_tabs; ?> name="wikiembed_options[tabs-style]" id="wiki-embed-tab-style" <?php checked( $wikiembed_options['tabs-style'] ); ?> />
						<span>
							<?php if ( ! empty( $disabled_tabs ) ) { ?>
								<em> Your theme support tabs styling</em>
							<?php } else { ?>
								<label for="wiki-embed-tab-style">Additional tabs styling, useful if you theme doesn't support tab styling </label>
							<?php } ?>
						</span>
						<br />
						<div class="help-div">Loads tabs css files on each page of the site.<br /></div>
						
						<?php $disabled_accordion = ( empty($accordion_support) ? '' : 'disabled="disabled"'); ?>
						<input type="checkbox" aria-required="true" value="1" <?php echo $disabled_accordion; ?> name="wikiembed_options[accordion-style]" id="wiki-embed-accordion-style" <?php checked( $wikiembed_options['accordion-style'] ); ?> />
						<span>
							<?php if ( ! empty( $disabled_accordion ) ) { ?>
								<em> Your theme support accordion styling</em>
							<?php } else { ?>
								<label for="wiki-embed-accordion-style">Additional Accordion styling, useful if you theme doesn't support accordion styling </label>
							<?php } ?>
						</span>
						<br />
						<div class="help-div">Loads accordion css files on each page of the site.<br /> </div>
					</td>
				</tr>
			</table>
			
			<h3>Global Settings </h3>
			<p>These settings are applied site-wide</p>
			<table class="form-table">
				<tr> <!-- Update Content -->
					<th valign="top" class="label" scope="row">
						<span class="alignleft">
							<label for="src">Update content from the wiki</label>
						</span>
					</th>
					<td class="field">
						<select name="wikiembed_options[wiki-update]" id="wiki-embed-update">
							<option value="5" <?php selected( $wikiembed_options['wiki-update'], "5" ); ?>>Every 5 minutes </option>
							<option value="30" <?php selected( $wikiembed_options['wiki-update'], "30" ); ?>>Every 30 minutes </option>
							<option value="360" <?php selected( $wikiembed_options['wiki-update'], "360" ); ?>>Every 6 hours </option>
							<option value="1440" <?php selected( $wikiembed_options['wiki-update'], "1440" ); ?>>Daily </option>
							<option value="262974383" <?php selected( $wikiembed_options['wiki-update'], "262974383" ); ?>>Manually</option>
						</select>
						<div class="help-div">
							Set the duration the content of the wiki page will be stored on your site, before it is refreshed again.
							<br />
							<em>Manually</em> means the content will be stored for <em>6 months</em> which will allow you to refresh the content manually.
						</div>
					</td>
				</tr>
				<tr><!-- Internal wiki links -->
					<th valign="top" class="label" scope="row">
						<span class="alignleft">
							<label for="src">Internal wiki links</label>
						</span>
						<br />
						<div class="help-div">Internal wiki links are links that take you to a different page on the same wiki.</div>
					</th>
					<td class="field">
						<label><input name="wikiembed_options[wiki-links]" type="radio" value="default"  <?php checked($wikiembed_options['wiki-links'],"default"); ?> /> Default &mdash; links takes you back to the wiki</label>
						<br />
						<label><input name="wikiembed_options[wiki-links]" type="radio" value="overlay" <?php checked($wikiembed_options['wiki-links'],"overlay"); ?> /> Overlay &mdash; links open with the content in an overlay window</label>
						<br />
						<label><input name="wikiembed_options[wiki-links]" type="radio" value="new-page" <?php checked($wikiembed_options['wiki-links'],"new-page"); ?>  /> WordPress Page &mdash; links open a WordPress page with the content of the wiki</label>
						<br />
						Note: You can make the links open in specific page by specifying a <a href="?page=wiki-embed">target url</a>. 
						<br />
						<label>
							email
							<input type="text" name="wikiembed_options[wiki-links-new-page-email]" value="<?php echo $wikiembed_options['wiki-links-new-page-email']; ?>"/>
						</label>
						<div class="help-div">Specify an email address if you would like to be contacted when some access a new page. that has not been cached yet. This will help you create a better site structure as the content on the wiki grows.</div>
					</td>
				</tr>
				<tr>
					<th valign="top" class="label" scope="row">
						<span class="alignleft">
							<label for="src">Credit wiki page</label>
						</span>
						<br />
						<div class="help-div">This makes it easy to insert a link back to the wiki page.</div>
					</th>
					<td>
						<input type="checkbox" aria-required="true" value="1" name="wikiembed_options[default][source]" id="wiki-embed-display-links" <?php checked( $wikiembed_options['default']['source']); ?> />
						<span>
							<label for="wiki-embed-display-links">Display a link to the content source after the embedded content</label>
						</span>  
						<br />
						<div id="display-wiki-source" >
							<div style="float:left; width:80px;" >Before the link <br /><input type="text" name="wikiembed_options[default][pre-source]" size="7" value="<?php echo esc_attr($wikiembed_options['default']['pre-source']); ?>" /><br /></div>
							<div style="float:left; width:230px; padding-top:23px;" >http://www.link-to-the-wiki-page.com</div>
						</div>
					</td>
				</tr>
			</table>
			
			<h3>Shortcode Defaults</h3>
			<p>Tired of checking off all the same settings across the site. Set the shortcodes defaults here</p>
			<table class="form-table">
				<tr>
					<th valign="top" class="label" scope="row">
					</th>
					<td class="field">
					<input type="radio" name="wikiembed_options[default][tabs]" value="1" id="wiki-embed-tabs" <?php checked( $wikiembed_options['default']['tabs'],1 ); ?> />
					<span><label for="wiki-embed-tabs">Convert section headings to tabs</label></span><br />
					<input type="radio" name="wikiembed_options[default][tabs]" value="2" id="wiki-embed-accordion" <?php checked( $wikiembed_options['default']['tabs'],2 ); ?> />
					<span><label for="wiki-embed-accordion">Convert section headings to accordion</label></span><br />
					<input type="radio" name="wikiembed_options[default][tabs]" value="0" id="wiki-embed-normal-headers" <?php checked( $wikiembed_options['default']['tabs'],0 ); ?> />
					<span><label for="wiki-embed-normal-headers">Don't convert section headings</label></span><br />
					
					<input type="checkbox" aria-required="true" value="1" name="wikiembed_options[default][no-edit]" id="wiki-remove-edit" <?php checked( $wikiembed_options['default']['no-edit'] ); ?> /> <span ><label for="wiki-remove-edit">Remove edit links</label></span>    <br />
					<div class="help-div">Often wiki pages have edit links displayed next to sections, which is not always desired. </div>
					<input type="checkbox" aria-required="true" value="1" name="wikiembed_options[default][no-contents]" id="wiki-embed-contents" <?php checked( $wikiembed_options['default']['no-contents'] ); ?> /> <span ><label for="wiki-embed-contents">Remove table of contents</label></span>    <br />
					<div class="help-div">Often wiki pages have a table of contents (a list of content) at the top of each page. </div>
					
					<input type="checkbox" aria-required="true" value="1" name="wikiembed_options[default][no-infobox]" id="wiki-embed-infobox" <?php checked( $wikiembed_options['default']['no-infobox'] ); ?> /> <span ><label for="wiki-embed-infobox">Remove infoboxes</label></span>    <br />
					<div class="help-div"></div>
					</td>
				</tr>
			</table>
			
			<h3>Security</h3>
			<p>Restrict the urls of wikis that you want content to be embedded from. This way only url from </p>
			<table class="form-table">
				<tr>
					<th valign="top" class="label" scope="row"></th>
					<td class="field">
						<span>Separate urls by new lines</span>
						<br />
						<textarea name="wikiembed_options[security][whitelist]"  rows="10" cols="50">
							<?php echo $wikiembed_options['security']['whitelist']; ?>
						</textarea>
						<div class="help-div">We are checking only the beginning of the url if it matches the url that you provided.  So for example: <em>http://en.wikipedia.org/wiki/</em> would allow any urls from the english wikipedia, but not from <em>http://de.wikipedia.org/wiki/</em> German wikipedia</div>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>	
	</div>
	<?php	
}

// Display contextual help for Books
function wiki_embed_add_help_text( $contextual_help, $screen_id, $screen ) { 
	if ( 'wiki-embed_page_wikiembed_settings_page' == $screen->id ) {
		$contextual_help =
			'<h3>' . __('Wiki Embed Explained') . '</h3>' .
			'<ul>' .
			'<li>' . __('Specify the correct genre such as Mystery, or Historic.') . '</li>' .
			'<li>' . __('Specify the correct writer of the book.  Remember that the Author module refers to you, the author of this book review.') . '</li>' .
			'</ul>' .
			'<p>' . __('If you want to schedule the book review to be published in the future:') . '</p>' .
			'<ul>' .
			'<li>' . __('Under the Publish module, click on the Edit link next to Publish.') . '</li>' .
			'<li>' . __('Change the date to the date to actual publish this article, then click on Ok.') . '</li>' .
			'</ul>' .
			'<h3>' . __('Shortcode') . '</h3>';
	}
	
	return $contextual_help;
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wikiembed_options_validate( $wikiembed_options ) {
	return array(
		'tabs'            => ( isset( $wikiembed_options['tabs']            ) && $wikiembed_options['tabs']            == 1 ? 1 : 0 ),
		'accordians'      => ( isset( $wikiembed_options['accordions']      ) && $wikiembed_options['accordions']      == 1 ? 1 : 0 ),
		'style'           => ( isset( $wikiembed_options['style']           ) && $wikiembed_options['style']           == 1 ? 1 : 0 ),
		'tabs-style'      => ( isset( $wikiembed_options['tabs-style']      ) && $wikiembed_options['tabs-style']      == 1 ? 1 : 0 ),
		'accordion-style' => ( isset( $wikiembed_options['accordion-style'] ) && $wikiembed_options['accordion-style'] == 1 ? 1 : 0 ),
		'wiki-update'     => ( is_numeric( $wikiembed_options['wiki-update'] ) ? $wikiembed_options['wiki-update'] : "30" ),
		'wiki-links'      => ( in_array( $wikiembed_options['wiki-links'], array( "default", "overlay", "new-page" ) ) ? $wikiembed_options['wiki-links'] : "default" ),
		'wiki-links-new-page-email' => wp_rel_nofollow( $wikiembed_options['wiki-links-new-page-email'] ),
		'default' => array(
			'source'      => ( isset( $wikiembed_options['default']['source'] ) && $wikiembed_options['default']['source'] == 1 ? 1 : 0 ),
			'pre-source'  => wp_rel_nofollow( $wikiembed_options['default']['pre-source'] ),
			'no-contents' => ( isset( $wikiembed_options['default']['no-contents'] ) && $wikiembed_options['default']['no-contents'] == 1 ? 1 : 0 ),
			'no-edit'     => ( isset( $wikiembed_options['default']['no-infobox']  ) && $wikiembed_options['default']['no-infobox']  == 1 ? 1 : 0 ),
			'no-infobox'  => ( isset( $wikiembed_options['default']['no-edit']     ) && $wikiembed_options['default']['no-edit']     == 1 ? 1 : 0 ),
			'tabs'        => ( is_numeric( $wikiembed_options['default']['tabs'] ) ? $wikiembed_options['default']['tabs'] : "0" ),
		),
		'security' => array(
			'whitelist' => ( isset( $wikiembed_options['security']['whitelist'] ) ? trim( $wikiembed_options['security']['whitelist'] ) : null ),
		),
	);
}
