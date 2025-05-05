<?php
/**
 * Tracker functionality for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Tracker {
    /**
     * Initialize the class
     */
    public static function init() {
        // Track user login
        add_action('wp_login', [__CLASS__, 'track_login'], 10, 2);
        
        // Track user logout
        add_action('wp_logout', [__CLASS__, 'track_logout']);
        
        // Track failed login
        add_action('wp_login_failed', [__CLASS__, 'track_login_failed']);
        
        // Track password reset
        add_action('after_password_reset', [__CLASS__, 'track_password_reset'], 10, 2);
        
        // Track user registration
        add_action('user_register', [__CLASS__, 'track_user_registration']);
        
        // Track user profile update
        add_action('profile_update', [__CLASS__, 'track_profile_update'], 10, 2);
        
        // Track post/page creation and updates
        add_action('transition_post_status', [__CLASS__, 'track_post_status_change'], 10, 3);
        
        // Track post/page deletion
        add_action('delete_post', [__CLASS__, 'track_post_deletion']);
        
        // Track comment creation
        add_action('wp_insert_comment', [__CLASS__, 'track_comment_creation'], 10, 2);
        
        // Track comment status changes
        add_action('transition_comment_status', [__CLASS__, 'track_comment_status_change'], 10, 3);
        
        // Track plugin activation/deactivation
        add_action('activated_plugin', [__CLASS__, 'track_plugin_activation']);
        add_action('deactivated_plugin', [__CLASS__, 'track_plugin_deactivation']);
        
        // Track theme switch
        add_action('switch_theme', [__CLASS__, 'track_theme_switch'], 10, 3);
        
        // Track WordPress updates
        add_action('upgrader_process_complete', [__CLASS__, 'track_wordpress_update'], 10, 2);
        
        // Track options changes
        add_action('updated_option', [__CLASS__, 'track_option_update'], 10, 3);
        
        // Track 404 errors (with reduced frequency)
        if (get_option('wpal_track_404_errors', true)) {
            add_action('template_redirect', function() {
                if (is_404() && !self::is_api_request()) {
                    // Only log 404 errors once per URL per day
                    self::track_404_error_with_limit();
                }
            });
        }
        
        // Track WooCommerce orders (if WooCommerce is active)
        if (class_exists('WooCommerce')) {
            add_action('woocommerce_order_status_changed', [__CLASS__, 'track_woocommerce_order_status'], 10, 4);
            add_action('woocommerce_new_order', [__CLASS__, 'track_woocommerce_new_order']);
        }
        
        // Track WP-CLI commands (if running in CLI)
        if (defined('WP_CLI') && WP_CLI) {
            add_action('cli_init', [__CLASS__, 'track_wp_cli_command']);
        }
    }
    
    /**
     * Check if current request is an API request
     */
    private static function is_api_request() {
        // Check for REST API request
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }
        
        // Check for WP-JSON in URL
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
            return true;
        }
        
        // Check for AJAX request
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return true;
        }
        
        // Check for XML-RPC request
        if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Track 404 error with rate limiting
     */
    private static function track_404_error_with_limit() {
        // Get current URL
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        // Create a unique key for this URL
        $url_key = md5($url);
        
        // Get tracked 404s
        $tracked_404s = get_option('wpal_tracked_404s', []);
        
        // Check if this URL was already tracked today
        $today = date('Y-m-d');
        if (isset($tracked_404s[$url_key]) && $tracked_404s[$url_key] === $today) {
            return; // Already tracked today, skip
        }
        
        // Update tracked 404s
        $tracked_404s[$url_key] = $today;
        update_option('wpal_tracked_404s', $tracked_404s);
        
        // Now track the 404 error
        self::track_404_error();
    }
    
    /**
     * Track user login
     */
    public static function track_login($user_login, $user) {
        self::log('User logged in', $user->ID, 'info');
    }
    
    /**
     * Track user logout
     */
    public static function track_logout() {
        $user_id = get_current_user_id();
        
        if ($user_id) {
            self::log('User logged out', $user_id, 'info');
        }
    }
    
    /**
     * Track failed login
     */
    public static function track_login_failed($username) {
        $user = get_user_by('login', $username);
        $user_id = $user ? $user->ID : 0;
        
        self::log('Login failed: ' . $username, $user_id, 'warning', [
            'username' => $username,
        ]);
    }
    
    /**
     * Track password reset
     */
    public static function track_password_reset($user, $new_password) {
        self::log('Password reset', $user->ID, 'info');
    }
    
    /**
     * Track user registration
     */
    public static function track_user_registration($user_id) {
        $user = get_userdata($user_id);
        
        self::log('User registered: ' . $user->user_login, $user_id, 'info', [
            'user_email' => $user->user_email,
        ]);
    }
    
    /**
     * Track user profile update
     */
    public static function track_profile_update($user_id, $old_user_data) {
        $user = get_userdata($user_id);
        
        self::log('User profile updated: ' . $user->user_login, $user_id, 'info', [
            'old_email' => $old_user_data->user_email,
            'new_email' => $user->user_email,
        ]);
    }
    
    /**
     * Track post status change
     */
    public static function track_post_status_change($new_status, $old_status, $post) {
        // Skip auto-drafts and revisions
        if ($post->post_type === 'revision' || $post->post_type === 'auto-draft') {
            return;
        }
        
        $user_id = get_current_user_id();
        
        // New post
        if ($old_status === 'new' || $old_status === 'auto-draft') {
            self::log(
                ucfirst($post->post_type) . ' created: ' . $post->post_title,
                $user_id,
                'info',
                [
                    'post_id' => $post->ID,
                    'post_type' => $post->post_type,
                    'status' => $new_status,
                ]
            );
            return;
        }
        
        // Status change
        if ($new_status !== $old_status) {
            self::log(
                ucfirst($post->post_type) . ' status changed: ' . $post->post_title . ' (' . $old_status . ' → ' . $new_status . ')',
                $user_id,
                'info',
                [
                    'post_id' => $post->ID,
                    'post_type' => $post->post_type,
                    'old_status' => $old_status,
                    'new_status' => $new_status,
                ]
            );
        }
        // Post update
        elseif ($new_status === 'publish' && $old_status === 'publish') {
            self::log(
                ucfirst($post->post_type) . ' updated: ' . $post->post_title,
                $user_id,
                'info',
                [
                    'post_id' => $post->ID,
                    'post_type' => $post->post_type,
                ]
            );
        }
    }
    
    /**
     * Track post deletion
     */
    public static function track_post_deletion($post_id) {
        $post = get_post($post_id);
        
        // Skip auto-drafts and revisions
        if ($post->post_type === 'revision' || $post->post_type === 'auto-draft') {
            return;
        }
        
        $user_id = get_current_user_id();
        
        self::log(
            ucfirst($post->post_type) . ' deleted: ' . $post->post_title,
            $user_id,
            'warning',
            [
                'post_id' => $post->ID,
                'post_type' => $post->post_type,
            ]
        );
    }
    
    /**
     * Track comment creation
     */
    public static function track_comment_creation($comment_id, $comment) {
        $user_id = get_current_user_id();
        
        self::log(
            'Comment added on: ' . get_the_title($comment->comment_post_ID),
            $user_id,
            'info',
            [
                'comment_id' => $comment_id,
                'post_id' => $comment->comment_post_ID,
                'comment_author' => $comment->comment_author,
                'comment_author_email' => $comment->comment_author_email,
            ]
        );
    }
    
    /**
     * Track comment status change
     */
    public static function track_comment_status_change($new_status, $old_status, $comment) {
        $user_id = get_current_user_id();
        
        self::log(
            'Comment status changed: ' . $old_status . ' → ' . $new_status,
            $user_id,
            'info',
            [
                'comment_id' => $comment->comment_ID,
                'post_id' => $comment->comment_post_ID,
                'comment_author' => $comment->comment_author,
                'old_status' => $old_status,
                'new_status' => $new_status,
            ]
        );
    }
    
    /**
     * Track plugin activation
     */
    public static function track_plugin_activation($plugin) {
        $user_id = get_current_user_id();
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        
        self::log(
            'Plugin activated: ' . $plugin_data['Name'],
            $user_id,
            'info',
            [
                'plugin' => $plugin,
                'version' => $plugin_data['Version'],
            ]
        );
    }
    
    /**
     * Track plugin deactivation
     */
    public static function track_plugin_deactivation($plugin) {
        $user_id = get_current_user_id();
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        
        self::log(
            'Plugin deactivated: ' . $plugin_data['Name'],
            $user_id,
            'info',
            [
                'plugin' => $plugin,
                'version' => $plugin_data['Version'],
            ]
        );
    }
    
    /**
     * Track theme switch
     */
    public static function track_theme_switch($new_theme_name, $new_theme, $old_theme) {
        $user_id = get_current_user_id();
        $old_theme_name = $old_theme ? $old_theme->get('Name') : 'Unknown';
        
        self::log(
            'Theme switched: ' . $old_theme_name . ' → ' . $new_theme_name,
            $user_id,
            'info',
            [
                'old_theme' => $old_theme_name,
                'new_theme' => $new_theme_name,
            ]
        );
    }
    
    /**
     * Track WordPress update
     */
    public static function track_wordpress_update($upgrader, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'core') {
            $user_id = get_current_user_id();
            
            self::log(
                'WordPress updated to version ' . get_bloginfo('version'),
                $user_id,
                'info',
                [
                    'version' => get_bloginfo('version'),
                ]
            );
        }
    }
    
    /**
     * Track option update
     */
    public static function track_option_update($option_name, $old_value, $new_value) {
        // Skip some common options that change frequently
        $skip_options = [
            'cron',
            'transient',
            '_transient',
            'wpal_',
            'widget_',
            'theme_mods_',
            'session_tokens',
            'active_plugins',
            'recently_activated',
            'acf_',
            'wp_mail_smtp',
            'woocommerce_',
            'elementor_',
            'wp_user_roles',
            'rewrite_rules',
            'uninstall_plugins',
            'recovery_mode_email_last_sent',
            'auto_core_update_notified',
            'can_compress_scripts',
            'finished_updating_comment_type',
            'recently_edited',
            'wp_attachment_pages_enabled',
            'db_upgraded',
            'auto_update',
            'wp_force_deactivated_plugins',
            'wp_calendar_block_has_published_posts',
            'wp_global_styles',
            'wp_styles',
            'wp_scripts',
            'jetpack',
            'yoast',
            'wp_seo',
            'rank_math',
            'wpforms',
            'formidable',
            'ninja_forms',
            'gravity_forms',
            'wp_mail_smtp',
            'updraftplus',
            'backwpup',
            'wordfence',
            'itsec',
            'sucuri',
            'wp_rocket',
            'autoptimize',
            'w3tc',
            'wpfastestcache',
            'litespeed',
            'breeze',
            'sg_optimizer',
            'wp_super_cache',
            'wp_cache',
            'rediscache',
            'wphb',
            'smush',
            'ewww',
            'imagify',
            'shortpixel',
            'wp_smush',
            'wp_optimize',
            'wp_sweep',
            'wp_mail_logging',
            'wp_mail_bank',
            'wp_mail_catcher',
            'wp_mail_smtp',
            'wp_mail_smtp_pro',
            'wp_mail_smtp_debug',
            'wp_mail_smtp_debug_events',
            'wp_mail_smtp_activated_time',
            'wp_mail_smtp_activation_prevent_redirect',
            'wp_mail_smtp_activation_redirect',
            'wp_mail_smtp_admin_notices',
            'wp_mail_smtp_debug_events_current_id',
            'wp_mail_smtp_migration_version',
            'wp_mail_smtp_review_notice',
            'wp_mail_smtp_version',
            'wp_mail_smtp_debug_events_db_version',
            'wp_mail_smtp_lite_sent_email_counter',
            'wp_mail_smtp_lite_weekly_sent_email_counter',
            'wp_mail_smtp_pro_sent_email_counter',
            'wp_mail_smtp_pro_weekly_sent_email_counter',
            'wp_mail_smtp_notifications',
            'wp_mail_smtp_notifications_data',
            'wp_mail_smtp_notifications_hidden',
            'wp_mail_smtp_notifications_feed',
            'wp_mail_smtp_notifications_feed_data',
            'wp_mail_smtp_notifications_feed_hidden',
            'wp_mail_smtp_notifications_feed_last_update',
            'wp_mail_smtp_notifications_last_update',
            'wp_mail_smtp_debug_events_retention_period',
            'wp_mail_smtp_debug_events_retention_check',
            'wp_mail_smtp_debug_events_retention_last_run',
            'wp_mail_smtp_debug_events_retention_period_days',
            'wp_mail_smtp_debug_events_retention_enabled',
            'wp_mail_smtp_debug_events_retention_status',
            'wp_mail_smtp_debug_events_retention_threshold',
            'wp_mail_smtp_debug_events_retention_threshold_count',
            'wp_mail_smtp_debug_events_retention_threshold_days',
            'wp_mail_smtp_debug_events_retention_threshold_enabled',
            'wp_mail_smtp_debug_events_retention_threshold_status',
            'wp_mail_smtp_debug_events_retention_threshold_unit',
            'wp_mail_smtp_debug_events_retention_unit',
            'wp_mail_smtp_debug_events_retention_value',
            'wp_mail_smtp_debug_events_retention_value_days',
            'wp_mail_smtp_debug_events_retention_value_enabled',
            'wp_mail_smtp_debug_events_retention_value_status',
            'wp_mail_smtp_debug_events_retention_value_unit',
        ];
        
        // Check if option should be skipped
        foreach ($skip_options as $skip) {
            if (strpos($option_name, $skip) === 0) {
                return;
            }
        }
        
        $user_id = get_current_user_id();
        
        // Don't log if values are the same
        if ($old_value === $new_value) {
            return;
        }
        
        // Sanitize values for logging
        $old_value_sanitized = is_array($old_value) ? 'array' : (is_object($old_value) ? 'object' : $old_value);
        $new_value_sanitized = is_array($new_value) ? 'array' : (is_object($new_value) ? 'object' : $new_value);
        
        // Truncate long values
        if (is_string($old_value_sanitized) && strlen($old_value_sanitized) > 50) {
            $old_value_sanitized = substr($old_value_sanitized, 0, 50) . '...';
        }
        
        if (is_string($new_value_sanitized) && strlen($new_value_sanitized) > 50) {
            $new_value_sanitized = substr($new_value_sanitized, 0, 50) . '...';
        }
        
        self::log(
            'Option updated: ' . $option_name,
            $user_id,
            'info',
            [
                'option' => $option_name,
                'old_value' => $old_value_sanitized,
                'new_value' => $new_value_sanitized,
            ]
        );
    }
    
    /**
     * Track 404 error
     */
    public static function track_404_error() {
        $user_id = get_current_user_id();
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        
        self::log(
            '404 Error: Page not found',
            $user_id,
            'warning',
            [
                'url' => $url,
                'referer' => $referer,
            ]
        );
    }
    
    /**
     * Track WooCommerce order status change
     */
    public static function track_woocommerce_order_status($order_id, $old_status, $new_status, $order) {
        $user_id = get_current_user_id();
        
        self::log(
            'WooCommerce order status changed: #' . $order_id . ' (' . $old_status . ' → ' . $new_status . ')',
            $user_id,
            'info',
            [
                'order_id' => $order_id,
                'old_status' => $old_status,
                'new_status' => $new_status,
                'order_total' => $order->get_total(),
                'currency' => $order->get_currency(),
            ]
        );
    }
    
    /**
     * Track WooCommerce new order
     */
    public static function track_woocommerce_new_order($order_id) {
        $user_id = get_current_user_id();
        $order = wc_get_order($order_id);
        
        self::log(
            'WooCommerce new order: #' . $order_id,
            $user_id,
            'info',
            [
                'order_id' => $order_id,
                'order_total' => $order->get_total(),
                'currency' => $order->get_currency(),
                'payment_method' => $order->get_payment_method_title(),
            ]
        );
    }
    
    /**
     * Track WP-CLI command
     */
    public static function track_wp_cli_command() {
        // Get the command being run
        global $argv;
        
        if (empty($argv)) {
            return;
        }
        
        // Remove script name
        array_shift($argv);
        
        // Get the command
        $command = implode(' ', $argv);
        
        self::log(
            'WP-CLI command executed: ' . $command,
            0, // No user ID in CLI
            'info',
            [
                'command' => $command,
            ]
        );
    }
    
    /**
     * Log an event
     */
    public static function log($action, $user_id = 0, $severity = 'info', $context = []) {
        // Skip API requests
        if (self::is_api_request()) {
            return;
        }
        
        // Get user information
        if ($user_id) {
            $user = get_userdata($user_id);
            $username = $user ? $user->user_login : 'Unknown';
            $user_role = $user ? WPAL_Helpers::get_user_role($user_id) : 'Guest';
        } else {
            $username = 'System';
            $user_role = 'System';
        }
        
        // Get IP and browser
        $ip = WPAL_Helpers::get_ip_address();
        $browser = WPAL_Helpers::get_browser_name();
        
        // Prepare log data
        $log_data = [
            'time' => current_time('mysql'),
            'user_id' => $user_id,
            'username' => $username,
            'user_role' => $user_role,
            'action' => $action,
            'ip' => $ip,
            'browser' => $browser,
            'severity' => $severity,
            'context' => json_encode($context),
        ];
        
        // Write log
        return WPAL_Helpers::write_log($log_data);
    }
}