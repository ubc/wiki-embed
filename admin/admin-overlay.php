<?php

add_action('admin_footer', 'wikiembed_overlay_popup_form');
add_action('media_buttons_context', 'wikiembed_overlay_buttons');


/**
 * wikiembed_overlay_buttons function.
 * 
 * @access public
 * @param mixed $context
 * @return void
 */
function wikiembed_overlay_buttons($context)
{
	global $pagenow;
	if(in_array($pagenow, array( "post.php", "post-new.php" ))):
	$wiki_embed_overlay_image_button = plugins_url('/wiki-embed/resources/img/icon.png');
    $output_link = '<a href="#TB_inline?height=400&width=670&inlineId=wiki_embed_form" class="thickbox" title="' .__("Wiki Embed", 'wiki-embed') . '" id="wiki-embed-overlay-button"><img src="'.				$wiki_embed_overlay_image_button.'" alt="' . __("Wiki Embed", 'wiki-embed') . '" /></a><style>#wiki_embed_form{ display:none;}</style>';
    return $context.$output_link;
    else:
    return $context;
    endif;
}

/**
 * wikiembed_overlay_popup_form function.
 * 
 * @access public
 * @return void
 */
function wikiembed_overlay_popup_form()
{
	global $wikiembed_options,$pagenow;
	if(in_array($pagenow, array( "post.php", "post-new.php" ))):
	
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
    
    endif;
}
