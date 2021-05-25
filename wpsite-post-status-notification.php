<?php
/**
 * Plugin Name:        Post Status Notifications
 * Plugin URI:        http://www.draftpress.com/plugin/post-status-notifications
 * Description:        Send post status notifications by email to Administrators and Contributors when posts are submitted for review or published. Great for multi-author sites to improve editorial workflow.
 * Version:        3.1.7
 * Author:        99 Robots
 * Author URI:        https://www.draftpress.com
 * License:            GPL2
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
/**
 *  WPSite_Post_Status_Notifications main class
 *
 * @since 1.0.0
 * @using Wordpress 3.8
 */
class WPSite_Post_Status_Notifications
{

    /**
     * WPSite_Post_Status_Notifications version.
     * @var string
     */
    public $version = '3.1.7';

    /**
     * The single instance of the class.
     * @var WPSite_Post_Status_Notifications
     */
    protected static $_instance = null;

    /**
     * Plugin url.
     * @var string
     */
    private $plugin_url = null;

    /**
     * Plugin path.
     * @var string
     */
    private $plugin_dir = null;

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
     * (default value: 'http://www.draftpress.com/plugin/post-status-notifications')
     *
     * @var string
     * @access private
     * @static
     */
    private static $web_page = 'http://www.draftpress.com/plugin/post-status-notifications';

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
    public static function default_data()
    {
        return array(
            'publish_notify' => 'contributor',
            'pending_notify' => 'admin',
            'post_types' => array('post'),
            'message' => array(
                'from_name' => get_bloginfo('name'),
                'cc_email' => '',
                'bcc_email' => '',
                'from_email' => 'wordpress@' . $_SERVER['HTTP_HOST'],
                'subject_published_contributor' => 'Published {post_type}: {post_title}',
                'subject_published' => 'Published {post_type}: {post_title}',
                'subject_published_global' => 'Published {post_type}: {post_title}',
                'subject_pending' => 'Please moderate: {post_title}',
                'content_published_contributor' => '{post_title} was just published! Check it out, and thanks for the hard work. {break_line}{break_line}View it: {post_url}',
                'content_published' => '{post_title} was just published! {break_line}{break_line}View it: {post_url}',
                'content_published_global' => '{post_title} was just published! {break_line}{break_line}View it: {post_url}',
                'content_pending' => 'A new {post_type}: {post_title} from contributor: {display_name} is now pending.',
                'share_links' => array(
                    'twitter' => true,
                    'facebook' => true,
                    'google' => true,
                    'linkedin' => true,
                ),
            ),
        );
    }

