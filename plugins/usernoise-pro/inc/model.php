<?php
global $unpro_model;
class UNPRO_Model{
	function __construct(){
		add_filter('un_feedback_post_type_params', array(&$this, '_un_feedback_post_type_params'), 11);
		add_action('un_feedback_created', array(&$this, 'action_un_feedback_created'), 10, 2);
		add_filter('posts_orderby', array(&$this, 'filter_posts_orderby'), 10, 2);
		add_filter('posts_join', array(&$this, 'filter_posts_join'), 10, 2);
		add_filter('un_admin_notification_message', array(&$this, '_un_admin_notification_message'), 10, 3);
		add_filter('un_admin_notification_email', array(&$this, 'filter_admin_notification_email'));
		add_filter('un_admin_notification_headers', array(&$this, 'filter_admin_notification_headers'), 10, 2);
		add_action('save_post', array(&$this, 'action_save_post'));
		add_filter('un_feedback_type_taxonomy_params', array(&$this, '_un_feedback_type_taxonomy_params'));
		add_action('wp_set_comment_status', array(&$this, '_set_comment_status'), 10, 2);
		add_filter('query_vars', array(&$this, '_query_vars'));
	}
	
	public function _query_vars($query_vars){
		$query_vars []= 'un_status';
		return $query_vars;
	}
	
	public function _set_comment_status($comment_id, $new_status){
		if (!($new_status == '1' || $new_status == 'approve')) return;
		$this->notify_feedback_author_on_comment($comment_id);
	}
	
	public function _un_feedback_type_taxonomy_params($params){
		$params['public'] = true;
		$params['rewrite'] = array('slug' => 'feedback-types');
		$params['label'] = __('Feedback types', 'usernoise-pro');
		return $params;
	}
	
	public function _un_feedback_post_type_params($params){
		$params['supports'] = array('title', 'editor', 'comments');
		$params['rewrite'] = array('slug' => 'feedback', 'feeds' => un_get_option(UNPRO_ENABLE_FEEDS));
		$params['public'] = true;
		$params['show_in_nav_menus'] = true;
		$params['has_archive'] = true;
		return $params;
	}
	
	
	public function _un_admin_notification_message($message, $id, $params){
		$message .= "\r\n\r\n";
		$email = un_feedback_author_email($id);
		if ($email)
			$message .= __('From:', 'usernoise-pro') . " <a href=\"mailto:" . esc_attr($email) ."\">" . esc_html($email) . "</a>\r\n";
		$post = get_post($id);
		$message .= $post->post_content;
		return $message;
	}
	
	public function action_un_feedback_created($id, $params){
		if (isset($params['referer']))
			add_post_meta($id, '_url', $params['referer']);
		if (isset($_SERVER['HTTP_USER_AGENT']))
			add_post_meta($id, '_user_agent', $_SERVER['HTTP_USER_AGENT']);
		if (isset($_SERVER['REMOTE_ADDR']))
			add_post_meta($id, '_ip', $_SERVER['REMOTE_ADDR']);
		if (isset($params['document']))
			add_post_meta($id, '_document', $params['document']);
		if (isset($params['debug']))
			add_post_meta($id, '_debug', json_decode(html_entity_decode(gzuncompress(base64_decode($params['debug'])))));
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			add_post_meta($id, '_x_forwarded_for', $_SERVER['HTTP_X_FORWARDED_FOR']);
	}
	
	public function action_save_post($id){
		global $unpro_model;
		$post = get_post($id);
		if ($post->post_type != FEEDBACK)
			return;
		if (isset($_REQUEST['un_status']))
			$unpro_model->set_feedback_status($id, $_REQUEST['un_status']);
	}
	
	public function filter_admin_notification_email($email){
		return un_get_option(UNPRO_ADMIN_NOTIFICATION_EMAIL);
	}
	
	public function filter_admin_notification_headers($headers, $id){
		if ($email = un_feedback_author_email($id)){
			$headers []= "Reply-To: $email";
		}
		return $headers;
	}

	public function filter_posts_orderby($orderby, &$query){
		if ($this->is_order_by_likes($query)){
			return 'posts_likes.meta_value DESC';
		}
		return $orderby;
	}
	
