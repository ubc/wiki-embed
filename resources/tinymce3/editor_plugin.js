// Docu : http://wiki.moxiecode.com/index.php/TinyMCE:Create_plugin/3.x#Creating_your_own_plugins

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('wikiembed');
	 
	tinymce.create('tinymce.plugins.wikiembed', {
		
		init : function(ed, url) {
		// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');

			ed.addCommand('wikiembed', function() {
				ed.windowManager.open({
					file : url + '/window.php',
					width : 500,
					height : 300,
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

			// Register example button
			ed.addButton('wikiembed', {
				title : 'Wiki Embed',
				cmd : 'wikiembed',
				image : url + '/img/wiki-embed.png'			});
			
			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('wikiembed', n.nodeName == 'IMG');
			});
		},
		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			return {
					longname  : 'wikiembed',
					author 	  : 'OLT DEV',
					authorurl : 'http://blogs.ubc.ca',
					infourl   : 'http://blogs.ubc.ca',
					version   : "0.1 beta"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('wikiembed', tinymce.plugins.wikiembed);
})();


