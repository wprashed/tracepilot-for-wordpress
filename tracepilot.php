<?php
/**
 * Plugin Name: TracePilot
 * Description: Activity logging, diagnostics, threat review, and export tooling for WordPress administrators.
 * Version: 1.0.0
 * Author: Rashed Hossain
 * Author URI: https://rashed.im/
 * Text Domain: tracepilot
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TracePilot_VERSION', '1.3.4');
define('TracePilot_PLUGIN_FILE', __FILE__);
define('TracePilot_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TracePilot_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TracePilot_PLUGIN_BASENAME', plugin_basename(__FILE__));

if (!defined('WPAL_VERSION')) {
    define('WPAL_VERSION', TracePilot_VERSION);
}
if (!defined('WPAL_PLUGIN_FILE')) {
    define('WPAL_PLUGIN_FILE', TracePilot_PLUGIN_FILE);
}
if (!defined('WPAL_PLUGIN_DIR')) {
    define('WPAL_PLUGIN_DIR', TracePilot_PLUGIN_DIR);
}
if (!defined('WPAL_PLUGIN_URL')) {
    define('WPAL_PLUGIN_URL', TracePilot_PLUGIN_URL);
}
if (!defined('WPAL_PLUGIN_BASENAME')) {
    define('WPAL_PLUGIN_BASENAME', TracePilot_PLUGIN_BASENAME);
}

require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-helpers.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-dashboard.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-api.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-export.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-notifications.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-tracker.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-visual-analytics.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-threat-detection.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-server-recommendations.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-google-search-console.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-diagnostics.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-geolocation.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-settings.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-archive.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-response-actions.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-file-integrity.php';
require_once TracePilot_PLUGIN_DIR . 'includes/class-tracepilot-vulnerability-scanner.php';

class TracePilot {
    /**
     * Singleton instance.
     *
     * @var TracePilot|null
     */
    private static $instance = null;

    /**
     * Module instances.
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
    public $archive;
    public $response_actions;
    public $file_integrity;
    public $vulnerability_scanner;

    /**
     * Get singleton.
     *
     * @return TracePilot
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->helpers = new TracePilot_Helpers();
        $this->dashboard = new TracePilot_Dashboard();
        $this->api = new TracePilot_API();
        $this->export = new TracePilot_Export();
        $this->notifications = new TracePilot_Notifications();
        $this->geolocation = new TracePilot_Geolocation();
        $this->settings = new TracePilot_Settings();
        $this->tracker = new TracePilot_Tracker();
        $this->visual_analytics = new TracePilot_Visual_Analytics();
        $this->threat_detection = new TracePilot_Threat_Detection();
        $this->server_recommendations = new TracePilot_Server_Recommendations();
        $this->google_search_console = new TracePilot_Google_Search_Console();
        $this->diagnostics = new TracePilot_Diagnostics();
        $this->archive = new TracePilot_Archive();
        $this->response_actions = new TracePilot_Response_Actions();
        $this->file_integrity = new TracePilot_File_Integrity();
        $this->vulnerability_scanner = new TracePilot_Vulnerability_Scanner();

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_init', array($this, 'redirect_legacy_admin_pages'), 1);
        add_action('admin_init', array($this, 'maybe_upgrade_schema'));
        add_filter('admin_body_class', array($this, 'add_admin_body_class'));
        add_filter('cron_schedules', array($this, 'register_cron_schedules'));
        add_action('wp', array($this, 'schedule_cron_jobs'));
        add_action('tracepilot_daily_cron', array($this, 'run_daily_tasks'));
        add_action('tracepilot_weekly_cron', array($this, 'run_weekly_tasks'));
        add_action('wpal_daily_cron', array($this, 'run_legacy_daily_tasks'));
        add_action('wpal_weekly_cron', array($this, 'run_legacy_weekly_tasks'));
    }

    /**
     * Activation.
     */
    public function activate() {
        TracePilot_Helpers::create_tables();
        TracePilot_Archive::create_table();
        $this->create_threats_table();

        $defaults = TracePilot_Helpers::get_default_settings();
        if (!get_option('wpal_options')) {
            add_option('wpal_options', $defaults);
        }
        if (!get_option('wpal_settings')) {
            add_option('wpal_settings', $defaults);
        }

        if (!wp_next_scheduled('tracepilot_daily_cron')) {
            wp_schedule_event(time(), 'daily', 'tracepilot_daily_cron');
        }
        if (!wp_next_scheduled('tracepilot_weekly_cron')) {
            wp_schedule_event(time(), 'weekly', 'tracepilot_weekly_cron');
        }
    }

    /**
     * Deactivation.
     */
    public function deactivate() {
        wp_clear_scheduled_hook('tracepilot_daily_cron');
        wp_clear_scheduled_hook('tracepilot_weekly_cron');
        wp_clear_scheduled_hook('wpal_daily_cron');
        wp_clear_scheduled_hook('wpal_weekly_cron');
    }

    /**
     * Register custom cron intervals.
     *
     * @param array $schedules Schedules.
     * @return array
     */
    public function register_cron_schedules($schedules) {
        if (!isset($schedules['weekly'])) {
            $schedules['weekly'] = array(
                'interval' => WEEK_IN_SECONDS,
                'display' => __('Once Weekly', 'tracepilot'),
            );
        }

        return $schedules;
    }

    /**
     * Ensure schema upgrades are applied.
     */
    public function maybe_upgrade_schema() {
        TracePilot_Helpers::create_tables();
        TracePilot_Archive::create_table();
        $this->create_threats_table();
    }

    /**
     * Redirect legacy WPAL page slugs to TracePilot slugs.
     */
    public function redirect_legacy_admin_pages() {
        if (!is_admin() || !isset($_GET['page'])) {
            return;
        }

        $legacy_page = sanitize_key(wp_unslash($_GET['page']));
        $map = array(
            'wp-activity-logger-pro' => 'tracepilot',
            'wp-activity-logger-pro-logs' => 'tracepilot-logs',
            'wp-activity-logger-pro-analytics' => 'tracepilot-analytics',
            'wp-activity-logger-pro-threat-detection' => 'tracepilot-threat-detection',
            'wp-activity-logger-pro-server' => 'tracepilot-server',
            'wp-activity-logger-pro-diagnostics' => 'tracepilot-diagnostics',
            'wp-activity-logger-pro-search-console' => 'tracepilot-search-console',
            'wp-activity-logger-pro-archive' => 'tracepilot-archive',
            'wp-activity-logger-pro-export' => 'tracepilot-export',
            'wp-activity-logger-pro-settings' => 'tracepilot-settings',
        );

        if (!isset($map[$legacy_page])) {
            return;
        }

        $query_args = array();
        foreach ((array) $_GET as $key => $value) {
            $sanitized_key = sanitize_key((string) $key);
            if ('page' === $sanitized_key) {
                continue;
            }

            if (is_array($value)) {
                $query_args[$sanitized_key] = array_map(
                    static function ($item) {
                        return sanitize_text_field(wp_unslash((string) $item));
                    },
                    $value
                );
            } else {
                $query_args[$sanitized_key] = sanitize_text_field(wp_unslash((string) $value));
            }
        }

        $query_args['page'] = $map[$legacy_page];
        $target = add_query_arg(
            $query_args,
            is_network_admin() ? network_admin_url('admin.php') : admin_url('admin.php')
        );

        wp_safe_redirect($target);
        exit;
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook Current admin hook.
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'tracepilot') === false) {
            return;
        }

        $chart_path = TracePilot_PLUGIN_DIR . 'assets/vendor/chartjs/chart.umd.min.js';

        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('tracepilot-admin', TracePilot_PLUGIN_URL . 'assets/css/tracepilot-admin.css', array(), TracePilot_VERSION);
        wp_enqueue_script('jquery-ui-datepicker');
        if (file_exists($chart_path)) {
            wp_enqueue_script(
                'tracepilot-chartjs',
                TracePilot_PLUGIN_URL . 'assets/vendor/chartjs/chart.umd.min.js',
                array(),
                TracePilot_VERSION,
                true
            );
        }

        wp_enqueue_script(
            'tracepilot-admin',
            TracePilot_PLUGIN_URL . 'assets/js/tracepilot-admin.js',
            array('jquery', 'jquery-ui-datepicker'),
            TracePilot_VERSION,
            true
        );
        wp_enqueue_script(
            'tracepilot-pages',
            TracePilot_PLUGIN_URL . 'assets/js/tracepilot-pages.js',
            array('jquery', 'tracepilot-admin'),
            TracePilot_VERSION,
            true
        );

        wp_localize_script(
            'tracepilot-admin',
            'tracepilot_admin_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tracepilot_nonce'),
                'current_page' => isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '',
                'confirm_delete' => __('Are you sure you want to delete this log entry?', 'tracepilot'),
                'confirm_delete_all' => __('Are you sure you want to delete all log entries? This action cannot be undone.', 'tracepilot'),
                'confirm_reset_settings' => __('Reset all settings to defaults?', 'tracepilot'),
                'confirm_delete_user_logs' => __('Delete all logs for this user?', 'tracepilot'),
                'enter_user_id' => __('Enter a user ID first.', 'tracepilot'),
                'running_scan' => __('Running scan...', 'tracepilot'),
                'scan_failed' => __('Unable to run the diagnostics scan.', 'tracepilot'),
                'export_url' => admin_url('admin-ajax.php'),
                'i18n' => array(
                    'saving' => __('Saving...', 'tracepilot'),
                    'analyzing' => __('Analyzing...', 'tracepilot'),
                    'unable_save_settings' => __('Unable to save settings.', 'tracepilot'),
                    'unable_analyze_threats' => __('Unable to analyze threats.', 'tracepilot'),
                    'scan_software' => __('Scan Software', 'tracepilot'),
                    'scanning' => __('Scanning...', 'tracepilot'),
                    'checking_software' => __('Checking installed software against vulnerability intelligence providers...', 'tracepilot'),
                    'unable_scan_software' => __('Unable to scan software.', 'tracepilot'),
                    'no_action_needed' => __('No action needed right now.', 'tracepilot'),
                    'update_to' => __('Update to', 'tracepilot'),
                    'or_newer' => __('or newer.', 'tracepilot'),
                    'review_integrity' => __('Review recent file changes against the integrity baseline.', 'tracepilot'),
                    'review_advisory' => __('Review the linked advisory and update or replace this component.', 'tracepilot'),
                    'local_file_changes' => __('local file changes', 'tracepilot'),
                    'no_components_in_scope' => __('No installed components were found in the current scan scope.', 'tracepilot'),
                    'run_scan_first' => __('Run a scan to generate a software security report.', 'tracepilot'),
                    'severity' => __('Severity', 'tracepilot'),
                    'type' => __('Type', 'tracepilot'),
                    'description' => __('Description', 'tracepilot'),
                    'ip' => __('IP', 'tracepilot'),
                    'time' => __('Time', 'tracepilot'),
                    'actions' => __('Actions', 'tracepilot'),
                    'force_logout' => __('Force Logout', 'tracepilot'),
                    'block_ip' => __('Block IP', 'tracepilot'),
                    'analyze_threats' => __('Analyze Threats', 'tracepilot'),
                    'analyzing_logs' => __('Analyzing activity logs for potential threats...', 'tracepilot'),
                    'analyze_server_needs' => __('Analyze Server Needs', 'tracepilot'),
                    'unable_analyze_server' => __('Unable to analyze server needs.', 'tracepilot'),
                    'baseline_created_with' => __('Baseline created with %d files.', 'tracepilot'),
                ),
            )
        );
    }

    /**
     * Add body class on plugin screens.
     *
     * @param string $classes Existing classes.
     * @return string
     */
    public function add_admin_body_class($classes) {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || false === strpos((string) $screen->id, 'tracepilot')) {
            return $classes;
        }

        return trim($classes . ' tracepilot-admin-screen');
    }

    /**
     * Create threats table.
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

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Schedule cron jobs.
     */
    public function schedule_cron_jobs() {
        if (!wp_next_scheduled('tracepilot_daily_cron')) {
            wp_schedule_event(time(), 'daily', 'tracepilot_daily_cron');
        }
        if (!wp_next_scheduled('tracepilot_weekly_cron')) {
            wp_schedule_event(time(), 'weekly', 'tracepilot_weekly_cron');
        }
    }

    /**
     * Run daily maintenance.
     */
    public function run_daily_tasks() {
        $this->cleanup_old_logs();

        if (method_exists($this->threat_detection, 'scheduled_threat_analysis')) {
            $this->threat_detection->scheduled_threat_analysis();
        }
    }

    /**
     * Run weekly tasks.
     */
    public function run_weekly_tasks() {
        /**
         * Fires when TracePilot weekly maintenance runs.
         *
         * @since 1.3.1
         */
        do_action('tracepilot_weekly_tasks');
    }

    /**
     * Run legacy daily cron hook through TracePilot namespace.
     */
    public function run_legacy_daily_tasks() {
        do_action('tracepilot_daily_cron');
    }

    /**
     * Run legacy weekly cron hook through TracePilot namespace.
     */
    public function run_legacy_weekly_tasks() {
        do_action('tracepilot_weekly_cron');
    }

    /**
     * Clean up expired logs.
     */
    private function cleanup_old_logs() {
        $settings = TracePilot_Helpers::get_settings();

        global $wpdb;
        TracePilot_Helpers::init();
        $rules = array(
            'info' => isset($settings['retention_info_days']) ? absint($settings['retention_info_days']) : 30,
            'warning' => isset($settings['retention_warning_days']) ? absint($settings['retention_warning_days']) : 60,
            'error' => isset($settings['retention_error_days']) ? absint($settings['retention_error_days']) : 90,
            'critical' => isset($settings['retention_error_days']) ? absint($settings['retention_error_days']) : 90,
        );

        foreach ($rules as $severity => $days) {
            if ($days > 0) {
                $wpdb->query(
                    $wpdb->prepare(
                        'DELETE FROM ' . TracePilot_Helpers::$db_table . ' WHERE severity = %s AND time < DATE_SUB(NOW(), INTERVAL %d DAY)',
                        $severity,
                        $days
                    )
                );
            }
        }

        if (!empty($settings['retention_action_rules'])) {
            $lines = preg_split('/\r\n|\r|\n/', (string) $settings['retention_action_rules']);
            foreach ($lines as $line) {
                $line = trim($line);
                if ('' === $line || false === strpos($line, '=')) {
                    continue;
                }
                list($action, $days) = array_map('trim', explode('=', $line, 2));
                $days = absint($days);
                if ($action && $days > 0) {
                    $wpdb->query(
                        $wpdb->prepare(
                            'DELETE FROM ' . TracePilot_Helpers::$db_table . ' WHERE action = %s AND time < DATE_SUB(NOW(), INTERVAL %d DAY)',
                            $action,
                            $days
                        )
                    );
                }
            }
        }
    }
}

function tracepilot() {
    return TracePilot::get_instance();
}

if (!function_exists('tracepilot_for_wordpress')) {
    function tracepilot_for_wordpress() {
        return tracepilot();
    }
}

if (!function_exists('wp_activity_logger_pro')) {
    function wp_activity_logger_pro() {
        return tracepilot();
    }
}

if (!class_exists('WP_Activity_Logger_Pro')) {
    class_alias('TracePilot', 'WP_Activity_Logger_Pro');
}

tracepilot();
