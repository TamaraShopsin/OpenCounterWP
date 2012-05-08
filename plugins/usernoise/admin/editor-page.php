<?php

class UN_Admin_Editor_Page{
	
	public function __construct(){
		add_action('admin_print_styles-post.php', array(&$this, 'action_print_styles'));
		add_action('add_meta_boxes_un_feedback', array(&$this, 'action_add_meta_boxes'));
		add_action('post_updated_messages', array(&$this, 'filter_post_updated_messages'));
		add_action('admin_enqueue_scripts', array(&$this, 'action_admin_enqueue_scripts'));
		add_filter('redirect_post_location', array(&$this, '_redirect_post_location'), 10, 2);
	}
	
	public function _redirect_post_location($location, $post_id){
		$post = get_post($post_id);
		if ($post->post_type != FEEDBACK) return $location;
		if (isset($_REQUEST['un_redirect_back']) && $_REQUEST['un_redirect_backp'])
			$location = $_REQUEST['un_redirect_back'];
		else
			$location = admin_url('edit.php?order=desc&post_type=un_feedback&post_status=pending');
		return $location;
	}
	
	public function action_add_meta_boxes($post){
		global $post_new_file;
		if (isset($post_new_file)){
			$post_new_file = null;
		}
		remove_meta_box('submitdiv', FEEDBACK, 'side');
		add_meta_box('submitdiv', __('Publish'), array(&$this, 'post_submit_meta_box'), 
			FEEDBACK, 'side', 'default');
		$title = un_get_feedback_type_span($post->ID);
		add_meta_box('un-feedback-body', 
			$title . ($title ? ": " : '') . esc_html($post->post_title), 
			array(&$this, 'description_meta_box'),
			FEEDBACK);
		add_meta_box('stub-http-headers', __('HTTP Headers', 'usernoise'),
			array(&$this, '_stub_pro_block'), FEEDBACK, 'side', 'default');
		add_meta_box('stub-discussion', __('Discussion'), array(&$this, '_stub_pro_block'), FEEDBACK);
		add_meta_box('stub-debug-info', __('WordPress Debug Info', 'usernoise'),
			array(&$this, '_stub_pro_block'), FEEDBACK);
	}
	
	public function _stub_pro_block($post){?>
		Please <strong><a href="http://codecanyon.net/item/usernoise-pro-advanced-modal-feedback-debug/1420436?ref=karevn" class="un-upgrade">Upgrade to Pro</a></strong> to enable this block.
	<?php
	}
	
	public function action_admin_enqueue_scripts($hook){
		global $post_type;
		if (!($post_type == FEEDBACK && $hook == 'post.php'))
			return;
		wp_enqueue_script('quicktags');
		wp_enqueue_script('un-editor-page', usernoise_url('/js/editor-page.js'));
	}
	
	public function filter_post_updated_messages($messages){
		$messages[FEEDBACK][6] = __('Feedback was marked as reviewed', 'usernoise');
		return $messages;
	}
	
	public function action_print_styles(){
		global $post_type;
		if ($post_type == FEEDBACK) {
				wp_enqueue_style('un-admin', usernoise_url('/css/admin.css'));
		}
	}
	
	public function reply_meta_box($post){
		global $un_h;
		require(usernoise_path('/html/reply-meta-box.php'));
	}
	
	public function description_meta_box($post){
		do_action('description_meta_box_top', &$post);
		if (un_feedback_has_author($post->ID)){
			echo '<div class="un-admin-section un-admin-section-first"><strong>' . __('Author') . ': ';
			un_feedback_author_link($post->ID);
			echo "</strong></div>";
		}
		do_action('description_meta_box_before_content', &$post);
		echo '<div class="un-admin-section un-admin-section-last">';
		echo nl2br(esc_html($post->post_content));
		echo '</div>';
		do_action('description_meta_box_bottom', &$post);
	}
		
	public function post_submit_meta_box($post) {
		global $action;
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);
		require(usernoise_path('/html/publish-meta-box.php'));
	}
}

$un_admin_editor_page = new UN_Admin_Editor_Page;