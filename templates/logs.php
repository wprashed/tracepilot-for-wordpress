<?php
/**
 * Template for the logs page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get logs
global $wpdb;
WPAL_Helpers::init();
$table_name = WPAL_Helpers::$db_table;

// Get all available user roles for filtering
$user_roles = $wpdb->get_col("SELECT DISTINCT user_role FROM $table_name WHERE user_role != ''");

// Get all available IP addresses for filtering
$ip_addresses = $wpdb->get_col("SELECT DISTINCT ip FROM $table_name WHERE ip != ''");

// Get logs with filters
$where_clauses = array();
$where_values = array();

// Apply role filter if set
$role_filter = isset($_GET['role_filter']) ? sanitize_text_field($_GET['role_filter']) : '';
if (!empty($role_filter)) {
    $where_clauses[] = "user_role = %s";
    $where_values[] = $role_filter;
}

// Apply IP filter if set
$ip_filter = isset($_GET['ip_filter']) ? sanitize_text_field($_GET['ip_filter']) : '';
if (!empty($ip_filter)) {
    $where_clauses[] = "ip = %s";
    $where_values[] = $ip_filter;
}

// Apply severity filter if set
$severity_filter = isset($_GET['severity_filter']) ? sanitize_text_field($_GET['severity_filter']) : '';
if (!empty($severity_filter)) {
    $where_clauses[] = "severity = %s";
    $where_values[] = $severity_filter;
}

// Apply date range filter if set
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

if (!empty($date_from)) {
    $where_clauses[] = "time >= %s";
    $where_values[] = $date_from . ' 00:00:00';
}

if (!empty($date_to)) {
    $where_clauses[] = "time <= %s";
    $where_values[] = $date_to . ' 23:59:59';
}

// Build the query
$query = "SELECT * FROM $table_name";
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}
$query .= " ORDER BY id DESC";

// Prepare the query if we have values
if (!empty($where_values)) {
    $query = $wpdb->prepare($query, $where_values);
}

// Get the logs
$logs = $wpdb->get_results($query);
?>

<div class="wrap wpal-wrap">
    <div class="wpal-dashboard-header">
        <h1 class="wpal-dashboard-title"><?php _e('Activity Logs', 'wp-activity-logger-pro'); ?></h1>
        <div class="wpal-dashboard-actions">
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-dashboard'); ?>" class="wpal-btn wpal-btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                <?php _e('Dashboard', 'wp-activity-logger-pro'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-export'); ?>" class="wpal-btn wpal-btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <?php _e('Export', 'wp-activity-logger-pro'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-archive'); ?>" class="wpal-btn wpal-btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>
                <?php _e('Archive', 'wp-activity-logger-pro'); ?>
            </a>
            <button id="wpal-delete-all-logs" class="wpal-btn wpal-btn-outline-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                <?php _e('Delete All', 'wp-activity-logger-pro'); ?>
            </button>
        </div>
    </div>
    
    <!-- Advanced Filters -->
    <div class="wpal-widget wpal-mb-4">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <?php _e('Advanced Filters', 'wp-activity-logger-pro'); ?>
            </h3>
            <div class="wpal-widget-actions">
                <button type="button" class="wpal-widget-action" id="wpal-toggle-filters">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
            </div>
        </div>
        <div class="wpal-widget-body" id="wpal-filters-body">
            <form method="get" action="<?php echo admin_url('admin.php'); ?>" id="wpal-filter-form">
                <input type="hidden" name="page" value="wp-activity-logger-pro-logs">
                
                <div class="wpal-filter-grid">
                    <div class="wpal-filter-item">
                        <label for="role_filter" class="wpal-form-label"><?php _e('User Role', 'wp-activity-logger-pro'); ?></label>
                        <select name="role_filter" id="role_filter" class="wpal-form-control">
                            <option value=""><?php _e('All Roles', 'wp-activity-logger-pro'); ?></option>
                            <?php foreach ($user_roles as $role) : ?>
                                <option value="<?php echo esc_attr($role); ?>" <?php selected($role_filter, $role); ?>><?php echo esc_html($role); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="wpal-filter-item">
                        <label for="ip_filter" class="wpal-form-label"><?php _e('IP Address', 'wp-activity-logger-pro'); ?></label>
                        <select name="ip_filter" id="ip_filter" class="wpal-form-control">
                            <option value=""><?php _e('All IPs', 'wp-activity-logger-pro'); ?></option>
                            <?php foreach ($ip_addresses as $ip) : ?>
                                <option value="<?php echo esc_attr($ip); ?>" <?php selected($ip_filter, $ip); ?>><?php echo esc_html($ip); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="wpal-filter-item">
                        <label for="severity_filter" class="wpal-form-label"><?php _e('Severity', 'wp-activity-logger-pro'); ?></label>
                        <select name="severity_filter" id="severity_filter" class="wpal-form-control">
                            <option value=""><?php _e('All Severities', 'wp-activity-logger-pro'); ?></option>
                            <option value="info" <?php selected($severity_filter, 'info'); ?>><?php _e('Info', 'wp-activity-logger-pro'); ?></option>
                            <option value="warning" <?php selected($severity_filter, 'warning'); ?>><?php _e('Warning', 'wp-activity-logger-pro'); ?></option>
                            <option value="error" <?php selected($severity_filter, 'error'); ?>><?php _e('Error', 'wp-activity-logger-pro'); ?></option>
                        </select>
                    </div>
                    
                    <div class="wpal-filter-item">
                        <label for="date_from" class="wpal-form-label"><?php _e('Date From', 'wp-activity-logger-pro'); ?></label>
                        <input type="date" name="date_from" id="date_from" class="wpal-form-control" value="<?php echo esc_attr($date_from); ?>">
                    </div>
                    
                    <div class="wpal-filter-item">
                        <label for="date_to" class="wpal-form-label"><?php _e('Date To', 'wp-activity-logger-pro'); ?></label>
                        <input type="date" name="date_to" id="date_to" class="wpal-form-control" value="<?php echo esc_attr($date_to); ?>">
                    </div>
                    
                    <div class="wpal-filter-item wpal-filter-actions">
                        <button type="submit" class="wpal-btn wpal-btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <?php _e('Apply Filters', 'wp-activity-logger-pro'); ?>
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-logs'); ?>" class="wpal-btn wpal-btn-outline-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            <?php _e('Clear Filters', 'wp-activity-logger-pro'); ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="wpal-widget">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                <?php _e('Activity Logs', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <?php if (empty($logs)) : ?>
                <div class="wpal-empty-state">
                    <div class="wpal-empty-state-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                    </div>
                    <h3 class="wpal-empty-state-title"><?php _e('No Logs Found', 'wp-activity-logger-pro'); ?></h3>
                    <p class="wpal-empty-state-description"><?php _e('There are no activity logs to display yet. Activity will be logged as users interact with your site.', 'wp-activity-logger-pro'); ?></p>
                </div>
            <?php else : ?>
                <div class="wpal-table-responsive">
                    <table id="wpal-logs-table" class="wpal-table wpal-table-striped">
                        <thead>
                            <tr>
                                <th><?php _e('ID', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('Time', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('User', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('Action', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('Severity', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('IP', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('Actions', 'wp-activity-logger-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log) : ?>
                                <tr id="log-row-<?php echo esc_attr($log->id); ?>">
                                    <td><?php echo esc_html($log->id); ?></td>
                                    <td><?php echo WPAL_Helpers::format_datetime($log->time); ?></td>
                                    <td>
                                        <?php if (!empty($log->user_id) && $log->user_id > 0) : ?>
                                            <?php echo esc_html($log->username); ?> 
                                            <span class="wpal-badge wpal-badge-info"><?php echo esc_html($log->user_role); ?></span>
                                        <?php else : ?>
                                            <?php echo esc_html($log->username); ?>
                                        <?php endif; ?>
                                    </td>
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
                                    <td>
                                        <?php if (!empty($log->ip)) : ?>
                                            <span class="wpal-ip-address" data-ip="<?php echo esc_attr($log->ip); ?>">
                                                <?php echo esc_html($log->ip); ?>
                                                <span class="wpal-ip-location-badge" id="ip-location-<?php echo esc_attr($log->id); ?>"></span>
                                            </span>
                                        <?php else : ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="wpal-action-buttons">
                                        <button class="wpal-btn wpal-btn-sm wpal-btn-outline-primary wpal-view-log" data-log-id="<?php echo esc_attr($log->id); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            <span class="sr-only"><?php _e('View', 'wp-activity-logger-pro'); ?></span>
                                        </button>
                                        <button class="wpal-btn wpal-btn-sm wpal-btn-outline-secondary wpal-archive-log" data-log-id="<?php echo esc_attr($log->id); ?>" data-log-action="<?php echo esc_attr($log->action); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>
                                            <span class="sr-only"><?php _e('Archive', 'wp-activity-logger-pro'); ?></span>
                                        </button>
                                        <button class="wpal-btn wpal-btn-sm wpal-btn-outline-danger wpal-delete-log" data-log-id="<?php echo esc_attr($log->id); ?>" data-log-action="<?php echo esc_attr($log->action); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            <span class="sr-only"><?php _e('Delete', 'wp-activity-logger-pro'); ?></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div id="wpal-log-details-modal" class="wpal-modal">
    <div class="wpal-modal-content">
        <div class="wpal-modal-header">
            <h3><?php _e('Log Details', 'wp-activity-logger-pro'); ?></h3>
            <button class="wpal-modal-close">&times;</button>
        </div>
        <div class="wpal-modal-body">
            <!-- Content will be loaded via AJAX -->
        </div>
        <div class="wpal-modal-footer">
            <button class="wpal-btn wpal-btn-secondary wpal-modal-cancel"><?php _e('Close', 'wp-activity-logger-pro'); ?></button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="wpal-confirm-modal" class="wpal-modal">
    <div class="wpal-modal-content">
        <div class="wpal-modal-header">
            <h3><?php _e('Confirm Action', 'wp-activity-logger-pro'); ?></h3>
            <button class="wpal-modal-close">&times;</button>
        </div>
        <div class="wpal-modal-body">
            <!-- Content will be dynamically added -->
        </div>
        <div class="wpal-modal-footer">
            <button class="wpal-btn wpal-btn-secondary wpal-modal-cancel"><?php _e('Cancel', 'wp-activity-logger-pro'); ?></button>
            <button id="wpal-confirm-delete-btn" class="wpal-btn wpal-btn-danger"><?php _e('Delete', 'wp-activity-logger-pro'); ?></button>
        </div>
    </div>
</div>

<!-- IP Geolocation Modal -->
<div id="wpal-ip-modal" class="wpal-modal">
    <div class="wpal-modal-content wpal-modal-lg">
        <div class="wpal-modal-header">
            <h3><?php _e('IP Geolocation', 'wp-activity-logger-pro'); ?> <span id="wpal-ip-address-display"></span></h3>
            <button class="wpal-modal-close">&times;</button>
        </div>
        <div class="wpal-modal-body">
            <div class="wpal-ip-info-container">
                <div class="wpal-ip-map" id="wpal-ip-map"></div>
                <div class="wpal-ip-details" id="wpal-ip-details">
                    <div class="wpal-spinner"></div>
                    <p><?php _e('Loading geolocation data...', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
        </div>
        <div class="wpal-modal-footer">
            <button class="wpal-btn wpal-btn-secondary wpal-modal-cancel"><?php _e('Close', 'wp-activity-logger-pro'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle filters
    $('#wpal-toggle-filters').on('click', function() {
        $('#wpal-filters-body').slideToggle();
        $(this).find('svg').toggleClass('feather-chevron-down feather-chevron-up');
    });
    
    // IP Geolocation
    $('.wpal-ip-address').on('click', function() {
        const ip = $(this).data('ip');
        $('#wpal-ip-address-display').text(ip);
        $('#wpal-ip-modal').addClass('wpal-modal-show');
        
        // Load geolocation data
        $.ajax({
            url: wpal_admin_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wpal_get_ip_geolocation',
                nonce: wpal_admin_vars.nonce,
                ip: ip
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Display geolocation details
                    let html = '<div class="wpal-ip-info">';
                    html += '<h4>' + data.country + '</h4>';
                    html += '<table class="wpal-table">';
                    html += '<tr><th><?php _e('City', 'wp-activity-logger-pro'); ?></th><td>' + data.city + '</td></tr>';
                    html += '<tr><th><?php _e('Region', 'wp-activity-logger-pro'); ?></th><td>' + data.region + '</td></tr>';
                    html += '<tr><th><?php _e('Country', 'wp-activity-logger-pro'); ?></th><td>' + data.country + '</td></tr>';
                    html += '<tr><th><?php _e('Continent', 'wp-activity-logger-pro'); ?></th><td>' + data.continent + '</td></tr>';
                    html += '<tr><th><?php _e('Latitude', 'wp-activity-logger-pro'); ?></th><td>' + data.latitude + '</td></tr>';
                    html += '<tr><th><?php _e('Longitude', 'wp-activity-logger-pro'); ?></th><td>' + data.longitude + '</td></tr>';
                    html += '<tr><th><?php _e('ISP', 'wp-activity-logger-pro'); ?></th><td>' + data.isp + '</td></tr>';
                    html += '<tr><th><?php _e('Timezone', 'wp-activity-logger-pro'); ?></th><td>' + data.timezone + '</td></tr>';
                    html += '</table>';
                    html += '</div>';
                    
                    $('#wpal-ip-details').html(html);
                    
                    // Initialize map
                    if (data.latitude && data.longitude) {
                        initMap(data.latitude, data.longitude, data.country, data.city);
                    }
                    
                    // Update IP badges with country code
                    $('.wpal-ip-location-badge').each(function() {
                        if ($(this).closest('.wpal-ip-address').data('ip') === ip) {
                            $(this).text(data.country_code);
                        }
                    });
                } else {
                    $('#wpal-ip-details').html('<div class="wpal-alert wpal-alert-danger">' + response.data.message + '</div>');
                }
            },
            error: function() {
                $('#wpal-ip-details').html('<div class="wpal-alert wpal-alert-danger"><?php _e('Error loading geolocation data.', 'wp-activity-logger-pro'); ?></div>');
            }
        });
    });
    
    // Initialize map
    function initMap(lat, lng, country, city) {
        const mapDiv = document.getElementById('wpal-ip-map');
        
        // Check if Leaflet is available
        if (typeof L !== 'undefined') {
            // Create map
            const map = L.map(mapDiv).setView([lat, lng], 10);
            
            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            // Add marker
            L.marker([lat, lng]).addTo(map)
                .bindPopup(city + ', ' + country)
                .openPopup();
        } else {
            // Fallback if Leaflet is not available
            mapDiv.innerHTML = '<div class="wpal-alert wpal-alert-info"><?php _e('Map view is not available. Please install Leaflet.js for map visualization.', 'wp-activity-logger-pro'); ?></div>';
        }
    }
    
    // Archive log
    $(document).on('click', '.wpal-archive-log', function(e) {
        e.preventDefault();
        
        const logId = $(this).data('log-id');
        const logAction = $(this).data('log-action') || 'Unknown';
        
        // Show confirmation modal
        $('#wpal-confirm-modal .wpal-modal-body').html(`
            <div class="wpal-confirm-archive">
                <div class="wpal-confirm-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>
                </div>
                <h3 class="wpal-confirm-title"><?php _e('Archive Log Entry', 'wp-activity-logger-pro'); ?></h3>
                <p class="wpal-confirm-message"><?php _e('Are you sure you want to archive the log entry for', 'wp-activity-logger-pro'); ?> "${logAction}"?</p>
            </div>
        `);
        
        // Change button text and class
        $('#wpal-confirm-delete-btn')
            .text('<?php _e('Archive', 'wp-activity-logger-pro'); ?>')
            .removeClass('wpal-btn-danger')
            .addClass('wpal-btn-primary')
            .data('log-id', logId)
            .data('action', 'archive');
        
        // Show the modal
        $('#wpal-confirm-modal').addClass('wpal-modal-show');
    });
    
    // Handle confirm action (delete or archive)
    $(document).on('click', '#wpal-confirm-delete-btn', function(e) {
        e.preventDefault();
        
        const logId = $(this).data('log-id');
        const action = $(this).data('action') || 'delete';
        const $button = $(this);
        
        // Disable button and show loading
        $button.prop('disabled', true).html('<div class="wpal-spinner"></div> ' + (action === 'archive' ? '<?php _e('Archiving...', 'wp-activity-logger-pro'); ?>' : '<?php _e('Deleting...', 'wp-activity-logger-pro'); ?>'));
        
        $.ajax({
            url: wpal_admin_vars.ajax_url,
            type: 'POST',
            data: {
                action: action === 'archive' ? 'wpal_archive_log' : 'wpal_delete_log',
                nonce: wpal_admin_vars.nonce,
                log_id: logId
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    $('#wpal-confirm-modal').removeClass('wpal-modal-show');
                    
                    // Remove row from table
                    $('#wpal-logs-table')
                        .DataTable()
                        .row($('#log-row-' + logId))
                        .remove()
                        .draw();
                    
                    // Show success message
                    $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>')
                        .insertAfter('.wpal-dashboard-header')
                        .delay(3000)
                        .fadeOut(function() {
                            $(this).remove();
                        });
                } else {
                    // Show error message in modal
                    $('#wpal-confirm-modal .wpal-modal-body').html(`
                        <div class="wpal-alert wpal-alert-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <div>${response.data.message}</div>
                        </div>
                    `);
                    
                    // Re-enable button
                    $button.prop('disabled', false).text(action === 'archive' ? '<?php _e('Archive', 'wp-activity-logger-pro'); ?>' : '<?php _e('Delete', 'wp-activity-logger-pro'); ?>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error ' + (action === 'archive' ? 'archiving' : 'deleting') + ' log:', error);
                
                // Show error message in modal
                $('#wpal-confirm-modal .wpal-modal-body').html(`
                    <div class="wpal-alert wpal-alert-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <div>An error occurred while ${action === 'archive' ? 'archiving' : 'deleting'} the log. Please try again.</div>
                    </div>
                `);
                
                // Re-enable button
                $button.prop('disabled', false).text(action === 'archive' ? '<?php _e('Archive', 'wp-activity-logger-pro'); ?>' : '<?php _e('Delete', 'wp-activity-logger-pro'); ?>');
            }
        });
    });
});
</script>

<style>
/* Filter styles */
.wpal-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.wpal-filter-item {
    margin-bottom: 10px;
}

.wpal-filter-actions {
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

/* IP Geolocation styles */
.wpal-ip-address {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    color: #2271b1;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.wpal-ip-address:hover {
    color: #135e96;
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

.wpal-ip-info-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.wpal-ip-map {
    height: 300px;
    background-color: #f0f0f1;
    border-radius: 4px;
}

.wpal-ip-details {
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 4px;
}

.wpal-ip-info h4 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
}

.wpal-modal-lg .wpal-modal-content {
    max-width: 800px;
}

@media (max-width: 768px) {
    .wpal-ip-info-container {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
// Load Leaflet.js for maps
wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
?>
