<?php

function un_the_feedback_status(){
	echo un_get_the_feedback_status();
}

function un_get_the_feedback_status(){
	global $unpro_model, $id;
	return $unpro_model->get_feedback_status_name($id);
}

function un_the_feedback_status_slug(){
	echo un_get_the_feedback_status_slug();
}

function un_get_the_feedback_status_slug(){
	global $unpro_model, $id;
	return $unpro_model->get_feedback_status($id);
}

function un_the_feedback_likes(){
	echo un_get_the_feedback_likes();
}

function un_get_the_feedback_likes(){
	global $id;
	return get_post_meta($id, '_likes', true);
}

function usernoisepro_url($path){
	return plugins_url() . '/' . USERNOISEPRO_DIR . $path;
}

function usernoisepro_path($path){
	return WP_PLUGIN_DIR . '/' . USERNOISEPRO_DIR . $path;
}
