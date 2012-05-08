<?php
class UNPRO_Feedback_List {
	function __construct(){
		add_action('admin_print_styles-edit.php', array(&$this, 'action_enqueue_scripts'));
		add_action('manage_un_feedback_posts_custom_column', 
			array(&$this, 'action_manage_feedback_posts_custom_column'), 10, 2);
		add_filter('un_feedback_columns', array(&$this, '_un_feedback_columns'));
		add_action('restrict_manage_posts', array(&$this, '_restrict_manage_posts'));
	}
	
	public function _un_feedback_columns($columns){
		$columns['un-status'] = __('Status', 'usernoise-pro');
		return $columns;
	}
		
	public function action_manage_feedback_posts_custom_column($column_name, $post_id){
		global $un_h;
		if ($column_name == 'un-status'){
			echo get_post_meta($post_id, '_status', true);
		}
	}
	
	public function action_enqueue_scripts(){
		global $post_type;
		if ($post_type != FEEDBACK)
			return;
		wp_enqueue_style('un-pro-admin', usernoisepro_url('/css/admin.css'));
	}
	
	public function _restrict_manage_posts(){
		global $post_type;
		global $wp;
		global $un_h;
		global $unpro_model;
		if ($post_type != FEEDBACK)
			return;
		$statuses = $unpro_model->get_statuses();
		$un_h->select('un_status', $un_h->hash2options($statuses, __('All feedback statuses', 'usernoise-pro')),
			stripslashes(isset($_REQUEST['un_status']) ? $_REQUEST['un_status'] : ''));
	}
}
new UNPRO_Feedback_List;
