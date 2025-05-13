<?php
/**
 * Template for the dashboard page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get stats
global $wpdb;
WPAL_Helpers::init();
$table_name = WPAL_Helpers::$db_table;

// Get total logs
$total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

// Get logs by severity
$logs_by_severity = $wpdb->get_results("
    SELECT 
        severity, 
        COUNT(*) as count 
    FROM $table_name 
    GROUP BY severity
");

$severity_counts = array(
    'info' => 0,
    'warning' => 0,
    'error' => 0
);

foreach ($logs_by_severity as $row) {
    $severity = !empty($row->severity) ? $row->severity : 'info';
    $severity_counts[$severity] = $row->count;
}

// Get recent logs
$recent_logs = $wpdb->get_results("
    SELECT * 
    FROM $table_name 
    ORDER BY time DESC 
    LIMIT 10
");

// Get top users
$top_users = $wpdb->get_results("
    SELECT 
        username, 
        COUNT(*) as count 
    FROM $table_name 
    WHERE username != '' 
    GROUP BY username 
    ORDER BY count DESC 
    LIMIT 5
");

// Get top actions
$top_actions = $wpdb->get_results("
    SELECT 
        action, 
        COUNT(*) as count 
    FROM $table_name 
    GROUP BY action 
    ORDER BY count DESC 
    LIMIT 5
");

// Get logs by day (last 7 days)
$logs_by_day = $wpdb->get_results("
    SELECT 
        DATE(time) as date, 
        COUNT(*) as count 
    FROM $table_name 
    WHERE time >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
    GROUP BY date 
    ORDER BY date ASC
");

// Get threat stats
$threat_table = $wpdb->prefix . 'wpal_threats';
$threat_count = 0;
$high_threats = 0;

if ($wpdb->get_var("SHOW TABLES LIKE '$threat_table'") == $threat_table) {
    $threat_count = $wpdb->get_var("SELECT COUNT(*) FROM $threat_table");
    $high_threats = $wpdb->get_var("SELECT COUNT(*) FROM $threat_table WHERE severity = 'high'");
}

// Format data for chart
$chart_labels = array();
$chart_data = array();

// Create date range for the last 7 days
$end_date = new DateTime();
$interval = new DateInterval('P1D');
$period = new DatePeriod(
    (new DateTime())->sub(new DateInterval('P6D')),
    $interval,
    $end_date->add($interval)
);

// Initialize data with zeros
foreach ($period as $date) {
    $date_str = $date->format('Y-m-d');
    $chart_labels[] = $date_str;
    $chart_data[$date_str] = 0;
}

// Fill in actual data
foreach ($logs_by_day as $row) {
    if (isset($chart_data[$row->date])) {
        $chart_data[$row->date] = (int) $row->count;
    }
}

// Convert to arrays for chart.js
$chart_values = array_values($chart_data);
?>

<div class="wrap wpal-wrap">
    <div class="wpal-dashboard-header">
        <h1 class="wpal-dashboard-title"><?php _e('Activity Logger Dashboard', 'wp-activity-logger-pro'); ?></h1>
        <div class="wpal-dashboard-actions">
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-logs'); ?>" class="wpal-btn wpal-btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                <?php _e('View All Logs', 'wp-activity-logger-pro'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-analytics'); ?>" class="wpal-btn wpal-btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                <?php _e('Analytics', 'wp-activity-logger-pro'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-export'); ?>" class="wpal-btn wpal-btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <?php _e('Export', 'wp-activity-logger-pro'); ?>
            </a>
        </div>
    </div>
    
    <div class="wpal-stats-grid">
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                </div>
                <div class="wpal-stat-card-title"><?php _e('Total Logs', 'wp-activity-logger-pro'); ?></div>
            </div>
            <p class="wpal-stat-card-value"><?php echo number_format($total_logs); ?></p>
        </div>
        
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                </div>
                <div class="wpal-stat-card-title"><?php _e('Info Logs', 'wp-activity-logger-pro'); ?></div>
            </div>
            <p class="wpal-stat-card-value"><?php echo number_format($severity_counts['info']); ?></p>
        </div>
        
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                <div class="wpal-stat-card-title"><?php _e('Warning Logs', 'wp-activity-logger-pro'); ?></div>
            </div>
            <p class="wpal-stat-card-value"><?php echo number_format($severity_counts['warning']); ?></p>
        </div>
        
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <div class="wpal-stat-card-title"><?php _e('Error Logs', 'wp-activity-logger-pro'); ?></div>
            </div>
            <p class="wpal-stat-card-value"><?php echo number_format($severity_counts['error']); ?></p>
        </div>
    </div>
    
    <div class="wpal-widgets-grid">
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    <?php _e('Activity Trend (Last 7 Days)', 'wp-activity-logger-pro'); ?>
                </h3>
                <div class="wpal-widget-actions">
                    <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-analytics'); ?>" class="wpal-widget-action">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                </div>
            </div>
            <div class="wpal-widget-body">
                <div class="wpal-chart-container">
                    <canvas id="wpal-activity-chart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <?php _e('Security Overview', 'wp-activity-logger-pro'); ?>
                </h3>
                <div class="wpal-widget-actions">
                    <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-threat-detection'); ?>" class="wpal-widget-action">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                </div>
            </div>
            <div class="wpal-widget-body">
                <?php if ($threat_count > 0) : ?>
                    <div class="wpal-stats-grid">
                        <div class="wpal-stat-card">
                            <div class="wpal-stat-card-header">
                                <div class="wpal-stat-card-icon warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                </div>
                                <div class="wpal-stat-card-title"><?php _e('Detected Threats', 'wp-activity-logger-pro'); ?></div>
                            </div>
                            <p class="wpal-stat-card-value"><?php echo number_format($threat_count); ?></p>
                        </div>
                        
                        <div class="wpal-stat-card">
                            <div class="wpal-stat-card-header">
                                <div class="wpal-stat-card-icon danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                </div>
                                <div class="wpal-stat-card-title"><?php _e('High Severity', 'wp-activity-logger-pro'); ?></div>
                            </div>
                            <p class="wpal-stat-card-value"><?php echo number_format($high_threats); ?></p>
                        </div>
                    </div>
                    
                    <div class="wpal-mt-4">
                        <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-threat-detection'); ?>" class="wpal-btn wpal-btn-danger wpal-btn-block">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield-off"><path d="M19.69 14a6.9 6.9 0 0 0 .31-2V5l-8-3-3.16 1.18"></path><path d="M4.73 4.73L4 5v7c0 6 8 10 8 10a20.29 20.29 0 0 0 5.62-4.38"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                            <?php _e('View Security Threats', 'wp-activity-logger-pro'); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <div class="wpal-empty-state">
                        <div class="wpal-empty-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <h3 class="wpal-empty-state-title"><?php _e('No Threats Detected', 'wp-activity-logger-pro'); ?></h3>
                        <p class="wpal-empty-state-description"><?php _e('Your site is secure. No security threats have been detected.', 'wp-activity-logger-pro'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="wpal-widgets-grid">
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    <?php _e('Recent Activities', 'wp-activity-logger-pro'); ?>
                </h3>
                <div class="wpal-widget-actions">
                    <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-logs'); ?>" class="wpal-widget-action">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                </div>
            </div>
            <div class="wpal-widget-body">
                <?php if (empty($recent_logs)) : ?>
                    <div class="wpal-empty-state">
                        <div class="wpal-empty-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                        </div>
                        <h3 class="wpal-empty-state-title"><?php _e('No Logs Found', 'wp-activity-logger-pro'); ?></h3>
                        <p class="wpal-empty-state-description"><?php _e('There are no activity logs to display yet. Activity will be logged as users interact with your site.', 'wp-activity-logger-pro'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="wpal-table-responsive">
                        <table class="wpal-table wpal-table-striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Time', 'wp-activity-logger-pro'); ?></th>
                                    <th><?php _e('User', 'wp-activity-logger-pro'); ?></th>
                                    <th><?php _e('Action', 'wp-activity-logger-pro'); ?></th>
                                    <th><?php _e('Severity', 'wp-activity-logger-pro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_logs as $log) : ?>
                                    <tr>
                                        <td><?php echo WPAL_Helpers::format_datetime($log->time); ?></td>
                                        <td><?php echo esc_html($log->username); ?></td>
                                        <td><?php echo esc_html($log->action); ?></td>
                                        <td>
                                            <?php if ($log->severity === 'error') : ?>
                                                <span class="wpal-badge wpal-badge-danger">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                    <?php _e('Error', 'wp-activity-logger-pro'); ?>
                                                </span>
                                            <?php elseif ($log->severity === 'warning') : ?>
                                                <span class="wpal-badge wpal-badge-warning">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                                    <?php _e('Warning', 'wp-activity-logger-pro'); ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="wpal-badge wpal-badge-info">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                                    <?php _e('Info', 'wp-activity-logger-pro'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-server"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                    <?php _e('Server Recommendations', 'wp-activity-logger-pro'); ?>
                </h3>
                <div class="wpal-widget-actions">
                    <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-server-recommendations'); ?>" class="wpal-widget-action">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                </div>
            </div>
            <div class="wpal-widget-body">
                <?php
                // Get server recommendations
                $server_recommendations = new WPAL_Server_Recommendations();
                $server_info = $server_recommendations->analyze_server_needs();
                ?>
                
                <div class="wpal-stats-grid">
                    <div class="wpal-stat-card">
                        <div class="wpal-stat-card-header">
                            <div class="wpal-stat-card-icon info">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-hard-drive"><line x1="22" y1="12" x2="2" y2="12"></line><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path><line x1="6" y1="16" x2="6.01" y2="16"></line><line x1="10" y1="16" x2="10.01" y2="16"></line></svg>
                            </div>
                            <div class="wpal-stat-card-title"><?php _e('Storage', 'wp-activity-logger-pro'); ?></div>
                        </div>
                        <p class="wpal-stat-card-value"><?php echo $server_info['recommendations']['storage']; ?> GB</p>
                    </div>
                    
                    <div class="wpal-stat-card">
                        <div class="wpal-stat-card-header">
                            <div class="wpal-stat-card-icon info">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-cpu"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
                            </div>
                            <div class="wpal-stat-card-title"><?php _e('CPU Cores', 'wp-activity-logger-pro'); ?></div>
                        </div>
                        <p class="wpal-stat-card-value"><?php echo $server_info['recommendations']['cpu']; ?></p>
                    </div>
                    
                    <div class="wpal-stat-card">
                        <div class="wpal-stat-card-header">
                            <div class="wpal-stat-card-icon info">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                            </div>
                            <div class="wpal-stat-card-title"><?php _e('RAM', 'wp-activity-logger-pro'); ?></div>
                        </div>
                        <p class="wpal-stat-card-value"><?php echo $server_info['recommendations']['ram']; ?> GB</p>
                    </div>
                    
                    <div class="wpal-stat-card">
                        <div class="wpal-stat-card-header">
                            <div class="wpal-stat-card-icon info">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-wifi"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                            </div>
                            <div class="wpal-stat-card-title"><?php _e('Hosting Type', 'wp-activity-logger-pro'); ?></div>
                        </div>
                        <p class="wpal-stat-card-value" style="font-size: 14px;"><?php echo $server_info['recommendations']['hosting_type']; ?></p>
                    </div>
                </div>
                
                <div class="wpal-mt-4">
                    <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-server-recommendations'); ?>" class="wpal-btn wpal-btn-primary wpal-btn-block">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-server"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                        <?php _e('View Detailed Recommendations', 'wp-activity-logger-pro'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="wpal-widgets-grid">
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <?php _e('Top Users', 'wp-activity-logger-pro'); ?>
                </h3>
            </div>
            <div class="wpal-widget-body">
                <?php if (empty($top_users)) : ?>
                    <div class="wpal-empty-state">
                        <div class="wpal-empty-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <h3 class="wpal-empty-state-title"><?php _e('No User Activity', 'wp-activity-logger-pro'); ?></h3>
                        <p class="wpal-empty-state-description"><?php _e('There is no user activity data to display yet.', 'wp-activity-logger-pro'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="wpal-table-responsive">
                        <table class="wpal-table wpal-table-striped">
                            <thead>
                                <tr>
                                    <th><?php _e('User', 'wp-activity-logger-pro'); ?></th>
                                    <th><?php _e('Activity Count', 'wp-activity-logger-pro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_users as $user) : ?>
                                    <tr>
                                        <td><?php echo esc_html($user->username); ?></td>
                                        <td><?php echo number_format($user->count); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    <?php _e('Top Actions', 'wp-activity-logger-pro'); ?>
                </h3>
            </div>
            <div class="wpal-widget-body">
                <?php if (empty($top_actions)) : ?>
                    <div class="wpal-empty-state">
                        <div class="wpal-empty-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                        </div>
                        <h3 class="wpal-empty-state-title"><?php _e('No Activity Data', 'wp-activity-logger-pro'); ?></h3>
                        <p class="wpal-empty-state-description"><?php _e('There is no activity data to display yet.', 'wp-activity-logger-pro'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="wpal-table-responsive">
                        <table class="wpal-table wpal-table-striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Action', 'wp-activity-logger-pro'); ?></th>
                                    <th><?php _e('Count', 'wp-activity-logger-pro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_actions as $action) : ?>
                                    <tr>
                                        <td><?php echo esc_html($action->action); ?></td>
                                        <td><?php echo number_format($action->count); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Activity chart
    const ctx = document.getElementById('wpal-activity-chart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: '<?php _e('Activity Count', 'wp-activity-logger-pro'); ?>',
                data: <?php echo json_encode($chart_values); ?>,
                backgroundColor: 'rgba(34, 113, 177, 0.2)',
                borderColor: 'rgba(34, 113, 177, 1)',
                borderWidth: 1,
                tension: 0.4
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
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>