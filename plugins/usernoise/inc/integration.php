<?php

class UN_Integration {
	
	public function __construct(){
		if (!is_admin()){
			add_action('init', array(&$this, '_init'));
		}
	}
	
	public function _init(){
		if (is_user_logged_in()){
			wp_enqueue_style('usernoise-adminbar', usernoise_url('/css/admin-bar.css'), null, UN_VERSION);
		}
		if (!un_get_option(UN_ENABLED) || ($this->is_mobile() && un_get_option(UN_DISABLE_ON_MOBILES)))
			return;
		wp_enqueue_script('usernoise-button', usernoise_url('/js/button.js'), array('jquery'),
		 	UN_VERSION);
		wp_enqueue_style('usernoise-button', usernoise_url('/css/button.css'));
		wp_localize_script('usernoise-button', 'usernoiseButton', apply_filters('un_localization_array', array(
			'text' => un_get_option(UN_FEEDBACK_BUTTON_TEXT, __('Feedback', 'usernoise')),
			'style' => sprintf("background-color: %s; color: %s", 
					un_get_option(UN_FEEDBACK_BUTTON_COLOR), un_get_option(UN_FEEDBACK_BUTTON_TEXT_COLOR)),
			'class' => implode(' ', un_button_class()),
			'windowUrl' => admin_url('admin-ajax.php') . "?action=un_load_window",
			'showButton' => apply_filters('un_show_button', true)
			)));
	}
	
	public function is_mobile(){
		if (function_exists('bnc_wptouch_is_mobile')){
			return bnc_wptouch_is_mobile();
		}
		$useragents = apply_filters('un_mobile_agents', array(		
			"iPhone",  				 	// Apple iPhone
			"iPod", 						// Apple iPod touch
			"incognito", 				// Other iPhone browser
			"webmate", 				// Other iPhone browser
			"Android", 			 	// 1.5+ Android
			"dream", 				 	// Pre 1.5 Android
			"CUPCAKE", 			 	// 1.5+ Android
			"blackberry9500",	 	// Storm
			"blackberry9530",	 	// Storm
			"blackberry9520",	 	// Storm v2
			"blackberry9550",	 	// Storm v2
			"blackberry 9800",	// Torch
			"blackberry",
			"webOS",					// Palm Pre Experimental
			"s8000", 				 	// Samsung Dolphin browser
			"bada",				 		// Samsung Dolphin browser
			"Googlebot-Mobile"	// the Google mobile crawler
		));
		
		foreach($useragents as $agent){
			if (preg_match("/$agent/i", $_SERVER['HTTP_USER_AGENT']))
				return true;
		}
		return false;
	}
	
}

$un_integration = new UN_Integration;