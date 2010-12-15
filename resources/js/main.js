jQuery(document).ready(function ($) {
	$(".wiki-embed-overlay a:not(.external,.new,sup.reference a,.ui-tabs-nav a)").click(function() {
		
		$.fn.colorbox({
			iframe: true, 
			innerWidth: 800, 
			innerHeight: "80%",
			href: WikiEmbedSettings.wiki_embed_ajaxurl+"?url="+$.URLEncode(this.href)+"&action=wiki_embed&title="+$.URLEncode(this.innerHTML),
			transition:"none"
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

