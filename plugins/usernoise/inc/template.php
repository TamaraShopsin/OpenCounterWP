<?php

add_filter('un_window_class', 'un_detect_browsers');

function usernoise_url($path){
	return plugins_url() . '/' . USERNOISE_DIR . $path;
}

function usernoise_path($path){
	return WP_PLUGIN_DIR . '/' . USERNOISE_DIR . $path;
}

function un_element_class($filter, $default = null){
	$classes = array();
	if ($default){
		if (is_array($default))
			$classes = array_merge($classes, $default);
		else
			$classes []= $default;
	}
	$classes = apply_filters($filter, $classes);
	if (empty($classes))
		return;
	echo 'class="' . esc_attr(join(' ', $classes)) . '" ';
}

function un_element_style($filter, $default = null){
	$style = array();
	if ($default){
		if (is_array($default))
			$style = array_merge($style, $default);
		else
			$style []= $default;
	}
	$style = apply_filters($filter, $style);
	if (empty($style))
		return;
	echo 'style="' . esc_attr(join('; ', $style)) . '" ';
}


function un_window_class(){
	un_element_class('un_window_class');
}

function un_feedback_has_author($id){
	$email = get_post_meta($id, '_email', true);
	$user = get_post_meta($id, '_author', true);
	return $user || $email;
}

function un_feedback_author_email($id){
	$email = get_post_meta($id, '_email', true);
	$user = get_post_meta($id, '_author', true);
	if (!$email && $user){
		$user = get_user_by('id', $user);
		$email = $user->user_email;
	}
	return $email;
}

function un_feedback_author_link($id){
	global $un_h;
	$email = get_post_meta($id, '_email', true);
	$user = get_post_meta($id, '_author', true);
	if ($email){
		$un_h->tag('a', array('href' => 'mailto:' . esc_html($email)), esc_html($email) );
	}
	if ($user){
		if ($email){
			echo (' ' . __('or', 'usernoise') . ' ');
		}
		$user_object = get_user_by('id', $user);
		if ($user_object){
			$un_h->tag('a', array('href' => 
				admin_url('user-edit.php?user_id=' . $user . 
					'_wp_http_referer=' . admin_url('post.php?post=' . $id . '&action=edit'))), 
					esc_html(get_userdata($user)->display_name));
		}
	}
}

function un_feedback_author_name($id){
	$email = get_post_meta($id, '_email', true);
	$user = get_post_meta($id, '_author', true);
	if ($user){
		return get_userdata($user)->display_name;
	}
	return preg_replace('/@.*/', '', $email);
}

function un_get_feedback_type_span($id, $show_text = true){
	global $un_h;
	if($type = wp_get_post_terms($id, FEEDBACK_TYPE)){
		$img = $un_h->_tag('span',
			array('class' => array('un-feedback-type', 'un-feedback-type-' . $type[0]->slug))
			);
		return $img . ($show_text ?  "&nbsp;" . __(esc_html($type[0]->name), 'usernoise') : '');
	}
	return null;
}

function un_button_style(){
	un_element_style('un-button_style');
}

function un_option_or_text($option_name, $default_text){
	$text = un_get_option($option_name);
	if (empty($text))
		echo $default_text;
	echo $text;
}

function un_feedback_button_text(){
	un_option_or_text(UN_FEEDBACK_BUTTON_TEXT, __('Feedback', 'usernoise'));
}

function un_submit_feedback_button_text(){
	un_option_or_text(UN_SUBMIT_FEEDBACK_BUTTON_TEXT, __('Submit feedback', 'usernoise'));
}

function un_feedback_form_text(){
	un_option_or_text(UN_FEEDBACK_FORM_TEXT, 
		__('Please tell us what do you think, any kind of feedback is highly appreciated.', 'usernoise'));
}

function capture_html($file){
	ob_start();
	require($file);
	return ob_get_clean();
}

function un_detect_browsers($classes){
	$old = false;
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') !== false){
		$classes []= 'ie7';
		$old = true;
	}
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8') !== false){
		$classes []= 'ie8';
		$old = true;
	}
	if (!$old){
		$classes []= 'css3';
	}
	return $classes;
}


add_filter('un_window_class', 'un_filter_set_window_font_class');
function un_filter_set_window_font_class($classes){
	$classes []= sanitize_title(un_get_option(UN_USE_FONT));
	return $classes;
}

function un_button_class(){
	if ($option = un_get_option(UN_FEEDBACK_BUTTON_POSITION))
		$classes []= "un-" . $option;
	if (empty($classes))
		$classes []= 'un-left';
	if (un_get_option(UN_FEEDBACK_BUTTON_SHOW_BORDER))
		$classes []= 'un-has-border';
	return apply_filters('un_button_class', $classes);
}

