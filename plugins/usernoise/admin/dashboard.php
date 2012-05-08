<?php
class UN_Dashboard{
	function __construct(){
		add_action('wp_dashboard_setup', array(&$this, 'action_wp_dashboard_setup'), 0);
		add_action('admin_print_styles-index.php', array(&$this, 'action_enqueue_scripts'));
	}
	
	public function action_enqueue_scripts(){
		if (!current_user_can('edit_others_un_feedback_items')) return;
		wp_enqueue_style('un-admin', usernoise_url('/css/admin.css'));
	}
	
	public function action_wp_dashboard_setup(){
		if (!current_user_can('edit_others_un_feedback_items')) return;
		wp_add_dashboard_widget('dashboard_usernoise', __('New feedback', 'usernoise'), array(&$this, 'widget_callback'));
	}
	
	public function widget_callback(){
		global $un_h;
		query_posts(array('post_type' => FEEDBACK, 'posts_per_page' => 6));
		$count = 0;
		if (have_posts()):
			echo('<ul>');
			while(have_posts() && $count++ < 5): the_post();
				echo("<li>");
				echo un_get_feedback_type_span(get_the_ID(), false);
				echo(" ");
				edit_post_link(get_the_title(), '', '', get_the_ID());
				echo("");
				if (un_feedback_has_author(get_the_ID())){
					echo(' ');
					_e('by');echo(" ");
					un_feedback_author_link(get_the_ID());
					echo('');
				}
				echo("</li>");
			endwhile;
			echo('</ul>');
			if (have_posts()):
				$un_h->tag('a', array('href' => 'edit.php?order=desc&post_type=' . FEEDBACK, 
					'id' => 'un-view-more'),
					__('View all feedback', 'usernoise'));
			endif;
		else: 
			_e('No new feedback now.', 'usernoise');
		endif;
		
	}
}

$un_dashboard_class = apply_filters('un_dashboard_class', 'UN_Dashboard');
$un_dashboard = new $un_dashboard_class();