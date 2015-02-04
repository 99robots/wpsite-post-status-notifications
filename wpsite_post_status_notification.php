<?php
/*
Plugin Name: WPSite Post Status Notifications
plugin URI: http://www.wpsite.net/plugin/post-status-notifications
Description: Send post status notifications by email to Administrators and Contributors when posts are submitted for review or published. Great for multi-author sites to improve editorial workflow.
version: 2.0.3
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
    define('WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM', '2.0.3');


/**
 * Activatation / Deactivation
 */

register_activation_hook( __FILE__, array('WPSitePostStatusNotifications', 'register_activation'));

/**
 * Hooks / Filter
 */

add_action('transition_post_status', array('WPSitePostStatusNotifications', 'wpsite_send_email'), 10, 3 );
add_action('init', array('WPSitePostStatusNotifications', 'load_textdoamin'));
add_action('admin_menu', array('WPSitePostStatusNotifications', 'wpsite_admin_menu'));

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", array('WPSitePostStatusNotifications', 'wpsite_post_status_notification_settings_link'));

/**
 *  WPSitePostStatusNotifications main class
 *
 * @since 1.0.0
 * @using Wordpress 3.8
 */

class WPSitePostStatusNotifications {

	/* Properties */

	private static $version_setting_name = 'wpsite_post_status_notification_verison';

	private static $text_domain = 'wpsite-post-status-notification';

	private static $settings_page = 'wpsite-post-status-notification-admin-settings';

	private static $web_page = 'http://www.wpsite.net/plugin/post-status-notifications';

	/* Share Links */

	private static $facebook_share_link = 'https://www.facebook.com/sharer/sharer.php?u=';

	private static $twitter_share_link = 'https://twitter.com/intent/tweet?url=';

	private static $google_share_link = 'https://plus.google.com/share?url=';

	private static $linkedin_share_link = 'https://www.linkedin.com/shareArticle?url=';

	/* Default Settings */

	private static $default = array(
		'publish_notify'	=> 'contributor',
		'pending_notify'	=> 'admin',
		'post_types'		=> array('post'),
		'message'			=> array(
			'cc_email'						=> '',
			'bcc_email'						=> '',
			'from_email'					=> '',
			'subject_published_contributor'	=> '',
			'subject_published'				=> '',
			'subject_pending'				=> '',
			'content_published_contributor'	=> '',
			'content_published'				=> '',
			'content_pending'				=> '',
			'share_links'					=> array(
				'twitter'	=> true,
				'facebook'	=> true,
				'google'	=> true,
				'linkedin'	=> true
			)
		)
	);

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
		$settings_link = '<a href="options-general.php?page=' . self::$settings_page . '">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	/**
	 * Hooks to 'admin_menu'
	 *
	 * @since 1.0.0
	 */
	static function wpsite_admin_menu() {

		 /* Cast the first sub menu to the settings menu */

	    $settings_page_load = add_submenu_page(
	    	'options-general.php', 												// parent slug
	    	__('WPsite Post Status Notifications', self::$text_domain), 						// Page title
	    	__('WPsite Post Status Notifications', self::$text_domain), 						// Menu name
	    	'manage_options', 											// Capabilities
	    	self::$settings_page, 										// slug
	    	array('WPSitePostStatusNotifications', 'wpsite_admin_menu_info_callback')	// Callback function
	    );
	    add_action("admin_print_scripts-$settings_page_load", array('WPSitePostStatusNotifications', 'inline_scripts_admin'));
	}

	/**
	 * Hooks to 'admin_print_scripts-$page'
	 *
	 * @since 1.0.0
	 */
	static function inline_scripts_admin() {
		wp_enqueue_style('wpsite_post_status_notifications_admin_css', WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL . '/css/wpsite_post_status_notifications_admin.css');
	}

