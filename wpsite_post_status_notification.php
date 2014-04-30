<?php
/*
Plugin Name: WPSite Post Status Notifications
plugin URI: http://www.wpsite.net/plugin/post-status-notifications
Description: Send post status notifications by email to Administrators and Contributors when posts are submitted for review or published. Great for multi-author sites to improve editorial workflow.
version: 1.0
Author: WPSITE.net
Author URI: http://wpsite.net
License: GPL2
*/

/** 
 * Global Definitions 
 */

/* Plugin Name */

if (!defined('WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_NAME'))
    define('WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

/* Plugin directory */

if (!defined('WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_DIR'))
    define('WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_NAME);

/* Plugin url */

if (!defined('WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL'))
    define('WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL', WP_PLUGIN_URL . '/' . WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_NAME);
  
/* Plugin verison */

if (!defined('WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM'))
    define('WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM', '1.0.0');


/** 
 * Activatation / Deactivation 
 */  

register_activation_hook( __FILE__, array('WPSitePostStatusNotification', 'register_activation'));

/** 
 * Hooks / Filter 
 */
 
add_action('transition_post_status', array('WPSitePostStatusNotification', 'wpsite_send_email'), 10, 3 );
add_action('init', array('WPSitePostStatusNotification', 'load_textdoamin'));
add_action('admin_menu', array('WPSitePostStatusNotification', 'wpsite_admin_menu_info'));

$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", array('WPSitePostStatusNotification', 'wpsite_post_status_notification_settings_link'));

/** 
 *  WPSitePostStatusNotification main class
 *
 * @since 1.0.0
 * @using Wordpress 3.8
 */

class WPSitePostStatusNotification {

	/* Properties */
	
	private static $version_setting_name = 'wpsite_post_status_notification_verison';
	
	private static $jquery_latest = 'http://code.jquery.com/jquery-latest.min.js';
	
	private static $text_domain = 'wpsite-post-status-notification';
	
	private static $info_page = 'wpsite-post-status-notification-admin-info';
	
	private static $web_page = 'http://www.wpsite.net/plugin/post-status-notifications';

	/**
	 * Load the text domain 
	 * 
	 * @since 1.0.0
	 */
	static function load_textdoamin() {
		load_plugin_textdomain(self::$text_domain, false, WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_DIR . '/languages');
	}
	
	/**
	 * Hooks to 'register_activation_hook' 
	 * 
	 * @since 1.0.0
	 */
	static function register_activation() {
	
		/* Check if multisite, if so then save as site option */
		
		if (is_multisite()) {
			add_site_option(self::$version_setting_name, WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM);
		} else {
			add_option(self::$version_setting_name, WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM);
		}
	}
	
	/**
	 * Hooks to 'plugin_action_links_' filter 
	 * 
	 * @since 1.0.0
	 */
	static function wpsite_post_status_notification_settings_link($links) { 
		$settings_link = '<a target="_blank" href="' . self::$web_page . '">Info</a>'; 
		array_unshift($links, $settings_link); 
		return $links; 
	}
	
	/**
	 * Hooks to 'admin_menu' 
	 * 
	 * @since 1.0.0
	 */
	static function wpsite_admin_menu_info() {
	
		 /* Cast the first sub menu to the settings menu */
	    
	    /*
$page_hook_suffix = add_submenu_page(
	    	'tools.php', 												// parent slug
	    	__('WPsite Post SN', self::$text_domain), 						// Page title
	    	__('WPsite Post SN', self::$text_domain), 						// Menu name
	    	'manage_options', 											// Capabilities
	    	self::$info_page, 										// slug
	    	array('WPSitePostStatusNotification', 'wpsite_admin_menu_info_callback')	// Callback function
	    );
*/
	}
	
	/**
	 * Callback to info page 
	 * 
	 * @since 1.0.0
	 */
	static function wpsite_admin_menu_info_callback() {
		?>
		<h1><?php _e('WPsite Post Status Notification', self::$text_domain); ?></h1>
		<p><?php _e('This plugin consists of two main features:', self::$text_domain); ?></p>
		<ol>
			<li><?php _e('Send an email to all admins when contributor submits a post to be published.', self::$text_domain); ?></li>
			<li><?php _e('Send an email to contributor when post is published.', self::$text_domain); ?></li>
		</ol>
		<?php
	}
	
	/**
	 * Email all admins when post status changes to pending 
	 * 
	 * @since 1.0.0
	 */
	static function wpsite_send_email( $new_status, $old_status, $post ) {
	
		// Notifiy Admin that Contributor has writen a post
		
	    if ($new_status == 'pending' && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {
	    
	    	$url = get_permalink($post->ID);
	    	$edit_link = get_edit_post_link($post->ID, '');
			$preview_link = get_permalink($post->ID) . '&preview=true';
	    	$username = get_userdata($post->post_author);
	    
	    	$subject = 'Please moderate: "' . $post->post_title . '"';
	    	$message = 'A new post: "' . $post->post_title . '" from contributor: ' . $username->user_login . ' is now pending.';
	    	//$message .= "\r\n" . get_edit_post_link($post->ID, '');
	    	
	    	$message .= "\r\n\r\n";
	    	$message .= "Author: $username->user_login\r\n";
	    	//$message .= "URL: $url\r\n";
	    	$message .= "Title of Post: $post->post_title";
	    	
	    	$message .= "\r\n\r\n";
	    	$message .= "Edit the post: $edit_link\r\n";
	    	$message .= "Preview it: $preview_link";
	    	
	    	$message .= "\r\n\r\n This was sent by WPsite Post Status Notification." .  "\r\n" .  "wpsite.net";
	    	
			$users = get_users(array(
				'role'	=> 'administrator'
			));
			
			foreach ($users as $user) {
				$result = wp_mail($user->user_email, $subject, $message);
			}
	    }
	    
	    // Notifiy Contributor that Admin has published their post
	    
	    else if ($old_status == 'pending' && $new_status == 'publish' && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {
		    $username = get_userdata($post->post_author);
		    $url = get_permalink($post->ID);
	    
	    	$subject = "Published Post:" . " " . $post->post_title;
	    	$message = '"' . $post->post_title . '"' . " was just published!.  Check it out, and thanks for the hard work.\r\n";
	    	$message .= $url;
	    	
	    	$message .= "\r\n\r\n This was sent by WPsite Post Status Notification." .  "\r\n" .  "wpsite.net";
			
			$result = wp_mail($username->user_email, $subject, $message);
	    }
	}
}

?>