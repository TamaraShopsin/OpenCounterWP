<?php
/*
Plugin Name: Simply Poll
Version: 1.4.1
Plugin URI: http://wolfiezero.com/wordpress/simply-poll/
Description: Simply, it adds polling functionailty to your WordPress site
Author: WolfieZero
Author URI: http://wolfiezero.com/
*/

require_once('config.php');
require_once('lib/logger.php');
require_once('lib/simplypoll.php');
require_once('lib/db.php');

global $logger;
$logger = new Logger(dirname(__FILE__).'/', SP_DEBUG);

if( !function_exists('add_action') ) {
	echo SP_DIRECT_ACCESS;
	exit;
}

// Registers the activation hook - runs the install function when the plugin is activated
register_activation_hook(__FILE__, 'spInstall');


add_action('init', 'spFiles');		// Load the enqued files
add_shortcode('poll', 'spClient');	// Load the poll for client view


// If the user is admin then call their class
if( is_admin() ) spAdmin();


/**
 * Simply Poll Client
 * Handles Simply Poll on the client side of the site
 * 
 * @param array $args
 * @return string HTML output of the poll
 */
function spClient($args) {	
	$simplyPoll = new SimplyPoll();
	return $simplyPoll->displayPoll($args);
}


/**
 * Simply Poll Admin 
 * Handles Simply Poll for the admin
 * 
 * @return null
 */
function spAdmin() {
	global $spAdmin;
	require('lib/admin.php');
	$spAdmin = new SimplyPollAdmin();
}


/**
 * Simply Poll Files
 * Loads in the files used for Simply Poll
 */
function spFiles() {	
	wp_register_style('sp-client', SP_CSS_CLIENT, false, SP_VERSION);
	wp_enqueue_style('sp-client');

	wp_enqueue_script('jquery');
	
	wp_enqueue_script('sp-client-ajax', plugins_url('script/simplypoll.js', __FILE__), array('jquery'), SP_VERSION, true);
	wp_localize_script('sp-client-ajax', 'spAjax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );

	// When Submit
	add_action('wp_ajax_spAjaxSubmit', 'spSubmit');				// ajax for logged in users
	add_action('wp_ajax_nopriv_spAjaxSubmit', 'spSubmit');		// ajax for not logged in users
	
	// When Results
	add_action('wp_ajax_spAjaxResults', 'spResults');			// ajax for logged in users
	add_action('wp_ajax_nopriv_spAjaxResults', 'spResults');	// ajax for not logged in users

	return true;
}


function spSubmit() {
	global $logger;
	$logger->log('spSubmit()');
	require(SP_SUBMIT);
	exit;
}

function spResults() {
	global $logger;
	$logger->log('spResults()');
	$logger->logVar($_POST, '$_POST');
	
	if( isset($_POST['pollid']) ) {
		$pollid = $_POST['pollid'];
	}
	
	$simplypoll	= new SimplyPoll(false);
	$results	= $simplypoll->grabPoll($pollid);
	
	$logger->logVar($results, '$results');
	
	$answers	= $results['answers'];
	$totalvotes	= $results['totalvotes'];
	
	require(SP_RESULTS);
	exit;
}


/**
 * Simply Poll Install Script
 * Installs Simply Poll correctly
 * 
 * @return bool
 */
function spInstall() {
	global $wpdb;
	
	$sql = '
		CREATE TABLE IF NOT EXISTS `'.SP_TABLE.'` (
			`id` INT NOT NULL AUTO_INCREMENT ,
			`question` VARCHAR( 512 ) NOT NULL ,
			`answers` TEXT NOT NULL ,
			`added` INT NOT NULL ,
			`active` INT NOT NULL ,
			`totalvotes` INT NOT NULL ,
			`updated` INT NOT NULL ,
			PRIMARY KEY ( `id` )
		)
	';
	
	$success = $wpdb->query($sql);

	return $success;
}