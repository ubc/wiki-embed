<?php


if(in_array($pagenow, array( "post.php", "post-new.php" ))):
	add_action('admin_footer', 'wikiembed_overlay_popup_form');
	add_action('media_buttons_context', 'wikiembed_overlay_buttons');
endif;

/**
 * wikiembed_overlay_buttons function.
 * 
 * @access public
 * @param mixed $context
 * @return void
 */
function wikiembed_overlay_buttons($context)
{
	$wiki_embed_overlay_image_button = plugins_url('/wiki-embed/resources/img/icon.png');
    $output_link = '<a href="#TB_inline?height=400&width=670&inlineId=wiki_embed_form" class="thickbox" title="' .__("Wiki Embed", 'wiki-embed') . '" id="wiki-embed-overlay-button"><img src="'.				$wiki_embed_overlay_image_button.'" alt="' . __("Wiki Embed", 'wiki-embed') . '" /></a><style>#wiki_embed_form{ display:none;}</style>';
    return $context.$output_link;
}

/**
 * wikiembed_overlay_popup_form function.
 * 
 * @access public
 * @return void
 */
function wikiembed_overlay_popup_form()
{
	global $wikiembed_options;
    ?>
    <script type="text/javascript">
    	
        function wiki_embed_insert_overlay_form(){
		   	var wikiEmbedUrl = jQuery("#wiki-embed-src").attr('value');
			// var wikiEmbedUpdate = jQuery("#wiki-embed-update").attr('value');
			// update='"+wikiEmbedUpdate+"'
			var wikiEmbedSource 	= (jQuery("#wiki-embed-display-links").attr('checked') 	? jQuery("#wiki-embed-display-links").attr('value'):"");
			var wikiEmbedOverlay 	= (jQuery("#wiki-embed-overlay").attr('checked') 		? jQuery("#wiki-embed-overlay").attr('value'):"");
			var wikiEmbedTabs 		= (jQuery("#wiki-embed-tabs").attr('checked') 			? jQuery("#wiki-embed-tabs").attr('value'):"");
			var wikiEmbedNoEdit 	= (jQuery("#wiki-embed-edit").attr('checked') 			? jQuery("#wiki-embed-edit").attr('value'):"");
			var wikiEmbedNoContents = (jQuery("#wiki-embed-contents").attr('checked') 		? jQuery("#wiki-embed-contents").attr('value'):"");
            
            var win = window.dialogArguments || opener || parent || top;
            win.send_to_editor("[wiki-embed url='"+wikiEmbedUrl+"' "+ wikiEmbedSource + wikiEmbedOverlay + wikiEmbedTabs + wikiEmbedNoEdit + wikiEmbedNoContents +" ]");
        }
    </script>

    <div id="wiki_embed_form">
        <div class="wiki_embed_form_wrap">
       	<div class="media-item media-blank">

	<h4 class="media-sub-title">Embed a MediaWiki Page</h4>
	<table class="describe"><tbody>
		<tr>
			<th valign="top" style="width: 130px;" class="label" scope="row">
				<span class="alignleft"><label for="src">Wiki URL</label></span>
				<span class="alignright"><abbr class="required" title="required" id="status_img">*</abbr></span>
			</th>
			<td class="field"><input type="text" aria-required="true" value="http://" name="wiki-embed-src" id="wiki-embed-src" size="60"><br /><br /></td>
		</tr>
		
		<!-- <tr>
			<th valign="top" style="width: 130px;" class="label" scope="row">
				<span class="alignleft"><label for="src">Update content</label></span>
				<span class="alignright"><abbr class="required" title="required" id="status_img">*</abbr></span>
			</th>
			<td class="field">
			<select name="wiki-embed-update" id="wiki-embed-update">
				<option value="1440">daily </option>
				<option value="360">every 6 hours </option>
				<option value="30">every 30 minutes </option>
				<option value="5">every 5 minutes </option>
			</select>
			</td>
		</tr> 		
		<tr>
			<th valign="top" class="label" scope="row">
			</th>
			<td class="field"><input type="checkbox" aria-required="true" value="source" name="wiki-embed-display-links" id="wiki-embed-display-links" <?php checked($wikiembed_options['default']['source'] ); ?> /><span ><label for="wiki-embed-display-links" > Display a link to the source at the bottom</label></span></td>
		</tr>
		-->

		
		<?php if($wikiembed_options['tabs']): ?>
		<tr>
			<th valign="top" class="label" scope="row">
			</th>
			<td class="field"><input type="checkbox" aria-required="true" value=" tabs" name="wiki-embed-tabs" id="wiki-embed-tabs" <?php checked($wikiembed_options['default']['tabs'] ); ?> /><span ><label for="wiki-embed-tabs"> Top section converted into tabs</label></span></td>
		</tr>
		<?php else: ?>
		<tr>
			<th valign="top" class="label" scope="row">
			</th>
			<td class="field"><input type="checkbox" disabled="disabled" /><span><label for="wiki-embed-tabs"> <del>Top section converted into tabs</del></label></span>
			&mdash; to enable see the <a href="">Settings page</a>
			</td>
		</tr>
		<?php endif; ?>
		
		<tr>
			<th valign="top" class="label" scope="row">
			</th>
			<td class="field"><input type="checkbox" aria-required="true" value=" no-edit" name="wiki-embed-edit" id="wiki-embed-edit"<?php checked($wikiembed_options['default']['no-edit'] ); ?> /> <span ><label for="wiki-embed-edit"> Remove edit links</label></span></td>
		</tr>
		
		<tr>
			<th valign="top" class="label" scope="row">
			</th>
			<td class="field"><input type="checkbox" aria-required="true" value=" no-contents" name="wiki-embed-contents" id="wiki-embed-contents" <?php checked($wikiembed_options['default']['no-contents'] ); ?> /> <span ><label for="wiki-embed-contents"> Remove contents index</label></span></td>
		</tr>
		<!-- 
		<?php if($wikiembed_options['overlay']): ?>
		<tr>
			<th valign="top" class="label" scope="row">
			</th>
			<td class="field"><input type="checkbox" aria-required="true" value=" overlay" name="wiki-embed-overlay" id="wiki-embed-overlay" <?php checked($wikiembed_options['default']['overlay'] ); ?> /><span ><label for="wiki-embed-overlay"> Links open in overlay</label></span></td>
		</tr>
		<?php else: ?>
		<tr>
			<th valign="top" class="label" scope="row">
			</th>
			<td class="field"><input type="checkbox" disabled="disabled" /><span><label for="wiki-embed-tabs"> <del>Links open in overlay</del></label></span>
			&mdash; to enable see the <a href="">Settings page</a>
			</td>
		</tr>
		<?php endif; ?>
		-->

		<tr>
			<td></td>
			<td><br />
				<input type="button" value="Insert into Post/ Page" onclick="wiki_embed_insert_overlay_form();" id="go_button" class="button">
			</td>
		</tr>
	
	</tbody></table>
</div>
        </div>
    </div>
    <?php
}

		
		/********************************************************************
		 * TinyMCE Magic  
		 *
		 *
		 ********************************************************************
		function wikiembed_addbuttons() {
			// Add only in Rich Editor mode
			if(is_admin() && get_user_option('rich_editing') == 'true'):
			// add the button for wp25 in a new way
				add_filter("mce_external_plugins", "add_wikiembed_tinymce_plugin", 5);
				add_filter('mce_buttons', 'register_wikiembed_button', 5);
			endif;
		}
		
		
		/**
		 * register_wikiembed_button function.
		 * 
		 * used to insert button in wordpress 2.5x editor
		 * @access public
		 * @param mixed $buttons
		 * @return void
		 
		function register_wikiembed_button($buttons) {
			array_push($buttons, "separator", "wikiembed");
			return $buttons;
		}
		
		/**
		 * add_wikiembed_tinymce_plugin function.
		 * 
		 * Load the TinyMCE plugin : editor_plugin.js (wp2.5)
		 * @access public
		 * @param mixed $plugin_array
		 * @return void
		 
		function add_wikiembed_tinymce_plugin($plugin_array) {
			$plugin_array['wikiembed'] = get_option('siteurl').'/wp-content/plugins/wiki-embed/resources/tinymce3/editor_plugin.js';	
			return $plugin_array;
		}
		
		/**
		 * wikiembed_mce_valid_elements function.
		 * 
		 * @access public
		 * @param mixed $init
		 * @return void
		 
		function wikiembed_mce_valid_elements($init) {
			if ( isset( $init['extended_valid_elements'] ) && ! empty( $init['extended_valid_elements'] ) ) {
				$init['extended_valid_elements'] .= ',' . 'pre[lang|line|escaped]';
			} else {
				$init['extended_valid_elements'] = 'pre[lang|line|escaped]';
			}
			return $init;
		}
		/**
		 * wikiembed_change_tinymce_version function.
		 * incrments the tinymce_version number which forces to update the js 
		 * @access public
		 * @param mixed $version
		 * @return void
		 
		function wikiembed_change_tinymce_version($version) {
			return ++$version;
		}
