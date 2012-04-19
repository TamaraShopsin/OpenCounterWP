<?php
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
		exit();
		
	global $wpdb;
	
	$form_table = $wpdb->prefix . 'vfb_pro_fields';
	$fields_table = $wpdb->prefix . 'vfb_pro_forms';
	$entries_table = $wpdb->prefix . 'vfb_pro_entries';
	
	$wpdb->query( "DROP TABLE IF EXISTS $form_table" );
	$wpdb->query( "DROP TABLE IF EXISTS $fields_table" );
	$wpdb->query( "DROP TABLE IF EXISTS $entries_table" );
	
	delete_option( 'vfb_pro_db_version' );
	delete_option( 'visual-form-builder-screen-options' );
	delete_option( 'vfb_db_upgrade' );
	delete_option( 'vfb_ignore_notice' );
	
	$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "usermeta WHERE meta_key IN ( 'vfb-form-settings' )" ) );
?>