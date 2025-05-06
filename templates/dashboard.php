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

$total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$info_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE severity = 'info'");
$warning_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE severity = 'warning'");
$error_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE severity = 'error'");
?>

<div class="wrap wpal-wrap">
    <div class="wpal-dashboard-header">
        <h1 class="wpal-dashboard-title"><?php _e('Activity Logger Dashboard', 'wp-activity-logger-pro'); ?></h1>
        <div class="wpal-dashboard-actions">
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro'); ?>" class="wpal-btn wpal-btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                <?php _e('View All Logs', 'wp-activity-logger-pro'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-settings'); ?>" class="wpal-btn wpal-btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <?php _e('Settings', 'wp-activity-logger-pro'); ?>
            </a>
        </div>
    </div>
    
    <div class="wpal-stats-grid">
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </div>
                <h3 class="wpal-stat-card-title"><?php _e('Total Logs', 'wp-activity-logger-pro'); ?></h3>
            </div>
            <p class="wpal-stat-card-value"><?php echo number_format($total_logs); ?></p>
        </div>
        
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                </div>
                <h3 class="wpal-stat-card-title"><?php _e('Info Logs', 'wp-activity-logger-pro'); ?></h3>
            </div>
            <p class="wpal-stat-card-value"><?php echo number_format($info_logs); ?></p>
        </div>
        
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                <h3 class="wpal-stat-card-title"><?php _e('Warning Logs', 'wp-activity-logger-pro'); ?></h3>
            </div>
            <p class="wpal-stat-card-value"><?php echo number_format($warning_logs); ?></p>
        </div>
        
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <h3 class="wpal-stat-card-title"><?php _e('Error Logs', 'wp-activity-logger-pro'); ?></h3>
            </div>
            <p class="wpal-stat-card-value"><?php echo number_format($error_logs); ?></p>
        </div>
    </div>
    
    <div class="wpal-widgets-grid">
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    <?php _e('Activity Over Time', 'wp-activity-logger-pro'); ?>
                </h3>
                <div class="wpal-widget-actions">
                    <button type="button" class="wpal-btn wpal-btn-sm wpal-btn-icon wpal-btn-secondary wpal-refresh-widget" data-widget="activity-chart">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    </button>
                </div>
            </div>
            <div class="wpal-widget-body" id="wpal-activity-chart-widget">
                <div class="wpal-text-center wpal-mt-4 wpal-mb-4">
                    <div class="spinner is-active"></div>
                    <p><?php _e('Loading chart...', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <?php _e('Top Users', 'wp-activity-logger-pro'); ?>
                </h3>
                <div class="wpal-widget-actions">
                    <button type="button" class="wpal-btn wpal-btn-sm wpal-btn-icon wpal-btn-secondary wpal-refresh-widget" data-widget="top-users">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    </button>
                </div>
            </div>
            <div class="wpal-widget-body" id="wpal-top-users-widget">
                <div class="wpal-text-center wpal-mt-4 wpal-mb-4">
                    <div class="spinner is-active"></div>
                    <p><?php _e('Loading users...', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-pie-chart"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                    <?php _e('Severity Breakdown', 'wp-activity-logger-pro'); ?>
                </h3>
                <div class="wpal-widget-actions">
                    <button type="button" class="wpal-btn wpal-btn-sm wpal-btn-icon wpal-btn-secondary wpal-refresh-widget" data-widget="severity-breakdown">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    </button>
                </div>
            </div>
            <div class="wpal-widget-body" id="wpal-severity-breakdown-widget">
                <div class="wpal-text-center wpal-mt-4 wpal-mb-4">
                    <div class="spinner is-active"></div>
                    <p><?php _e('Loading chart...', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <?php _e('Recent Activity', 'wp-activity-logger-pro'); ?>
                </h3>
                <div class="wpal-widget-actions">
                    <button type="button" class="wpal-btn wpal-btn-sm wpal-btn-icon wpal-btn-secondary wpal-refresh-widget" data-widget="recent-logs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    </button>
                </div>
            </div>
            <div class="wpal-widget-body" id="wpal-recent-logs-widget">
                <div class="wpal-text-center wpal-mt-4 wpal-mb-4">
                    <div class="spinner is-active"></div>
                    <p><?php _e('Loading logs...', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Refresh widget
    $('.wpal-refresh-widget').on('click', function() {
        const widget = $(this).data('widget');
        const $widgetBody = $('#wpal-' + widget + '-widget');
        
        $widgetBody.html('<div class="wpal-text-center wpal-mt-4 wpal-mb-4"><div class="spinner is-active"></div><p>Loading...</p></div>');
        
        switch (widget) {
            case 'activity-chart':
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpal_get_activity_chart',
                        nonce: wpal_admin_vars.nonce
                    },
                    success: function(response) {
                        $widgetBody.html(response);
                        initCharts();
                    }
                });
                break;
            case 'top-users':
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpal_get_top_users',
                        nonce: wpal_admin_vars.nonce
                    },
                    success: function(response) {
                        $widgetBody.html(response);
                    }
                });
                break;
            case 'severity-breakdown':
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpal_get_severity_breakdown',
                        nonce: wpal_admin_vars.nonce
                    },
                    success: function(response) {
                        $widgetBody.html(response);
                        initCharts();
                    }
                });
                break;
            case 'recent-logs':
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpal_get_recent_logs',
                        nonce: wpal_admin_vars.nonce
                    },
                    success: function(response) {
                        $widgetBody.html(response);
                    }
                });
                break;
        }
    });
});
</script>