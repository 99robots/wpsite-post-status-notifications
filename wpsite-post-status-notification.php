<?php
/**
 * Plugin Name:  Post Status Notifications
 * Plugin URI:   http://www.draftpress.com/products
 * Description:  Send post status notifications by email to Administrators and Contributors when posts are submitted for review or published. Great for multi-author sites to improve editorial workflow.
 * Version:      3.2.1
 * Author:       DraftPress
 * Author URI:   https://www.draftpress.com
 * License:      GPL2
 *
 * @package WPSite_Post_Status_Notifications
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* Plugin Name */
if ( ! defined( 'WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_NAME' ) ) {
	define( 'WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
}

/* Plugin directory */
if ( ! defined( 'WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_DIR' ) ) {
	define( 'WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/* Plugin url */
if ( ! defined( 'WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL' ) ) {
	define( 'WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL', plugins_url() . '/' . WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_NAME );
}

/* Plugin verison */
if ( ! defined( 'WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM' ) ) {
	define( 'WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM', '3.2.1' );
}

/**
 *  WPSite_Post_Status_Notifications main class
 *
 * @since 1.0.0
 * @using WordPress 3.8
 */
class WPSite_Post_Status_Notifications {


	/**
	 * WPSite_Post_Status_Notifications version.
	 *
	 * @var string
	 */
	public $version = '3.2.1';


	/**
	 * The single instance of the class.
	 *
	 * @var WPSite_Post_Status_Notifications
	 */
	protected static $instance = null;


	/**
	 * Plugin url.
	 *
	 * @var string
	 */
	private $plugin_url = null;

	/**
	 * Plugin path.
	 *
	 * @var string
	 */
	private $plugin_dir = null;

	/**
	 * Settings_page
	 *
	 * (default value: 'wpsite-post-status-notification-admin-settings')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $settings_page = 'wpsite-post-status-notification-admin-settings';

	/**
	 * Web_page
	 *
	 * (default value: 'http://www.draftpress.com/plugin/post-status-notifications')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $web_page = 'http://www.draftpress.com/plugin/post-status-notifications';

	/**
	 * Facebook_share_link
	 *
	 * (default value: 'https://www.facebook.com/sharer/sharer.php?u=')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $facebook_share_link = 'https://www.facebook.com/sharer/sharer.php?u=';

	/**
	 * Twitter_share_link
	 *
	 * (default value: 'https://twitter.com/intent/tweet?url=')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $twitter_share_link = 'https://twitter.com/intent/tweet?url=';

	/**
	 * Linkedin_share_link
	 *
	 * (default value: 'https://www.linkedin.com/shareArticle?url=')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $linkedin_share_link = 'https://www.linkedin.com/shareArticle?url=';

	/**
	 * Default
	 *
	 * @var mixed
	 * @access private
	 * @static
	 */
	public static function default_data() {

		return array(
			'publish_notify' => 'contributor',
			'pending_notify' => 'admin',
			'post_types'     => array( 'post' ),
			'message'        => array(
				'from_name'                     => get_bloginfo( 'name' ),
				'cc_email'                      => '',
				'bcc_email'                     => '',
				'from_email'                    => 'wordpress@' . ( ! empty( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' ),
				'subject_published_contributor' => 'Published {post_type}: {post_title}',
				'subject_published'             => 'Published {post_type}: {post_title}',
				'subject_published_global'      => 'Published {post_type}: {post_title}',
				'subject_pending'               => 'Please moderate: {post_title}',
				'content_published_contributor' => '{post_title} was just published! Check it out, and thanks for the hard work. {break_line}{break_line}View it: {post_url}',
				'content_published'             => '{post_title} was just published! {break_line}{break_line}View it: {post_url}',
				'content_published_global'      => '{post_title} was just published! {break_line}{break_line}View it: {post_url}',
				'content_pending'               => 'A new {post_type}: {post_title} from contributor: {display_name} is now pending.',
				'share_links'                   => array(
					'twitter'  => true,
					'facebook' => true,
					'linkedin' => true,
				),
			),
		);
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpsite-post-status-notification' ), WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpsite-post-status-notification' ), WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM );
	}

	/**
	 * Main WPSite_Post_Status_Notifications instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return WPSite_Post_Status_Notifications
	 */
	public static function instance() {

		if ( is_null( self::$instance ) && ! ( self::$instance instanceof WPSite_Post_Status_Notifications ) ) {
			self::$instance = new WPSite_Post_Status_Notifications();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * WPSite_Post_Status_Notifications constructor.
	 */
	public function __construct() {

		// Load the plugin.
		$this->hooks();
	}

	/**
	 * Add hooks to begin.
	 *
	 * @return void
	 */
	private function hooks() {

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		if ( is_admin() ) {

			$plugin = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin", array( $this, 'plugin_links' ) );

			add_action( 'transition_post_status', array( $this, 'wpsite_send_email' ), 10, 3 );
			add_action( 'admin_menu', array( $this, 'register_pages' ) );

			// Enqueue editor script.
			add_action( 'enqueue_block_editor_assets', array( $this, 'wpsite_enqueue_editor_script' ) );

			// Ajax to handle post save.
			add_action( 'wp_ajax_wpsite_handle_post_save', array( $this, 'wpsite_handle_post_save' ) );

		}
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'wpsite-post-status-notification' );

		load_textdomain(
			'wpsite-post-status-notification',
			WP_LANG_DIR . '/wpsite-post-status-notification/wpsite-post-status-notification-' . $locale . '.mo'
		);

		load_plugin_textdomain(
			'wpsite-post-status-notification',
			false,
			$this->plugin_dir() . '/languages/'
		);
	}

	/**
	 * Hooks to 'plugin_action_links_' filter
	 *
	 * @since 1.0.0
	 * @param array $links The links.
	 * @return array
	 */
	public function plugin_links( $links ) {

		$settings_link = '<a href="options-general.php?page=' . self::$settings_page . '">Settings</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Hooks to 'admin_menu'
	 *
	 * @since 1.0.0
	 */
	public function register_pages() {

		/* Cast the first sub menu to the settings menu */

		$settings_page_load = add_submenu_page(
			'options-general.php',
			esc_html__( 'Post Status Notifications', 'wpsite-post-status-notification' ),
			esc_html__( 'Post Status Notifications', 'wpsite-post-status-notification' ),
			'manage_options',
			self::$settings_page,
			array( $this, 'page_settings' )
		);
		add_action( "load-$settings_page_load", array( $this, 'admin_scripts' ) );
	}

	/**
	 * Hooks to 'load-$page'
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {

		wp_enqueue_style( 'wpsite_post_status_notifications_settings_css', WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL . '/css/settings.css', array(), WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM );
		wp_enqueue_style( 'wpsite_post_status_notifications_bootstrap_css', WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL . '/css/nnr-bootstrap.min.css', array(), WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM );

		wp_enqueue_script( 'wpsite_post_status_notifications_bootstrap_js', WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL . '/js/bootstrap.min.js', array( 'jquery' ), WPSITE_POST_STATUS_NOTIFICATION_VERSION_NUM, true );
	}

	/**
	 * Callback to info page
	 *
	 * @since 1.0.0
	 */
	public function page_settings() {

		$post_types = $this->get_post_types();

		$settings = get_option( 'wpsite_post_status_notifications_settings' );

		// Default values.
		if ( false === $settings ) {
			$settings = self::default_data();
		}

		// Save data and check nonce.
		if ( isset( $_POST['submit'] ) && check_admin_referer( 'wpsite_post_status_notifications_admin_settings' ) ) {

			// Determine Post Types.

			$post_types_array = array();

			foreach ( $post_types as $post_type ) {

				$post_type_val = ! empty( $_POST[ 'wpsite_post_status_notifications_settings_post_types_' . $post_type ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wpsite_post_status_notifications_settings_post_types_' . $post_type ] ) ) : '';

				if ( ! empty( $post_type_val ) ) {
					$post_types_array[] = $post_type;
				}
			}

			$default_data = self::default_data();

			$settings = array(
				'publish_notify' => ! empty( $_POST['wpsite_post_status_notifications_settings_publish_notify'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_publish_notify'] ) ) : '',
				'pending_notify' => ! empty( $_POST['wpsite_post_status_notifications_settings_pending_notify'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_pending_notify'] ) ) : '',
				'post_types'     => $post_types_array,
				'message'        => array(
					'from_name'                     => ! empty( $_POST['wpsite_post_status_notifications_settings_message_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_from_name'] ) ) : '',
					'cc_email'                      => ! empty( $_POST['wpsite_post_status_notifications_settings_message_cc_email'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_cc_email'] ) ) : '',
					'bcc_email'                     => ! empty( $_POST['wpsite_post_status_notifications_settings_message_bcc_email'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_bcc_email'] ) ) : '',
					'from_email'                    => ! empty( $_POST['wpsite_post_status_notifications_settings_message_from_email'] ) ? sanitize_email( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_from_email'] ) ) : '',
					'subject_published'             => ! empty( $_POST['wpsite_post_status_notifications_settings_message_subject_published'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_subject_published'] ) ) : '',
					'subject_published_global'      => ! empty( $_POST['wpsite_post_status_notifications_settings_message_subject_published_global'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_subject_published_global'] ) ) : '',
					'subject_published_contributor' => ! empty( $_POST['wpsite_post_status_notifications_settings_message_subject_published_contributor'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_subject_published_contributor'] ) ) : '',
					'subject_pending'               => ! empty( $_POST['wpsite_post_status_notifications_settings_message_subject_pending'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_subject_pending'] ) ) : '',
					'content_published'             => ! empty( $_POST['wpsite_post_status_notifications_settings_message_content_published'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_content_published'] ) ) : '',
					'content_published_global'      => ! empty( $_POST['wpsite_post_status_notifications_settings_message_content_published_global'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_content_published_global'] ) ) : '',
					'content_published_contributor' => ! empty( $_POST['wpsite_post_status_notifications_settings_message_content_published_contributor'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_content_published_contributor'] ) ) : '',
					'content_pending'               => ! empty( $_POST['wpsite_post_status_notifications_settings_message_content_pending'] ) ? sanitize_text_field( wp_unslash( $_POST['wpsite_post_status_notifications_settings_message_content_pending'] ) ) : '',
					'share_links'                   => array(
						'twitter'  => ! empty( $_POST['wpsite_post_status_notifications_settings_message_share_links_twitter'] ) ? true : false,
						'facebook' => ! empty( $_POST['wpsite_post_status_notifications_settings_message_share_links_facebook'] ) ? true : false,
						'linkedin' => ! empty( $_POST['wpsite_post_status_notifications_settings_message_share_links_linkedin'] ) ? true : false,
					),
				),
			);

			$settings['message'] = array(
				'from_name'                     => ! empty( $settings['message']['from_name'] ) ? $settings['message']['from_name'] : $default_data['message']['from_name'],
				'cc_email'                      => ! empty( $settings['message']['cc_email'] ) ? $settings['message']['cc_email'] : $default_data['message']['cc_email'],
				'bcc_email'                     => ! empty( $settings['message']['bcc_email'] ) ? $settings['message']['bcc_email'] : $default_data['message']['bcc_email'],
				'from_email'                    => ! empty( $settings['message']['from_email'] ) ? $settings['message']['from_email'] : $default_data['message']['from_email'],
				'subject_published'             => ! empty( $settings['message']['subject_published'] ) ? $settings['message']['subject_published'] : $default_data['message']['subject_published'],
				'subject_published_global'      => ! empty( $settings['message']['subject_published_global'] ) ? $settings['message']['subject_published_global'] : $default_data['message']['subject_published_global'],
				'subject_published_contributor' => ! empty( $settings['message']['subject_published_contributor'] ) ? $settings['message']['subject_published_contributor'] : $default_data['message']['subject_published_contributor'],
				'subject_pending'               => ! empty( $settings['message']['subject_pending'] ) ? $settings['message']['subject_pending'] : $default_data['message']['subject_pending'],
				'content_published'             => ! empty( $settings['message']['content_published'] ) ? $settings['message']['content_published'] : $default_data['message']['content_published'],
				'content_published_global'      => ! empty( $settings['message']['content_published_global'] ) ? $settings['message']['content_published_global'] : $default_data['message']['content_published_global'],
				'content_published_contributor' => ! empty( $settings['message']['content_published_contributor'] ) ? $settings['message']['content_published_contributor'] : $default_data['message']['content_published_contributor'],
				'content_pending'               => ! empty( $settings['message']['content_pending'] ) ? $settings['message']['content_pending'] : $default_data['message']['content_pending'],
				'share_links'                   => array(
					'twitter'  => $settings['message']['share_links']['twitter'],
					'facebook' => $settings['message']['share_links']['facebook'],
					'linkedin' => $settings['message']['share_links']['linkedin'],
				),
			);

			update_option( 'wpsite_post_status_notifications_settings', $settings );
		}

		require 'admin/dashboard.php';
	}

	/**
	 * Email all admins when post status changes to pending
	 *
	 * @since 1.0.0
	 * @param string $new_status The new status.
	 * @param string $old_status The old status.
	 * @param object $post The post.
	 * @return mixed
	 */
	public function wpsite_send_email( $new_status, $old_status, $post ) {

		// Get settings.
		$settings = get_option( 'wpsite_post_status_notifications_settings' );

		// Default values.
		$default_data = self::default_data();

		if ( false === $settings ) {
			$settings = $default_data;
		}

		$settings['message'] = array(
			'from_name'                     => '' !== $settings['message']['from_name'] ? $settings['message']['from_name'] : $default_data['message']['from_name'],
			'cc_email'                      => '' !== $settings['message']['cc_email'] ? $settings['message']['cc_email'] : $default_data['message']['cc_email'],
			'bcc_email'                     => '' !== $settings['message']['bcc_email'] ? $settings['message']['bcc_email'] : $default_data['message']['bcc_email'],
			'from_email'                    => '' !== $settings['message']['from_email'] ? $settings['message']['from_email'] : $default_data['message']['from_email'],
			'subject_published'             => '' !== $settings['message']['subject_published'] ? $settings['message']['subject_published'] : $default_data['message']['subject_published'],
			'subject_published_contributor' => '' !== $settings['message']['subject_published_contributor'] ? $settings['message']['subject_published_contributor'] : $default_data['message']['subject_published_contributor'],
			'subject_pending'               => '' !== $settings['message']['subject_pending'] ? $settings['message']['subject_pending'] : $default_data['message']['subject_pending'],
			'content_published'             => '' !== $settings['message']['content_published'] ? $settings['message']['content_published'] : $default_data['message']['content_published'],
			'content_published_contributor' => '' !== $settings['message']['content_published_contributor'] ? $settings['message']['content_published_contributor'] : $default_data['message']['content_published_contributor'],
			'content_pending'               => '' !== $settings['message']['content_pending'] ? $settings['message']['content_pending'] : $default_data['message']['content_pending'],
			'share_links'                   => array(
				'twitter'  => $settings['message']['share_links']['twitter'],
				'facebook' => $settings['message']['share_links']['facebook'],
				'linkedin' => $settings['message']['share_links']['linkedin'],
			),
		);

		// If status did not change.
		if ( $new_status === $old_status ) {
			return null;
		}

		// Set all headers.
		$headers = array();

		if ( isset( $settings['message']['from_name'] ) && '' !== $settings['message']['from_name'] ) {
			$fromname = $settings['message']['from_name'];
		}

		if ( isset( $settings['message']['from_email'] ) && '' !== $settings['message']['from_email'] ) {
			$headers[] = 'From: ' . $fromname . ' <' . $settings['message']['from_email'] . ">\r\n";
		}

		if ( isset( $settings['message']['cc_email'] ) && '' !== $settings['message']['cc_email'] ) {
			$headers[] = 'Cc: ' . $settings['message']['cc_email'] . "\r\n";
		}

		if ( isset( $settings['message']['bcc_email'] ) && '' !== $settings['message']['bcc_email'] ) {
			$headers[] = 'Bcc: ' . $settings['message']['bcc_email'] . "\r\n";
		}

		if ( isset( $settings['message']['share_links'] ) ) {
			$check = false;
			foreach ( $settings['message']['share_links'] as $link ) {
				if ( $link ) {
					$share_links_check = true;
				}
			}
		}

		$share_links = '';
		$url         = get_permalink( $post->ID );

		if ( isset( $share_links_check ) && $share_links_check ) {
			$share_links = "\r\n\r\nShare Links\r\n";

			if ( $settings['message']['share_links']['twitter'] ) {
				$share_links .= 'Twitter: ' . esc_url( self::$twitter_share_link . $url ) . "\r\n";
			}

			if ( $settings['message']['share_links']['facebook'] ) {
				$share_links .= 'Facebook: ' . esc_url( self::$facebook_share_link . $url ) . "\r\n";
			}

			if ( $settings['message']['share_links']['linkedin'] ) {
				$share_links .= 'LinkedIn: ' . esc_url( self::$linkedin_share_link . $url ) . "\r\n";
			}
		}

		// Notifiy Admins that Contributor has writen a post.
		if ( in_array( $post->post_type, $settings['post_types'], true ) && 'pending' === $new_status && user_can( $post->post_author, 'edit_posts' ) && ! user_can( $post->post_author, 'publish_posts' ) ) {

			$url          = get_permalink( $post->ID );
			$edit_link    = get_edit_post_link( $post->ID, '' );
			$preview_link = get_permalink( $post->ID ) . '&preview=true';
			$username     = get_userdata( $post->post_author );

			$subject = $this->parse_tags( $post, $username, $settings['message']['subject_pending'] );
			$message = $this->parse_tags( $post, $username, $settings['message']['content_pending'] );

			$message .= "\r\n\r\n";
			$message .= 'Author: ' . $username->display_name . "\r\n";
			$message .= 'Title of ' . $post->post_type . ': ' . $post->post_title;

			$message .= "\r\n\r\n";
			$message .= 'Edit the ' . $post->post_type . ': ' . $edit_link . "\r\n";
			$message .= 'Preview it: ' . $preview_link;

			$nnr_pending_notify = $settings['pending_notify'];

			if ( 'both' === $nnr_pending_notify ) {
				$users = get_users( array( 'role__in' => array( 'administrator', 'editor' ) ) );
			} else {
				$users = get_users( array( 'role' => $settings['pending_notify'] ) );
			}

			foreach ( $users as $user ) {
				$result = wp_mail( $user->user_email, $subject, $message, $headers );
			}
		}

		// Notifiy Contributor or All Admins or All Users that a post was published.
		if ( in_array( $post->post_type, $settings['post_types'], true ) && 'publish' === $new_status ) {

			// Notify Contributor that their post was published.
			if ( isset( $settings['publish_notify'] ) && 'author' === $settings['publish_notify'] && 'pending' === $old_status && user_can( $post->post_author, 'edit_posts' ) && ! user_can( $post->post_author, 'publish_posts' ) ) {

				$username = get_userdata( $post->post_author );
				$subject  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['subject_published_contributor'] );
				$message  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['content_published_contributor'] );
				$message .= $share_links;

				$result = wp_mail( $username->user_email, $subject, $message, $headers );
			}

			// Notify All Admins or All Users.
			if ( isset( $settings['publish_notify'] ) && 'author' !== $settings['publish_notify'] ) {

				// Notify All Admins.
				if ( 'admins' === $settings['publish_notify'] && 'pending' === $old_status ) {

					$subject  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['subject_published_contributor'] );
					$message  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['content_published_contributor'] );
					$message .= $share_links;

					$users = get_users( array( 'role' => 'administrator' ) );

					foreach ( $users as $user ) {
						$result = wp_mail( $user->user_email, $subject, $message, $headers );
					}
				}
				if ( 'admins' === $settings['publish_notify'] && 'pending' !== $old_status ) {

					$subject  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['subject_published'] );
					$message  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['content_published'] );
					$message .= $share_links;

					$users = get_users( array( 'role' => 'administrator' ) );

					foreach ( $users as $user ) {
						$result = wp_mail( $user->user_email, $subject, $message, $headers );
					}
				}

				// Notify All Editors.
				if ( 'editors' === $settings['publish_notify'] && ( 'pending' === $old_status || $old_status !== $new_status ) ) {

					$subject  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['subject_published'] );
					$message  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['content_published'] );
					$message .= $share_links;

					$users = get_users( array( 'role' => 'editor' ) );

					foreach ( $users as $user ) {
						$result = wp_mail( $user->user_email, $subject, $message, $headers );
					}
				}

				// Notify All Users.
				if ( 'users' === $settings['publish_notify'] && ( 'pending' === $old_status || $old_status !== $new_status ) ) {

					$exclude_array = array();

					if ( 'pending' === $old_status && user_can( $post->post_author, 'edit_posts' ) && ! user_can( $post->post_author, 'publish_posts' ) ) {

						$username = get_userdata( $post->post_author );
						$subject  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['subject_published_contributor'] );
						$message  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['content_published_contributor'] );
						$message .= $share_links;

						$result = wp_mail( $username->user_email, $subject, $message, $headers );

						$exclude_array[] = $post->post_author;
					}

					$subject  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['subject_published'] );
					$message  = $this->parse_tags( $post, get_userdata( $post->post_author ), $settings['message']['content_published'] );
					$message .= $share_links;

					$users = get_users( array( 'exclude' => $exclude_array ) );

					foreach ( $users as $user ) {
						$result = wp_mail( $user->user_email, $subject, $message, $headers );
					}
				}
			}
		}
	}

	/**
	 * Enqueue editor script.
	 *
	 * @return void
	 */
	public function wpsite_enqueue_editor_script() {

		wp_enqueue_script( 'wpsite-gutenberg-hooks', WPSITE_POST_STATUS_NOTIFICATION_PLUGIN_URL . '/js/gutenberg-hooks.js', array( 'wp-edit-post' ), '1.0', true );
		wp_script_add_data( 'wpsite-gutenberg-hooks', 'defer', true );

		// create nonce.
		$wpsite_post_status_notification_nonce = wp_create_nonce( 'wpsite_post_status_notification' );

		wp_localize_script(
			'wpsite-gutenberg-hooks',
			'wpsite_gutenberg_hooks',
			array(
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'wp_nonce' => $wpsite_post_status_notification_nonce,
			)
		);
	}

	/**
	 * Send email on post status change
	 *
	 * @return void
	 */
	public function wpsite_handle_post_save() {

		// Get nonce.
		$wp_nonce = ! empty( $_POST['wp_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_nonce'] ) ) : '';

		// Verify nonce.
		if ( empty( $wp_nonce ) || ! wp_verify_nonce( $wp_nonce, 'wpsite_post_status_notification' ) ) {
			wp_send_json_error( 'Invalid nonce.' );
			wp_die();
		}

		// Get the post ID.
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

		// Get the post.
		$post = get_post( $post_id );

		// Get the post status.
		$post_status = isset( $_POST['post_status'] ) ? sanitize_text_field( wp_unslash( $_POST['post_status'] ) ) : '';

		// Get the old post status.
		$old_post_status = isset( $_POST['previous_status'] ) ? sanitize_text_field( wp_unslash( $_POST['previous_status'] ) ) : '';

		// Send the email.
		$this->wpsite_send_email( $post_status, $old_post_status, $post );

		// Return a success message.
		wp_send_json_success( 'Email sent successfully.' );
		wp_die();
	}



	/**
	 * Parse the tags added by people
	 *
	 * @access public
	 * @static
	 * @param mixed $post The post.
	 * @param mixed $user The user.
	 * @param mixed $text The text.
	 * @return string
	 */
	public function parse_tags( $post, $user, $text ) {

		// Replace post title.
		$text = str_replace( '{post_title}', $post->post_title, $text );

		// Replace post url.
		$text = str_replace( '{post_url}', get_permalink( $post->ID ), $text );

		// Replace post type.
		$text = str_replace( '{post_type}', $post->post_type, $text );

		// Replace user display name.
		$text = str_replace( '{display_name}', $user->display_name, $text );

		// Add a break line.
		$text = str_replace( '{break_line}', "\r\n", $text );

		return $text;
	}

	/**
	 * Returns all post types that are queryable and public
	 *
	 * @return array
	 */
	public function get_post_types() {

		$post_types = get_post_types( array( 'public' => true ) );

		unset( $post_types['attachment'] );
		unset( $post_types['page'] );

		return $post_types;
	}

	// Helpers.

	/**
	 * Get plugin directory.
	 *
	 * @return string
	 */
	public function plugin_dir() {

		if ( is_null( $this->plugin_dir ) ) {
			$this->plugin_dir = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/';
		}

		return $this->plugin_dir;
	}

	/**
	 * Get plugin uri.
	 *
	 * @return string
	 */
	public function plugin_url() {

		if ( is_null( $this->plugin_url ) ) {
			$this->plugin_url = untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/';
		}

		return $this->plugin_url;
	}
}
new WPSite_Post_Status_Notifications();
