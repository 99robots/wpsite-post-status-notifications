<?php

	/* if uninstall not called from WordPress exit */
	
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
		exit ();
	
	/* Delete all existence of this plugin */

	global $wpdb;
	
	$version_name = 'wpsite_post_status_notification_verison';
	
	if ( !is_multisite() ) {
	
		// Delete blog option
		
		delete_option($version_name);
	} 
	
	else {
	
		// Delete site option
	
		delete_site_option($version_name);
	}
?>