	public function filter_posts_join($join, &$query){
		global $wpdb;
		if ($this->is_order_by_likes($query)){
			 $join .= " LEFT OUTER JOIN $wpdb->postmeta posts_likes ON $wpdb->posts.ID = posts_likes.post_id AND posts_likes.meta_key = '_likes' ";
		}
		if ($this->is_status_query($query)){
			$join .= $wpdb->prepare(" INNER JOIN $wpdb->postmeta un_posts_statuses ON $wpdb->posts.ID = un_posts_statuses.post_id AND un_posts_statuses.meta_key = '_status' AND un_posts_statuses.meta_value = %s ", stripslashes($query->query_vars['un_status']));
		}
		return $join;
	}
	
	private function is_status_query($query){
		return isset($query->query_vars['un_status']) && trim($query->query_vars['un_status']);
	}
	
	private function is_order_by_likes($query){
		return isset($query->query_vars['orderby']) && $query->query_vars['orderby'] == 'likes';
	}
	
	public function add_like($id){
		$post = get_post($id);
		if ($post->post_type != FEEDBACK){
			wp_die(__('Hacking, huh?'));
		}
		if (get_post_meta($id, '_likes', true)){
			global $wpdb;
			$likes = get_post_meta($id, '_likes', true);
			$likes++;
			update_post_meta($id, '_likes', $likes);
			return $likes;
		} else {
			add_post_meta($id, '_likes', 1);
			return 1;
		}
	}
	
	public function validate_comment($params){
		if (empty($params['comment_author']) && !$params['user_id'])
			return array(__('Enter your name', 'usernoise-pro'));
		if (empty($params['comment_author_email']) && !$params['user_id'])
			return array(__('Enter an email', 'usernoise-pro'));
		if (empty($params['comment_content']))
			return array(__('Please say something', 'usernoise-pro'));
		return array();
	}
	
	public function get_statuses(){
		return apply_filters('un_statuses', array(
			'new' => __('New', 'usernoise-pro'),
			'rejected' => __('Rejected', 'usernoise-pro'),
			'planned' => __('Planned', 'usernoise-pro'),
			'in_progress' => __('In progress', 'usernoise-pro'),
			'done' => __('Done', 'usernoise-pro')
		));
	}
	public function get_default_status(){
		$statuses = $this->get_statuses();
		$keys = array_keys($statuses);
		return apply_filters('un_default_status', $keys[0]);
	}
	
	public function set_feedback_status($feedback, $status){
		if (is_object($feedback))
			$feedback = $feedback->ID;
		update_post_meta($feedback, '_status', $status);
	}
	
	public function get_feedback_status($feedback){
		if (is_object($feedback))
			$feedback = $feedback->ID;
		if (!($status = get_post_meta($feedback, '_status', true)))
			$status = $this->get_default_status();
		return $status;
	}
	
	public function get_feedback_status_name($feedback){
		$statuses = $this->get_statuses();
		return $statuses[$this->get_feedback_status($feedback)];
	}
	
	public function author_email($id){
		$email = get_post_meta($id, '_email', true);
		$user = get_post_meta($id, '_author', true);
		if ($user){
			$user = get_userdata($user)->user_email;
		}
		if ($user) return $user;
		return $email;
	}
	
	public function notify_feedback_author_on_comment($comment_id){
		$comment = get_comment($comment_id);
		$post = get_post($comment->comment_post_ID);
		$email = get_post_meta($post->ID, '_email', true);
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
		if (!$email){
			$author  = get_userdata( $post->post_author );
			$email = $author->user_email;
		}
		if ($email == get_option('admin_email') || !trim($email))
			return;
		if ( !(get_option('comments_notify') && $approved && 
			( ! $comment->user_id ) || $post->post_author != $comment->user_id ) )
			return;
		$subject = sprintf( __('[%1$s] Comment: "%2$s"'), $blogname, $post->post_title );
		$notify_message  = sprintf( __( 'New comment on your feedback "%s" at %s', 'usernoise-pro' ), 
			$post->post_title, get_bloginfo('url') ) . "\r\n";
		/* translators: 1: comment author, 2: author IP, 3: author domain */
		$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
		$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
		$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
		
		if ( EMPTY_TRASH_DAYS )
			$notify_message .= sprintf( __('Trash it: %s'), admin_url("comment.php?action=trash&c=$comment_id") ) . "\r\n";
		else
			$notify_message .= sprintf( __('Delete it: %s'), admin_url("comment.php?action=delete&c=$comment_id") ) . "\r\n";
		$notify_message .= sprintf( __('Spam it: %s'), admin_url("comment.php?action=spam&c=$comment_id") ) . "\r\n";
		@wp_mail($email, $subject, $notify_message );
	}
}

$unpro_model = new UNPRO_Model();