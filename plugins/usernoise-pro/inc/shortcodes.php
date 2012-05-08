<?php

class UNPRO_Shortcodes {
	function __construct(){
		add_shortcode('usernoise-link', array(&$this, '_usernoise_link'));
	}
	
	public function _usernoise_link($attributes, $content){
		return '<a href="#" rel="usernoise">' . $content . "</a>";
	}
}
$unpro_shortcodes = new UNPRO_Shortcodes;