<?php

class WP_Widget_Recent_Feedback extends WP_Widget {
	
	function __construct() {
		global $unpro_h;
		$widget_ops = array('classname' => 'widget_recent_feedback', 
			'description' => __( "The most recent feedback") );
		parent::__construct('unpro-recent-feedback', __('Recent Feedback'), $widget_ops);
	}

	function widget($args, $instance) {
		global $un_model;
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Feedback', 'usernoise-pro') : $instance['title'], $instance, $this->id_base);
		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
			$number = 10;
		$query = array('post_type' => FEEDBACK, 
			'posts_per_page' => $number, 
			'no_found_rows' => true, 
			'post_status' => 'publish');
		if ($instance['type'])
			$query['tax_query'] = array(
				array('taxonomy' => FEEDBACK_TYPE, 'field' => 'id', 'terms' => (int)$instance['type'])
			);

		if ($instance['status'])
			$query['meta_query'] = array(
				array('key' => '_status', 'value' => $instance['status'])
			);
		$r = new WP_Query($query);
		$has_show_type_filter = has_filter('the_title', 'un_filter_title');
		if (isset($instance['show-type']) && !$instance['show-type'] && $has_show_type_filter)
			remove_filter('the_title', 'un_filter_title');
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php  while ($r->have_posts()) : $r->the_post(); ?>
		<?php $likes = get_post_meta(get_the_ID(), '_likes', true)?>
		<?php $type = $un_model->get_feedback_type(get_the_ID()); ?>
		<li>
			<a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ($type) echo "$type->name: " ?><?php if ( get_the_title() ) the_title(); else the_ID(); ?><?php echo $likes ? " <small>(" . sprintf(_n("%d like", '%d likes', (int)$likes), $likes) . ")</small>" : ''?></a>
		</li>
		<?php endwhile; ?>
		</ul>
		<?php if (!isset($instance['powered']) || $instance['powered']): ?>
				<div style="text-align: right;"><small style="font-size: 90%">Powered by <a href="http://codecanyon.net/item/usernoise-pro-advanced-modal-feedback-debug/1420436?ref=karevn">Usernoise Pro</a></small></div>
		<?php endif ?>
		<?php echo $after_widget; ?>
<?php
		if (isset($instance['show-type']) && $instance['show-type'] && $has_show_type_filter)
			add_filter('the_title', 'un_filter_title', 10, 2);
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['number'] = $new_instance['number'];
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['type'] = absint($new_instance['type']);
		$instance['status'] = $new_instance['status'];
		$instance['likes'] = absint($new_instance['likes']);
		$instance['powered'] = absint($new_instance['powered']);
		$instance['show-type'] = absint($new_instance['show-type']);
		return $instance;
	}


	function form( $instance ) {
		global $unpro_model;
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
		$type = isset($instance['type']) ? absint($instance['type']) : 0;
		$status = isset($instance['status']) ? ($instance['status']) : null;
		$likes = isset($instance['likes']) ? $instance['likes'] : 0;
		$powered = isset($instance['powered']) ? $instance['powered'] : 1;
		$show_type = isset($instance['show-type']) ? $instance['show-type'] : 1;
		$h = new HTML_Helpers_0_4;
		$h->tag('p', 
			$h->_label(__('Title:'), array('for' => $this->get_field_id('title'))) .
			$h->_text_field($this->get_field_name('title'), $title, 
				array('id' => $this->get_field_id('title'), 'class' => 'widefat'))
		);
		$h->tag('p', 
			$h->_label(__('Number of items to show:'), array('for' => $this->get_field_id('number'))) .
			$h->_text_field($this->get_field_name('number'), $number, array('id' => $this->get_field_id('number'), 'size' => 3))
		);
		$h->tag('p', 
			$h->_label(__('Type:'), array('for' => $this->get_field_id('type'))) .
			$h->_select($this->get_field_name('type'),
				$h->collection2options(get_terms(FEEDBACK_TYPE, array('hide_empty' => false)),
					'term_id', 'name', __('Any', 'usernoise-pro')),
			 $type, array('id' => $this->get_field_id('type'), 'class' => 'widefat'))
		);
		
		$h->tag('p', 
			$h->_label(__('Status:', 'usernoise-pro'), array('for' => $this->get_field_id('status'))) .
			$h->_select($this->get_field_name('status'),
				$h->hash2options($unpro_model->get_statuses(), __('Any', 'usernoise-pro')),
			 $status, array('id' => $this->get_field_id('status'), 'class' => 'widefat'))
		);
		$h->tag('p',
			$h->_checkbox($this->get_field_name('likes'), 1, $likes,
				array('class' => 'checkbox', 'id' => $this->get_field_id('likes'))) ." " .
			$h->_label(__('Show likes', 'usernoise-pro'), 
				array('for' => $this->get_field_id('likes')))
		);
		$h->tag('p',
			$h->_checkbox($this->get_field_name('show-type'), 1, $show_type,
				array('class' => 'checkbox', 'id' => $this->get_field_id('show-type'))) ." " .
			$h->_label(__('Show feedback type', 'usernoise-pro'), 
				array('for' => $this->get_field_id('show-type')))
		);
		
		$h->tag('p',
			$h->_checkbox($this->get_field_name('powered'), 1, $powered,
				array('class' => 'checkbox', 'id' => $this->get_field_id('powered'))) ." " .
			$h->_label(__('Show Powered by', 'usernoise-pro'), 
				array('for' => $this->get_field_id('powered')))
		);
	}
}
function unpro_widgets_init(){
	register_widget('WP_Widget_Recent_Feedback');
}
add_action('widgets_init', 'unpro_widgets_init');
