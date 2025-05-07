<?php
/**
 * Template for the log details modal
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get log ID
$log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;

if (!$log_id) {
    echo '<p class="wpal-text-center">' . __('Invalid log ID.', 'wp-activity-logger-pro') . '</p>';
    return;
}

// Get log details
global $wpdb;
WPAL_Helpers::init();
$log = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM " . WPAL_Helpers::$db_table . " WHERE id = %d",
    $log_id
));

if (!$log) {
    echo '<p class="wpal-text-center">' . __('Log not found.', 'wp-activity-logger-pro') . '</p>';
    return;
}

// Get context
$context = json_decode($log->context, true);
?>

<div class="wpal-log-details">
    <div class="wpal-log-details-header">
        <div class="wpal-log-details-icon">
            <?php if ($log->severity === 'error') : ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <?php elseif ($log->severity === 'warning') : ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            <?php else : ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <?php endif; ?>
        </div>
        <h3 class="wpal-log-details-title"><?php echo esc_html($log->action); ?></h3>
    </div>
    
    <div class="wpal-log-details-meta">
        <div class="wpal-log-details-meta-item">
            <div class="wpal-log-details-meta-label"><?php _e('ID', 'wp-activity-logger-pro'); ?></div>
            <div class="wpal-log-details-meta-value"><?php echo esc_html($log->id); ?></div>
        </div>
        
        <div class="wpal-log-details-meta-item">
            <div class="wpal-log-details-meta-label"><?php _e('Time', 'wp-activity-logger-pro'); ?></div>
            <div class="wpal-log-details-meta-value"><?php echo WPAL_Helpers::format_datetime($log->time); ?></div>
        </div>
        
        <div class="wpal-log-details-meta-item">
            <div class="wpal-log-details-meta-label"><?php _e('User', 'wp-activity-logger-pro'); ?></div>
            <div class="wpal-log-details-meta-value"><?php echo esc_html($log->username); ?> (ID: <?php echo esc_html($log->user_id); ?>)</div>
        </div>
        
        <div class="wpal-log-details-meta-item">
            <div class="wpal-log-details-meta-label"><?php _e('User Role', 'wp-activity-logger-pro'); ?></div>
            <div class="wpal-log-details-meta-value"><?php echo esc_html($log->user_role); ?></div>
        </div>
        
        <div class="wpal-log-details-meta-item">
            <div class="wpal-log-details-meta-label"><?php _e('Action', 'wp-activity-logger-pro'); ?></div>
            <div class="wpal-log-details-meta-value"><?php echo esc_html($log->action); ?></div>
        </div>
        
        <div class="wpal-log-details-meta-item">
            <div class="wpal-log-details-meta-label"><?php _e('Severity', 'wp-activity-logger-pro'); ?></div>
            <div class="wpal-log-details-meta-value">
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
            </div>
        </div>
        
        <div class="wpal-log-details-meta-item">
            <div class="wpal-log-details-meta-label"><?php _e('IP Address', 'wp-activity-logger-pro'); ?></div>
            <div class="wpal-log-details-meta-value"><?php echo esc_html($log->ip); ?></div>
        </div>
        
        <div class="wpal-log-details-meta-item">
            <div class="wpal-log-details-meta-label"><?php _e('Browser', 'wp-activity-logger-pro'); ?></div>
            <div class="wpal-log-details-meta-value"><?php echo esc_html($log->browser); ?></div>
        </div>
        
        <?php if (!empty($log->object_type)) : ?>
            <div class="wpal-log-details-meta-item">
                <div class="wpal-log-details-meta-label"><?php _e('Object Type', 'wp-activity-logger-pro'); ?></div>
                <div class="wpal-log-details-meta-value"><?php echo esc_html($log->object_type); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($log->object_id)) : ?>
            <div class="wpal-log-details-meta-item">
                <div class="wpal-log-details-meta-label"><?php _e('Object ID', 'wp-activity-logger-pro'); ?></div>
                <div class="wpal-log-details-meta-value"><?php echo esc_html($log->object_id); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($log->object_name)) : ?>
            <div class="wpal-log-details-meta-item">
                <div class="wpal-log-details-meta-label"><?php _e('Object Name', 'wp-activity-logger-pro'); ?></div>
                <div class="wpal-log-details-meta-value"><?php echo esc_html($log->object_name); ?></div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($context)) : ?>
        <h4><?php _e('Context', 'wp-activity-logger-pro'); ?></h4>
        <div class="wpal-log-details-content">
            <pre><?php print_r($context); ?></pre>
        </div>
    <?php endif; ?>
</div>