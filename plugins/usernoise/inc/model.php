<?php

class UN_Model{
	
	public function __construct(){
		add_action('init', array(&$this, 'action_init'));
		add_action('parse_query', array(&$this, 'action_parse_query'));
		add_filter('un_feedback_post_type_params', array(&$this, 'filter_un_feedback_post_type_params'));
	}
	
	public function action_init(){
		global $wp;
		$wp->add_query_var('feedback_type_id');
		register_taxonomy(FEEDBACK_TYPE, FEEDBACK, 
			apply_filters('un_feedback_type_taxonomy_params', array(
				'public' => false,
				'show_ui' => false,
				'rewrite' => false
		)));
		register_post_type(FEEDBACK, apply_filters('un_feedback_post_type_params', array(
			'label' => _x('Feedback', 'admin', 'usernoise'),
			'labels' => array(
				'name' => _x('Feedback', 'admin', 'usernoise'),
				'singular_name' => _x('Feedback', 'admin', 'usernoise'),
				'add_new' => __('Add new', 'usernoise', 'usernoise'),
				'add_new_item' => __('Add new feedback', 'usernoise'),
				'edit_item' => __('View feedback', 'usernoise'),
				'new_item' => __('New feedback', 'usernoise'),
				'view_item' => __('View feedback', 'usernoise'),
				'search_items' => __('Search feedback', 'usernoise'),
				'not_found' => __('Feedback not found', 'usernoise'),
				'not_found_in_trash' => __('Feedback not found in Trash', 'usernoise'),
				'menu_name' => __('Usernoise', 'usernoise')
			),
			'description' => __('Feedback left by users using a form in a lightbox', 'usernoise'),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'supports' => array('title', 'editor', 'comments'),
			'rewrite' => false,
			'show_in_nav_menus' => false,
			'capability_type' => array(FEEDBACK, 'un_feedback_items')
		)));
	}
	
	public function filter_un_feedback_post_type_params($params){
		$params['supports'] = array(null);
		return $params;
	}
	
	public function validate_feedback_form($params){
		$errors = array();
		if (un_get_option(UN_FEEDBACK_FORM_SHOW_SUMMARY) && !trim($params['title'])){
			$errors []= __('Please enter a summary.', 'usernoise');
		}
		if (!trim($params['description']))
			$errors []= __('Please enter the feedback.', 'usernoise');
		if (un_get_option(UN_FEEDBACK_FORM_SHOW_EMAIL) && empty($params['email']) && !is_user_logged_in() || 
				(isset($params['email']) && $params['email'] && !is_email($params['email'])))
			$errors []= __('Please enter a valid email address.', 'usernoise');
		return apply_filters('un_validate_feedback', $errors, $params);
	}
	
	public function create_feedback($params){
		if (isset($params['title']) && $params['title'])
			$title = $params['title'];
		$content = $params['description'];
		if (empty($params['title']))
			$title = substr($content, 0, 150) . (strlen($content) < 150 ? '' : "…");
		$id = wp_insert_post(array(
			'post_type' => FEEDBACK,
			'post_title' => apply_filters('un_feedback_title', $title, $params),
			'post_content' => apply_filters('un_feedback_content', $content, $params),
			'post_status' => 'pending'
		));
		$email = isset($params['email']) ? trim($params['email']) : '';
		if ($email)
			add_post_meta($id, '_email', $email);
		if (is_user_logged_in()){
			add_post_meta($id, '_author', get_current_user_id());
		}
		wp_set_post_terms($id, $params['type'], FEEDBACK_TYPE);
		do_action('un_feedback_created', $id, $params);
		$this->send_admin_message($id, $params);
	}
	
	public function send_admin_message($id, $params){
		if (!un_get_option(UN_ADMIN_NOTIFY_ON_FEEDBACK))
			return;
		$type = $params['type'] ? $params['type'] : __('feedback');
		$message = sprintf(__('A new %s has been submitted. View it: <a href="%s">%s</a>.'), 
			$type,
			admin_url('post.php?action=edit&post=' . $id),
			admin_url('post.php?action=edit&post=' . $id)
		);
		$message = apply_filters('un_feedback_received_message', $message, $id, $params);
		$message = apply_filters('un_admin_notification_message', $message, $id, $params);
		$subject = apply_filters('un_admin_notification_subject', 
			sprintf(__('New %s submitted at %s'), __($type, 'usernoise'), get_bloginfo('name')));
		$to = apply_filters('un_admin_notification_email', get_option('admin_email'));
		$headers = apply_filters('un_admin_notification_headers', array('Content-type: text/html'), $id);
		wp_mail($to, $subject, $message, $headers);
		do_action('un_admin_notification_sent', $id, $params, $message);
	}
	
	public function action_parse_query(&$q){
		if (isset($q->query_vars['feedback_type_id']) && 
			$q->query_vars['feedback_type_id']){
			if (empty($q->query_vars['tax_query'])){
				$q->query_vars['tax_query'] = array();
			}
			$q->query_vars['tax_query'] []= array(
				'taxonomy' => FEEDBACK_TYPE,
				'field' => 'id',
				'terms' => (int)$q->query_vars['feedback_type_id']
				);
		}
		if (isset($q->query_vars['feedback_type_slug']) && 
			$q->query_vars['feedback_type_slug']){
			if (empty($q->query_vars['tax_query'])){
				$q->query_vars['tax_query'] = array();
			}
			$q->query_vars['tax_query'] []= array(
				'taxonomy' => FEEDBACK_TYPE,
				'field' => 'slug',
				'terms' => $q->query_vars['feedback_type_slug']
				);
		}
	}
	
	public function get_pending_feedback_count($args = array()){
		return count($this->get_pending_feedback($args));
	}
	
	public function get_pending_feedback($args = array()){
		$defaults = array('post_type' => FEEDBACK, 'numberposts' => -1, 'post_status' => 'pending');
		$args = wp_parse_args($args, $defaults);
		return get_posts($args);
	}
	
	public function get_feedback_type($feedback){
		if (is_object($feedback))
			$feedback = $feedback->ID;
		$terms = wp_get_post_terms($feedback, FEEDBACK_TYPE);
		if (count($terms))
			return $terms[0];
		return null;
	}
	
	public function get_plural_feedback_type_label($type){
		$types = array(
			'idea' => __('ideas', 'usernoise'),
			'question' => __('questions', 'usernoise'),
			'problem' => __('problems', 'usernoise'),
			'praise' => __('praises', 'usernoise')
		);
		return $types[$type];
	}
}

$un_model = new UN_Model;
