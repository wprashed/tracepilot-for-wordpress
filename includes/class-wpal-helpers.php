<?php
class WPAL_Helpers {
    public static $csv_file;
    public static $log_dir;
    public static $db_table;

    public static function init() {
        self::$log_dir = WPAL_PATH . 'logs/';
        self::$csv_file = self::$log_dir . 'activity.csv';
        self::$db_table = $GLOBALS['wpdb']->prefix . 'wpal_logs';
    }

    public static function plugin_activation() {
        self::init();
        
        // Create logs directory if it doesn't exist
        if (!file_exists(self::$log_dir)) {
            mkdir(self::$log_dir, 0755, true);
        }
        
        // Create CSV file if it doesn't exist
        if (!file_exists(self::$csv_file)) {
            file_put_contents(self::$csv_file, "Time,User,Action,IP,UserRole,Browser,Severity\n");
        }
        
        // Create database table
        self::create_db_table();
        
        // Schedule cron jobs
        if (!wp_next_scheduled('wpal_daily_maintenance')) {
            wp_schedule_event(time(), 'daily', 'wpal_daily_maintenance');
        }
        
        if (!wp_next_scheduled('wpal_send_daily_report')) {
            wp_schedule_event(time(), 'daily', 'wpal_send_daily_report');
        }
        
        // Set default options
        $default_options = [
            'retention_days' => 30,
            'log_storage' => 'both', // 'csv', 'db', or 'both'
            'notification_email' => get_option('admin_email'),
            'notification_events' => ['login_failed', 'plugin_activated', 'plugin_deactivated'],
            'daily_report' => true,
            'webhook_url' => '',
            'severity_colors' => [
                'info' => '#28a745',
                'warning' => '#ffc107',
                'error' => '#dc3545',
            ],
        ];
        
        foreach ($default_options as $key => $value) {
            if (get_option('wpal_' . $key) === false) {
                update_option('wpal_' . $key, $value);
            }
        }
    }
    
