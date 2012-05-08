<?php
class UN_Admin_Bar{
	function __construct(){
		add_action('admin_bar_menu', array(&$this, 'admin_bar_menu'), 60);
		add_action( 'admin_bar_menu', array(&$this, 'action_admin_bar_menu'), 80 );
	}
	
	function admin_bar_menu($wp_admin_bar){
		global $un_model;
		$new_feedback = '';
		if (!current_user_can('edit_others_un_feedback_items'))
			return;
		if ($new_feedback_count = $un_model->get_pending_feedback_count()){
			$new_feedback = "<span id=\"un-new-feedback-count\">$new_feedback_count</span>";
		}
		$wp_admin_bar->add_menu(array(
			'title' => sprintf(__('Usernoise %s'), $new_feedback),
			'href' => admin_url('edit.php?order=desc&post_type=' . FEEDBACK . 
				($new_feedback_count ? '&post_status=pending' : '')),
			'id' => 'un-usernoise'
			));
		$wp_admin_bar->add_menu(array(
			'title' => sprintf(__('Feedback', 'usernoise', 'button'), $new_feedback),
			'href' => admin_url('edit.php?order=desc&post_type=' . FEEDBACK . 
				($new_feedback_count ? '&post_status=pending' : '')),
			'id' => 'un-feedback',
			'parent' => 'un-usernoise'
			));
		$wp_admin_bar->add_menu(array(
			'title' => __('Settings', 'usernoise'),
			'href' => admin_url('options-general.php?page=usernoise'),
			'id' => 'un-settings',
			'parent' => 'un-usernoise'
			));
		
	}
	
	function action_admin_bar_menu(){
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('new-feedback');
	}
}

$un_admin_bar_class = apply_filters('un_admin_bar_class', 'UN_Admin_Bar');
$un_admin_bar = new $un_admin_bar_class;