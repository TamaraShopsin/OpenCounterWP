<?php
class UNPRO_Updater{
	var $api_url = 'http://api.karevn.com/';
	
	public function __construct(){
		add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_for_updates'));
		// Take over the Plugin info screen
		add_filter('plugins_api', array(&$this, 'my_plugin_api_call'), 10, 3);
	}
	
	// Take over the update check
	public function check_for_updates($checked_data) {
		if (empty($checked_data->checked))
			return $checked_data;
		// Start checking for an update
		$request = $this->prepare_request('basic_check', 
			array(
			'slug' => USERNOISEPRO_SLUG,
			'version' => $checked_data->checked[USERNOISEPRO_BASENAME]
			)
		);
		$raw_response = wp_remote_post($this->api_url, $request);
		if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)){
			$response = unserialize($raw_response['body']);
			if (is_object($response) && !empty($response) && 
				version_compare($checked_data->checked[USERNOISEPRO_BASENAME], $response->version) == -1) // Feed the update data into WP updater
				$checked_data->response[USERNOISEPRO_BASENAME] = $response;
		}
		return $checked_data;
	}



	public function my_plugin_api_call($def, $action, $args) {
		if ($args->slug != USERNOISEPRO_SLUG)
			return false;
	
		// Get the current version
		$plugin_info = get_site_transient('update_plugins');
		$current_version = $plugin_info->checked[USERNOISEPRO_BASENAME];
		$args->version = $current_version;
		$request = wp_remote_post($this->api_url, $this->prepare_request($action, $args));
		if (is_wp_error($request)) {
			$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
		} else {
			$res = unserialize($request['body']);
			if ($res === false)
				$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
		}
		return $res;
	}


	function prepare_request($action, $args) {
		global $wp_version;
	
		return array(
			'body' => array(
				'action' => $action, 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url')),
				'url' => get_bloginfo('url')
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);	
	}
}

$unpro_updater = new UNPRO_Updater;
