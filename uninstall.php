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
		
		/*
// Delete post meta data
		
		$posts = get_posts(array('posts_per_page' => -1));
		
		foreach ($posts as $post) {
			$post_meta = get_post_meta($post->ID);
			delete_post_meta($post->ID, $post_meta_data_name);
		} 
		
		// Delete user meta data
		
		$users = get_users();
		
		foreach ($users as $user) {
			delete_user_meta($user->ID, $user_meta_data_name);
		}
*/
	} 
	
	else {
	
		// Delete site option
	
		delete_site_option($version_name);
		
		/*
// Used to delete each option from each blog
		
	    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	
	    foreach ( $blog_ids as $blog_id ) 
	    {
	        switch_to_blog( $blog_id );
	        
	        // Delete blog option
		
			delete_option($blog_option_name);
			
			// Delete post meta data
			
			$posts = get_posts(array('posts_per_page' => -1));
			
			foreach ($posts as $post) {
				$post_meta = get_post_meta($post->ID);
				delete_post_meta($post->ID, $post_meta_data_name);
			} 
			
			// Delete user meta data
			
			$users = get_users();
			
			foreach ($users as $user) {
				delete_user_meta($user->ID, $user_meta_data_name);
			}
	        
	        restore_current_blog();
	    }
*/
	}
?>