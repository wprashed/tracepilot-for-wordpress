<?php
/**
 * Template for the threat detection page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wpal-wrap">
    <div class="wpal-dashboard-header">
        <h1 class="wpal-dashboard-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            <?php _e('Threat Detection', 'wp-activity-logger-pro'); ?>
        </h1>
        <div class="wpal-dashboard-actions">
            <button id="wpal-analyze-threats" class="wpal-btn wpal-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <?php _e('Analyze Threats', 'wp-activity-logger-pro'); ?>
            </button>
        </div>
    </div>
    
    <div class="wpal-alert wpal-alert-info">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        <div>
            <?php _e('The threat detection system analyzes your activity logs to identify potential security threats. It looks for patterns such as multiple failed login attempts, unusual login times, suspicious file modifications, and more.', 'wp-activity-logger-pro'); ?>
        </div>
    </div>
    
    <div id="wpal-threat-summary" class="wpal-stats-grid" style="display: none;">
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <div class="wpal-stat-card-title"><?php _e('Total Threats', 'wp-activity-logger-pro'); ?></div>
            </div>
            <p class="wpal-stat-card-value" id="wpal-total-threats">0</p>
        </div>
        
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <div class="wpal-stat-card-title"><?php _e('High Severity', 'wp-activity-logger-pro'); ?></div>
            </div>
            <p class="wpal-stat-card-value" id="wpal-high-threats">0</p>
        </div>
        
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                <div class="wpal-stat-card-title"><?php _e('Medium Severity', 'wp-activity-logger-pro'); ?></div>
            </div>
            <p class="wpal-stat-card-value" id="wpal-medium-threats">0</p>
        </div>
        
        <div class="wpal-stat-card">
            <div class="wpal-stat-card-header">
                <div class="wpal-stat-card-icon info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                </div>
                <div class="wpal-stat-card-title"><?php _e('Low Severity', 'wp-activity-logger-pro'); ?></div>
            </div>
            <p class="wpal-stat-card-value" id="wpal-low-threats">0</p>
        </div>
    </div>
    
    <div id="wpal-threat-loading" style="display: none;" class="wpal-text-center wpal-mt-4">
        <div class="wpal-spinner"></div>
        <p><?php _e('Analyzing activity logs for potential threats...', 'wp-activity-logger-pro'); ?></p>
    </div>
    
    <div id="wpal-threat-results" class="wpal-widget" style="display: none;">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <?php _e('Detected Threats', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <div class="wpal-table-responsive">
                <table class="wpal-table wpal-table-striped">
                    <thead>
                        <tr>
                            <th><?php _e('Severity', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('Type', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('Description', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('IP', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('Time', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('Actions', 'wp-activity-logger-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wpal-threats-table">
                        <!-- Threats will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div id="wpal-no-threats" class="wpal-widget" style="display: none;">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <?php _e('Threat Analysis Results', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <div class="wpal-empty-state">
                <div class="wpal-empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3 class="wpal-empty-state-title"><?php _e('No Threats Detected', 'wp-activity-logger-pro'); ?></h3>
                <p class="wpal-empty-state-description"><?php _e('Good news! No security threats were detected in your activity logs.', 'wp-activity-logger-pro'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="wpal-widget wpal-mt-4">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <?php _e('Threat Detection Settings', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <form method="post" action="options.php">
                <?php settings_fields('wpal_options'); ?>
                
                <div class="wpal-form-group">
                    <label class="wpal-form-label">
                        <input type="checkbox" name="wpal_options[enable_threat_detection]" value="1" <?php checked(isset($options['enable_threat_detection']) && $options['enable_threat_detection']); ?>>
                        <?php _e('Enable Threat Detection', 'wp-activity-logger-pro'); ?>
                    </label>
                    <p class="description"><?php _e('Automatically analyze activity logs for potential security threats.', 'wp-activity-logger-pro'); ?></p>
                </div>
                
                <div class="wpal-form-group">
                    <label class="wpal-form-label">
                        <input type="checkbox" name="wpal_options[enable_threat_notifications]" value="1" <?php checked(isset($options['enable_threat_notifications']) && $options['enable_threat_notifications']); ?>>
                        <?php _e('Enable Threat Notifications', 'wp-activity-logger-pro'); ?>
                    </label>
                    <p class="description"><?php _e('Receive email notifications when high-severity threats are detected.', 'wp-activity-logger-pro'); ?></p>
                </div>
                
                <div class="wpal-form-group">
                    <label class="wpal-form-label" for="wpal_notification_email"><?php _e('Notification Email', 'wp-activity-logger-pro'); ?></label>
                    <input type="email" id="wpal_notification_email" name="wpal_options[notification_email]" class="wpal-form-control" value="<?php echo esc_attr(isset($options['notification_email']) ? $options['notification_email'] : get_option('admin_email')); ?>">
                    <p class="description"><?php _e('Email address to receive threat notifications.', 'wp-activity-logger-pro'); ?></p>
                </div>
                
                <div class="wpal-form-group">
                    <label class="wpal-form-label"><?php _e('Threat Types to Monitor', 'wp-activity-logger-pro'); ?></label>
                    <div>
                        <label class="wpal-form-label">
                            <input type="checkbox" name="wpal_options[monitor_failed_logins]" value="1" <?php checked(isset($options['monitor_failed_logins']) && $options['monitor_failed_logins']); ?>>
                            <?php _e('Failed Login Attempts', 'wp-activity-logger-pro'); ?>
                        </label>
                    </div>
                    <div>
                        <label class="wpal-form-label">
                            <input type="checkbox" name="wpal_options[monitor_unusual_logins]" value="1" <?php checked(isset($options['monitor_unusual_logins']) && $options['monitor_unusual_logins']); ?>>
                            <?php _e('Unusual Login Times/Locations', 'wp-activity-logger-pro'); ?>
                        </label>
                    </div>
                    <div>
                        <label class="wpal-form-label">
                            <input type="checkbox" name="wpal_options[monitor_file_changes]" value="1" <?php checked(isset($options['monitor_file_changes']) && $options['monitor_file_changes']); ?>>
                            <?php _e('Suspicious File Modifications', 'wp-activity-logger-pro'); ?>
                        </label>
                    </div>
                    <div>
                        <label class="wpal-form-label">
                            <input type="checkbox" name="wpal_options[monitor_privilege_escalation]" value="1" <?php checked(isset($options['monitor_privilege_escalation']) && $options['monitor_privilege_escalation']); ?>>
                            <?php _e('Privilege Escalation', 'wp-activity-logger-pro'); ?>
                        </label>
                    </div>
                </div>
                
                <div class="wpal-form-group">
                    <button type="submit" class="wpal-btn wpal-btn-primary">
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
    $('#wpal-analyze-threats').on('click', function() {
        const $button = $(this);
        
        // Disable button and show loading
        $button.prop('disabled', true).html('<div class="wpal-spinner"></div> <?php _e('Analyzing...', 'wp-activity-logger-pro'); ?>');
        $('#wpal-threat-loading').show();
        $('#wpal-threat-results').hide();
        $('#wpal-no-threats').hide();
        $('#wpal-threat-summary').hide();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpal_analyze_threats',
                nonce: '<?php echo wp_create_nonce('wpal_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Update summary
                    $('#wpal-total-threats').text(data.summary.total);
                    $('#wpal-high-threats').text(data.summary.high);
                    $('#wpal-medium-threats').text(data.summary.medium);
                    $('#wpal-low-threats').text(data.summary.low);
                    $('#wpal-threat-summary').show();
                    
                    // Show results or no threats message
                    if (data.threats.length > 0) {
                        // Populate threats table
                        const threatsHtml = [];
                        
                        data.threats.forEach(function(threat) {
                            const severityClass = threat.severity === 'high' ? 'danger' : (threat.severity === 'medium' ? 'warning' : 'info');
                            const severityIcon = threat.severity === 'high' ? 
                                '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>' :
                                (threat.severity === 'medium' ? 
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>' :
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
                                );
                            
                            threatsHtml.push(`
                                <tr>
                                    <td>
                                        <span class="wpal-badge wpal-badge-${severityClass}">
                                            ${severityIcon}
                                            ${threat.severity.charAt(0).toUpperCase() + threat.severity.slice(1)}
                                        </span>
                                    </td>
                                    <td>${threat.type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</td>
                                    <td>${threat.description}</td>
                                    <td>${threat.ip || '-'}</td>
                                    <td>${threat.last_attempt || threat.login_time || '-'}</td>
                                    <td class="wpal-action-buttons">
                                        <button class="wpal-btn wpal-btn-sm wpal-btn-outline-danger wpal-block-ip" data-ip="${threat.ip}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield-off"><path d="M19.69 14a6.9 6.9 0 0 0 .31-2V5l-8-3-3.16 1.18"></path><path d="M4.73 4.73L4 5v7c0 6 8 10 8 10a20.29 20.29 0 0 0 5.62-4.38"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                            <span class="sr-only"><?php _e('Block IP', 'wp-activity-logger-pro'); ?></span>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                        
                        $('#wpal-threats-table').html(threatsHtml.join(''));
                        $('#wpal-threat-results').show();
                    } else {
                        $('#wpal-no-threats').show();
                    }
                } else {
                    alert(response.data.message);
                }
                
                // Hide loading
                $('#wpal-threat-loading').hide();
                
                // Re-enable button
                $button.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> <?php _e('Analyze Threats', 'wp-activity-logger-pro'); ?>');
            },
            error: function() {
                alert('<?php _e('An error occurred while analyzing threats.', 'wp-activity-logger-pro'); ?>');
                
                // Hide loading
                $('#wpal-threat-loading').hide();
                
                // Re-enable button
                $button.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> <?php _e('Analyze Threats', 'wp-activity-logger-pro'); ?>');
            }
        });
    });
    
    // Block IP
    $(document).on('click', '.wpal-block-ip', function() {
        const ip = $(this).data('ip');
        
        if (confirm('<?php _e('Are you sure you want to block this IP address?', 'wp-activity-logger-pro'); ?>')) {
            alert('<?php _e('IP blocking functionality will be implemented in a future update.', 'wp-activity-logger-pro'); ?>');
        }
    });
});
</script>