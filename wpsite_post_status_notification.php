<?php
/*
Plugin Name: WPSite Post Status Notifications
plugin URI: http://www.wpsite.net/plugin/post-status-notifications
Description: Send post status notifications by email to Administrators and Contributors when posts are submitted for review or published. Great for multi-author sites to improve editorial workflow.
version: 1.1
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
    define('WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM', '1.1.0');


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
	
	/* Share Link */
	
	private static $facebook_share_link = 'https://www.facebook.com/sharer/sharer.php?u=';
	
	private static $twitter_share_link = 'https://twitter.com/intent/tweet?url=';
	
	private static $google_share_link = 'https://plus.google.com/share?url=';
	
	private static $linkedin_share_link = 'https://www.linkedin.com/shareArticle?url=';
	
	/* Default Settings */
	
	private static $default = array(
		'notify'		=> 'author',
		'post_types'	=> array('post'),
		'message'		=> array(
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
		$settings_link = '<a href="tools.php?page=' . self::$settings_page . '">Settings</a>'; 
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
		wp_enqueue_style('wpsite_post_status_notifications_admin_css', WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL . '/include/css/wpsite_post_status_notifications_admin.css');
	}
	
	/**
	 * Callback to info page 
	 * 
	 * @since 1.0.0
	 */
	static function wpsite_admin_menu_info_callback() {
	
		/* Get all post types that are public */
										
		$post_types = get_post_types(array('public' => true)); 
	
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
				'notify'		=> $_POST['wpsite_post_status_notifications_settings_notify_users'],
				'post_types'	=> $post_types_array,
				'message'		=> array(
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
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$( "#tabs" ).tabs();
		});
		</script>
		
		<div class="wrap wpsite_admin_panel">
			<div class="wpsite_admin_panel_banner">
				<h1><?php _e('WPsite Post Status Notifications', self::$text_domain); ?></h1>
			</div>
					
			<div id="wpsite_admin_panel_settings" class="wpsite_admin_panel_content">
				
				<form method="post">
				
				<div id="tabs">
						<ul>
							<li><a href="#wpsite_div_general"><span class="wpsite_admin_panel_content_tabs"><?php _e('General', self::$text_domain); ?></span></a></li>
							<li><a href="#wpsite_div_email"><span class="wpsite_admin_panel_content_tabs"><?php _e('Email',self::$text_domain); ?></span></a></li>
						</ul>
						
						<div id="wpsite_div_general">
							
							<h3><?php _e('Post Types', self::$text_domain); ?></h3>
							
							<table>
								<tbody>
								
									<!-- Include these post types -->
									
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Include these post types', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
											
												<?php foreach ($post_types as $post_type) { ?>
													<input type="checkbox" id="wpsite_post_status_notifications_settings_post_types_<?php echo $post_type; ?>" name="wpsite_post_status_notifications_settings_post_types_<?php echo $post_type; ?>" <?php echo (isset($settings['post_types']) && in_array($post_type, $settings['post_types']) ? 'checked="checked"' : '');?>/><label><?php printf(__('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%s', self::$text_domain), $post_type); ?></label><br />
												<?php } ?>
												
											</td>
										</th>
									</tr>
									
								</tbody>
							</table>
							
							<h3><?php _e('Share Links', self::$text_domain); ?></h3>
									
							<table>
								<tbody>
								
									<!-- Twitter -->
									
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Twitter', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_share_links_twitter" name="wpsite_post_status_notifications_settings_message_share_links_twitter" type="checkbox" value="users" <?php echo isset($settings['message']['share_links']['twitter']) && $settings['message']['share_links']['twitter'] ? 'checked="checked"' : ''; ?>>
											</td>
										</th>
									</tr>
									
									<!-- Facebook -->
									
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Facebook', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_share_links_facebook" name="wpsite_post_status_notifications_settings_message_share_links_facebook" type="checkbox" value="users" <?php echo isset($settings['message']['share_links']['facebook']) && $settings['message']['share_links']['facebook'] ? 'checked="checked"' : ''; ?>>
											</td>
										</th>
									</tr>
									
									<!-- Google -->
									
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Google', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_share_links_google" name="wpsite_post_status_notifications_settings_message_share_links_google" type="checkbox" value="users" <?php echo isset($settings['message']['share_links']['google']) && $settings['message']['share_links']['google'] ? 'checked="checked"' : ''; ?>>
											</td>
										</th>
									</tr>
									
									<!-- LinkedIn -->
									
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('LinkedIn', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_share_links_linkedin" name="wpsite_post_status_notifications_settings_message_share_links_linkedin" type="checkbox" value="users" <?php echo isset($settings['message']['share_links']['linkedin']) && $settings['message']['share_links']['linkedin'] ? 'checked="checked"' : ''; ?>>
											</td>
										</th>
									</tr>
									
								</tbody>
							</table>
							
							<h3><?php _e('When a post is published notify: ', self::$text_domain); ?></h3>
									
							<table>
								<tbody>
									
									<!-- Notify Contributor only -->
								
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Contributor only', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<input name="wpsite_post_status_notifications_settings_notify_users" type="radio" value="author" <?php echo isset($settings['notify']) && $settings['notify'] == 'author' ? 'checked' : ''; ?>>
											</td>
										</th>
									</tr>
									
									<!-- Notify all Users-->
								
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('All Users', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input name="wpsite_post_status_notifications_settings_notify_users" type="radio" value="users" <?php echo isset($settings['notify']) && $settings['notify'] == 'users' ? 'checked' : ''; ?>>
											</td>
										</th>
									</tr>
									
									<!-- Notify Admins only -->
								
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Admins only', self::$text_domain); ?></label>
											<td class="wpsite_admin_table_td">
												<input name="wpsite_post_status_notifications_settings_notify_users" type="radio" value="admins" <?php echo isset($settings['notify']) && $settings['notify'] == 'admins' ? 'checked' : ''; ?>>
											</td>
										</th>
									</tr>
									
								</tbody>
							</table>
							
						</div>
						
						
						<div id="wpsite_div_email">
							
							<h3><?php _e('Headers', self::$text_domain); ?></h3>
							
							<em><label><?php _e('Leave blank for defaults.', self::$text_domain); ?></label></em>
							
							<table>
								<tbody>
								
									<!-- From -->
									
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('From', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_from_email" name="wpsite_post_status_notifications_settings_message_from_email" type="text" size="50" value="<?php echo esc_attr($settings['message']['from_email']); ?>"><br/>
												<em><label><?php _e('email (e.g. example@gmail.com or example@gmail.com, example1@gmail.com)', self::$text_domain); ?></label></em><br/>
												<em><label><?php _e('default (wordpress@yoursite.com)', self::$text_domain); ?></label></em>
											</td>
										</th>
									</tr>
								
									<!-- Cc -->
											
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Cc', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_cc_email" name="wpsite_post_status_notifications_settings_message_cc_email" type="text" size="50" value="<?php echo esc_attr($settings['message']['cc_email']); ?>"><br/>
												<em><label><?php _e('email (e.g. example@gmail.com or example@gmail.com, example1@gmail.com)', self::$text_domain); ?></label></em><br/>
												<em><label><?php _e('default (none)', self::$text_domain); ?></label></em>
											</td>
										</th>
									</tr>
									
									<!-- Bcc -->
											
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Bcc', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<input id="wpsite_post_status_notifications_settings_message_bcc_email" name="wpsite_post_status_notifications_settings_message_bcc_email" type="text" size="50" value="<?php echo esc_attr($settings['message']['bcc_email']); ?>"><br/>
												<em><label><?php _e('email (e.g. example@gmail.com or example@gmail.com, example1@gmail.com)', self::$text_domain); ?></label></em><br/>
												<em><label><?php _e('default (none)', self::$text_domain); ?></label></em>
											</td>
										</th>
									</tr>
									
								</tbody>
							</table>
									
							<h3><?php _e('Content and Subjects', self::$text_domain); ?></h3>
							
							<em><label><?php _e('Leave blank for defaults.', self::$text_domain); ?></label></em>
							
							<table>
								<tbody>
								
									<!-- Email when post is published -->
									
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Email when post is published', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<label><?php _e('Subject', self::$text_domain); ?></label><br/>
												<input id="wpsite_post_status_notifications_settings_message_subject_published" name="wpsite_post_status_notifications_settings_message_subject_published" type="text" size="50" value="<?php echo esc_attr($settings['message']['subject_published']); ?>"/><br/>
												
												<label><?php _e('Content', self::$text_domain); ?></label><br/>
												<textarea rows="10" cols="50" id="wpsite_post_status_notifications_settings_message_content_published" name="wpsite_post_status_notifications_settings_message_content_published"><?php echo esc_attr($settings['message']['content_published']); ?></textarea>
											</td>
										</th>
									</tr>
		
									
									<!-- Email sent to contributor when their post is published -->
									
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Email sent to contributor when their post is published', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<label><?php _e('Subject', self::$text_domain); ?></label><br/>
												<input id="wpsite_post_status_notifications_settings_message_subject_published_contributor" name="wpsite_post_status_notifications_settings_message_subject_published_contributor" type="text" size="50" value="<?php echo esc_attr($settings['message']['subject_published_contributor']); ?>"/><br/>
												
												<label><?php _e('Content', self::$text_domain); ?></label><br/>
												<textarea rows="10" cols="50" id="wpsite_post_status_notifications_settings_message_content_published_contributor" name="wpsite_post_status_notifications_settings_message_content_published_contributor"><?php echo esc_attr($settings['message']['content_published_contributor']); ?></textarea>
											</td>
										</th>
									</tr>
		
									
									<!-- Email sent to admin when contributor submits post for review -->
									
									<tr>
										<th class="wpsite_admin_table_th">
											<label><?php _e('Email sent to admin when contributor submits post for review', self::$text_domain); ?></label><br/>
											<td class="wpsite_admin_table_td">
												<label><?php _e('Subject', self::$text_domain); ?></label><br/>
												<input id="wpsite_post_status_notifications_settings_message_subject_pending" name="wpsite_post_status_notifications_settings_message_subject_pending" type="text" size="50" value="<?php echo esc_attr($settings['message']['subject_pending']); ?>"/><br/>
												
												<label><?php _e('Content', self::$text_domain); ?></label><br/>
												<textarea rows="10" cols="50" id="wpsite_post_status_notifications_settings_message_content_pending" name="wpsite_post_status_notifications_settings_message_content_pending"><?php echo esc_attr($settings['message']['content_pending']); ?></textarea>
											</td>
										</th>
									</tr>
										
								</tbody>
							</table>
							
							<em><label><?php _e('*Please note that this does not affect the Share Links.', self::$text_domain); ?></label></em>
							
						</div>
					</div>
					
					<?php wp_nonce_field('wpsite_post_status_notifications_admin_settings'); ?>
						
					<?php submit_button(); ?>
						
				</form>
		 
			</div>
					
			<div id="wpsite_admin_panel_sidebar" class="wpsite_admin_panel_content">
				<div class="wpsite_admin_panel_sidebar_img">
					<img src="http://www.wpsite.net/wp-content/uploads/2011/10/logo-only-100h.png">
				</div>
			</div>
		</div>
		<?php
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
		$just_published_contributor = '"' . $post->post_title . '"' . " was just published!.  Check it out, and thanks for the hard work.\r\n";
	    $just_published = '"' . $post->post_title . '"' . " was just published!.\r\n";
	
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
				'role'	=> 'administrator'
			));
			
			foreach ($users as $user) {
				$result = wp_mail($user->user_email, $subject, $message, $headers);
			}
	    }
	    
	    // Notifiy Contributor or All Admins or All Users that a post was published
	    
	    if (in_array($post->post_type, $settings['post_types']) && $new_status == 'publish') {
	    
	    	// Notify Contributor that their post was published
	    
	    	if (isset($settings['notify']) && $settings['notify'] == 'author' && $old_status == 'pending' && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {
				$username = get_userdata($post->post_author);
				
				// Custom Subject and Message
				
				if (isset($settings['message']['subject_published_contributor']) && $settings['message']['subject_published_contributor'] != '') {
					$subject = $settings['message']['subject_published_contributor'];
				} else {
					$subject = "Published " . $post->post_type . ":" . " " . $post->post_title;
				}
				
				if (isset($settings['message']['content_published_contributor']) && $settings['message']['content_published_contributor'] != '') {
					$message = $settings['message']['content_published_contributor'];
				} else {
					$message = $just_published_contributor . $url;
				}
				
				$message .= $share_links . $wpsite_info;
				
				$result = wp_mail($username->user_email, $subject, $message, $headers);
			}
		   
			// Notify All Admins or All Users
			
			if (isset($settings['notify']) && $settings['notify'] != 'author') {
			
				// Notify All Admins
			
				if ($settings['notify'] == 'admins' && ($old_status == 'pending' || $old_status != $new_status)) {
				
					// Custom Subject and Message
	    	
			    	if (isset($settings['message']['subject_published']) && $settings['message']['subject_published'] != '') {
						$subject = $settings['message']['subject_published'];
					} else {
						$subject = "Published " . $post->post_type . ":" . " " . $post->post_title;
					}
					
					if (isset($settings['message']['content_published']) && $settings['message']['content_published'] != '') {
						$message = $settings['message']['content_published'];
					} else {
						$message = $just_published . $url;
					}
					
			    	$message .= $share_links . $wpsite_info;
		    	
		    	
					$users = get_users(array(
						'role'		=> 'administrator'
					));
					
					foreach ($users as $user) {
						$result = wp_mail($user->user_email, $subject, $message, $headers);
					}
				}
				
				// Notify All Users
				
				if ($settings['notify'] == 'users' && ($old_status == 'pending' || $old_status != $new_status)) {
				
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
							$message = $settings['message']['content_published_contributor'];
						} else {
							$message = $just_published_contributor . $url;
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
						$message = $settings['message']['content_published'];
					} else {
						$message = $just_published . $url;
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