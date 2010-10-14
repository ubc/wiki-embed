<?php
$wpconfig = realpath("../../../../../wp-config.php");
if (!file_exists($wpconfig))  {
	echo "Could not found wp-config.php. Error in path :\n\n".$wpconfig ;	
	die;	
}
require_once($wpconfig);
require_once(ABSPATH.'/wp-admin/admin.php');
global $wpdb,$wikiembed_options;
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Wiki Embed</title>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>	
	<script language="javascript" type="text/javascript" src="js/tinymce.js"></script>
	<link rel="stylesheet" href="css/window.css" tyle="text/css">
</head>

<body id="link" onload="tinyMCEPopup.executeOnLoad('init_wikiembed();');document.body.style.display='';" style="display: none">
<div class="title">Wiki Embed</div>

	<form name="wikiembed" action="#">
		<table border="0" cellpadding="4" cellspacing="0">
         <tr>
			<td nowrap="nowrap">
			<label for="url"><?php _e("Wiki url", 'wiki_embed_main'); ?></label></td>
			<td>
			<input  type="text" id="url" name="url" value="http://" size="53" class="input-text" />
			</td>
          </tr>
          <tr>
			<td nowrap="nowrap">
			<label for="update"><?php _e("Update content every", 'wiki_embed_main'); ?></label></td>
			<td>
			<select id="update" name="update">
				<option value="1440">day</option>
				<option value="360">6 hours</option>
				<option value="30">30 min</option>
				<option value="0">time you visit the page</option>
			</select>
			</td>
          </tr>
          <tr>
			<td nowrap="nowrap">
			</td>
			<td>
			<input type="checkbox" value="source" id="source" name="source" checked="checked"  /> <label for="overlay"><?php _e("Display a link to the source at the bottom", 'wiki_embed_main'); ?></label>
						</td>
          </tr>
          <tr>
			<td nowrap="nowrap">
			</td>
			<td>
				<input type="checkbox" value="overlay" id="overlay" name="overlay"  /> <label for="overlay"><?php _e("Links open in overlay", 'wiki_embed_main'); ?></label>
			</td>
          </tr>
          <tr>
			<td nowrap="nowrap">
			</td>
			<td>
			<input type="checkbox" value="tabs" id="tabs" name="tabs"  /> <label for="tabs"><?php _e("Top section converted into tabs", 'wiki_embed_main'); ?></label>
			</td>
          </tr>
          <tr>
			<td nowrap="nowrap">
			</td>
			<td>
			<input type="checkbox" value="no-edit" id="no-edit" name="no-edit"  /> <label for="no-edit"><?php _e("Remove edit links", 'wiki_embed_main'); ?></label>
			</td>
          </tr>
          <tr>
			<td nowrap="nowrap">
			</td>
			<td>
			<input type="checkbox" value="no-contents" id="no-contents" name="no-contents"  /> <label for="no-contents"><?php _e("Remove contents index", 'wiki_embed_main'); ?></label>
			</td>
          </tr>
          
        </table>
        <div class="mceActionPanel">
		<div style="float: left">
			    <input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'wiki_embed_main'); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
				<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'wiki_embed_main'); ?>" onclick="insertwikiembedcode();" />
		</div>
		</div>

		</form>
		
</body>
</html>