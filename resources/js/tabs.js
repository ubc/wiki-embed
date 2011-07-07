/* Tabs are enables in the wikiembed */
jQuery(document).ready(function ($) {
		var numerber_of_selected = 0;
		$('.wiki-embed-tabs').tabs({create: function(event, ui) { 
			// try to see is the # coresponds to a particular tab 
			var url_hash = document.URL.split('#');
			if( url_hash[1] )
			{
				var re = /_/gi;
				var url_text = url_hash[1].replace(re," ");
				var count = 0;
				
				$(this).find('.wiki-embed-tabs-nav li a').each(function(){
					var link_text = jQuery.trim( $(this).text() );
					
					if( link_text == url_text){
						numerber_of_selected = count;
					}
					count++;
				}); // end of each 
			$(this).tabs('option','selected',numerber_of_selected)
			numerber_of_selected = 0;
			}
		}}); // end of tabs 
});