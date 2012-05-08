<?php
/*
Plugin Name: Usernoise
Plugin URI: http://karevn.com
Description: Usernoise is a modal contact / feedback form with smooth interface.
Version: 2.0.3
Author: Nikolay Karev
Author URI: http://karevn.com
*/


define('UN_VERSION', '2.0.3');

load_plugin_textdomain('usernoise', false, basename(dirname(__FILE__)) . '/languages/');

define('FEEDBACK', 'un_feedback');
define('FEEDBACK_TYPE', 'feedback_type');
define('USERNOISE', 'usernoise');
define('USERNOISE_DIR', dirname(plugin_basename(__FILE__)));
define('USERNOISE_MAIN', __FILE__);

define('UN_FEEDBACK_FORM_TITLE', 'feedback_form_title');
define('UN_USE_FONT', 'use_font');
define('UN_FEEDBACK_FORM_TEXT', 'feedback_form_text');
define('UN_FEEDBACK_BUTTON_TEXT', 'feedback_button_text');
define('UN_FEEDBACK_BUTTON_COLOR', 'feedback_button_color');
define('UN_FEEDBACK_BUTTON_POSITION', 'feedback_button_position');
define('UN_FEEDBACK_BUTTON_TEXT_COLOR', 'feedback_button_text_color');
define('UN_FEEDBACK_BUTTON_SHOW_BORDER', 'feedback_button_show_border');
define('UN_SUBMIT_FEEDBACK_BUTTON_TEXT', 'submit_feedback_button_text');
define('UN_FEEDBACK_FORM_SHOW_SUMMARY', 'feedback_form_show_summary');
define('UN_FEEDBACK_FORM_SHOW_TYPE', 'feedback_form_show_type');
define('UN_FEEDBACK_FORM_SHOW_EMAIL', 'feedback_form_show_email');
if (!defined('UN_ENABLED')){
	define('UN_ENABLED', 'enabled');
}
define('UN_SHOW_POWERED_BY', 'show_powered_by');
define('UN_ADMIN_NOTIFY_ON_FEEDBACK', 'admin_notify_on_feedback');
define('UN_THANKYOU_TITLE', 'thankyou_title');
define('UN_THANKYOU_TEXT', 'thankyou_text');
define('UN_DISABLE_ON_MOBILES', 'disable_on_mobiles');
define('UN_LOAD_IN_FOOTER', 'load_in_footer');


require('vendor/plugin-options-framework/plugin-options-framework.php');
$un_h = new HTML_Helpers_0_4;
require('inc/template.php');
if (is_admin()) require('admin/upgrade.php');
require('admin/settings.php');
require('inc/model.php');

require('inc/migrations.php');
require('admin/notifications.php');

if (is_admin()){
	require('admin/editor-page.php');
	require('admin/menu.php');
	require('admin/feedback-list.php');
	require('admin/dashboard.php');

	if (defined('DOING_AJAX') && un_get_option(UN_ENABLED, true)){
		require('inc/controller.php');
	}
} else if (un_get_option(UN_ENABLED, true)){
	require('admin/admin-bar.php');
	require('inc/integration.php');
	require('inc/controller.php');
}


function un_get_feedback_capabilities(){
	return array('edit_un_feedback_items', 'edit_un_feedback', 'delete_un_feedback', 
		'publish_un_feedback', 'publish_un_feedback_items', 
		'edit_others_un_feedback_items', 'edit_published_feedback');
}
function un_get_capable_roles(){
	return array('administrator', 'editor');
}

function un_activation_hook(){
	global $un_default_options;
	foreach(array(
		'idea' => __('Idea'), 'question' => __('Question', 'usernoise'), 'problem' => __('Problem', 'usernoise'),
		'praise' => __('Praise', 'usernoise')) as $type => $value){
		if (null == get_term_by('slug', $type, 'feedback_type')){
			wp_insert_term($value, FEEDBACK_TYPE, array('slug' => $type));
		}
	}
	flush_rewrite_rules();
}

function un_deactivation_hook(){
	delete_option('un_version');
	global $wp_roles;
	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();
	foreach(un_get_capable_roles() as $role)
		foreach(un_get_feedback_capabilities() as $cap)
			$wp_roles->remove_cap($role, $cap);
	flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'un_deactivation_hook');
register_activation_hook(__FILE__, 'un_activation_hook');
