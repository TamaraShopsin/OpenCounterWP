<?php

global $wpdb;

define('SP_VERSION',		'1.4.1');
define('SP_DEBUG',			false);

define('SP_DIR',			dirname(__FILE__).'/');
define('SP_URL',			plugins_url('/', __FILE__));

define('SP_SUBMIT',			'lib/submit.php');
define('SP_DISPLAY',		'view/client/display.php');
define('SP_RESULTS',		'view/client/results.php');
define('SP_ADMIN_FOLDER',	'view/admin');

define('SP_TABLE',			$wpdb->get_blog_prefix().'sp_polls');

define('SP_DIRECT_ACCESS',	'I don\'t think you should be here?');

define('SP_CSS_CLIENT',		plugins_url('view/client/simply-poll.css', __FILE__));
define('SP_CSS_ADMIN',		plugins_url('view/admin/admin-simply-poll.css', __FILE__));
define('SP_JS_CLIENT',		plugins_url('script/simplypoll.js', __FILE__));
define('SP_JS_ADMIN',		plugins_url('script/simplypoll-admin.js', __FILE__));