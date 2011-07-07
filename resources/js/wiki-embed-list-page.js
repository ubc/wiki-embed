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
	
	
	$("#show-help").click(function(){
				if(jQuery(this).text() == "Explain More")
					jQuery(this).text("Explain Less");
				else 
					jQuery(this).text("Explain More");
			
				
				jQuery(".help-div").slideToggle();
				
				return false;
			})
})
	
	