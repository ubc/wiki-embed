<?php

add_action('wp_ajax_wiki_embed_add_link', 'wikiembed_list_page_add_link');
add_action('wp_ajax_wiki_embed_remove_link', 'wikiembed_list_page_remove_link');



function wikiembed_list_page()
{
	global $wikiembeds, $wikiembed_options;
	
	if ( !empty($_POST) && wp_verify_nonce($_POST['wikiembed-list'],'wikiembed-list') && isset($_POST['wikiembed']) )
	{
  		switch($_POST['action'])
  		{
  			case 'trash':
  				if($_POST['wikiembed']):
  				
	  				foreach($wikiembeds  as $wikiembeds_hash => $wikiembeds_item):
	  					$bits = explode(",",$wikiembeds_hash);
			
	  					if(in_array($bits[0],$_POST['wikiembed']))
	  					{
	  						unset($wikiembeds[$wikiembeds_hash]);
							delete_transient( md5($wikiembeds_hash) );
	  						$undoItems[] = $wikiembeds[$wiki_page_id];
	  					}
	  				endforeach;
	  				unset($bits);
  				
  				update_option( 'wikiembeds', $wikiembeds );
  				endif;
  			break;
  			
  			case 'clear-cache':
  				if($_POST['wikiembed']):
  				
	  				foreach($wikiembeds  as $wikiembeds_hash => $wikiembeds_item):
	  					$bits = explode(",",$wikiembeds_hash);
			
	  					if(in_array($bits[0],$_POST['wikiembed']))
	  					{
	  						unset($wikiembeds[$wikiembeds_hash]['expires_on']);
							delete_transient( md5($wikiembeds_hash) );
	  						$undoItems[] = $wikiembeds[$wiki_page_id];
	  					}
	  				endforeach;
	  				unset($bits);
  				
  				update_option( 'wikiembeds', $wikiembeds );
  				endif;
  			
  			break;
  		}	
	}
	
	// lets try to recreate the undo funcnction
	
	 if(is_array($undoItems))
	 {
	 
	 
	 
	 }	
	
	?>
	<style type="text/css">
	.help-div{ display: none; padding-bottom: 10px; font-size: 10px; color:#777; width: 400px; }
	th .help-div{ width: 200px;}
	tr.child td.desciption-title{
	padding-left: 20px;
	}
	.widefat td,.widefat th {
	 border:0;
	}
	tr.parent  td,
	tr.parent  th{
	 border-top:1px solid #DFDFDF;
	}
	#show-help{
	background:#21759B; padding:3px 10px; font-size:9px; text-decoration:none; color:#FFF;
	-moz-border-radius:4px;
	}
	#show-help:hover{
		background:#D54E21;	}
	span.spacer{
		display:block;
		padding-bottom: 5px;
	}
	span.active { 
		background-color:#FFFBCC;
		border:1px solid #E6DB55;
		color:#555555;
	 	padding: 1px 3px;
	 	-moz-border-radius: 3px; /* FF1+ */
	  	-webkit-border-radius: 3px; /* Saf3-4 */
	          border-radius: 3px; /* Opera 10.5, IE 9, Saf5, Chrome */
	}
	
	span.non-active{
		background-color:#FFB78C;
		border:1px solid #FF853C !important;
		
		color:#222;
	 	padding: 1px 3px;
	 	-moz-border-radius: 3px; /* FF1+ */
	  	-webkit-border-radius: 3px; /* Saf3-4 */
	          border-radius: 3px; /* Opera 10.5, IE 9, Saf5, Chrome */

	}
	</style>
	<div class="wrap">
	<h2>Wiki Embed</h2>
	<p>Here is a list of all the wiki content that is being embedded</p>
	<form method="post" acction=""> 
	<div class="tablenav">
		<div class="alignleft actions">
		<select name="action">
			<option selected="selected" value="-1">Bulk Actions</option>
			<option value="clear-cache">Clear Cache</option>
			<option value="trash">Delete Entry</option>
		</select>
		
		<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="Apply">
		
		</div>
				
		<div class="clear"></div>
	</div>
	
	<table cellspacing="0" class="widefat post fixed">
	<thead>
	<tr>
	<th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
	<th style="" class="manage-column column-title" id="title" scope="col">URL</th>
	<th style="" class="manage-column column-url" id="url" scope="col">Target URL <?php echo ($wikiembed_options['wiki-links'] == 'new-page'? "<span class='active' >active</span>": "<span class='non-active'>not applicable</span>"); ?> </th>
	<th style="" class="manage-column column-date" id="date" scope="col">Cache Expires On</th>
	</tr>
	</thead>

	<tfoot>
	<tr>
	<th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
	<th style="" class="manage-column column-title" id="title" scope="col">URL</th>
	<th style="" class="manage-column column-url" id="url" scope="col">Target URL <?php echo ($wikiembed_options['wiki-links'] == 'new-page'? "<span class='active' >active</span>": "<span class='non-active'>not applicable</span>"); ?></th>
	<th style="" class="manage-column column-date" id="date" scope="col">Cache Expires On</th>
	
	</tr>
	</tfoot>
	
	
	<tbody>
	
	<?php if($wikiembeds):
		$counter = 0;
		ksort($wikiembeds);
		$previous_url = null;
		foreach($wikiembeds as $hash => $item): 
			
			$bits = explode(",",$hash);
			
			
			$url = parse_url($bits[0],PHP_URL_PATH);
			
			if($previous_url != $bits[0]):
			$previous_url = $bits[0];
			$counter++;
		?>
	<tr valign="top" class="<?php echo ( ($counter % 2) ? 'alternate': ''); ?> parent" >
		<th class="check-column" scope="row"><input type="checkbox" value="<?php echo $hash; ?>" name="wikiembed[]"></th>
		<td><a href="<?php echo esc_url( $bits[0] ); ?>"><?php echo $url; ?></a><br />
		<?php
		/* echo "[wiki-embed " ;
		foreach($bits as $count => $bit):
		 	if($count == 0)
		 		echo 'url="'.$bit.'" ';
			else
				echo " {$bit} ";
		
		endforeach; 
		echo "]"; */ ?>
		</td>
		
		<td>
		<?php if( !$item['url'] ): ?>
			<p><span class="spacer">none</span>
			<a href="#" class="add-target-url" id="<?php echo urlencode($hash); ?>">Add Target URL</a></p>
			<p style="display:none;">
				<input type="text" name="<?php echo urlencode($hash); ?>" value="http://" size="80" />
				<input type="button" value="Add Target URL" class="button submit-target-url button-primary" /> 
				<a href="#" class="cancel-tagert-url button-secondary">cancel</a>
			</p>
		<?php else: // REMOVE  ?>
			<p><span class="spacer"><a href="<?php echo esc_url($item['url']); ?>"><?php echo $item['url']; ?></a></span> 
			<a href="#" class="add-target-url" id="<?php echo urlencode($hash); ?>">Edit</a> <span class="divider">|</span> <span class="trash"><a class="remove-link" rel="<?php echo urlencode($hash); ?>" href="#remove">Remove</a></span></p>
			<p style="display:none;">
				<input type="text" name="<?php echo urlencode($hash); ?>" class="" value="<?php echo $item['url']; ?>" size="80" />
				<input type="button" value="Edit Target URL" class="button submit-target-url button-primary" /> 
				<a href="#" class="cancel-tagert-url button-secondary">cancel</a>
			</p>
		<?php endif; ?>
		</td>
		<td><?php 
		if($item['expires_on'] > time())
			echo date("Y/m/d h:i A",$item['expires_on']);
		else
			echo "expired";
		 ?></td>
	</tr>	
	<?php 
	else: ?>
	<tr valign="top" class="<?php echo ( ($counter % 2) ? 'alternate': ''); ?> child " style="display:none;" ref="" >
		<th class="check-column" scope="row"></th>
		<td class="desciption-title"><a href="<?php echo esc_url( $bits[0] ); ?>"><?php echo $url; ?></a><br />
		<?php echo "[wiki-embed " ;
		foreach($bits as $count => $bit):
		 	if($count == 0)
		 		echo 'url="'.$bit.'" ';
			else
				echo " {$bit} ";
		
		endforeach; 
		echo "]"; ?>
		</td>
		
		<td>
		
		</td>
		<td><?php 
		if($item['expires_on'] > time())
			echo date("Y/m/d h:i A",$item['expires_on']);
		else
			echo "expired";
		 ?></td>
	</tr>	
	
	<?php 
	endif;
	

	
	endforeach;
	else: ?>
	 <tr valign="top" class="alternate">
		<td>
		</td>
		<td>You don't have any Wiki Embeds Stored <br />
		Try embeding a wiki using a shortcode. 
		</td>
		<td>
		</td>
		<td>
		</td>
	</tr>
	<?php 
	endif;
	?>

	</tbody>
	
	
	</table>
	current time: <?php echo date("Y/m/d h:i:s A",time()); ?>
	 <?php wp_nonce_field('wikiembed-list','wikiembed-list'); ?>

	</form>
	
	<script type="text/javascript" >
	
	function isURL(s) {
 		var regexp = /http:\/\/[A-Za-z0-9\.-]{3,}\.[A-Za-z]{3}/;
 	alert(regexp.test(s));
	}


	jQuery(function($){
		$("a.add-target-url").click(function(e){
		 $(this).parent().hide().next().show();
		 // make the text box be focus 
		 
		var input =  $(this).parent().next().children('input[type=text]');
			input.focus().select();
		 	input.keypress(function(e)
         	{
            code= (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
           		var data = {
				action: 'wiki_embed_add_link',
				url: input.val(),
				id: input.attr('name')
				};
				var el = $(this).siblings('input.button');
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				
								
				jQuery.post(ajaxurl, data, function(response) {
					if(response == "success")
					{
						el.parent().hide().prev().show();
						var patent = el.parent().prev();
						patent.children('a').html("Edit");
						patent.children('span.spacer').html("<a href='"+input.val()+"'>"+input.val()+"</a> ");
						if(el.val() == "Add Target URL")
						{
							el.val("Edit Target URL");
							patent.append(" <span class='divider'>|</span> <span class='trash'><a class='remove-link' rel='"+input.attr('name')+"' href='#remove'>Remove</a></span>");
						}
						
					}
				});
			e.preventDefault();
            }
            
          });

		 e.preventDefault();
		});
		$('a.cancel-tagert-url').click(function(e){
			$(this).parent().hide().prev().show();
			e.preventDefault();
		});
		
		// remove links 
		$('a.remove-link').live("click",function(){
			
			el = $(this);
			var data = {
				action: 'wiki_embed_remove_link',
				id: el.attr('rel')
			};
			jQuery.post(ajaxurl, data, function(response) {
				

				if(response == "success")
				{
					// change the edit to 
					el.parent().parent().children('a').html("Add Target URL");
					// change the Button 
					el.parent().parent().next().children('input.button').val("Add Target URL");

					// replace the the link with 
					el.parent().parent().children(".spacer").html("none");
					el.parent().parent().children(".divider").remove();
					// remove the button just clicked
					el.parent().remove();
				}
			
			});
			return false;
		});
		
		/// submit the form and save the 
		$('input.submit-target-url').click(function(){
			
			// get the image rolling 
			var el = $(this);
			var input = el.prev();
			
			
			var data = {
			action: 'wiki_embed_add_link',
			url: input.val(),
			id: input.attr('name')
			};
	
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				if(response == "success")
				{
					el.parent().hide().prev().show();
					var patent = el.parent().prev();
					patent.children('a').html("Edit");
					patent.children('span.spacer').html("<a href='"+input.val()+"'>"+input.val()+"</a> ");
					if(el.val() == "Add Target URL")
					{
						el.val("Edit Target URL");
						patent.append(" <span class='divider'>|</span> <span class='trash'><a class='remove-link' rel='"+input.attr('name')+"' href='#remove'>Remove</a></span>");
					}
					
				}
			});
		}); // end of submit click 
		
	})
	
	</script>
	<?php /*
	<p>Current time : <?php echo date("Y/m/d h:i:s A"); ?></p>
	<h3>Pre Cache Wiki Page <a href="#" id="show-help" >Explain More</a> </h3>
	<form action="" method="post">
	<table class="form-table">
		<tbody>
		<tr class="form-field form-required">
			<th scope="row">Wiki url</th>
			<td>
			<input type="text" name="url" title="Domain" class="regular-text" name="http://">
			<div class="help-div">The line of text that appears in your address bar when you browse to the <br /> wiki page. </div>

			</td>
		</tr>
		<tr class="form-field form-required">
			<th scope="row">Target url (optional)</th>
			<td>
			<input type="text" name="target" title="Domain" class="regular-text" name="http://">
			</td>
		</tr>		
		<tr>
			<th valign="top" class="label" scope="row">
			</th>
			<td class="field">
			<input type="checkbox" aria-required="true" value="1" name="wikiembed_options[default][tabs]" id="wiki-embed-tabs" <?php checked($wikiembed_options['default']['tabs'] ); ?> /> <span ><label for="wiki-embed-tabs">Top section converted into tabs</label></span>   <br />
			<div class="help-div">Wiki pages are usually divided up though heading into section. This settings turns these sections into tabs. <br /> </div>
			
			<input type="checkbox" aria-required="true" value="1" name="wikiembed_options[default][no-edit]" id="wiki-embed-edit" <?php checked($wikiembed_options['default']['no-edit'] ); ?> /> <span ><label for="wiki-embed-edit">Remove edit links</label></span>    <br />
			<div class="help-div">Often wiki pages have edit links displayed next to them, which is not always desired. </div>
			<input type="checkbox" aria-required="true" value="1" name="wikiembed_options[default][no-contents]" id="wiki-embed-contents" <?php checked($wikiembed_options['default']['no-contents'] ); ?> /> <span ><label for="wiki-embed-contents">Remove contents index</label></span>    <br />
			<div class="help-div">Often wiki pages have a  contents index (list of content) at the top of each page. </div>
			</td>
		</tr>
		</tbody>
	</table>
	<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Pre Cache Wiki Page') ?>" />
	</p>
	 <?php wp_nonce_field('wikiembed-precache','wikiembed-precache'); ?>
	</form>
	 
	</div>
	
	<script type="text/javascript">
			jQuery("#show-help").click(function(){
				if(jQuery(this).text() == "Explain More")
					jQuery(this).text("Explain Less");
				else 
					jQuery(this).text("Explain More");
			
				
				jQuery(".help-div").slideToggle();
				
				return false;
			})
			
	</script> <?php 
	 */
}

function wikiembed_list_page_add_link(){
	global $wikiembeds;
	
	if(isset($_POST['id']) && isset($wikiembeds[urldecode($_POST['id'])]) &&  esc_url($_POST['url'])):
		$wikiembeds[urldecode($_POST['id'])]['url'] = esc_url($_POST['url']);
		echo "success";
		update_option( 'wikiembeds', $wikiembeds );
	else: 
		echo "fail";
	endif;
	die(); // removed extra zero :) 
}

function wikiembed_list_page_remove_link()
{
	global $wikiembeds;
	
	if(isset($_POST['id']) && isset($wikiembeds[urldecode($_POST['id'])])):
		unset($wikiembeds[urldecode($_POST['id'])]['url']);
		echo "success";
		update_option( 'wikiembeds', $wikiembeds );
	else: 
		echo "fail";
	endif;
	die(); // removed extra zero :) 

}
