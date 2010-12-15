/* javascript that is added if you are looking at the site. and need help managing your wiki embeds. */

jQuery(document).ready(function($){
	$('.wiki-embed-set-target-url').click(function(e){
		var link = $(this);
		var data = {
			action: 'wiki_embed_add_link',
			url: link.attr('rel'),
			id: link.attr('alt')
		};
	
		// since 2.8 wiki_embed_ajaxurl is always defined in the admin header and points to admin-ajax.php
		
						
		jQuery.post(wiki_embed_ajaxurl, data, function(response) {
			if(response == "success")
			{
				link.hide().after('<span>Traget URL set: '+link.attr('rel')+'</span>');
				window.location.hash = 'blah';
				
			}
		});
		e.preventDefault();
	});

});