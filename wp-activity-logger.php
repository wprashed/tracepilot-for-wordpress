<?php
/**
 * Plugin Name: WP Activity Logger Pro
 * Plugin URI: https://example.com/wp-activity-logger-pro
 * Description: Advanced activity logging for WordPress with real-time notifications, detailed reports, and more.
 * Version: 1.1.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: wp-activity-logger-pro
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPAL_VERSION', '1.1.0');
define('WPAL_PLUGIN_FILE', __FILE__);
define('WPAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPAL_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-helpers.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-dashboard.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-api.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-export.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-notifications.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-tracker.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-visual-analytics.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-threat-detection.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-server-recommendations.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-google-search-console.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-diagnostics.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-geolocation.php';
require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-settings.php';

// Initialize the plugin
class WP_Activity_Logger_Pro {
    /**
     * Instance of this class
     */
    private static $instance;

    /**
     * Plugin classes
     */
    public $helpers;
    public $dashboard;
    public $api;
    public $export;
    public $notifications;
    public $tracker;
    public $visual_analytics;
    public $threat_detection;
    public $server_recommendations;
    public $google_search_console;
    public $diagnostics;
    public $geolocation;
    public $settings;

    /**
     * Get the singleton instance
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
    private function __construct() {
        // Initialize classes
        $this->helpers = new WPAL_Helpers();
        $this->dashboard = new WPAL_Dashboard();
        $this->api = new WPAL_API();
        $this->export = new WPAL_Export();
        $this->notifications = new WPAL_Notifications();
        $this->geolocation = new WPAL_Geolocation();
        $this->settings = new WPAL_Settings();
        $this->tracker = new WPAL_Tracker();
        $this->visual_analytics = new WPAL_Visual_Analytics();
        $this->threat_detection = new WPAL_Threat_Detection();
        $this->server_recommendations = new WPAL_Server_Recommendations();
        $this->google_search_console = new WPAL_Google_Search_Console();
        $this->diagnostics = new WPAL_Diagnostics();

        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Schedule cron jobs
        add_action('wp', array($this, 'schedule_cron_jobs'));
        
        // Register cron actions
        add_action('wpal_daily_cron', array($this, 'run_daily_tasks'));

        // Add this line to check if the dashboard is properly initialized
        add_action('admin_init', array($this, 'check_dashboard_initialization'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        WPAL_Helpers::create_tables();
        
        // Create threats table
        $this->create_threats_table();
        
        // Create archive table
        $this->create_archive_table();

        // Add default options
        $default_options = array(
            'log_user_actions' => 1,
            'log_system_actions' => 1,
            'log_retention' => 30,
            'enable_notifications' => 0,
            'notification_email' => get_option('admin_email'),
            'notification_events' => array('login_failed', 'plugin_activated', 'plugin_deactivated', 'theme_switched'),
            'enable_threat_detection' => 1,
            'enable_threat_notifications' => 1,
            'monitor_failed_logins' => 1,
            'monitor_unusual_logins' => 1,
            'monitor_file_changes' => 1,
            'monitor_privilege_escalation' => 1,
            'enable_geolocation' => 1,
        );

        add_option('wpal_options', $default_options);

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
        
        // Clear scheduled hooks
        wp_clear_scheduled_hook('wpal_daily_cron');
    }

    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('wp-activity-logger-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wp-activity-logger-pro') === false) {
            return;
        }

        // Enqueue styles
        wp_enqueue_style('wpal-admin', WPAL_PLUGIN_URL . 'assets/css/wpal-admin.css', array(), WPAL_VERSION);
        wp_enqueue_style('wpal-menu', WPAL_PLUGIN_URL . 'assets/css/wpal-menu.css', array(), WPAL_VERSION);
        
        // Enqueue DataTables
        wp_enqueue_style('wpal-datatables', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css', array(), '1.11.5');
        wp_enqueue_script('wpal-datatables', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), '1.11.5', true);
        
        // Enqueue Chart.js
        wp_enqueue_script('wpal-chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js', array(), '3.7.1', true);
        
        // Enqueue admin script
        wp_enqueue_script('wpal-admin', WPAL_PLUGIN_URL . 'assets/js/wpal-admin.js', array('jquery', 'wpal-datatables', 'wpal-chartjs'), WPAL_VERSION, true);
        
        // Localize script
        wp_localize_script('wpal-admin', 'wpal_admin_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpal_nonce'),
            'confirm_delete' => __('Are you sure you want to delete this log entry?', 'wp-activity-logger-pro'),
            'confirm_delete_all' => __('Are you sure you want to delete ALL log entries? This action cannot be undone.', 'wp-activity-logger-pro'),
        ));
    }
    
    /**
     * Create threats table
     */
    private function create_threats_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpal_threats';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            time datetime NOT NULL,
            type varchar(50) NOT NULL,
            severity varchar(20) NOT NULL,
            description text NOT NULL,
            context longtext,
            status varchar(20) NOT NULL DEFAULT 'new',
            PRIMARY KEY  (id),
            KEY time (time),
            KEY type (type),
            KEY severity (severity),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create archive table
     */
    private function create_archive_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpal_archive';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            time datetime NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            username varchar(60) DEFAULT NULL,
            user_role varchar(60) DEFAULT NULL,
            action varchar(255) NOT NULL,
            description text NOT NULL,
            severity varchar(20) NOT NULL DEFAULT 'info',
            ip varchar(45) DEFAULT NULL,
            location varchar(255) DEFAULT NULL,
            country varchar(100) DEFAULT NULL,
            country_code varchar(10) DEFAULT NULL,
            object_type varchar(50) DEFAULT NULL,
            object_id bigint(20) unsigned DEFAULT NULL,
            object_name varchar(255) DEFAULT NULL,
            context longtext DEFAULT NULL,
            archived_at datetime NOT NULL,
            archived_by bigint(20) unsigned DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY time (time),
            KEY user_id (user_id),
            KEY action (action),
            KEY severity (severity),
            KEY ip (ip),
            KEY country_code (country_code),
            KEY object_type (object_type),
            KEY object_id (object_id),
            KEY archived_at (archived_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Schedule cron jobs
     */
    public function schedule_cron_jobs() {
        if (!wp_next_scheduled('wpal_daily_cron')) {
            wp_schedule_event(time(), 'daily', 'wpal_daily_cron');
        }
    }
    
    /**
     * Run daily tasks
     */
    public function run_daily_tasks() {
        // Clean up old logs
        $this->cleanup_old_logs();
        
        // Run threat analysis
        $this->threat_detection->scheduled_threat_analysis();
    }
    
    /**
     * Clean up old logs
     */
    private function cleanup_old_logs() {
        $options = get_option('wpal_options', array());
        $retention_days = isset($options['log_retention']) ? intval($options['log_retention']) : 30;
        
        if ($retention_days > 0) {
            global $wpdb;
            WPAL_Helpers::init();
            $table_name = WPAL_Helpers::$db_table;
            
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_name WHERE time < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $retention_days
            ));
        }
    }

    /**
     * Check if dashboard is properly initialized
     */
    public function check_dashboard_initialization() {
        if (!has_action('admin_menu', array($this->dashboard, 'add_admin_menu'))) {
            // Re-add the admin menu if it wasn't properly hooked
            add_action('admin_menu', array($this->dashboard, 'add_admin_menu'));
        }
    }

    /**
     * Force menu registration
     */
    public function force_menu_registration() {
        // Remove existing menu hooks to prevent duplicates
        remove_action('admin_menu', array($this->dashboard, 'add_admin_menu'));
        
        // Re-add the menu hook
        add_action('admin_menu', array($this->dashboard, 'add_admin_menu'), 10);
        
        // Force menu to be added immediately if we're on an admin page
        if (is_admin()) {
            $this->dashboard->add_admin_menu();
        }
        
        return true;
    }
}

// Initialize the plugin
function wp_activity_logger_pro() {
    return WP_Activity_Logger_Pro::get_instance();
}

// Start the plugin
wp_activity_logger_pro();