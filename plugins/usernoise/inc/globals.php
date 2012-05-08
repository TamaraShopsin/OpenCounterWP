<?php
$un_model_class = apply_filters('un_model_class', 'UN_Model');
$un_model = new $un_model_class;

if (!is_admin() || defined('DOING_AJAX')){
	$un_controller_class = apply_filters('un_controller_class', 'UN_Controller');
	$un_controller = new $un_controller_class;
}
if (!is_admin()){
	$un_integration_class = apply_filters('un_integration_class', 'UN_Integration');
	$un_integration = new $un_integration_class;
} else {
	$un_admin_editor_page_class = apply_filters('un_admin_editor_page_class', 'UN_Admin_Editor_Page');
	$un_admin_editor_page = new $un_admin_editor_page_class;
	$un_settings_class = apply_filters('un_settings_class', 'UN_Settings');
	$un_settings = new $un_settings_class;
}