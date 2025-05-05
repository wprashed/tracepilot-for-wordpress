<?php
/**
 * API functionality for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_API {
    /**
     * Initialize the class
     */
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }
    
    /**
     * Register REST API routes
     */
    public static function register_routes() {
        register_rest_route('wpal/v1', '/stats', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_stats'],
            'permission_callback' => [__CLASS__, 'check_permissions'],
        ]);
        
        register_rest_route('wpal/v1', '/logs', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_logs'],
            'permission_callback' => [__CLASS__, 'check_permissions'],
        ]);
        
        register_rest_route('wpal/v1', '/logs/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_log'],
            'permission_callback' => [__CLASS__, 'check_permissions'],
            'args' => [
                'id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);
        
        register_rest_route('wpal/v1', '/logs/filter', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'filter_logs'],
            'permission_callback' => [__CLASS__, 'check_permissions'],
        ]);
        
        register_rest_route('wpal/v1', '/users', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_users'],
            'permission_callback' => [__CLASS__, 'check_permissions'],
        ]);
        
        register_rest_route('wpal/v1', '/actions', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_actions'],
            'permission_callback' => [__CLASS__, 'check_permissions'],
        ]);
        
        register_rest_route('wpal/v1', '/push', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'push_connection'],
            'permission_callback' => [__CLASS__, 'check_permissions'],
        ]);
    }
    
    /**
     * Check permissions for API requests
     */
    public static function check_permissions() {
        return current_user_can('manage_options');
    }
    
    /**
     * Get dashboard statistics
     */
    public static function get_stats($request) {
        global $wpdb;
        WPAL_Helpers::init();
        $table_name = WPAL_Helpers::$db_table;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            return new WP_Error('table_not_found', 'Activity log table not found', ['status' => 404]);
        }
        
        // Get daily activity for the last 30 days
        $daily_activity = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(time) as date, COUNT(*) as count 
                FROM $table_name 
                WHERE time >= DATE_SUB(NOW(), INTERVAL %d DAY) 
                GROUP BY DATE(time) 
                ORDER BY date ASC",
                30
            )
        );
        
        // Get top 10 users by activity
        $user_activity = $wpdb->get_results(
            "SELECT username, COUNT(*) as count 
            FROM $table_name 
            GROUP BY username 
            ORDER BY count DESC 
            LIMIT 10"
        );
        
        // Get top 10 action types
        $action_types = $wpdb->get_results(
            "SELECT 
                CASE 
                    WHEN action LIKE 'Login%' THEN 'Login' 
                    WHEN action LIKE 'Logout%' THEN 'Logout' 
                    WHEN action LIKE 'Plugin%' THEN 'Plugin' 
                    WHEN action LIKE 'Theme%' THEN 'Theme' 
                    WHEN action LIKE 'Post%' THEN 'Post' 
                    WHEN action LIKE 'Page%' THEN 'Page' 
                    WHEN action LIKE 'User%' THEN 'User' 
                    WHEN action LIKE 'Media%' THEN 'Media' 
                    WHEN action LIKE 'Comment%' THEN 'Comment' 
                    ELSE 'Other' 
                END as action_type, 
                COUNT(*) as count 
            FROM $table_name 
            GROUP BY action_type 
            ORDER BY count DESC 
            LIMIT 10"
        );
        
        // Get severity distribution
        $severity_distribution = $wpdb->get_results(
            "SELECT severity, COUNT(*) as count 
            FROM $table_name 
            GROUP BY severity 
            ORDER BY count DESC"
        );
        
        // Get total logs
        $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Get logs in the last 24 hours
        $logs_24h = $wpdb->get_var(
            "SELECT COUNT(*) 
            FROM $table_name 
            WHERE time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        // Get logs in the last 7 days
        $logs_7d = $wpdb->get_var(
            "SELECT COUNT(*) 
            FROM $table_name 
            WHERE time >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        // Get logs in the last 30 days
        $logs_30d = $wpdb->get_var(
            "SELECT COUNT(*) 
            FROM $table_name 
            WHERE time >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        // Get unique users
        $unique_users = $wpdb->get_var("SELECT COUNT(DISTINCT username) FROM $table_name");
        
        // Get unique IPs
        $unique_ips = $wpdb->get_var("SELECT COUNT(DISTINCT ip) FROM $table_name");
        
        // Get error count
        $error_count = $wpdb->get_var(
            "SELECT COUNT(*) 
            FROM $table_name 
            WHERE severity = 'error'"
        );
        
        // Get warning count
        $warning_count = $wpdb->get_var(
            "SELECT COUNT(*) 
            FROM $table_name 
            WHERE severity = 'warning'"
        );
        
        // Get info count
        $info_count = $wpdb->get_var(
            "SELECT COUNT(*) 
            FROM $table_name 
            WHERE severity = 'info'"
        );
        
        // Return all stats
        return [
            'daily_activity' => $daily_activity,
            'user_activity' => $user_activity,
            'action_types' => $action_types,
            'severity_distribution' => $severity_distribution,
            'total_logs' => (int) $total_logs,
            'logs_24h' => (int) $logs_24h,
            'logs_7d' => (int) $logs_7d,
            'logs_30d' => (int) $logs_30d,
            'unique_users' => (int) $unique_users,
            'unique_ips' => (int) $unique_ips,
            'error_count' => (int) $error_count,
            'warning_count' => (int) $warning_count,
            'info_count' => (int) $info_count,
        ];
    }
    
    /**
     * Get logs with pagination
     */
    public static function get_logs($request) {
        global $wpdb;
        WPAL_Helpers::init();
        $table_name = WPAL_Helpers::$db_table;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            return new WP_Error('table_not_found', 'Activity log table not found', ['status' => 404]);
        }
        
        // Get pagination parameters
        $per_page = isset($request['per_page']) ? intval($request['per_page']) : 25;
        $page = isset($request['page']) ? intval($request['page']) : 1;
        $offset = ($page - 1) * $per_page;
        
        // Get logs
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY time DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        
        // Get total logs
        $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Parse context JSON
        foreach ($logs as $log) {
            if (!empty($log->context)) {
                $log->context = json_decode($log->context);
            }
        }
        
        // Set headers
        $total_pages = ceil($total_logs / $per_page);
        
        return [
            'logs' => $logs,
            'total' => (int) $total_logs,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page,
        ];
    }
    
    /**
     * Get a single log entry
     */
    public static function get_log($request) {
        global $wpdb;
        WPAL_Helpers::init();
        $table_name = WPAL_Helpers::$db_table;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            return new WP_Error('table_not_found', 'Activity log table not found', ['status' => 404]);
        }
        
        // Get log ID
        $log_id = $request['id'];
        
        // Get log
        $log = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $log_id
            )
        );
        
        if (!$log) {
            return new WP_Error('log_not_found', 'Log entry not found', ['status' => 404]);
        }
        
        // Parse context JSON
        if (!empty($log->context)) {
            $log->context = json_decode($log->context);
        }
        
        return $log;
    }
    
    /**
     * Filter logs
     */
    public static function filter_logs($request) {
        global $wpdb;
        WPAL_Helpers::init();
        $table_name = WPAL_Helpers::$db_table;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            return new WP_Error('table_not_found', 'Activity log table not found', ['status' => 404]);
        }
        
        // Get pagination parameters
        $per_page = isset($request['per_page']) ? intval($request['per_page']) : 25;
        $page = isset($request['page']) ? intval($request['page']) : 1;
        $offset = ($page - 1) * $per_page;
        
        // Build query
        $query = "SELECT * FROM $table_name WHERE 1=1";
        $count_query = "SELECT COUNT(*) FROM $table_name WHERE 1=1";
        $query_args = [];
        
        // Filter by user
        if (isset($request['user']) && !empty($request['user'])) {
            $query .= " AND username = %s";
            $count_query .= " AND username = %s";
            $query_args[] = $request['user'];
        }
        
        // Filter by user ID
        if (isset($request['user_id']) && !empty($request['user_id'])) {
            $query .= " AND user_id = %d";
            $count_query .= " AND user_id = %d";
            $query_args[] = intval($request['user_id']);
        }
        
        // Filter by action
        if (isset($request['action']) && !empty($request['action'])) {
            $query .= " AND action LIKE %s";
            $count_query .= " AND action LIKE %s";
            $query_args[] = '%' . $wpdb->esc_like($request['action']) . '%';
        }
        
        // Filter by IP
        if (isset($request['ip']) && !empty($request['ip'])) {
            $query .= " AND ip = %s";
            $count_query .= " AND ip = %s";
            $query_args[] = $request['ip'];
        }
        
        // Filter by severity
        if (isset($request['severity']) && !empty($request['severity'])) {
            $query .= " AND severity = %s";
            $count_query .= " AND severity = %s";
            $query_args[] = $request['severity'];
        }
        
        // Filter by date range
        if (isset($request['date_from']) && !empty($request['date_from'])) {
            $query .= " AND time >= %s";
            $count_query .= " AND time >= %s";
            $query_args[] = $request['date_from'] . ' 00:00:00';
        }
        
        if (isset($request['date_to']) && !empty($request['date_to'])) {
            $query .= " AND time <= %s";
            $count_query .= " AND time <= %s";
            $query_args[] = $request['date_to'] . ' 23:59:59';
        }
        
        // Add order and limit
        $query .= " ORDER BY time DESC LIMIT %d OFFSET %d";
        $query_args[] = $per_page;
        $query_args[] = $offset;
        
        // Get logs
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                $query,
                $query_args
            )
        );
        
        // Get total logs
        $total_logs = $wpdb->get_var(
            $wpdb->prepare(
                $count_query,
                array_slice($query_args, 0, -2)
            )
        );
        
        // Parse context JSON
        foreach ($logs as $log) {
            if (!empty($log->context)) {
                $log->context = json_decode($log->context);
            }
        }
        
        // Set headers
        $total_pages = ceil($total_logs / $per_page);
        
        return [
            'logs' => $logs,
            'total' => (int) $total_logs,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page,
        ];
    }
    
    /**
     * Get users who have activity logs
     */
    public static function get_users($request) {
        global $wpdb;
        WPAL_Helpers::init();
        $table_name = WPAL_Helpers::$db_table;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            return new WP_Error('table_not_found', 'Activity log table not found', ['status' => 404]);
        }
        
        // Get users
        $users = $wpdb->get_results(
            "SELECT DISTINCT username, user_id, user_role 
            FROM $table_name 
            ORDER BY username ASC"
        );
        
        return $users;
    }
    
    /**
     * Get unique action types
     */
    public static function get_actions($request) {
        global $wpdb;
        WPAL_Helpers::init();
        $table_name = WPAL_Helpers::$db_table;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            return new WP_Error('table_not_found', 'Activity log table not found', ['status' => 404]);
        }
        
        // Get actions
        $actions = $wpdb->get_results(
            "SELECT DISTINCT action 
            FROM $table_name 
            ORDER BY action ASC"
        );
        
        return array_map(function($item) {
            return $item->action;
        }, $actions);
    }
    
    /**
     * Handle WebSocket push connection
     */
    public static function push_connection($request) {
        // This is just a placeholder for the WebSocket connection
        // The actual WebSocket server would be implemented separately
        // or using a plugin like WP Pusher
        
        return [
            'status' => 'success',
            'message' => 'WebSocket connection endpoint',
        ];
    }
    
    /**
     * Send push notification
     */
    public static function send_push_notification($log) {
        // Check if push notifications are enabled
        $push_enabled = get_option('wpal_push_enabled', false);
        
        if (!$push_enabled) {
            return;
        }
        
        // In a real implementation, this would send a notification to connected clients
        // For now, we'll just log that a notification would be sent
        error_log('WPAL: Push notification would be sent for log ID ' . $log->id);
        
        // Example of how to send to a webhook
        $webhook_url = get_option('wpal_webhook_url', '');
        
        if (!empty($webhook_url)) {
            wp_remote_post($webhook_url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'type' => 'activity_log',
                    'data' => $log,
                ]),
            ]);
        }
        
        // Send to Slack if enabled
        self::send_to_slack($log);
        
        // Send to Discord if enabled
        self::send_to_discord($log);
        
        // Send to Telegram if enabled
        self::send_to_telegram($log);
    }
    
    /**
     * Send notification to Slack
     */
    private static function send_to_slack($log) {
        $slack_webhook = get_option('wpal_slack_webhook', '');
        
        if (empty($slack_webhook)) {
            return;
        }
        
        // Format message for Slack
        $color = '#28a745'; // Default to info color
        
        if ($log->severity === 'warning') {
            $color = '#ffc107';
        } elseif ($log->severity === 'error') {
            $color = '#dc3545';
        }
        
        $message = [
            'attachments' => [
                [
                    'fallback' => 'Activity Log: ' . $log->action,
                    'color' => $color,
                    'title' => 'Activity Log: ' . $log->action,
                    'fields' => [
                        [
                            'title' => 'User',
                            'value' => $log->username,
                            'short' => true,
                        ],
                        [
                            'title' => 'IP',
                            'value' => $log->ip,
                            'short' => true,
                        ],
                        [
                            'title' => 'Time',
                            'value' => $log->time,
                            'short' => true,
                        ],
                        [
                            'title' => 'Severity',
                            'value' => strtoupper($log->severity),
                            'short' => true,
                        ],
                    ],
                    'footer' => 'WP Activity Logger Pro',
                    'ts' => strtotime($log->time),
                ],
            ],
        ];
        
        // Send to Slack
        wp_remote_post($slack_webhook, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($message),
        ]);
    }
    
    /**
     * Send notification to Discord
     */
    private static function send_to_discord($log) {
        $discord_webhook = get_option('wpal_discord_webhook', '');
        
        if (empty($discord_webhook)) {
            return;
        }
        
        // Format message for Discord
        $color = 0x28a745; // Default to info color (hex to decimal)
        
        if ($log->severity === 'warning') {
            $color = 0xffc107;
        } elseif ($log->severity === 'error') {
            $color = 0xdc3545;
        }
        
        $message = [
            'embeds' => [
                [
                    'title' => 'Activity Log: ' . $log->action,
                    'color' => $color,
                    'fields' => [
                        [
                            'name' => 'User',
                            'value' => $log->username,
                            'inline' => true,
                        ],
                        [
                            'name' => 'IP',
                            'value' => $log->ip,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Time',
                            'value' => $log->time,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Severity',
                            'value' => strtoupper($log->severity),
                            'inline' => true,
                        ],
                    ],
                    'footer' => [
                        'text' => 'WP Activity Logger Pro',
                    ],
                    'timestamp' => date('c', strtotime($log->time)),
                ],
            ],
        ];
        
        // Send to Discord
        wp_remote_post($discord_webhook, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($message),
        ]);
    }
    
    /**
     * Send notification to Telegram
     */
    private static function send_to_telegram($log) {
        $telegram_bot_token = get_option('wpal_telegram_bot_token', '');
        $telegram_chat_id = get_option('wpal_telegram_chat_id', '');
        
        if (empty($telegram_bot_token) || empty($telegram_chat_id)) {
            return;
        }
        
        // Format message for Telegram
        $severity = strtoupper($log->severity);
        $message = "🔔 *Activity Log*\n";
        $message .= "Action: {$log->action}\n";
        $message .= "User: {$log->username}\n";
        $message .= "IP: {$log->ip}\n";
        $message .= "Time: {$log->time}\n";
        $message .= "Severity: {$severity}";
        
        // Send to Telegram
        $telegram_api_url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";
        
        wp_remote_post($telegram_api_url, [
            'body' => [
                'chat_id' => $telegram_chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ],
        ]);
    }
}