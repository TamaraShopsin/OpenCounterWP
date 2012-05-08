<?php
global $unpro_integration;
class UNPRO_Integration {
	var $data = array();
	var $query_posts_args = array();
	
	function __construct(){
		global $wp_rewrite;
		add_action('un_head', array(&$this, '_un_head'));
		if (is_admin())
			return;
		if (un_get_option(UNPRO_USE_ADVANCED_DEBUG)){
			$this->data = array(
			'rewrite' => $wp_rewrite->rewrite_rules(),
			'filled_query_vars' => $this->get_filled_query_vars(),
			'empty_query_vars' => $this->get_empty_query_vars(),
			'rewrite_tags' => $this->get_rewrite_tags()
			);
		}
		add_action('wp_footer', array(&$this, 'action_footer'), 0);
		add_action('wp_head', array(&$this, '_wp_head'));
		add_action('pre_get_posts', array(&$this, 'action_pre_get_posts'), 9999);
		add_filter('un_localization_array', array(&$this, '_un_localization_array'));
		add_filter('un_show_button', array(&$this, '_un_show_button'));
		if (un_get_option(UNPRO_USE_ADVANCED_DEBUG) && !defined('SAVEQUERIES')){
			define('SAVEQUERIES', 1);
		}
		
	}
	
	public function _un_show_button($value){
		return !un_get_option(UNPRO_HIDE_FEEDBACK_BUTTON);
	}
	
	public function _un_localization_array($localization){
		$localization['custom_button_id'] = un_get_option(UNPRO_CUSTOM_BUTTON_ID);
		return $localization;
	}
	public function action_pre_get_posts(&$query){
		$this->query_posts_args []= $query->query_vars;
	}
	
	public function _wp_head(){
		echo "\r\n<style>\r\n";
		$this->css_rule('#un-button', un_get_option(UNPRO_CUSTOM_BUTTON_CSS));
		echo "</style>\r\n";
	}
	
	public function _un_head(){
		echo "\r\n<style>\r\n";
		echo un_get_option(UNPRO_FORM_CSS);
		echo "\r\n</style>\r\n";
	}
	
	private function css_rule($selector, $body){
		echo "\r\n$selector{\r\n";
		echo $body;
		echo "\r\n}\r\n";
	}
	
	public function action_footer(){
		if (un_get_option(UNPRO_USE_ADVANCED_DEBUG)){
			global $wpdb, $wp_filter;
			$this->data['query_posts_args'] = $this->query_posts_args;
			$this->data['sql_queries'] = $wpdb->queries;
			$this->data['filters'] = $this->get_filters();
			echo "<script>\r\nvar usernoise_debug = '" . base64_encode(gzcompress(json_encode($this->data), 9)) . "';\r\n</script>";
		}
	}
	
	public function get_filters(){
		global $wp_filter;
		$result = array();
		foreach($wp_filter as $name => $filter){
			$filter = is_object($filter) ? get_object_vars($filter): $filter;
			$result[$name] = array();
			foreach($filter as $priority => $functions){
				$result[$name][$priority] = array_keys($functions);
			}
		}
		return $result;
	}
	
	public function get_filled_query_vars(){
		global $wp_query;
		$result = array();
		foreach($wp_query->query_vars as $key => $value){
			if ($value)
				$result[$key] = $value;
		}
		return $result;
	}
	
	public function get_empty_query_vars(){
		global $wp_query;
		$result = array();
		foreach($wp_query->query_vars as $key => $value){
			if (!$value)
				$result[] = $key;
		}
		return $result;
	}
	
	public function get_rewrite_tags(){
		global $wp_rewrite;
		$result = array();
		for($i = 0; $i < count($wp_rewrite->rewritecode); $i++){
				$result[]= array($wp_rewrite->rewritecode[$i],
												$wp_rewrite->rewritereplace[$i],
												$wp_rewrite->queryreplace[$i]);
		}
		return $result;
	}
	
}

$unpro_integration = new UNPRO_Integration;
