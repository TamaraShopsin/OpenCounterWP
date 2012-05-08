<?php
global $unpro_settings;
class UNPRO_Settings{
	public function __construct(){
		add_filter('un_options', array(&$this, 'filter_un_options'));
		add_filter('un_notification_options', array(&$this, 'filter_notification_options'));
	}
	
	public function filter_notification_options($options){
		$options []= array('type' => 'text', 'name' => UNPRO_ADMIN_NOTIFICATION_EMAIL,
			'title' => __('Admin notifications email', 'usernoise-pro'), 'class' => 'medium',
			'default' => get_option('admin_email'));
		return $options;
	}
	
	public function filter_un_options($options){
		$options []= array('type' => 'tab', 'title' => __('Pro', 'usernoise'));
		$options []= array('type' => 'checkbox', 'name' => UNPRO_ENABLE_DISCUSSIONS,
			'title' => __('Enable feedback list &amp; discussions.', 'usernoise-pro'),
			'label' => __('Enable feedback list &amp; discussions.', 'usernoise-pro'),
			'default' => true);
		$options []= array('type' => 'checkbox', 'name' => UNPRO_USE_ADVANCED_DEBUG,
			'title' => __('Enable advanced debug info', 'usernoise-pro'),
			'label' => __('Enable advanced debug info', 'usernoise-pro'),
			'legend' => __('Dont use in production to prevent system info disclosure.'));
		$options []= array('type' => 'checkbox', 'name' => UNPRO_ENABLE_FEEDS,
			'title' => __('Enable RSS feeds for feedback published', 'usernoise-pro'),
			'default' => false);
		$options []= array('type' => 'section', 'title' => __('Button', 'usernoise-pro'));
		$options []= array('type' => 'checkbox', 'name' => UNPRO_HIDE_FEEDBACK_BUTTON,
			'title' => __('Hide Feedback button', 'usernoise-pro'),
			'label' => __('Hide Feedback button', 'usernoise-pro'),
			'legend' => __('You can hide Feedback button to show it programmatically.', 'usernoise-pro'));
		$options []= array('type' => 'text', 'name' => UNPRO_CUSTOM_BUTTON_ID,
			'title' => __('Alternate "Feedback" button ID', 'usernoise-pro'),
			'class' => 'small',
			'legend' => __('Usernoise window will be shown upon click of element with that ID. For example, if you have the next link: <code>&lt;a href="#" id="my-link"&gt;some link&lt;/a&gt;</code>, put "my-link" here.'));
		$options []= array('type' => 'textarea', 'name' => UNPRO_CUSTOM_BUTTON_CSS,
			'title' => __('Custom CSS properties', 'usernoise-pro'),
			'legend' => __('Usernoise button custom CSS properties. Remove /* and */ to enable. Don\'t put here HTML tags.', 'usernoise-pro'),
			'default' => "/*\r\ncolor: white;\r\nbackground: #444;\r\nfont: 14px bold;\r\nborder: 2px solid white;\r\n*/",
			'rows' => '6');
		$options []= array('type' => 'section', 'title' => __('Form design', 'usernoise-pro'));
		$options []= array('type' => 'textarea', 'name' => UNPRO_FORM_CSS, 
			'title' => __('Custom CSS for Usernoise window', 'usernoise-pro'),
			'legend' => __("You can override the form's style by applying your own CSS. Don't forget to remove comment symbols and do not put HTML tags here.", 'usernoise-pro'),
			'default' => "/*
h2{
	font-size: 29px;
}
p{
	font-size: 13px;
	line-height: 18px;
}
#types-wrapper li{
	
}
#types-wrapper li a{
	
}
textarea#un-description{
	
}
form input[type=text]{
	
}
form input[type=submit]{
	
}
*/");
		return $options;
	}
	
}

$unpro_settings = new UNPRO_Settings;