    public static function plugin_deactivation() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('wpal_daily_maintenance');
        wp_clear_scheduled_hook('wpal_send_daily_report');
    }
    
    public static function create_db_table() {
        global $wpdb;
        $table_name = self::$db_table;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            time datetime NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            username varchar(60) DEFAULT NULL,
            user_role varchar(255) DEFAULT NULL,
            action text NOT NULL,
            ip varchar(45) DEFAULT NULL,
            browser varchar(255) DEFAULT NULL,
            severity varchar(20) DEFAULT 'info',
            context text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY time (time),
            KEY severity (severity)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function write_log($entry) {
        self::init();
        
        // Add timestamp if not provided
        if (!isset($entry['time'])) {
            $entry['time'] = current_time('mysql');
        }
        
        // Add context as JSON if it's an array
        if (isset($entry['context']) && is_array($entry['context'])) {
            $entry['context'] = json_encode($entry['context']);
        }
        
        // Default severity
        if (!isset($entry['severity'])) {
            $entry['severity'] = 'info';
        }
        
        // Get storage method
        $storage = get_option('wpal_log_storage', 'both');
        
        // Write to CSV if needed
        if ($storage === 'csv' || $storage === 'both') {
            $csv_entry = [
                $entry['time'],
                $entry['username'],
                $entry['action'],
                $entry['ip'],
                $entry['user_role'],
                $entry['browser'],
                $entry['severity'],
            ];
            
            $line = implode(',', array_map('self::esc_csv', $csv_entry)) . "\n";
            file_put_contents(self::$csv_file, $line, FILE_APPEND);
        }
        
        // Write to database if needed
        if ($storage === 'db' || $storage === 'both') {
            global $wpdb;
            $wpdb->insert(
                self::$db_table,
                $entry,
                [
                    'time' => '%s',
                    'user_id' => '%d',
                    'username' => '%s',
                    'user_role' => '%s',
                    'action' => '%s',
                    'ip' => '%s',
                    'browser' => '%s',
                    'severity' => '%s',
                    'context' => '%s',
                ]
            );
        }
        
        // Trigger action for webhooks, push notifications, etc.
        do_action('wpal_new_log', $entry);
    }
    
    public static function esc_csv($value) {
        return '"' . str_replace('"', '""', $value) . '"';
    }

    public static function get_csv_data() {
        self::init();
        
        if (!file_exists(self::$csv_file)) {
            return [];
        }
        
        $data = array_map('str_getcsv', file(self::$csv_file));
        $headers = array_shift($data);
        $formatted = [];
        
        foreach ($data as $row) {
            if (count($row) === count($headers)) {
                $formatted[] = array_combine($headers, $row);
            }
        }
        
        return $formatted;
    }
    
    public static function get_db_logs($limit = 100, $offset = 0, $where = '') {
        global $wpdb;
        $table = self::$db_table;
        
        $sql = "SELECT * FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        $sql .= " ORDER BY time DESC LIMIT %d OFFSET %d";
        
        $query = $wpdb->prepare($sql, $limit, $offset);
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    public static function get_filtered_logs($params) {
        global $wpdb;
        $table = self::$db_table;
        $storage = get_option('wpal_log_storage', 'both');
        
        // Use database if available
        if ($storage === 'db' || $storage === 'both') {
            $where = [];
            $where_args = [];
            
            if (!empty($params['from'])) {
                $where[] = 'time >= %s';
                $where_args[] = $params['from'];
            }
            
            if (!empty($params['to'])) {
                $where[] = 'time <= %s';
                $where_args[] = $params['to'];
            }
            
            if (!empty($params['user'])) {
                $where[] = 'username = %s';
                $where_args[] = $params['user'];
            }
            
            if (!empty($params['action_type'])) {
                $where[] = 'action LIKE %s';
                $where_args[] = '%' . $wpdb->esc_like($params['action_type']) . '%';
            }
            
            if (!empty($params['severity'])) {
                $where[] = 'severity = %s';
                $where_args[] = $params['severity'];
            }
            
            $limit = isset($params['limit']) ? intval($params['limit']) : 100;
            $offset = isset($params['offset']) ? intval($params['offset']) : 0;
            
            $sql = "SELECT * FROM $table";
            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            $sql .= " ORDER BY time DESC LIMIT %d OFFSET %d";
            
            $query = $wpdb->prepare($sql, array_merge($where_args, [$limit, $offset]));
            return $wpdb->get_results($query, ARRAY_A);
        } else {
            // Fallback to CSV
            $logs = self::get_csv_data();
            
            // Apply filters
            if (!empty($params['from'])) {
                $from_date = new DateTime($params['from']);
                $logs = array_filter($logs, function($log) use ($from_date) {
                    $log_date = new DateTime($log['Time']);
                    return $log_date >= $from_date;
                });
            }
            
            if (!empty($params['to'])) {
                $to_date = new DateTime($params['to']);
                $logs = array_filter($logs, function($log) use ($to_date) {
                    $log_date = new DateTime($log['Time']);
                    return $log_date <= $to_date;
                });
            }
            
            if (!empty($params['user'])) {
                $logs = array_filter($logs, function($log) use ($params) {
                    return $log['User'] === $params['user'];
                });
            }
            
            if (!empty($params['action_type'])) {
                $logs = array_filter($logs, function($log) use ($params) {
                    return strpos($log['Action'], $params['action_type']) !== false;
                });
            }
            
            if (!empty($params['severity'])) {
                $logs = array_filter($logs, function($log) use ($params) {
                    return isset($log['Severity']) && $log['Severity'] === $params['severity'];
                });
            }
            
            // Apply limit and offset
            $limit = isset($params['limit']) ? intval($params['limit']) : 100;
            $offset = isset($params['offset']) ? intval($params['offset']) : 0;
            
            return array_slice(array_values($logs), $offset, $limit);
        }
    }
    
    public static function count_logs() {
        global $wpdb;
        $table = self::$db_table;
        $storage = get_option('wpal_log_storage', 'both');
        
        if ($storage === 'db' || $storage === 'both') {
            return $wpdb->get_var("SELECT COUNT(*) FROM $table");
        } else {
            return count(self::get_csv_data());
        }
    }
    
    public static function get_user_activity_stats() {
        global $wpdb;
        $table = self::$db_table;
        $storage = get_option('wpal_log_storage', 'both');
        
        if ($storage === 'db' || $storage === 'both') {
            $results = $wpdb->get_results(
                "SELECT username, COUNT(*) as count FROM $table GROUP BY username ORDER BY count DESC LIMIT 10",
                ARRAY_A
            );
            return $results;
        } else {
            $logs = self::get_csv_data();
            $stats = [];
            
            foreach ($logs as $log) {
                $user = $log['User'];
                if (!isset($stats[$user])) {
                    $stats[$user] = 0;
                }
                $stats[$user]++;
            }
            
            arsort($stats);
            
            $results = [];
            foreach (array_slice($stats, 0, 10, true) as $user => $count) {
                $results[] = [
                    'username' => $user,
                    'count' => $count,
                ];
            }
            
            return $results;
        }
    }
    
    public static function get_action_type_stats() {
        global $wpdb;
        $table = self::$db_table;
        $storage = get_option('wpal_log_storage', 'both');
        
        if ($storage === 'db' || $storage === 'both') {
            // Extract action types (first word of action)
            $results = $wpdb->get_results(
                "SELECT SUBSTRING_INDEX(action, ' ', 1) as action_type, COUNT(*) as count 
                FROM $table GROUP BY action_type ORDER BY count DESC LIMIT 10",
                ARRAY_A
            );
            return $results;
        } else {
            $logs = self::get_csv_data();
            $stats = [];
            
            foreach ($logs as $log) {
                $action = $log['Action'];
                $action_type = explode(' ', $action)[0];
                
                if (!isset($stats[$action_type])) {
                    $stats[$action_type] = 0;
                }
                $stats[$action_type]++;
            }
            
            arsort($stats);
            
            $results = [];
            foreach (array_slice($stats, 0, 10, true) as $type => $count) {
                $results[] = [
                    'action_type' => $type,
                    'count' => $count,
                ];
            }
            
            return $results;
        }
    }
    
    public static function get_daily_activity_stats() {
        global $wpdb;
        $table = self::$db_table;
        $storage = get_option('wpal_log_storage', 'both');
        
        // Get last 14 days
        $days = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $days[$date] = 0;
        }
        
        if ($storage === 'db' || $storage === 'both') {
            $results = $wpdb->get_results(
                "SELECT DATE(time) as date, COUNT(*) as count 
                FROM $table 
                WHERE time >= DATE_SUB(NOW(), INTERVAL 14 DAY) 
                GROUP BY DATE(time) 
                ORDER BY date",
                ARRAY_A
            );
            
            foreach ($results as $row) {
                $days[$row['date']] = intval($row['count']);
            }
        } else {
            $logs = self::get_csv_data();
            
            foreach ($logs as $log) {
                $date = date('Y-m-d', strtotime($log['Time']));
                if (isset($days[$date])) {
                    $days[$date]++;
                }
            }
        }
        
        $results = [];
        foreach ($days as $date => $count) {
            $results[] = [
                'date' => $date,
                'count' => $count,
            ];
        }
        
        return $results;
    }
    
    public static function perform_maintenance() {
        self::init();
        
        // Get retention period
        $retention_days = intval(get_option('wpal_retention_days', 30));
        if ($retention_days <= 0) {
            return; // No cleanup needed
        }
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$retention_days days"));
        $storage = get_option('wpal_log_storage', 'both');
        
        // Clean database if needed
        if ($storage === 'db' || $storage === 'both') {
            global $wpdb;
            $table = self::$db_table;
            $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE time < %s", $cutoff_date));
        }
        
        // Clean CSV if needed
        if ($storage === 'csv' || $storage === 'both') {
            if (!file_exists(self::$csv_file)) {
                return;
            }
            
            $data = array_map('str_getcsv', file(self::$csv_file));
            $headers = array_shift($data);
            
            $filtered_data = array_filter($data, function($row) use ($headers, $cutoff_date) {
                if (count($row) !== count($headers)) {
                    return false;
                }
                
                $log = array_combine($headers, $row);
                return strtotime($log['Time']) >= strtotime($cutoff_date);
            });
            
            // Write back to file
            $output = fopen(self::$csv_file, 'w');
            fputcsv($output, $headers);
            foreach ($filtered_data as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }
    }
    
    public static function ajax_clear_logs() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        check_ajax_referer('wpal_admin', 'nonce');
        
        self::init();
        $storage = get_option('wpal_log_storage', 'both');
        
        // Clear database if needed
        if ($storage === 'db' || $storage === 'both') {
            global $wpdb;
            $table = self::$db_table;
            $wpdb->query("TRUNCATE TABLE $table");
        }
        
        // Clear CSV if needed
        if ($storage === 'csv' || $storage === 'both') {
            $headers = "Time,User,Action,IP,UserRole,Browser,Severity\n";
            file_put_contents(self::$csv_file, $headers);
        }
        
        wp_send_json_success('Logs cleared successfully');
    }
    
    public static function get_browser_name() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (empty($user_agent)) {
            return 'Unknown';
        }
        
        $browsers = [
            'Edge' => 'Edge',
            'Firefox' => 'Firefox',
            'Chrome' => 'Chrome',
            'Opera' => 'Opera',
            'MSIE' => 'Internet Explorer',
            'Trident/7.0' => 'Internet Explorer',
            'Safari' => 'Safari',
        ];
        
        foreach ($browsers as $key => $value) {
            if (strpos($user_agent, $key) !== false) {
                return $value;
            }
        }
        
        return 'Unknown';
    }

    // Add this function to the WPAL_Helpers class
    public static function check_and_fix_database() {
        self::init();
        
        global $wpdb;
        $table_name = self::$db_table;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            // Table doesn't exist, create it
            self::create_db_table();
            
            // Add some initial data so dashboard doesn't show empty
            $current_user = wp_get_current_user();
            $entry = [
                'time' => current_time('mysql'),
                'user_id' => $current_user->ID,
                'username' => $current_user->user_login,
                'user_role' => implode(', ', $current_user->roles),
                'action' => 'Database table created',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'browser' => self::get_browser_name(),
                'severity' => 'info',
                'context' => json_encode(['automatic' => true]),
            ];
            
            $wpdb->insert(
                self::$db_table,
                $entry,
                [
                    'time' => '%s',
                    'user_id' => '%d',
                    'username' => '%s',
                    'user_role' => '%s',
                    'action' => '%s',
                    'ip' => '%s',
                    'browser' => '%s',
                    'severity' => '%s',
                    'context' => '%s',
                ]
            );
        }
        
        return $table_exists;
    }
}