	/**
	 * Callback to info page
	 *
	 * @since 1.0.0
	 */
	static function wpsite_admin_menu_info_callback() {

		/* Get all post types that are public */

		$post_types = self::get_post_types();

		$settings = get_option('wpsite_post_status_notifications_settings');

		/* Default values */

		if ($settings === false) {
			$settings = self::$default;
		}

		/* Save data nd check nonce */

		if (isset($_POST['submit']) && check_admin_referer('wpsite_post_status_notifications_admin_settings')) {

			/* Determine Post Types */

			$post_types_array = array();

			foreach ($post_types as $post_type) {
				if (isset($_POST['wpsite_post_status_notifications_settings_post_types_' . $post_type]) && $_POST['wpsite_post_status_notifications_settings_post_types_' . $post_type])
					$post_types_array[] = $post_type;
			}

			$settings = array(
				'publish_notify'	=> $_POST['wpsite_post_status_notifications_settings_publish_notify'],
				'pending_notify'	=> $_POST['wpsite_post_status_notifications_settings_pending_notify'],
				'post_types'		=> $post_types_array,
				'message'			=> array(
					'cc_email'		=> isset($_POST['wpsite_post_status_notifications_settings_message_cc_email']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_cc_email'])) : '',
					'bcc_email'		=> isset($_POST['wpsite_post_status_notifications_settings_message_bcc_email']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_bcc_email'])) : '',
					'from_email'	=> isset($_POST['wpsite_post_status_notifications_settings_message_from_email']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_from_email'])) : '',
					'subject_published'	=> isset($_POST['wpsite_post_status_notifications_settings_message_subject_published']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_published'])) : '',
					'subject_published_contributor'	=> isset($_POST['wpsite_post_status_notifications_settings_message_subject_published_contributor']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_published_contributor'])) : '',
					'subject_pending'	=> isset($_POST['wpsite_post_status_notifications_settings_message_subject_pending']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_pending'])) : '',
					'content_published'	=> isset($_POST['wpsite_post_status_notifications_settings_message_content_published']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_published'])) : '',
					'content_published_contributor'	=> isset($_POST['wpsite_post_status_notifications_settings_message_content_published_contributor']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_published_contributor'])) : '',
					'content_pending'	=> isset($_POST['wpsite_post_status_notifications_settings_message_content_pending']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_pending'])) : '',
					'share_links'	=> array(
						'twitter'	=> isset($_POST['wpsite_post_status_notifications_settings_message_share_links_twitter']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_twitter'] ? true : false,
						'facebook'	=> isset($_POST['wpsite_post_status_notifications_settings_message_share_links_facebook']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_facebook'] ? true : false,
						'google'	=> isset($_POST['wpsite_post_status_notifications_settings_message_share_links_google']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_google'] ? true : false,
						'linkedin'	=> isset($_POST['wpsite_post_status_notifications_settings_message_share_links_linkedin']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_linkedin'] ? true : false,
					)
				)
			);

			/* 'active'	=> isset($_POST['wpsite_follow_us_settings_twitter_active']) && $_POST['wpsite_follow_us_settings_twitter_active'] ? true : false, */

			update_option('wpsite_post_status_notifications_settings', $settings);
		}

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-tabs');

		require('admin/dashboard.php');
	}

	/**
	 * Returns all post types that are queryable and public
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function get_post_types() {

		$post_types = get_post_types(array('public' => true));

		unset($post_types['attachment']);
		unset($post_types['page']);

		return $post_types;
	}

	/**
	 * Email all admins when post status changes to pending
	 *
	 * @since 1.0.0
	 */
	static function wpsite_send_email( $new_status, $old_status, $post ) {

		$settings = get_option('wpsite_post_status_notifications_settings');

		/* Default values */

		if ($settings === false) {
			$settings = self::$default;
		}

		// If status did not change

		if ($new_status == $old_status) {
			return null;
		}

		// Set all headers

		$headers = array();

		if (isset($settings['message']['from_email']) && $settings['message']['from_email'] != '') {
			$headers[] = "From: " . $settings['message']['from_email'] . "\r\n";
		}

		if (isset($settings['message']['cc_email']) && $settings['message']['cc_email'] != '') {
			$headers[] = "Cc: " . $settings['message']['cc_email'] . "\r\n";
		}

		if (isset($settings['message']['bcc_email']) && $settings['message']['bcc_email'] != '') {
			$headers[] = "Bcc: " . $settings['message']['bcc_email'] . "\r\n";
		}

		if (isset($settings['message']['share_links'])) {
			$check = false;
			foreach ($settings['message']['share_links'] as $link) {
				if ($link) {
					$share_links_check = true;
				}
			}
		}

		$url = get_permalink($post->ID);
		$share_links = '';

		if (isset($share_links_check) && $share_links_check) {
			$share_links = "\r\n\r\nShare Links\r\n";

			if ($settings['message']['share_links']['twitter']) {
				$share_links .= "Twitter: " . esc_url(self::$twitter_share_link . $url) . "\r\n";
			}

			if ($settings['message']['share_links']['facebook']) {
				$share_links .= "Facebook: " . esc_url(self::$facebook_share_link . $url) . "\r\n";
			}

			if ($settings['message']['share_links']['google']) {
				$share_links .= "Google+: " . esc_url(self::$google_share_link . $url) . "\r\n";
			}

			if ($settings['message']['share_links']['linkedin']) {
				$share_links .= "LinkedIn: " . esc_url(self::$linkedin_share_link . $url) . "\r\n";
			}
		}

		$wpsite_info = "\r\n\r\nThis was sent by WPsite Post Status Notifications." .  "\r\n" .  "wpsite.net";
		$just_published_contributor = '"' . $post->post_title . '"' . " was just published!  Check it out, and thanks for the hard work." . "\r\n\r\n" . "View it: $url";
	    $just_published = '"' . $post->post_title . '"' . " was just published!" . "\r\n\r\n"  . "View it: $url";

		// Notifiy Admins that Contributor has writen a post

	    if (in_array($post->post_type, $settings['post_types']) && $new_status == 'pending' && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {

	    	$url = get_permalink($post->ID);
	    	$edit_link = get_edit_post_link($post->ID, '');
			$preview_link = get_permalink($post->ID) . '&preview=true';
	    	$username = get_userdata($post->post_author);

	    	// Custom Subject and Message

	    	if (isset($settings['message']['subject_pending']) && $settings['message']['subject_pending'] != '') {
				$subject = $settings['message']['subject_pending'];
			} else {
				$subject = 'Please moderate: "' . $post->post_title . '"';
			}

			if (isset($settings['message']['content_pending']) && $settings['message']['content_pending'] != '') {
				$message = $settings['message']['content_pending'];
			} else {
				$message = 'A new ' . $post->post_type . ': "' . $post->post_title . '" from contributor: ' . $username->user_login . ' is now pending.';
			}

	    	$message .= "\r\n\r\n";
	    	$message .= "Author: $username->user_login\r\n";
	    	$message .= "Title of " . $post->post_type . ": $post->post_title";

	    	$message .= "\r\n\r\n";
	    	$message .= "Edit the " . $post->post_type . ": $edit_link\r\n";
	    	$message .= "Preview it: $preview_link";

	    	$message .= $wpsite_info;

			$users = get_users(array(
				'role'	=> $settings['pending_notify']
			));

			foreach ($users as $user) {
				$result = wp_mail($user->user_email, $subject, $message, $headers);
			}
	    }

	    // Notifiy Contributor or All Admins or All Users that a post was published

	    if (in_array($post->post_type, $settings['post_types']) && $new_status == 'publish') {

	    	// Notify Contributor that their post was published

	    	if (isset($settings['publish_notify']) && $settings['publish_notify'] == 'author' && $old_status == 'pending' && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {
				$username = get_userdata($post->post_author);

				// Custom Subject and Message

				if (isset($settings['message']['subject_published_contributor']) && $settings['message']['subject_published_contributor'] != '') {
					$subject = $settings['message']['subject_published_contributor'];
				} else {
					$subject = "Published " . $post->post_type . ":" . " " . $post->post_title;
				}

				if (isset($settings['message']['content_published_contributor']) && $settings['message']['content_published_contributor'] != '') {
					$message = $settings['message']['content_published_contributor'] . "\r\n\r\n" . $just_published_contributor;
				} else {
					$message = $just_published_contributor;
				}

				$message .= $share_links . $wpsite_info;

				$result = wp_mail($username->user_email, $subject, $message, $headers);
			}

			// Notify All Admins or All Users

			if (isset($settings['publish_notify']) && $settings['publish_notify'] != 'author') {

				// Notify All Admins

				if ($settings['publish_notify'] == 'admins' && $old_status == 'pending') {

					// Custom Subject and Message

			    	if (isset($settings['message']['subject_published']) && $settings['message']['subject_published'] != '') {
						$subject = $settings['message']['subject_published'];
					} else {
						$subject = "Published " . $post->post_type . ":" . " " . $post->post_title;
					}

					if (isset($settings['message']['content_published']) && $settings['message']['content_published'] != '') {
						$message = $settings['message']['content_published'] . "\r\n\r\n" . $just_published;
					} else {
						$message = $just_published;
					}

			    	$message .= $share_links . $wpsite_info;


					$users = get_users(array(
						'role'		=> 'administrator'
					));

					foreach ($users as $user) {
						$result = wp_mail($user->user_email, $subject, $message, $headers);
					}
				}

				// Notify All Editors

				if ($settings['publish_notify'] == 'editors' && ($old_status == 'pending' || $old_status != $new_status)) {

					// Custom Subject and Message

			    	if (isset($settings['message']['subject_published']) && $settings['message']['subject_published'] != '') {
						$subject = $settings['message']['subject_published'];
					} else {
						$subject = "Published " . $post->post_type . ":" . " " . $post->post_title;
					}

					if (isset($settings['message']['content_published']) && $settings['message']['content_published'] != '') {
						$message = $settings['message']['content_published'] . "\r\n\r\n" . $just_published;
					} else {
						$message = $just_published;
					}

			    	$message .= $share_links . $wpsite_info;


					$users = get_users(array(
						'role'		=> 'editor'
					));

					foreach ($users as $user) {
						$result = wp_mail($user->user_email, $subject, $message, $headers);
					}
				}

				// Notify All Users

				if ($settings['publish_notify'] == 'users' && ($old_status == 'pending' || $old_status != $new_status)) {

					$exclude_array = array();

					if ($old_status == 'pending' && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {

						$username = get_userdata($post->post_author);

						// Custom Subject and Message

				    	if (isset($settings['message']['subject_published_contributor']) && $settings['message']['subject_published_contributor'] != '') {
							$subject = $settings['message']['subject_published_contributor'];
						} else {
							$subject = "Published " . $post->post_type . ":" . " " . $post->post_title;
						}

						if (isset($settings['message']['content_published_contributor']) && $settings['message']['content_published_contributor'] != '') {
							$message = $settings['message']['content_published_contributor'] . "\r\n\r\n" . $just_published_contributor;
						} else {
							$message = $just_published_contributor;
						}

				    	$message .= $share_links . $wpsite_info;

						$result = wp_mail($username->user_email, $subject, $message, $headers);

						$exclude_array[] = $post->post_author;
					}

					// Custom Subject and Message

			    	if (isset($settings['message']['subject_published']) && $settings['message']['subject_published'] != '') {
						$subject = $settings['message']['subject_published'];
					} else {
						$subject = "Published " . $post->post_type . ":" . " " . $post->post_title;
					}

					if (isset($settings['message']['content_published']) && $settings['message']['content_published'] != '') {
						$message = $settings['message']['content_published'] . "\r\n\r\n" . $just_published;
					} else {
						$message = $just_published;
					}

			    	$message .= $share_links . $wpsite_info;

					$users = get_users(array(
						'exclude'	=> $exclude_array
					));

					foreach ($users as $user) {
						$result = wp_mail($user->user_email, $subject, $message, $headers);
					}
				}
			}
	    }
	}
}

?>