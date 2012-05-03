<?php
/*
Plugin Name: Adjustly Collapse
Plugin URI: http://www.psdcovers.com/adjustly-collapse
Description: Allows contributors to create a collapsible element within an article or theme which is triggered by a hyperlink or other element.
Version: 1.0.0
Author: PSDCovers.com
Author URI: http://www.psdcovers.com/
*/

define('AJ_COLLAPSE_vNum','1.0.0');

// Check for location modifications in wp-config
// Then define accordingly
if ( !defined('WP_CONTENT_URL') ) {
	define('AJ_PLUGPATH',get_option('siteurl').'/wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/');
	define('AJ_PLUGDIR', ABSPATH.'/wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/');
} else {
	define('AJ_PLUGPATH',WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/');
	define('AJ_PLUGDIR',WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__)).'/');
}

// Create Text Domain For Translations
load_plugin_textdomain('AJ', false, basename(dirname(__FILE__)) . '/languages/');

add_action( 'wp_print_scripts', 'load_aj_collapse' );
add_action( 'wp_print_styles', 'load_aj_style' );

function load_aj_collapse() {
 	wp_enqueue_script('aj-collapse-slider', AJ_PLUGPATH.'aj-collapse.js', array( 'jquery' ));
}

function load_aj_style() {
 	wp_enqueue_style('aj-collapse-style', AJ_PLUGPATH.'aj-collapse.css', false, 'all');
}

?>