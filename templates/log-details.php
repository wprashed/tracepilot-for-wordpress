<?php
/**
 * Template for the log details
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get log ID
$log_id = isset($_GET['log_id']) ? intval($_GET['log_id']) : 0;

if ($log_id <= 0) {
    echo '<div class="wpal-alert wpal-alert-danger">' . __('Invalid log ID.', 'wp-activity-logger-pro') . '</div>';
    return;
}

// Get log
global $wpdb;
WPAL_Helpers::init();
$table_name = WPAL_Helpers::$db_table;
$log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $log_id));

if (!$log) {
    echo '<div class="wpal-alert wpal-alert-danger">' . __('Log not found.', 'wp-activity-logger-pro') . '</div>';
    return;
}

// Parse context
$context = !empty($log->context) ? json_decode($log->context, true) : array();
?>

<div class="wpal-log-details">
    <div class="wpal-log-details-header">
        <h2 class="wpal-log-details-title"><?php echo esc_html($log->action); ?></h2>
        <div class="wpal-log-details-meta">
            <span class="wpal-log-details-time"><?php echo WPAL_Helpers::format_datetime($log->time); ?></span>
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
    
    <div class="wpal-log-details-description">
        <?php echo esc_html($log->description); ?>
    </div>
    
    <div class="wpal-log-details-grid">
        <div class="wpal-log-details-section">
            <h3 class="wpal-log-details-section-title"><?php _e('User Information', 'wp-activity-logger-pro'); ?></h3>
            <table class="wpal-table wpal-table-bordered">
                <tr>
                    <th><?php _e('Username', 'wp-activity-logger-pro'); ?></th>
                    <td>
                        <?php if (!empty($log->user_id) && $log->user_id > 0) : ?>
                            <a href="<?php echo admin_url('user-edit.php?user_id=' . $log->user_id); ?>" target="_blank">
                                <?php echo esc_html($log->username); ?>
                            </a>
                        <?php else : ?>
                            <?php echo esc_html($log->username); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (!empty($log->user_role)) : ?>
                    <tr>
                        <th><?php _e('User Role', 'wp-activity-logger-pro'); ?></th>
                        <td><?php echo esc_html($log->user_role); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <div class="wpal-log-details-section">
            <h3 class="wpal-log-details-section-title"><?php _e('Location Information', 'wp-activity-logger-pro'); ?></h3>
            <table class="wpal-table wpal-table-bordered">
                <tr>
                    <th><?php _e('IP Address', 'wp-activity-logger-pro'); ?></th>
                    <td>
                        <?php if (!empty($log->ip)) : ?>
                            <span class="wpal-ip-address" data-ip="<?php echo esc_attr($log->ip); ?>">
                                <?php echo esc_html($log->ip); ?>
                                <?php if (!empty($log->country_code)) : ?>
                                    <span class="wpal-ip-location-badge"><?php echo esc_html($log->country_code); ?></span>
                                <?php endif; ?>
                            </span>
                        <?php else : ?>
                            <?php _e('Unknown', 'wp-activity-logger-pro'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (!empty($log->location)) : ?>
                    <tr>
                        <th><?php _e('Location', 'wp-activity-logger-pro'); ?></th>
                        <td><?php echo esc_html($log->location); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($log->country)) : ?>
                    <tr>
                        <th><?php _e('Country', 'wp-activity-logger-pro'); ?></th>
                        <td><?php echo esc_html($log->country); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <?php if (!empty($log->object_type) || !empty($log->object_id) || !empty($log->object_name)) : ?>
        <div class="wpal-log-details-section">
            <h3 class="wpal-log-details-section-title"><?php _e('Object Information', 'wp-activity-logger-pro'); ?></h3>
            <table class="wpal-table wpal-table-bordered">
                <?php if (!empty($log->object_type)) : ?>
                    <tr>
                        <th><?php _e('Object Type', 'wp-activity-logger-pro'); ?></th>
                        <td><?php echo esc_html($log->object_type); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($log->object_id)) : ?>
                    <tr>
                        <th><?php _e('Object ID', 'wp-activity-logger-pro'); ?></th>
                        <td><?php echo esc_html($log->object_id); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($log->object_name)) : ?>
                    <tr>
                        <th><?php _e('Object Name', 'wp-activity-logger-pro'); ?></th>
                        <td><?php echo esc_html($log->object_name); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($context) && is_array($context)) : ?>
        <div class="wpal-log-details-section">
            <h3 class="wpal-log-details-section-title"><?php _e('Additional Information', 'wp-activity-logger-pro'); ?></h3>
            <table class="wpal-table wpal-table-bordered">
                <?php foreach ($context as $key => $value) : ?>
                    <?php
                    // Skip some keys that are already displayed
                    if (in_array($key, array('user_id', 'username', 'user_role', 'ip', 'location', 'country', 'country_code', 'object_type', 'object_id', 'object_name'))) {
                        continue;
                    }
                    
                    // Format value
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }
                    ?>
                    <tr>
                        <th><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></th>
                        <td><?php echo esc_html($value); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.wpal-log-details {
    padding: 20px;
}

.wpal-log-details-header {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e5e5;
}

.wpal-log-details-title {
    margin: 0 0 10px 0;
    font-size: 24px;
    font-weight: 600;
}

.wpal-log-details-meta {
    display: flex;
    align-items: center;
    gap: 10px;
}

.wpal-log-details-time {
    color: #666;
}

.wpal-log-details-description {
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 4px;
    font-size: 16px;
}

.wpal-log-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.wpal-log-details-section {
    margin-bottom: 20px;
}

.wpal-log-details-section-title {
    margin: 0 0 10px 0;
    font-size: 18px;
    font-weight: 600;
}

.wpal-ip-address {
    display: inline-flex;
    align-items: center;
    color: #2271b1;
    text-decoration: underline;
    text-underline-offset: 2px;
    cursor: pointer;
}

.wpal-ip-location-badge {
    display: inline-block;
    margin-left: 5px;
    padding: 2px 4px;
    background-color: #f0f0f1;
    border-radius: 3px;
    font-size: 10px;
    font-weight: bold;
}

@media (max-width: 768px) {
    .wpal-log-details-grid {
        grid-template-columns: 1fr;
    }
}
</style>
