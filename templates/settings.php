<?php
/**
 * Template for the settings page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get settings
$settings = get_option('wpal_settings', array());

// Default values
$retention_period = isset($settings['retention_period']) ? intval($settings['retention_period']) : 30;
$notification_email = isset($settings['notification_email']) ? $settings['notification_email'] : get_option('admin_email');
$notification_events = isset($settings['notification_events']) ? $settings['notification_events'] : array('error');
$push_notifications = isset($settings['push_notifications']) ? $settings['push_notifications'] : '';
$push_url = isset($settings['push_url']) ? $settings['push_url'] : '';
$export_format = isset($settings['export_format']) ? $settings['export_format'] : 'csv';

// Handle form submission
if (isset($_POST['wpal_save_settings'])) {
    // Verify nonce
    check_admin_referer('wpal_settings_nonce');
    
    // Sanitize and save settings
    $settings = array(
        'retention_period' => isset($_POST['retention_period']) ? intval($_POST['retention_period']) : 30,
        'notification_email' => isset($_POST['notification_email']) ? sanitize_email($_POST['notification_email']) : get_option('admin_email'),
        'notification_events' => isset($_POST['notification_events']) ? array_map('sanitize_text_field', $_POST['notification_events']) : array(),
        'push_notifications' => isset($_POST['push_notifications']) ? 'on' : '',
        'push_url' => isset($_POST['push_url']) ? esc_url_raw($_POST['push_url']) : '',
        'export_format' => isset($_POST['export_format']) ? sanitize_text_field($_POST['export_format']) : 'csv'
    );
    
    // Save settings
    update_option('wpal_settings', $settings);
    
    // Show success message
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully.', 'wp-activity-logger-pro') . '</p></div>';
    
    // Update variables
    $retention_period = $settings['retention_period'];
    $notification_email = $settings['notification_email'];
    $notification_events = $settings['notification_events'];
    $push_notifications = $settings['push_notifications'];
    $push_url = $settings['push_url'];
    $export_format = $settings['export_format'];
}
?>

<div class="wrap wpal-wrap">
    <div class="wpal-dashboard-header">
        <h1 class="wpal-dashboard-title"><?php _e('Settings', 'wp-activity-logger-pro'); ?></h1>
        <div class="wpal-dashboard-actions">
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-dashboard'); ?>" class="wpal-btn wpal-btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                <?php _e('Dashboard', 'wp-activity-logger-pro'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro'); ?>" class="wpal-btn wpal-btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                <?php _e('View Logs', 'wp-activity-logger-pro'); ?>
            </a>
        </div>
    </div>
    
    <div class="wpal-widget">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <?php _e('Plugin Settings', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <div class="wpal-settings-tabs">
                <a href="#" class="wpal-settings-tab wpal-active" data-target="general"><?php _e('General', 'wp-activity-logger-pro'); ?></a>
                <a href="#" class="wpal-settings-tab" data-target="notifications"><?php _e('Notifications', 'wp-activity-logger-pro'); ?></a>
                <a href="#" class="wpal-settings-tab" data-target="export"><?php _e('Export', 'wp-activity-logger-pro'); ?></a>
                <a href="#" class="wpal-settings-tab" data-target="advanced"><?php _e('Advanced', 'wp-activity-logger-pro'); ?></a>
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('wpal_settings_nonce'); ?>
                
                <div id="general" class="wpal-settings-content wpal-active">
                    <div class="wpal-settings-section">
                        <h3 class="wpal-settings-section-title"><?php _e('Log Retention', 'wp-activity-logger-pro'); ?></h3>
                        <p class="wpal-settings-description"><?php _e('Configure how long logs should be kept in the database.', 'wp-activity-logger-pro'); ?></p>
                        
                        <div class="wpal-form-group">
                            <label for="retention_period" class="wpal-form-label"><?php _e('Retention Period (days)', 'wp-activity-logger-pro'); ?></label>
                            <input type="number" name="retention_period" id="retention_period" class="wpal-form-control" value="<?php echo esc_attr($retention_period); ?>" min="0">
                            <p class="description"><?php _e('Set to 0 to keep logs indefinitely.', 'wp-activity-logger-pro'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div id="notifications" class="wpal-settings-content">
                    <div class="wpal-settings-section">
                        <h3 class="wpal-settings-section-title"><?php _e('Email Notifications', 'wp-activity-logger-pro'); ?></h3>
                        <p class="wpal-settings-description"><?php _e('Configure email notifications for important events.', 'wp-activity-logger-pro'); ?></p>
                        
                        <div class="wpal-form-group">
                            <label for="notification_email" class="wpal-form-label"><?php _e('Notification Email', 'wp-activity-logger-pro'); ?></label>
                            <input type="email" name="notification_email" id="notification_email" class="wpal-form-control" value="<?php echo esc_attr($notification_email); ?>">
                        </div>
                        
                        <div class="wpal-form-group">
                            <label class="wpal-form-label"><?php _e('Notify On', 'wp-activity-logger-pro'); ?></label>
                            <div>
                                <label>
                                    <input type="checkbox" name="notification_events[]" value="error" <?php checked(in_array('error', $notification_events)); ?>>
                                    <?php _e('Errors', 'wp-activity-logger-pro'); ?>
                                </label>
                            </div>
                            <div>
                                <label>
                                    <input type="checkbox" name="notification_events[]" value="warning" <?php checked(in_array('warning', $notification_events)); ?>>
                                    <?php _e('Warnings', 'wp-activity-logger-pro'); ?>
                                </label>
                            </div>
                            <div>
                                <label>
                                    <input type="checkbox" name="notification_events[]" value="info" <?php checked(in_array('info', $notification_events)); ?>>
                                    <?php _e('Info', 'wp-activity-logger-pro'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="wpal-settings-section">
                        <h3 class="wpal-settings-section-title"><?php _e('Push Notifications', 'wp-activity-logger-pro'); ?></h3>
                        <p class="wpal-settings-description"><?php _e('Configure push notifications to external services.', 'wp-activity-logger-pro'); ?></p>
                        
                        <div class="wpal-form-group">
                            <label>
                                <input type="checkbox" name="push_notifications" <?php checked($push_notifications, 'on'); ?>>
                                <?php _e('Enable Push Notifications', 'wp-activity-logger-pro'); ?>
                            </label>
                        </div>
                        
                        <div class="wpal-form-group">
                            <label for="push_url" class="wpal-form-label"><?php _e('Webhook URL', 'wp-activity-logger-pro'); ?></label>
                            <input type="url" name="push_url" id="push_url" class="wpal-form-control" value="<?php echo esc_attr($push_url); ?>">
                            <p class="description"><?php _e('Enter the webhook URL to send notifications to.', 'wp-activity-logger-pro'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div id="export" class="wpal-settings-content">
                    <div class="wpal-settings-section">
                        <h3 class="wpal-settings-section-title"><?php _e('Export Settings', 'wp-activity-logger-pro'); ?></h3>
                        <p class="wpal-settings-description"><?php _e('Configure default export settings.', 'wp-activity-logger-pro'); ?></p>
                        
                        <div class="wpal-form-group">
                            <label for="export_format" class="wpal-form-label"><?php _e('Default Export Format', 'wp-activity-logger-pro'); ?></label>
                            <select name="export_format" id="export_format" class="wpal-form-control">
                                <option value="csv" <?php selected($export_format, 'csv'); ?>><?php _e('CSV', 'wp-activity-logger-pro'); ?></option>
                                <option value="json" <?php selected($export_format, 'json'); ?>><?php _e('JSON', 'wp-activity-logger-pro'); ?></option>
                                <option value="xml" <?php selected($export_format, 'xml'); ?>><?php _e('XML', 'wp-activity-logger-pro'); ?></option>
                                <option value="pdf" <?php selected($export_format, 'pdf'); ?>><?php _e('PDF', 'wp-activity-logger-pro'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div id="advanced" class="wpal-settings-content">
                    <div class="wpal-settings-section">
                        <h3 class="wpal-settings-section-title"><?php _e('Advanced Settings', 'wp-activity-logger-pro'); ?></h3>
                        <p class="wpal-settings-description"><?php _e('Configure advanced plugin settings.', 'wp-activity-logger-pro'); ?></p>
                        
                        <div class="wpal-alert wpal-alert-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <div>
                                <p><?php _e('These settings are for advanced users only. Changing these settings may affect the performance of your website.', 'wp-activity-logger-pro'); ?></p>
                            </div>
                        </div>
                        
                        <div class="wpal-form-group">
                            <button type="button" id="wpal-reset-settings" class="wpal-btn wpal-btn-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                <?php _e('Reset Settings', 'wp-activity-logger-pro'); ?>
                            </button>
                            <p class="description"><?php _e('Reset all settings to default values.', 'wp-activity-logger-pro'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="wpal-form-group">
                    <button type="submit" name="wpal_save_settings" class="wpal-btn wpal-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <?php _e('Save Settings', 'wp-activity-logger-pro'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle settings tabs
    $('.wpal-settings-tab').on('click', function(e) {
        e.preventDefault();
        
        const target = $(this).data('target');
        
        // Update active tab
        $('.wpal-settings-tab').removeClass('wpal-active');
        $(this).addClass('wpal-active');
        
        // Show target tab content
        $('.wpal-settings-content').removeClass('wpal-active');
        $('#' + target).addClass('wpal-active');
    });
    
    // Handle reset settings
    $('#wpal-reset-settings').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to reset all settings to default values? This action cannot be undone.', 'wp-activity-logger-pro'); ?>')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpal_reset_settings',
                    nonce: wpal_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert(response.data);
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred while resetting settings.', 'wp-activity-logger-pro'); ?>');
                }
            });
        }
    });
});
</script>