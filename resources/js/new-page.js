/* Wiki page opens up in a new page */
jQuery(document).ready(function ($) {

	$(".wiki-embed-new-page a:not(.external,.new,sup.reference a,.wiki-embed-tabs-nav a, #toc a, .image,a[href$='.pdf'])").each(function() {
		var url = this.href.split("#");
		if(url[1]){
			$(this).attr("href",WikiEmbedSettings.siteurl+"?wikiembed-url="+$.URLEncode(url[0])+"&wikiembed-title="+$.URLEncode(this.innerHTML)+"#"+url[1]);
		}else{
			$(this).attr("href",WikiEmbedSettings.siteurl+"?wikiembed-url="+$.URLEncode(url[0])+"&wikiembed-title="+$.URLEncode(this.innerHTML));
		}
		
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

