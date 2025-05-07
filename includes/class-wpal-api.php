<?php
/**
 * API class for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_API {
    /**
     * Constructor
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Get activity chart data
     */
    public function get_activity_chart() {
        // Check nonce
        check_ajax_referer('wpal_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'wp-activity-logger-pro'));
        }
        
        global $wpdb;
        WPAL_Helpers::init();
        
        // Get logs for the last 30 days
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(time) as date, COUNT(*) as count FROM " . WPAL_Helpers::$db_table . " 
            WHERE time >= %s AND time <= %s 
            GROUP BY DATE(time) 
            ORDER BY date ASC",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        ));
        
        // Prepare data for chart
        $dates = array();
        $counts = array();
        
        // Fill in missing dates
        $current_date = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        $end_date_obj->modify('+1 day'); // Include end date
        
        $date_counts = array();
        foreach ($logs as $log) {
            $date_counts[$log->date] = $log->count;
        }
        
        while ($current_date < $end_date_obj) {
            $date_str = $current_date->format('Y-m-d');
            $dates[] = $date_str;
            $counts[] = isset($date_counts[$date_str]) ? $date_counts[$date_str] : 0;
            $current_date->modify('+1 day');
        }
        
        // Output chart HTML
        ?>
        <canvas id="wpal-activity-chart" height="300"></canvas>
        <script>
        jQuery(document).ready(function($) {
            const ctx = document.getElementById('wpal-activity-chart').getContext('2d');
            const activityChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($dates); ?>,
                    datasets: [{
                        label: '<?php _e('Activity Count', 'wp-activity-logger-pro'); ?>',
                        data: <?php echo json_encode($counts); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                }
            });
        });
        </script>
        <?php
        
        wp_die();
    }

    /**
     * Get top users
     */
    public function get_top_users() {
        // Check nonce
        check_ajax_referer('wpal_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'wp-activity-logger-pro'));
        }
        
        global $wpdb;
        WPAL_Helpers::init();
        
        // Get top 10 users
        $users = $wpdb->get_results(
            "SELECT username, user_role, COUNT(*) as count FROM " . WPAL_Helpers::$db_table . " 
            GROUP BY username 
            ORDER BY count DESC 
            LIMIT 10"
        );
        
        // Output users HTML
        if (empty($users)) {
            echo '<p class="wpal-text-center">' . __('No user activity found.', 'wp-activity-logger-pro') . '</p>';
            wp_die();
        }
        
        ?>
        <div class="wpal-table-responsive">
            <table class="wpal-table wpal-table-striped">
                <thead>
                    <tr>
                        <th><?php _e('User', 'wp-activity-logger-pro'); ?></th>
                        <th><?php _e('Role', 'wp-activity-logger-pro'); ?></th>
                        <th><?php _e('Activity Count', 'wp-activity-logger-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?php echo esc_html($user->username); ?></td>
                            <td><?php echo esc_html($user->user_role); ?></td>
                            <td><?php echo esc_html($user->count); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        
        wp_die();
    }

    /**
     * Get severity breakdown
     */
    public function get_severity_breakdown() {
        // Check nonce
        check_ajax_referer('wpal_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'wp-activity-logger-pro'));
        }
        
        global $wpdb;
        WPAL_Helpers::init();
        
        // Get severity counts
        $severities = $wpdb->get_results(
            "SELECT severity, COUNT(*) as count FROM " . WPAL_Helpers::$db_table . " 
            GROUP BY severity 
            ORDER BY count DESC"
        );
        
        // Prepare data for chart
        $labels = array();
        $data = array();
        $colors = array();
        
        foreach ($severities as $severity) {
            $labels[] = ucfirst($severity->severity);
            $data[] = $severity->count;
            
            // Set color based on severity
            switch ($severity->severity) {
                case 'info':
                    $colors[] = 'rgba(54, 162, 235, 0.8)';
                    break;
                case 'warning':
                    $colors[] = 'rgba(255, 159, 64, 0.8)';
                    break;
                case 'error':
                    $colors[] = 'rgba(255, 99, 132, 0.8)';
                    break;
                default:
                    $colors[] = 'rgba(75, 192, 192, 0.8)';
            }
        }
        
        // Output chart HTML
        ?>
        <canvas id="wpal-severity-chart" height="300"></canvas>
        <script>
        jQuery(document).ready(function($) {
            const ctx = document.getElementById('wpal-severity-chart').getContext('2d');
            const severityChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($data); ?>,
                        backgroundColor: <?php echo json_encode($colors); ?>,
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
        </script>
        <?php
        
        wp_die();
    }

    /**
     * Get recent logs
     */
    public function get_recent_logs() {
        // Check nonce
        check_ajax_referer('wpal_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'wp-activity-logger-pro'));
        }
        
        global $wpdb;
        WPAL_Helpers::init();
        
        // Get recent logs
        $logs = $wpdb->get_results(
            "SELECT * FROM " . WPAL_Helpers::$db_table . " 
            ORDER BY time DESC 
            LIMIT 10"
        );
        
        // Output logs HTML
        if (empty($logs)) {
            echo '<p class="wpal-text-center">' . __('No logs found.', 'wp-activity-logger-pro') . '</p>';
            wp_die();
        }
        
        ?>
        <div class="wpal-table-responsive">
            <table class="wpal-table wpal-table-striped">
                <thead>
                    <tr>
                        <th><?php _e('User', 'wp-activity-logger-pro'); ?></th>
                        <th><?php _e('Action', 'wp-activity-logger-pro'); ?></th>
                        <th><?php _e('Severity', 'wp-activity-logger-pro'); ?></th>
                        <th><?php _e('Time', 'wp-activity-logger-pro'); ?></th>
                        <th><?php _e('Actions', 'wp-activity-logger-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <?php
                        // Get severity badge
                        $severity_badge = '';
                        switch ($log->severity) {
                            case 'info':
                                $severity_badge = '<span class="wpal-badge wpal-badge-info"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg> ' . __('Info', 'wp-activity-logger-pro') . '</span>';
                                break;
                            case 'warning':
                                $severity_badge = '<span class="wpal-badge wpal-badge-warning"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg> ' . __('Warning', 'wp-activity-logger-pro') . '</span>';
                                break;
                            case 'error':
                                $severity_badge = '<span class="wpal-badge wpal-badge-danger"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> ' . __('Error', 'wp-activity-logger-pro') . '</span>';
                                break;
                            default:
                                $severity_badge = '<span class="wpal-badge wpal-badge-info"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg> ' . __('Info', 'wp-activity-logger-pro') . '</span>';
                        }
                        ?>
                        <tr>
                            <td><?php echo esc_html($log->username); ?></td>
                            <td><?php echo esc_html($log->action); ?></td>
                            <td><?php echo $severity_badge; ?></td>
                            <td><?php echo WPAL_Helpers::format_datetime($log->time); ?></td>
                            <td>
                                <button type="button" class="wpal-btn wpal-btn-sm wpal-btn-icon wpal-btn-secondary wpal-view-log" data-log-id="<?php echo esc_attr($log->id); ?>" title="<?php _e('View Details', 'wp-activity-logger-pro'); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        
        wp_die();
    }

    /**
     * Get log details
     */
    public function get_log_details() {
        // Check nonce
        check_ajax_referer('wpal_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'wp-activity-logger-pro'));
        }
        
        // Get log ID
        $log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;
        
        if (!$log_id) {
            wp_die(__('Invalid log ID.', 'wp-activity-logger-pro'));
        }
        
        // Include log details template
        include WPAL_PLUGIN_DIR . 'templates/log-details.php';
        
        wp_die();
    }

    /**
     * Delete a log entry
     */
    public function delete_log() {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to delete logs.', 'wp-activity-logger-pro'));
        }
        
        // Get log ID
        $log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;
        
        // Verify nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'wpal_delete_log_' . $log_id)) {
            wp_send_json_error(__('Security check failed.', 'wp-activity-logger-pro'));
        }
        
        // Delete log
        global $wpdb;
        WPAL_Helpers::init();
        $result = $wpdb->delete(
            WPAL_Helpers::$db_table,
            array('id' => $log_id),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(__('Failed to delete log.', 'wp-activity-logger-pro'));
        }
        
        // Log the deletion
        //WPAL_Helpers::log_activity(
            //'log_deleted',
            //sprintf(__('Log entry #%d was deleted', 'wp-activity-logger-pro'), $log_id),
           // 'info'
       // );
        
        wp_send_json_success(__('Log deleted successfully.', 'wp-activity-logger-pro'));
    }

    /**
     * Delete all logs
     */
    public function delete_all_logs() {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to delete logs.', 'wp-activity-logger-pro'));
        }
        
        // Verify nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'wpal_delete_nonce')) {
            wp_send_json_error(__('Security check failed.', 'wp-activity-logger-pro'));
        }
        
        // Delete all logs
        global $wpdb;
        WPAL_Helpers::init();
        $result = $wpdb->query("TRUNCATE TABLE " . WPAL_Helpers::$db_table);
        
        if ($result === false) {
            wp_send_json_error(__('Failed to delete logs.', 'wp-activity-logger-pro'));
        }
        
        // Log the deletion
        WPAL_Helpers::log_activity(
            'logs_deleted_all',
            __('All logs were deleted', 'wp-activity-logger-pro'),
            'warning'
        );
        
        wp_send_json_success(__('All logs deleted successfully.', 'wp-activity-logger-pro'));
    }
}