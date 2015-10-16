<?php
/*
Plugin Name: Post Status Notifications
plugin URI: http://www.99robots.com/plugin/post-status-notifications
Description: Send post status notifications by email to Administrators and Contributors when posts are submitted for review or published. Great for multi-author sites to improve editorial workflow.
version: 3.0.0
Author: 99 Robots
Author URI: https://www.99robots.com
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
    define('WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_DIR', plugin_dir_path(__FILE__) );

/* Plugin url */

if (!defined('WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL'))
    define('WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL', plugins_url() . '/' . WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_NAME);

/* Plugin verison */

if (!defined('WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM'))
    define('WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM', '3.0.0');


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

	/**
	 * version_setting_name
	 *
	 * (default value: 'wpsite_post_status_notification_verison')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $version_setting_name = 'wpsite_post_status_notification_verison';

	/**
	 * text_domain
	 *
	 * (default value: 'wpsite-post-status-notification')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $text_domain = 'wpsite-post-status-notification';

	/**
	 * settings_page
	 *
	 * (default value: 'wpsite-post-status-notification-admin-settings')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $settings_page = 'wpsite-post-status-notification-admin-settings';

	/**
	 * web_page
	 *
	 * (default value: 'http://www.99robots.com/plugin/post-status-notifications')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $web_page = 'http://www.99robots.com/plugin/post-status-notifications';

	/**
	 * facebook_share_link
	 *
	 * (default value: 'https://www.facebook.com/sharer/sharer.php?u=')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $facebook_share_link = 'https://www.facebook.com/sharer/sharer.php?u=';

	/**
	 * twitter_share_link
	 *
	 * (default value: 'https://twitter.com/intent/tweet?url=')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $twitter_share_link = 'https://twitter.com/intent/tweet?url=';

	/**
	 * google_share_link
	 *
	 * (default value: 'https://plus.google.com/share?url=')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $google_share_link = 'https://plus.google.com/share?url=';

	/**
	 * linkedin_share_link
	 *
	 * (default value: 'https://www.linkedin.com/shareArticle?url=')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $linkedin_share_link = 'https://www.linkedin.com/shareArticle?url=';

	/**
	 * default
	 *
	 * @var mixed
	 * @access private
	 * @static
	 */
	static function default_data() {
		return array(
			'publish_notify'	=> 'contributor',
			'pending_notify'	=> 'admin',
			'post_types'		=> array('post'),
			'message'			=> array(
				'cc_email'						=> '',
				'bcc_email'						=> '',
				'from_email'					=> 'wordpress@' . $_SERVER['HTTP_HOST'],
				'subject_published_contributor'	=> 'Published {post_type}: {post_title}',
				'subject_published'				=> 'Published {post_type}: {post_title}',
				'subject_published_global'		=> 'Published {post_type}: {post_title}',
				'subject_pending'				=> 'Please moderate: {post_title}',
				'content_published_contributor'	=> '{post_title} was just published! Check it out, and thanks for the hard work. {break_line}{break_line}View it: {post_url}',
				'content_published'				=> '{post_title} was just published! {break_line}{break_line}View it: {post_url}',
				'content_published_global'		=> '{post_title} was just published! {break_line}{break_line}View it: {post_url}',
				'content_pending'				=> 'A new {post_type}: {post_title} from contributor: {display_name} is now pending.',
				'share_links'					=> array(
					'twitter'	=> true,
					'facebook'	=> true,
					'google'	=> true,
					'linkedin'	=> true
				)
			)
		);
	}

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
	    	'options-general.php', 														// parent slug
	    	__('Post Status Notifications', self::$text_domain), 						// Page title
	    	__('Post Status Notifications', self::$text_domain), 						// Menu name
	    	'manage_options', 															// Capabilities
	    	self::$settings_page, 														// slug
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

		wp_enqueue_style('wpsite_post_status_notifications_settings_css', WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL . '/css/settings.css');
		wp_enqueue_style('wpsite_post_status_notifications_bootstrap_css', WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL . '/css/nnr-bootstrap.min.css');

		wp_enqueue_script('wpsite_post_status_notifications_bootstrap_js', WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL . '/js/bootstrap.min.js', array('jquery'));
	}

	/**
	 * Callback to info page
	 *
	 * @since 1.0.0
	 */
	static function wpsite_admin_menu_info_callback() {

		// Get all post types that are public

		$post_types = self::get_post_types();

		$settings = get_option('wpsite_post_status_notifications_settings');

		// Default values

		if ($settings === false) {
			$settings = self::default_data();
		}

		// Save data and check nonce

		if (isset($_POST['submit']) && check_admin_referer('wpsite_post_status_notifications_admin_settings')) {

			// Determine Post Types

			$post_types_array = array();

			foreach ($post_types as $post_type) {
				if (isset($_POST['wpsite_post_status_notifications_settings_post_types_' . $post_type]) && $_POST['wpsite_post_status_notifications_settings_post_types_' . $post_type])
					$post_types_array[] = $post_type;
			}

			$default_data = self::default_data();

			$settings = array(
				'publish_notify'	=> $_POST['wpsite_post_status_notifications_settings_publish_notify'],
				'pending_notify'	=> $_POST['wpsite_post_status_notifications_settings_pending_notify'],
				'post_types'		=> $post_types_array,
				'message'			=> array(
					'cc_email'		=> isset($_POST['wpsite_post_status_notifications_settings_message_cc_email']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_cc_email'])) : $default_data['message']['cc_email'],
					'bcc_email'		=> isset($_POST['wpsite_post_status_notifications_settings_message_bcc_email']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_bcc_email'])) : $default_data['message']['bcc_email'],
					'from_email'	=> isset($_POST['wpsite_post_status_notifications_settings_message_from_email']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_from_email'])) : $default_data['message']['from_email'],
					'subject_published'	=> isset($_POST['wpsite_post_status_notifications_settings_message_subject_published']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_published'])) : $default_data['message']['subject_published'],
					'subject_published_global'	=> isset($_POST['wpsite_post_status_notifications_settings_message_subject_published_global']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_published_global'])) : $default_data['message']['subject_published_global'],
					'subject_published_contributor'	=> isset($_POST['wpsite_post_status_notifications_settings_message_subject_published_contributor']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_published_contributor'])) :  $default_data['message']['subject_published_contributor'],
					'subject_pending'	=> isset($_POST['wpsite_post_status_notifications_settings_message_subject_pending']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_pending'])) :  $default_data['message']['subject_pending'],
					'content_published'	=> isset($_POST['wpsite_post_status_notifications_settings_message_content_published']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_published'])) :  $default_data['message']['content_published'],
					'content_published_global'	=> isset($_POST['wpsite_post_status_notifications_settings_message_content_published_global']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_published_global'])) :  $default_data['message']['content_published_global'],
					'content_published_contributor'	=> isset($_POST['wpsite_post_status_notifications_settings_message_content_published_contributor']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_published_contributor'])) :  $default_data['message']['content_published_contributor'],
					'content_pending'	=> isset($_POST['wpsite_post_status_notifications_settings_message_content_pending']) ?stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_pending'])) :  $default_data['message']['content_pending'],
					'share_links'	=> array(
						'twitter'	=> isset($_POST['wpsite_post_status_notifications_settings_message_share_links_twitter']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_twitter'] ? true : false,
						'facebook'	=> isset($_POST['wpsite_post_status_notifications_settings_message_share_links_facebook']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_facebook'] ? true : false,
						'google'	=> isset($_POST['wpsite_post_status_notifications_settings_message_share_links_google']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_google'] ? true : false,
						'linkedin'	=> isset($_POST['wpsite_post_status_notifications_settings_message_share_links_linkedin']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_linkedin'] ? true : false,
					)
				)
			);

			$settings['message'] = array(
				'cc_email'						=> $settings['message']['cc_email'] != '' ? $settings['message']['cc_email'] : $default_data['message']['cc_email'],
				'bcc_email'						=> $settings['message']['bcc_email'] != '' ? $settings['message']['bcc_email'] : $default_data['message']['bcc_email'],
				'from_email'					=> $settings['message']['from_email'] != '' ? $settings['message']['from_email'] : $default_data['message']['from_email'],
				'subject_published'				=> $settings['message']['subject_published'] != '' ? $settings['message']['subject_published'] : $default_data['message']['subject_published'],
				'subject_published_global'		=> $settings['message']['subject_published_global'] != '' ? $settings['message']['subject_published_global'] : $default_data['message']['subject_published_global'],
				'subject_published_contributor'	=> $settings['message']['subject_published_contributor'] != '' ? $settings['message']['subject_published_contributor'] : $default_data['message']['subject_published_contributor'],
				'subject_pending'				=> $settings['message']['subject_pending'] != '' ? $settings['message']['subject_pending'] : $default_data['message']['subject_pending'],
				'content_published'				=> $settings['message']['content_published'] != '' ? $settings['message']['content_published'] : $default_data['message']['content_published'],
				'content_published_global'		=> $settings['message']['content_published_global'] != '' ? $settings['message']['content_published_global'] : $default_data['message']['content_published_global'],
				'content_published_contributor'	=> $settings['message']['content_published_contributor'] != '' ? $settings['message']['content_published_contributor'] : $default_data['message']['content_published_contributor'],
				'content_pending'				=> $settings['message']['content_pending'] != '' ? $settings['message']['content_pending'] : $default_data['message']['content_pending'],
				'share_links'	=> array(
					'twitter'	=> $settings['message']['share_links']['twitter'],
					'facebook'	=> $settings['message']['share_links']['facebook'],
					'google'	=> $settings['message']['share_links']['google'],
					'linkedin'	=> $settings['message']['share_links']['linkedin'],
				)
			);

			update_option('wpsite_post_status_notifications_settings', $settings);
		}

		require('admin/dashboard.php');
	}

	/**
	 * Email all admins when post status changes to pending
	 *
	 * @since 1.0.0
	 */
	static function wpsite_send_email( $new_status, $old_status, $post ) {

		$settings = get_option('wpsite_post_status_notifications_settings');

		// Default values

		if ($settings === false) {
			$settings = self::default_data();
		}

		$settings['message'] = array(
			'cc_email'						=> $settings['message']['cc_email'] != '' ? $settings['message']['cc_email'] : $default_data['message']['cc_email'],
			'bcc_email'						=> $settings['message']['bcc_email'] != '' ? $settings['message']['bcc_email'] : $default_data['message']['bcc_email'],
			'from_email'					=> $settings['message']['from_email'] != '' ? $settings['message']['from_email'] : $default_data['message']['from_email'],
			'subject_published'				=> $settings['message']['subject_published'] != '' ? $settings['message']['subject_published'] : $default_data['message']['subject_published'],
			'subject_published_contributor'	=> $settings['message']['subject_published_contributor'] != '' ? $settings['message']['subject_published_contributor'] : $default_data['message']['subject_published_contributor'],
			'subject_pending'				=> $settings['message']['subject_pending'] != '' ? $settings['message']['subject_pending'] : $default_data['message']['subject_pending'],
			'content_published'				=> $settings['message']['content_published'] != '' ? $settings['message']['content_published'] : $default_data['message']['content_published'],
			'content_published_contributor'	=> $settings['message']['content_published_contributor'] != '' ? $settings['message']['content_published_contributor'] : $default_data['message']['content_published_contributor'],
			'content_pending'				=> $settings['message']['content_pending'] != '' ? $settings['message']['content_pending'] : $default_data['message']['content_pending'],
			'share_links'	=> array(
				'twitter'	=> $settings['message']['share_links']['twitter'],
				'facebook'	=> $settings['message']['share_links']['facebook'],
				'google'	=> $settings['message']['share_links']['google'],
				'linkedin'	=> $settings['message']['share_links']['linkedin'],
			)
		);

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

		// Notifiy Admins that Contributor has writen a post

	    if (in_array($post->post_type, $settings['post_types']) && $new_status == 'pending' && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {

	    	$url = get_permalink($post->ID);
	    	$edit_link = get_edit_post_link($post->ID, '');
			$preview_link = get_permalink($post->ID) . '&preview=true';
	    	$username = get_userdata($post->post_author);

			$subject = self::parse_tags($post, $username, $settings['message']['subject_pending']);
			$message = self::parse_tags($post, $username, $settings['message']['content_pending']);

	    	$message .= "\r\n\r\n";
	    	$message .= "Author: " . $username->display_name . "\r\n";
	    	$message .= "Title of " . $post->post_type . ": " . $post->post_title;

	    	$message .= "\r\n\r\n";
	    	$message .= "Edit the " . $post->post_type . ": " . $edit_link . "\r\n";
	    	$message .= "Preview it: " . $preview_link;

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
				$subject = self::parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published_contributor']);
				$message = self::parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published_contributor']);
				$message .= $share_links;

				$result = wp_mail($username->user_email, $subject, $message, $headers);
			}

			// Notify All Admins or All Users

			if (isset($settings['publish_notify']) && $settings['publish_notify'] != 'author') {

				// Notify All Admins

				if ($settings['publish_notify'] == 'admins' && $old_status == 'pending') {

					$subject = self::parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published']);
					$message = self::parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published']);
			    	$message .= $share_links;

					$users = get_users(array(
						'role'		=> 'administrator'
					));

					foreach ($users as $user) {
						$result = wp_mail($user->user_email, $subject, $message, $headers);
					}
				}

				// Notify All Editors

				if ($settings['publish_notify'] == 'editors' && ($old_status == 'pending' || $old_status != $new_status)) {

					$subject = self::parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published']);
					$message = self::parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published']);
			    	$message .= $share_links;

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
						$subject = self::parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published_contributor']);
						$message = self::parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published_contributor']);
				    	$message .= $share_links;

						$result = wp_mail($username->user_email, $subject, $message, $headers);

						$exclude_array[] = $post->post_author;
					}

					$subject = self::parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published']);
					$message = self::parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published']);
			    	$message .= $share_links;

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

	/**
	 * Parse the tags added by people
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function parse_tags($post, $user, $text){

		// Replace post title

		$text = str_replace('{post_title}', $post->post_title, $text);

		// Replace post url

		$text = str_replace('{post_url}', get_permalink($post->ID), $text);

		// Replace post type

		$text = str_replace('{post_type}', $post->post_type, $text);

		// Replace user display name

		$text = str_replace('{display_name}', $user->display_name, $text);

		// Add a break line

		$text = str_replace('{break_line}', "\r\n", $text);

		return $text;

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
}

?>