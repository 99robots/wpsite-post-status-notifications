<?php

// if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all existence of this plugin
global $wpdb;

$option_name = 'wpsite_post_status_notifications_settings';

if ( ! is_multisite() ) {

	// Delete blog option
	delete_option( $option_name );
} else {

	// Used to delete each option from each blog
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );

		// Delete blog option
		delete_option( $option_name );
	}

	restore_current_blog();
}
