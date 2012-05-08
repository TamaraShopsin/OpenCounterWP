<?php
class UNPRO_Controller{
	function __construct(){
		global $un_controller;
		add_action('un_after_feedback_form', array(&$this, 'action_un_after_feedback_form'));
		add_action('un_before_feedback_form', array(&$this, 'action_un_before_feedback_form'));
		add_action('un_feedback_content_top', array(&$this, 'action_un_feedback_content_top'));
		add_action('wp_ajax_unpro_get_feedback', array(&$this, 'action_un_get_feedback'));
		add_action('wp_ajax_nopriv_unpro_get_feedback', array(&$this, 'action_un_get_feedback'));
		add_action('wp_ajax_unpro_like', array(&$this, 'action_like'));
		add_action('wp_ajax_nopriv_unpro_like', array(&$this, 'action_like'));
		add_action('wp_ajax_unpro_submit_comment', array(&$this, 'action_unpro_submit_comment'));
		add_action('wp_ajax_nopriv_unpro_submit_comment', array(&$this, 'action_unpro_submit_comment'));
		add_action('wp_ajax_nopriv_unpro_get_comments', array(&$this, 'action_unpro_get_comments'));
		add_action('wp_ajax_unpro_get_comments', array(&$this, 'action_unpro_get_comments'));
		remove_action('un_feedback_form_body', array(&$un_controller, 'action_un_feedback_form_body'), 100);
		add_action('un_feedback_form_body', array(&$this, 'action_un_feedback_form_body'));
		add_action('un_after_feedback_wrapper', array(&$this, '_un_after_feedback_wrapper'));
		add_filter('un_window_class', array(&$this, '_un_window_class'));
		add_action('un_head', array(&$this, '_un_head'));
	}
	
	public function action_like(){
		$id = (int)$_REQUEST['id'];
		global $unpro_model;
		setcookie('likes', (isset($_COOKIE['likes']) ? $_COOKIE['likes']  . "," : '') . $id,
			time() + 86400 * 365 * 10,'/');
		echo $unpro_model->add_like($id);
		exit;
	}
	
	public function _un_head(){?>
		<link rel="stylesheet" href="<?php echo esc_attr(usernoisepro_url('/css/usernoise-pro.css')) ?>" type="text/css">
		<link rel="stylesheet" href="<?php echo esc_attr(usernoisepro_url('/css/fixes.css')) ?>" type="text/css">
		<script src="<?php echo esc_attr(usernoisepro_url('/vendor/jquery.tinyscrollbar.js')) ?>"></script>
		<script src="<?php echo esc_attr(usernoisepro_url('/vendor/jquery.resize.js')) ?>"></script>
		<script>
			var pro = <?php echo json_encode(array(
			'popular_feedback' => __('Popular feedback', 'usernoise-pro'),
			'popular_idea' => __('Popular ideas', 'usernoise-pro'),
			'popular_problem' => __('Popular problems', 'usernoise-pro'),
			'popular_question' => __('Popular questions', 'usernoise-pro'),
			'popular_praise' => __('Popular praises', 'usernoise-pro'),
			'enable_discussions' => un_get_option(UNPRO_ENABLE_DISCUSSIONS) ? 'true' : 'false',
			'ajaxurl' => admin_url('admin-ajax.php')
		)); ?>
		</script>
		<script src="<?php echo esc_attr(usernoisepro_url('/js/pro.js')) ?>"></script>
		<?php
	}
	
	
	public function _un_window_class($classes){
		$classes[]= 'pro';
		return $classes;
	}
	
	public function _un_after_feedback_wrapper(){
		global $un_h;
		$h = $un_h;
		require usernoisepro_path('/html/pro.php');
	}
	
	public function action_un_feedback_form_body(){
		if (un_get_option(UN_SHOW_POWERED_BY)) require_once(usernoisepro_path('/html/powered-by.php'));
	}
	
	public function action_un_get_feedback(){
		global $post, $unpro_model;
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		$page = (int)$_REQUEST['page'];
		$params = array('post_type' => FEEDBACK, 'post_status' => 'publish', 
			'paged' => $page, 'posts_per_page' => 5, 
			'feedback_type_slug' => $type,
			'orderby' => 'likes', 'order' => 'DESC');
		ob_start();
		$query = new WP_Query($params);
		if ($query->have_posts()){
			while($query->have_posts()): $query->the_post();
				require(usernoisepro_path('/html/feedback-item.php'));
			endwhile;
		} else {
			$type = 
			require(usernoisepro_path('/html/feedback-blank-slate.php'));
		}
		echo json_encode(array(
			'next_page' => $query->max_num_pages > $page ? $page + 1 : null,
			'html' => ob_get_clean()
		));
		exit;
	}
	