    /**
     * Cloning is forbidden.
     */
    public function __clone()
    {
        wc_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'wpsite-post-status-notification'), $this->version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     */
    public function __wakeup()
    {
        wc_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'wpsite-post-status-notification'), $this->version);
    }

    /**
     * Main WPSite_Post_Status_Notifications instance.
     *
     * Ensure only one instance is loaded or can be loaded.
     *
     * @return WPSite_Post_Status_Notifications
     */
    public static function instance()
    {

        if (is_null(self::$_instance) && !(self::$_instance instanceof WPSite_Post_Status_Notifications)) {
            self::$_instance = new WPSite_Post_Status_Notifications();
            self::$_instance->hooks();
        }

        return self::$_instance;
    }

    /**
     * WPSite_Post_Status_Notifications constructor.
     */
    private function __construct()
    {

    }

    /**
     * Add hooks to begin.
     * @return void
     */
    private function hooks()
    {

        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));

        if (is_admin()) {

            $plugin = plugin_basename(__FILE__);
            add_filter("plugin_action_links_$plugin", array($this, 'plugin_links'));

            add_action('transition_post_status', array($this, 'wpsite_send_email'), 10, 3);
            add_action('admin_menu', array($this, 'register_pages'));
        }
    }

    /**
     * Load the plugin text domain for translation.
     * @return void
     */
    public function load_plugin_textdomain()
    {

        $locale = apply_filters('plugin_locale', get_locale(), 'wpsite-post-status-notification');

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
     */
    public function plugin_links($links)
    {

        $settings_link = '<a href="options-general.php?page=' . self::$settings_page . '">Settings</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Hooks to 'admin_menu'
     *
     * @since 1.0.0
     */
    public function register_pages()
    {

        /* Cast the first sub menu to the settings menu */

        $settings_page_load = add_submenu_page(
            'options-general.php',
            esc_html__('Post Status Notifications', 'wpsite-post-status-notification'),
            esc_html__('Post Status Notifications', 'wpsite-post-status-notification'),
            'manage_options',
            self::$settings_page,
            array($this, 'page_settings')
        );
        add_action("load-$settings_page_load", array($this, 'admin_scripts'));
    }

    /**
     * Hooks to 'load-$page'
     *
     * @since 1.0.0
     */
    public function admin_scripts()
    {

        wp_enqueue_style('wpsite_post_status_notifications_settings_css', wpsite_psn()->plugin_url() . 'css/settings.css');
        wp_enqueue_style('wpsite_post_status_notifications_bootstrap_css', wpsite_psn()->plugin_url() . 'css/nnr-bootstrap.min.css');

        wp_enqueue_script('wpsite_post_status_notifications_bootstrap_js', wpsite_psn()->plugin_url() . 'js/bootstrap.min.js', array('jquery'));
    }

    /**
     * Callback to info page
     *
     * @since 1.0.0
     */
    public function page_settings()
    {

        $post_types = $this->get_post_types();

        $settings = get_option('wpsite_post_status_notifications_settings');

        // Default values
        if (false === $settings) {
            $settings = self::default_data();
        }

        // Save data and check nonce
        if (isset($_POST['submit']) && check_admin_referer('wpsite_post_status_notifications_admin_settings')) {

            // Determine Post Types

            $post_types_array = array();

            foreach ($post_types as $post_type) {
                if (isset($_POST['wpsite_post_status_notifications_settings_post_types_' . $post_type]) && $_POST['wpsite_post_status_notifications_settings_post_types_' . $post_type]) {
                    $post_types_array[] = $post_type;
                }
            }

            $default_data = self::default_data();

            $settings = array(
                'publish_notify' => isset($_POST['wpsite_post_status_notifications_settings_publish_notify']) ? $_POST['wpsite_post_status_notifications_settings_publish_notify'] : '',
                'pending_notify' => isset($_POST['wpsite_post_status_notifications_settings_pending_notify']) ? $_POST['wpsite_post_status_notifications_settings_pending_notify'] : '',
                'post_types' => $post_types_array,
                'message' => array(
                    'from_name' => isset($_POST['wpsite_post_status_notifications_settings_message_from_name']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_from_name'])) : $default_data['message']['from_name'],
                    'cc_email' => isset($_POST['wpsite_post_status_notifications_settings_message_cc_email']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_cc_email'])) : $default_data['message']['cc_email'],
                    'bcc_email' => isset($_POST['wpsite_post_status_notifications_settings_message_bcc_email']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_bcc_email'])) : $default_data['message']['bcc_email'],
                    'from_email' => isset($_POST['wpsite_post_status_notifications_settings_message_from_email']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_from_email'])) : $default_data['message']['from_email'],
                    'subject_published' => isset($_POST['wpsite_post_status_notifications_settings_message_subject_published']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_published'])) : $default_data['message']['subject_published'],
                    'subject_published_global' => isset($_POST['wpsite_post_status_notifications_settings_message_subject_published_global']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_published_global'])) : $default_data['message']['subject_published_global'],
                    'subject_published_contributor' => isset($_POST['wpsite_post_status_notifications_settings_message_subject_published_contributor']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_published_contributor'])) : $default_data['message']['subject_published_contributor'],
                    'subject_pending' => isset($_POST['wpsite_post_status_notifications_settings_message_subject_pending']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_subject_pending'])) : $default_data['message']['subject_pending'],
                    'content_published' => isset($_POST['wpsite_post_status_notifications_settings_message_content_published']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_published'])) : $default_data['message']['content_published'],
                    'content_published_global' => isset($_POST['wpsite_post_status_notifications_settings_message_content_published_global']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_published_global'])) : $default_data['message']['content_published_global'],
                    'content_published_contributor' => isset($_POST['wpsite_post_status_notifications_settings_message_content_published_contributor']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_published_contributor'])) : $default_data['message']['content_published_contributor'],
                    'content_pending' => isset($_POST['wpsite_post_status_notifications_settings_message_content_pending']) ? stripcslashes(sanitize_text_field($_POST['wpsite_post_status_notifications_settings_message_content_pending'])) : $default_data['message']['content_pending'],
                    'share_links' => array(
                        'twitter' => isset($_POST['wpsite_post_status_notifications_settings_message_share_links_twitter']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_twitter'] ? true : false,
                        'facebook' => isset($_POST['wpsite_post_status_notifications_settings_message_share_links_facebook']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_facebook'] ? true : false,
                        'google' => isset($_POST['wpsite_post_status_notifications_settings_message_share_links_google']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_google'] ? true : false,
                        'linkedin' => isset($_POST['wpsite_post_status_notifications_settings_message_share_links_linkedin']) && $_POST['wpsite_post_status_notifications_settings_message_share_links_linkedin'] ? true : false,
                    ),
                ),
            );

            $settings['message'] = array(
                'from_name' => '' !== $settings['message']['from_name'] ? $settings['message']['from_name'] : $default_data['message']['from_name'],
                'cc_email' => '' !== $settings['message']['cc_email'] ? $settings['message']['cc_email'] : $default_data['message']['cc_email'],
                'bcc_email' => '' !== $settings['message']['bcc_email'] ? $settings['message']['bcc_email'] : $default_data['message']['bcc_email'],
                'from_email' => '' !== $settings['message']['from_email'] ? $settings['message']['from_email'] : $default_data['message']['from_email'],
                'subject_published' => '' !== $settings['message']['subject_published'] ? $settings['message']['subject_published'] : $default_data['message']['subject_published'],
                'subject_published_global' => '' !== $settings['message']['subject_published_global'] ? $settings['message']['subject_published_global'] : $default_data['message']['subject_published_global'],
                'subject_published_contributor' => '' !== $settings['message']['subject_published_contributor'] ? $settings['message']['subject_published_contributor'] : $default_data['message']['subject_published_contributor'],
                'subject_pending' => '' !== $settings['message']['subject_pending'] ? $settings['message']['subject_pending'] : $default_data['message']['subject_pending'],
                'content_published' => '' !== $settings['message']['content_published'] ? $settings['message']['content_published'] : $default_data['message']['content_published'],
                'content_published_global' => '' !== $settings['message']['content_published_global'] ? $settings['message']['content_published_global'] : $default_data['message']['content_published_global'],
                'content_published_contributor' => '' !== $settings['message']['content_published_contributor'] ? $settings['message']['content_published_contributor'] : $default_data['message']['content_published_contributor'],
                'content_pending' => '' !== $settings['message']['content_pending'] ? $settings['message']['content_pending'] : $default_data['message']['content_pending'],
                'share_links' => array(
                    'twitter' => $settings['message']['share_links']['twitter'],
                    'facebook' => $settings['message']['share_links']['facebook'],
                    'google' => $settings['message']['share_links']['google'],
                    'linkedin' => $settings['message']['share_links']['linkedin'],
                ),
            );

            update_option('wpsite_post_status_notifications_settings', $settings);
        }

        require 'admin/dashboard.php';
    }

    /**
     * Email all admins when post status changes to pending
     *
     * @since 1.0.0
     */
    public function wpsite_send_email($new_status, $old_status, $post)
    {

        $settings = get_option('wpsite_post_status_notifications_settings');

        // Default values
        $default_data = self::default_data();

        if (false === $settings) {
            $settings = $default_data;
        }

        $settings['message'] = array(
            'from_name' => '' !== $settings['message']['from_name'] ? $settings['message']['from_name'] : $default_data['message']['from_name'],
            'cc_email' => '' !== $settings['message']['cc_email'] ? $settings['message']['cc_email'] : $default_data['message']['cc_email'],
            'bcc_email' => '' !== $settings['message']['bcc_email'] ? $settings['message']['bcc_email'] : $default_data['message']['bcc_email'],
            'from_email' => '' !== $settings['message']['from_email'] ? $settings['message']['from_email'] : $default_data['message']['from_email'],
            'subject_published' => '' !== $settings['message']['subject_published'] ? $settings['message']['subject_published'] : $default_data['message']['subject_published'],
            'subject_published_contributor' => '' !== $settings['message']['subject_published_contributor'] ? $settings['message']['subject_published_contributor'] : $default_data['message']['subject_published_contributor'],
            'subject_pending' => '' !== $settings['message']['subject_pending'] ? $settings['message']['subject_pending'] : $default_data['message']['subject_pending'],
            'content_published' => '' !== $settings['message']['content_published'] ? $settings['message']['content_published'] : $default_data['message']['content_published'],
            'content_published_contributor' => '' !== $settings['message']['content_published_contributor'] ? $settings['message']['content_published_contributor'] : $default_data['message']['content_published_contributor'],
            'content_pending' => '' !== $settings['message']['content_pending'] ? $settings['message']['content_pending'] : $default_data['message']['content_pending'],
            'share_links' => array(
                'twitter' => $settings['message']['share_links']['twitter'],
                'facebook' => $settings['message']['share_links']['facebook'],
                'google' => $settings['message']['share_links']['google'],
                'linkedin' => $settings['message']['share_links']['linkedin'],
            ),
        );

        // If status did not change
        if ($new_status === $old_status) {
            return null;
        }

        // Set all headers
        $headers = array();

        if (isset($settings['message']['from_name']) && '' !== $settings['message']['from_name']) {
            $fromname = $settings['message']['from_name'];
        }

        if (isset($settings['message']['from_email']) && '' !== $settings['message']['from_email']) {
            $headers[] = 'From: ' . $fromname . ' <' . $settings['message']['from_email'] . ">\r\n";
        }

        if (isset($settings['message']['cc_email']) && '' !== $settings['message']['cc_email']) {
            $headers[] = 'Cc: ' . $settings['message']['cc_email'] . "\r\n";
        }

        if (isset($settings['message']['bcc_email']) && '' !== $settings['message']['bcc_email']) {
            $headers[] = 'Bcc: ' . $settings['message']['bcc_email'] . "\r\n";
        }

        if (isset($settings['message']['share_links'])) {
            $check = false;
            foreach ($settings['message']['share_links'] as $link) {
                if ($link) {
                    $share_links_check = true;
                }
            }
        }

        $share_links = '';
        $url = get_permalink($post->ID);

        if (isset($share_links_check) && $share_links_check) {
            $share_links = "\r\n\r\nShare Links\r\n";

            if ($settings['message']['share_links']['twitter']) {
                $share_links .= 'Twitter: ' . esc_url(self::$twitter_share_link . $url) . "\r\n";
            }

            if ($settings['message']['share_links']['facebook']) {
                $share_links .= 'Facebook: ' . esc_url(self::$facebook_share_link . $url) . "\r\n";
            }

            if ($settings['message']['share_links']['google']) {
                $share_links .= 'Google+: ' . esc_url(self::$google_share_link . $url) . "\r\n";
            }

            if ($settings['message']['share_links']['linkedin']) {
                $share_links .= 'LinkedIn: ' . esc_url(self::$linkedin_share_link . $url) . "\r\n";
            }
        }
        // Notifiy Admins that Contributor has writen a post
        if (in_array($post->post_type, $settings['post_types']) && 'pending' === $new_status && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {

            $url = get_permalink($post->ID);
            $edit_link = get_edit_post_link($post->ID, '');
            $preview_link = get_permalink($post->ID) . '&preview=true';
            $username = get_userdata($post->post_author);

            $subject = $this->parse_tags($post, $username, $settings['message']['subject_pending']);
            $message = $this->parse_tags($post, $username, $settings['message']['content_pending']);

            $message .= "\r\n\r\n";
            $message .= 'Author: ' . $username->display_name . "\r\n";
            $message .= 'Title of ' . $post->post_type . ': ' . $post->post_title;

            $message .= "\r\n\r\n";
            $message .= 'Edit the ' . $post->post_type . ': ' . $edit_link . "\r\n";
            $message .= 'Preview it: ' . $preview_link;

            $users = get_users(array('role' => $settings['pending_notify']));

            foreach ($users as $user) {
                $result = wp_mail($user->user_email, $subject, $message, $headers);
            }
        }

        // Notifiy Contributor or All Admins or All Users that a post was published
        if (in_array($post->post_type, $settings['post_types']) && 'publish' === $new_status) {

            // Notify Contributor that their post was published
            if (isset($settings['publish_notify']) && 'author' === $settings['publish_notify'] && 'pending' === $old_status && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {

                $username = get_userdata($post->post_author);
                $subject = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published_contributor']);
                $message = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published_contributor']);
                $message .= $share_links;

                $result = wp_mail($username->user_email, $subject, $message, $headers);
            }

            // Notify All Admins or All Users
            if (isset($settings['publish_notify']) && 'author' !== $settings['publish_notify']) {

                // Notify All Admins
                if ('admins' === $settings['publish_notify'] && 'pending' === $old_status) {

                    $subject = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published_contributor']);
                    $message = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published_contributor']);
                    $message .= $share_links;

                    $users = get_users(array('role' => 'administrator'));

                    foreach ($users as $user) {
                        $result = wp_mail($user->user_email, $subject, $message, $headers);
                    }
                }
                if ('admins' === $settings['publish_notify'] && 'pending' !== $old_status) {

                    $subject = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published']);
                    $message = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published']);
                    $message .= $share_links;

                    $users = get_users(array('role' => 'administrator'));

                    foreach ($users as $user) {
                        $result = wp_mail($user->user_email, $subject, $message, $headers);
                    }
                }

                // Notify All Editors
                if ('editors' === $settings['publish_notify'] && ('pending' === $old_status || $old_status != $new_status)) {

                    $subject = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published']);
                    $message = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published']);
                    $message .= $share_links;

                    $users = get_users(array('role' => 'editor'));

                    foreach ($users as $user) {
                        $result = wp_mail($user->user_email, $subject, $message, $headers);
                    }
                }

                // Notify All Users
                if ('users' === $settings['publish_notify'] && ('pending' === $old_status || $old_status != $new_status)) {

                    $exclude_array = array();

                    if ('pending' === $old_status && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {

                        $username = get_userdata($post->post_author);
                        $subject = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published_contributor']);
                        $message = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published_contributor']);
                        $message .= $share_links;

                        $result = wp_mail($username->user_email, $subject, $message, $headers);

                        $exclude_array[] = $post->post_author;
                    }

                    $subject = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['subject_published']);
                    $message = $this->parse_tags($post, get_userdata($post->post_author), $settings['message']['content_published']);
                    $message .= $share_links;

                    $users = get_users(array('exclude' => $exclude_array));

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
    public function parse_tags($post, $user, $text)
    {

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
     * @return array
     */
    public function get_post_types()
    {

        $post_types = get_post_types(array('public' => true));

        unset($post_types['attachment']);
        unset($post_types['page']);

        return $post_types;
    }

    // Helpers -----------------------------------------------------------

    /**
     * Get plugin directory.
     * @return string
     */
    public function plugin_dir()
    {

        if (is_null($this->plugin_dir)) {
            $this->plugin_dir = untrailingslashit(plugin_dir_path(__FILE__)) . '/';
        }

        return $this->plugin_dir;
    }

    /**
     * Get plugin uri.
     * @return string
     */
    public function plugin_url()
    {

        if (is_null($this->plugin_url)) {
            $this->plugin_url = untrailingslashit(plugin_dir_url(__FILE__)) . '/';
        }

        return $this->plugin_url;
    }

    /**
     * Get plugin version
     *
     * @return string
     */
    public function get_version()
    {
        return $this->version;
    }
}

/**
 * Main instance of WPSite_Post_Status_Notifications.
 *
 * Returns the main instance of WPSite_Post_Status_Notifications to prevent the need to use globals.
 *
 * @return WPSite_Post_Status_Notifications
 */
function wpsite_psn()
{
    return WPSite_Post_Status_Notifications::instance();
}

// Init the plugin.
wpsite_psn();
