<?php
/**
 * WP Activity Logger Helpers
 *
 * @package WP Activity Logger
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}

class WPAL_Helpers {
    /**
     * Database table name
     */
    public static $db_table;

    /**
     * Initialize
     */
    public static function init() {
        global $wpdb;
        self::$db_table = $wpdb->prefix . 'wpal_logs';
    }

    /**
     * Create tables
     */
    public static function create_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpal_logs';
        
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
            PRIMARY KEY  (id),
            KEY time (time),
            KEY user_id (user_id),
            KEY action (action),
            KEY severity (severity),
            KEY ip (ip),
            KEY country_code (country_code),
            KEY object_type (object_type),
            KEY object_id (object_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Log activity
     */
    public static function log_activity($action, $description, $severity = 'info', $args = array()) {
        global $wpdb;
        self::init();
        
        // Get current user if not provided
        if (!isset($args['user_id'])) {
            $user = wp_get_current_user();
            $args['user_id'] = $user->ID;
            $args['username'] = $user->ID ? $user->user_login : 'Guest';
            
            // Get user role
            if ($user->ID) {
                $user_roles = $user->roles;
                $args['user_role'] = !empty($user_roles) ? $user_roles[0] : '';
            } else {
                $args['user_role'] = 'guest';
            }
        }
        
        // Get IP if not provided
        if (!isset($args['ip'])) {
            $args['ip'] = self::get_ip_address();
        }
        
        // Prepare data
        $data = array(
            'time' => current_time('mysql'),
            'user_id' => isset($args['user_id']) ? $args['user_id'] : null,
            'username' => isset($args['username']) ? $args['username'] : null,
            'user_role' => isset($args['user_role']) ? $args['user_role'] : null,
            'action' => $action,
            'description' => $description,
            'severity' => $severity,
            'ip' => isset($args['ip']) ? $args['ip'] : null,
            'location' => isset($args['location']) ? $args['location'] : null,
            'country' => isset($args['country']) ? $args['country'] : null,
            'country_code' => isset($args['country_code']) ? $args['country_code'] : null,
            'object_type' => isset($args['object_type']) ? $args['object_type'] : null,
            'object_id' => isset($args['object_id']) ? $args['object_id'] : null,
            'object_name' => isset($args['object_name']) ? $args['object_name'] : null,
            'context' => isset($args['context']) ? (is_array($args['context']) || is_object($args['context']) ? json_encode($args['context']) : $args['context']) : null
        );
        
        // Insert log
        $wpdb->insert(self::$db_table, $data);
        
        // Get log ID
        $log_id = $wpdb->insert_id;
        
        // Trigger action
        do_action('wpal_after_log_activity', $log_id, $action, $description, $severity, $args);
        
        return $log_id;
    }

    /**
     * Format datetime
     */
    public static function format_datetime($datetime, $format = '') {
        if (empty($format)) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }
        
        $timestamp = strtotime($datetime);
        
        return date_i18n($format, $timestamp);
    }

    /**
     * Get IP address
     */
    public static function get_ip_address() {
        // Check for CloudFlare IP
        $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : '';
        
        // Check for proxy headers
        if (empty($ip) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            
            // Check if multiple IPs are set and get the first one
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
        }
        
        // Check for remote address
        if (empty($ip) && isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Validate IP
        $ip = filter_var($ip, FILTER_VALIDATE_IP);
        
        return $ip ?: '';
    }
}
