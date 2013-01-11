/* Overlay JS needed */
jQuery(document).ready(function ($) {
		$(".wiki-embed-overlay a:not(.external,.new,sup.reference a,.wiki-embed-tabs-nav a, h2 a, #toc a, .image,a[href$='.pdf'])").click(function() {
			
			var url = this.href.split("#");
			
			if( url[1] ){
				var encoded_url = $.URLEncode(url[0]);
				var hash = "#"+url[1];
			}else{
				var encoded_url = $.URLEncode(url[0]);
				var hash ="";
			}

			// add the remove attribute 
			var remove = $(this).parents(".wiki-embed").attr("remove");
			remove  = ( remove ) ? "&remove="+$.URLEncode(remove) : "";
			
			$.fn.colorbox({
				iframe: true, 
				innerWidth: 900, 
				innerHeight: "80%",
				href: WikiEmbedSettings.ajaxurl+"?url="+encoded_url+"&action=wiki_embed&title="+$.URLEncode(this.innerHTML)+remove+hash,
				transition:"none",
				onLoad: function () { $('#colorbox').show(); }
				});
			return false;		
		});
			
});


jQuery.extend({URLEncode:function(c){var o='';var x=0;c=c.toString();var r=/(^[a-zA-Z0-9_.]*)/;
  while(x<c.length){var m=r.exec(c.substr(x));
    if(m!=null && m.length>1 && m[1]!=''){o+=m[1];x+=m[1].length;
    }else{if(c[x]==' ')o+='+';else{var d=c.charCodeAt(x);var h=d.toString(16);
    o+='%'+(h.length<2?'0':'')+h.toUpperCase();}x++;}}return o;},
URLDecode:function(s){var o=s;var binVal,t;var r=/(%[^%]{2})/;
  while((m=r.exec(o))!=null && m.length>1 && m[1]!=''){b=parseInt(m[1].substr(1),16);
  t=String.fromCharCode(b);o=o.replace(m[1],t);}return o;}
});

