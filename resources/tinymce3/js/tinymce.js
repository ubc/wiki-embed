function init_wikiembed() {
	tinyMCEPopup.resizeToInnerSize();
}

function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function insertwikiembedcode() {
	var wikiEmbed = 'url="'+document.getElementById('url').value+'" ';
		wikiEmbed += 'update="'+document.getElementById('update').value+'" ';
	
	if(document.getElementById('source').checked)
			wikiEmbed += "source ";
	
	if(document.getElementById('overlay').checked)
			wikiEmbed += "overlay ";		
	
	if(document.getElementById('tabs').checked)
			wikiEmbed += "tabs "; 
	
	if(document.getElementById('no-edit').checked)
		wikiEmbed += "no-edit "; 
	
	if(document.getElementById('no-contents').checked)
		wikiEmbed += "no-contents "; 
	
	
	
	window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, '[wiki-embed '+wikiEmbed+']');
	
	
	tinyMCEPopup.editor.execCommand('mceRepaint');
	tinyMCEPopup.close();
	return;
}
