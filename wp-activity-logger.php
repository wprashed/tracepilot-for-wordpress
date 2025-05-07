<?php
/**
 * Plugin Name: WP Activity Logger Pro
 * Plugin URI: https://example.com/wp-activity-logger-pro
 * Description: A comprehensive activity logging solution for WordPress
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: wp-activity-logger-pro
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPAL_VERSION', '1.0.0');
define('WPAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPAL_PLUGIN_FILE', __FILE__);
define('WPAL_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-helpers.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-dashboard.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-api.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-notifications.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-export.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-tracker.php';

/**
 * Main plugin class
 */
class WP_Activity_Logger_Pro {
    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Dashboard instance
     */
    public $dashboard;

    /**
     * API instance
     */
    public $api;

    /**
     * Notifications instance
     */
    public $notifications;

    /**
     * Export instance
     */
    public $export;

    /**
     * Tracker instance
     */
    public $tracker;

    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize components
        $this->dashboard = new WPAL_Dashboard();
        $this->api = new WPAL_API();
        $this->notifications = new WPAL_Notifications();
        $this->export = new WPAL_Export();
        $this->tracker = new WPAL_Tracker();

        // Register activation and deactivation hooks
        register_activation_hook(WPAL_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(WPAL_PLUGIN_FILE, array($this, 'deactivate'));

        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));

        // Register AJAX handlers
        add_action('wp_ajax_wpal_get_activity_chart', array($this->api, 'get_activity_chart'));
        add_action('wp_ajax_wpal_get_top_users', array($this->api, 'get_top_users'));
        add_action('wp_ajax_wpal_get_severity_breakdown', array($this->api, 'get_severity_breakdown'));
        add_action('wp_ajax_wpal_get_recent_logs', array($this->api, 'get_recent_logs'));
        add_action('wp_ajax_wpal_get_log_details', array($this->api, 'get_log_details'));
        add_action('wp_ajax_wpal_delete_log', array($this->api, 'delete_log'));
        add_action('wp_ajax_wpal_delete_all_logs', array($this->api, 'delete_all_logs'));
        add_action('wp_ajax_wpal_export_logs', array($this->export, 'export_logs'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('wp-activity-logger-pro', false, dirname(WPAL_PLUGIN_BASENAME) . '/languages');

        // Add admin menu
        add_action('admin_menu', array($this->dashboard, 'add_admin_menu'));

        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this->dashboard, 'enqueue_admin_scripts'));

        // Initialize tracker
        $this->tracker->init();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        WPAL_Helpers::create_tables();

        // Add default options
        $this->add_default_options();

        // Log activation
        WPAL_Helpers::log_activity(
            'plugin_activated',
            __('WP Activity Logger Pro plugin activated', 'wp-activity-logger-pro'),
            'info'
        );
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Log deactivation
        WPAL_Helpers::log_activity(
            'plugin_deactivated',
            __('WP Activity Logger Pro plugin deactivated', 'wp-activity-logger-pro'),
            'info'
        );
    }

    /**
     * Add default options
     */
    private function add_default_options() {
        $default_options = array(
            'retention_period' => 30, // Days to keep logs
            'log_severity' => array('info', 'warning', 'error'),
            'notification_email' => get_option('admin_email'),
            'notification_events' => array('error'),
            'export_format' => 'csv'
        );

        update_option('wpal_settings', $default_options);
    }
}

// Initialize the plugin
function wpal_init() {
    return WP_Activity_Logger_Pro::get_instance();
}

// Start the plugin
wpal_init();