	public function action_unpro_submit_comment(){
		global $unpro_model;
		$post_id = $_REQUEST['post_id'];
		$post = get_post($post_id);
		if ($post->post_type != FEEDBACK)
			wp_die(__('Hackin, huh?'));
		add_action('comment_duplicate_trigger', array(&$this, 'action_comment_duplicate_trigger'));
		$comment = array(
			'comment_post_ID' => $post_id,
			'comment_author' => isset($_REQUEST['name']) && stripslashes($_REQUEST['name']) == __('Name') ? '' : (isset($_REQUEST['name']) ? stripslashes($_REQUEST['name']) : ''),
			'comment_author_email' => isset($_REQUEST['email']) &&  stripslashes($_REQUEST['email']) == __('Your email (will not be published)', 'usernoise-pro') ? '' : (isset($_REQUEST['email']) ? stripslashes($_REQUEST['email']) : ''),
			'comment_content' => stripslashes($_REQUEST['comment']) == _x( 'Comment', 'noun') ? '' : stripslashes($_REQUEST['comment']),
			'comment_author_url' => '',
			'comment_type' => null,
			'user_id' => get_current_user_id()
		);
		$errors = $unpro_model->validate_comment($comment);
		if (!empty($errors)){
			echo json_encode(array('errors' => join('<br>', $errors)));
			exit;
		}
		add_action('comment_post', array(&$this, '_comment_post'), 10, 2);
		$comment_id = wp_new_comment( $comment );
		remove_action('comment_post', array(&$this, '_comment_post'), 10, 2);
		global $comment;
		$comment = get_comment($comment_id);
		ob_start();
		require(usernoisepro_path('/html/comment.php'));
		$html = ob_get_clean();
		$query = new WP_Query(array('p' => $post_id, 'post_type' => FEEDBACK));
		$query->the_post();
		ob_start();
		comments_number(__('No Responses', 'usernoise-pro'), __('One Response', 'usernoise-pro'), 
			__('% Responses', 'usernoise-pro'));
		$count = ob_get_clean();
		echo json_encode(array('success' => true, 'html' => $html, 'count' => $count));
		exit;
	}
	
	public function _comment_post($comment_id, $approved){
		global $unpro_model;
		if (!$approved) return;
		$unpro_model->notify_feedback_author_on_comment($comment_id);
	}
	
	public function action_unpro_get_comments(){
		global $wp_query, $unpro_model, $un_model, $un_h;
		$h = $un_h;
		add_filter('comment_feed_limits', array(&$this, 'filter_unpro_comment_feed_limits'));
		add_filter('comment_feed_orderby', array(&$this, 'filter_unpro_comment_feed_orderby'));

		$query = new WP_Query(array('p' => $_REQUEST['post_id'], 'post_type' => FEEDBACK, 
			'withcomments' => 1, 'feed' => true));
		ob_start();
		$query->the_post();
		$type = $un_model->get_feedback_type(get_the_ID());
		$type = $type->slug;
		$id_or_email = $unpro_model->author_email(get_the_ID());
		require(usernoisepro_path('/html/feedback-description.php'));
		if ($query->have_comments()){
			while($query->have_comments()){
				$query->the_comment();
				require(usernoisepro_path('/html/comment.php'));
			}
		} else {
			require(usernoisepro_path('/html/comments-blank-slate.php'));
		}
		$comments = ob_get_clean();
		ob_start();
		comments_number(__('No Responses', 'usernoise-pro'), __('One Response', 'usernoise-pro'), 
			__('% Responses', 'usernoise-pro'));
		$count = ob_get_clean();
		echo json_encode(array('comments' => $comments, 'count' => $count));
		exit;
	}
	
	function filter_unpro_comment_feed_orderby(){
		return 'comment_date_gmt ASC';
	}
	
	function filter_unpro_comment_feed_limits(){
		return '';
	}
	
	public function action_comment_duplicate_trigger($commentdata){
		die(json_encode(array('errors' => __('Duplicate comment detected', 'usernoise-pro'))));
	}
	
	public static function filter_un_controller_class(){
		return 'UNPRO_Controller';
	}
	
	public function action_un_after_feedback_form(){
		global $unpro_model, $un_h;
		$h = $un_h;
		require(usernoisepro_path('/html/pro.php'));
	}
	
	public function action_un_before_feedback_form(){
		echo '<div id="feedback-blocks-wrapper">';
	}
	
	public function action_un_feedback_content_top(){
		?><a href="#" id="un-feedback-close"><img src="<?php echo usernoise_url("/vendor/facebox/closelabel.png") ?>" title="close" class="close_image" ></a><?php
	}
	
}
global $unpro_controller;
if (un_get_option(UNPRO_ENABLE_DISCUSSIONS)){
	$unpro_controller = new UNPRO_Controller;
}

