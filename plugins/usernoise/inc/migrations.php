<?php

class UN_Migrations {
	function __construct(){
		add_action('plugins_loaded', array(&$this, 'action_plugins_loaded'));
	}
	
	public function action_plugins_loaded(){
		global $wpdb;
		$db_version = get_option('un_version');
		if ($db_version == UN_VERSION)
			return;
		if (!$db_version){
			add_option('un_version', UN_VERSION);
			$wpdb->query("UPDATE $wpdb->postmeta 
				INNER JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
				SET meta_key = '_email' 
				WHERE meta_key = 'email' AND post_type = 'feedback'
				");
		}
		if (version_compare($db_version, '0.4') == -1){
			$wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_type = %s WHERE post_type = %s",
				FEEDBACK, 'feedback'));
			global $wp_roles;
			if ( ! isset( $wp_roles ) )
				$wp_roles = new WP_Roles();
			foreach(un_get_capable_roles() as $role)
				foreach(un_get_feedback_capabilities() as $cap)
							$wp_roles->add_cap($role, $cap);
		}
		if ((version_compare($db_version, '0.6') == -1)){
			$options = array(UN_USE_FONT,
				UN_FEEDBACK_BUTTON_TEXT,
				UN_FEEDBACK_BUTTON_COLOR,
				UN_FEEDBACK_BUTTON_TEXT_COLOR,
				UN_FEEDBACK_BUTTON_POSITION,
				UN_FEEDBACK_FORM_TITLE,
				UN_FEEDBACK_FORM_TEXT,
				UN_FEEDBACK_FORM_SHOW_SUMMARY,
				UN_FEEDBACK_FORM_SHOW_TYPE,
				UN_FEEDBACK_FORM_SHOW_EMAIL,
				UN_SUBMIT_FEEDBACK_BUTTON_TEXT,
				UN_THANKYOU_TITLE,
				UN_THANKYOU_TEXT,
				UN_ADMIN_NOTIFY_ON_FEEDBACK,
				UN_SUBMIT_FEEDBACK_BUTTON_TEXT,
				UN_SHOW_POWERED_BY,
				UN_FEEDBACK_BUTTON_SHOW_BORDER,
				UN_DISABLE_ON_MOBILES,
				UN_ENABLED, UN_LOAD_IN_FOOTER);
			foreach($options as $option){
				$value = get_option('un_' . $option);
				if ($value){
					un_set_option($option, $value);
				}
				delete_option('un_' . $option);
			}
		}
		update_option('un_version', UN_VERSION);
	}
}

$un_migrations = new UN_Migrations();

?>