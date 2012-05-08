<?php

class UN_Admin_Menu {
	function __construct(){
		add_action('admin_menu', array(&$this, 'action_admin_menu'));
	}
	public function action_admin_menu(){
		global $un_model;
		$pending_feedback_count = '';
		if ($count = $un_model->get_pending_feedback_count()){
			$pending_feedback_count = "<span class=\"awaiting-mod\"><span>$count</span></span>";
		}
		$feedback_slug = "edit.php?order=desc&post_type=" . FEEDBACK . ($count ? '&post_status=pending' : '');
		add_menu_page(__('Usernoise', 'usernoise'), __('Usernoise', 'usernoise') . $pending_feedback_count, 
			get_post_type_object(FEEDBACK)->cap->edit_posts, $feedback_slug);
		add_submenu_page($feedback_slug, __('Settings', 'usernoise'), __('Settings', 'usernoise'), 
			'manage_options', 'options-general.php?page=usernoise');
	}
}

$un_admin_menu_class = apply_filters('un_admin_menu_class', 'UN_Admin_Menu');
$un_admin_menu = new $un_admin_menu_class;