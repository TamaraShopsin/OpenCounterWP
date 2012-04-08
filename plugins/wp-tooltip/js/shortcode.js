/**
Plugin Name: WordPress Tooltip
Description: WordPress tooltip lets you add tooltips to content on your posts and pages.
Author: Muhammad Haris - @mharis
Version: 1.0.0
Author URI: http://mharis.net
*/

(function() {  
    tinymce.create('tinymce.plugins.wp_tooltip', {
        init : function(ed, url) {
        	ed.addCommand('wp_tooltip_cmd', function() {
				ed.windowManager.open({
					file : url + '/button-tooltip.php',
					width : 220 + parseInt(ed.getLang('button.delta_width', 0)),
					height : 270 + parseInt(ed.getLang('button.delta_height', 0)),
					inline : 1
					}, {
					plugin_url : url
				});
			});
    
            ed.addButton('wp_tooltip', {
                title : 'Add a Tooltip',
                image : url + '/button-tooltip.png',
                cmd: 'wp_tooltip_cmd',
            });
        },
		getInfo : function() {
			return {
				longname : 'Insert a Tooltip',
				author : 'Muhammad Haris',
				authorurl : 'http://mharis.net',
				infourl : 'http://mharis.net',
				version : tinymce.majorVersion + '.' + tinymce.minorVersion
			};
		},
    });
    tinymce.PluginManager.add('wp_tooltip', tinymce.plugins.wp_tooltip);
})();