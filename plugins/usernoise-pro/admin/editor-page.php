<?php
class UNPRO_Admin_Editor_Page{
	var $debug_info;
	var $h;
	function __construct(){
		$this->h = new HTML_Helpers_0_4;
		add_action('admin_print_styles-post.php', array(&$this, 'action_enqueue_scripts'));
		add_action('wp_ajax_un_preview_html', array(&$this, 'action_preview_html'));
		if (un_get_option(UNPRO_ENABLE_DISCUSSIONS))
			add_action('post_submitbox_misc_actions', array(&$this, 'post_submitbox_misc_actions'));
		add_action('add_meta_boxes_un_feedback', array(&$this, 'action_add_meta_boxes'), 11);
	}
	
	public function action_preview_html(){
		$id = $_REQUEST['id'];
		echo get_post_meta($id, '_document', true);
		exit;
	}
	
	public function action_add_meta_boxes($post){
		remove_meta_box('stub-http-headers', FEEDBACK, 'side');
		remove_meta_box('stub-debug-info', FEEDBACK, 'advanced');
		remove_meta_box('stub-discussion', FEEDBACK, 'advanced');
		remove_meta_box('un-feedback-body', FEEDBACK, 'advanced');
		add_meta_box('un-feedback-details', __('Details', 'usernoise-pro'),
			array(&$this, 'details_meta_box'), FEEDBACK, 'side');
		add_meta_box('un-technical-details', __('HTTP Headers', 'usernoise'), 
			array(&$this, 'headers_meta_box'), FEEDBACK, 'side');
		add_meta_box('un-page-html', __('HTML Dump', 'usernoise-pro'),
			array(&$this, 'html_meta_box'), FEEDBACK);
		if ($this->debug_info = get_post_meta($post->ID, '_debug', true)){
			if (isset($this->debug_info->rewrite))
				add_meta_box('un-rewrite', __('Rewrite Rules', 'usernoise'),
					array(&$this, 'rewrite_meta_box'), FEEDBACK);
			if (isset($this->debug_info->sql_queries))
				add_meta_box('un-sql-queries', __('SQL Queries', 'usernoise-pro'),
					array(&$this, 'sql_meta_box'), FEEDBACK);
			if (isset($this->debug_info->filled_query_vars)){
				add_meta_box('un-query-vars', __('WP query_vars', 'usernoise-pro'),
					array(&$this, 'query_vars_meta_box'), FEEDBACK);
			}
			if (isset($this->debug_info->filters)){
				add_meta_box('un-filters', __('WP filters', 'usernoise-pro'),
					array(&$this, 'filters_meta_box'), FEEDBACK);
			}
		}
	}
	
	public function details_meta_box($post){
		echo '<div class="un-admin-section un-admin-section-first"><strong>' . __('Type', 'usernoise-pro') . ': ';
		echo un_get_feedback_type_span($post->ID);
		echo "</strong></div>";
		echo '<div class="un-admin-section un-admin-section"><strong>' . __('Likes', 'usernoise-pro') . ': ';
		$likes_count = get_post_meta($post->ID, '_likes', true);
		echo $likes_count ? $likes_count : 0 ;
		echo "</strong></div>";
		if (un_feedback_has_author($post->ID)){
			echo '<div class="un-admin-section un-admin-section-last"><strong>' . __('Author') . ': ';
			un_feedback_author_link($post->ID);
			echo "</strong></div>";
		}
	}
	
	public function filters_meta_box($post){
		$filters = $this->debug_info->filters;
		$h = &$this->h;
		require(usernoisepro_path('/html/filters-meta-box.php'));
	}
	
	public function query_vars_meta_box($post){
		$query_vars = $this->debug_info->filled_query_vars;
		$empty = $this->debug_info->empty_query_vars;
		$h = &$this->h;
		require_once(usernoisepro_path('/html/query-vars-meta-box.php'));
	}
	
	public function rewrite_meta_box($post){
		$h = &$this->h;
		$rewrite = get_object_vars($this->debug_info->rewrite);
		require_once(usernoisepro_path('/html/rewrite-meta-box.php'));
	}
	
	public function sql_meta_box($post){
		$sql = $this->debug_info->sql_queries;
		$h = &$this->h;
		require_once(usernoisepro_path('/html/sql-queries-meta-box.php'));
	}
	
	public function post_submitbox_misc_actions(){
		global $post;
		global $unpro_model;
		if ($post->post_type != FEEDBACK)
			return;
		?>
		<div class="misc-pub-section misc-pub-section-last">
			<span id="timestamp"><?php _e('Status:') ?></span>
			<?php $this->h->select('un_status', $this->h->hash2options($unpro_model->get_statuses()), $unpro_model->get_feedback_status($post)) ?>
		</div><?php // /misc-pub-section ?>
		<?php
	}
	
	
	public function action_enqueue_scripts(){
		global $post_type;
		if ($post_type != FEEDBACK)
			return;
		wp_enqueue_style('un-pro-admin', usernoisepro_url('/css/admin.css'));
	}
	
	public function html_meta_box($post){
		$this->h->tag('pre', esc_html(get_post_meta($post->ID, '_document', true)));
	}
	
	public function headers_meta_box($post){
		$h = &$this->h;
		require(usernoisepro_path('/html/details_meta_box.php'));
	}
	
}
$unpro_admin_editor_page = new UNPRO_Admin_Editor_Page;
