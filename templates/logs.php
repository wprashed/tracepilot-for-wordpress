<?php
/**
 * Template for the logs page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
WPAL_Helpers::init();

// Get pagination parameters
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get filter parameters
$user_filter = isset($_GET['user']) ? sanitize_text_field($_GET['user']) : '';
$action_filter = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$severity_filter = isset($_GET['severity']) ? sanitize_text_field($_GET['severity']) : '';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

// Build query
$where = [];
$where_values = [];

if (!empty($user_filter)) {
    $where[] = "username LIKE %s";
    $where_values[] = '%' . $wpdb->esc_like($user_filter) . '%';
}

if (!empty($action_filter)) {
    $where[] = "action LIKE %s";
    $where_values[] = '%' . $wpdb->esc_like($action_filter) . '%';
}

if (!empty($severity_filter)) {
    $where[] = "severity = %s";
    $where_values[] = $severity_filter;
}

if (!empty($date_from)) {
    $where[] = "time >= %s";
    $where_values[] = $date_from . ' 00:00:00';
}

if (!empty($date_to)) {
    $where[] = "time <= %s";
    $where_values[] = $date_to . ' 23:59:59';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Count total items
$count_query = "SELECT COUNT(*) FROM " . WPAL_Helpers::$db_table . " " . $where_clause;
if (!empty($where_values)) {
    $count_query = $wpdb->prepare($count_query, $where_values);
}
$total_items = $wpdb->get_var($count_query);

// Get logs
$query = "SELECT * FROM " . WPAL_Helpers::$db_table . " " . $where_clause . " ORDER BY time DESC LIMIT %d OFFSET %d";
$query_args = array_merge($where_values, [$per_page, $offset]);
$logs = $wpdb->get_results($wpdb->prepare($query, $query_args));

// Get unique users, actions, and severities for filters
$users = $wpdb->get_col("SELECT DISTINCT username FROM " . WPAL_Helpers::$db_table . " ORDER BY username ASC");
$actions = $wpdb->get_col("SELECT DISTINCT action FROM " . WPAL_Helpers::$db_table . " ORDER BY action ASC");
$severities = $wpdb->get_col("SELECT DISTINCT severity FROM " . WPAL_Helpers::$db_table . " ORDER BY severity ASC");

// Calculate pagination
$total_pages = ceil($total_items / $per_page);
$base_url = admin_url('admin.php?page=wp-activity-logger-pro');

// Add filter parameters to pagination URL
if (!empty($user_filter)) {
    $base_url .= '&user=' . urlencode($user_filter);
}

if (!empty($action_filter)) {
    $base_url .= '&action=' . urlencode($action_filter);
}

if (!empty($severity_filter)) {
    $base_url .= '&severity=' . urlencode($severity_filter);
}

if (!empty($date_from)) {
    $base_url .= '&date_from=' . urlencode($date_from);
}

if (!empty($date_to)) {
    $base_url .= '&date_to=' . urlencode($date_to);
}
?>

<div class="wrap wpal-wrap">
    <h1 class="wp-heading-inline"><?php _e('Activity Logs', 'wp-activity-logger-pro'); ?></h1>
    
    <div class="wpal-card">
        <div class="wpal-card-header">
            <h2 class="wpal-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <?php _e('Filter Logs', 'wp-activity-logger-pro'); ?>
            </h2>
        </div>
        <div class="wpal-card-body">
            <form method="get" action="<?php echo admin_url('admin.php'); ?>">
                <input type="hidden" name="page" value="wp-activity-logger-pro">
                
                <div class="wpal-filter-section">
                    <div class="wpal-form-group">
                        <label for="date_from" class="wpal-form-label"><?php _e('Date From', 'wp-activity-logger-pro'); ?></label>
                        <input type="date" id="date_from" name="date_from" class="wpal-form-control" value="<?php echo esc_attr($date_from); ?>">
                    </div>
                    
                    <div class="wpal-form-group">
                        <label for="date_to" class="wpal-form-label"><?php _e('Date To', 'wp-activity-logger-pro'); ?></label>
                        <input type="date" id="date_to" name="date_to" class="wpal-form-control" value="<?php echo esc_attr($date_to); ?>">
                    </div>
                    
                    <div class="wpal-form-group">
                        <label for="user" class="wpal-form-label"><?php _e('User', 'wp-activity-logger-pro'); ?></label>
                        <select id="user" name="user" class="wpal-form-select">
                            <option value=""><?php _e('All Users', 'wp-activity-logger-pro'); ?></option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?php echo esc_attr($user); ?>" <?php selected($user_filter, $user); ?>><?php echo esc_html($user); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="wpal-form-group">
                        <label for="action" class="wpal-form-label"><?php _e('Action', 'wp-activity-logger-pro'); ?></label>
                        <select id="action" name="action" class="wpal-form-select">
                            <option value=""><?php _e('All Actions', 'wp-activity-logger-pro'); ?></option>
                            <?php foreach ($actions as $action) : ?>
                                <option value="<?php echo esc_attr($action); ?>" <?php selected($action_filter, $action); ?>><?php echo esc_html($action); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="wpal-form-group">
                        <label for="severity" class="wpal-form-label"><?php _e('Severity', 'wp-activity-logger-pro'); ?></label>
                        <select id="severity" name="severity" class="wpal-form-select">
                            <option value=""><?php _e('All Severities', 'wp-activity-logger-pro'); ?></option>
                            <?php foreach ($severities as $severity) : ?>
                                <option value="<?php echo esc_attr($severity); ?>" <?php selected($severity_filter, $severity); ?>><?php echo esc_html(ucfirst($severity)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="wpal-filter-actions">
                        <button type="submit" class="wpal-btn wpal-btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                            <?php _e('Filters', 'wp-activity-logger-pro'); ?>
                        </button>
                        
                        <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro'); ?>" class="wpal-btn wpal-btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            <?php _e('Reset', 'wp-activity-logger-pro'); ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="wpal-card wpal-mt-4">
        <div class="wpal-card-header wpal-d-flex wpal-justify-content-between wpal-align-items-center">
            <h2 class="wpal-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                <?php _e('Activity Logs', 'wp-activity-logger-pro'); ?>
                <span class="wpal-badge wpal-badge-info"><?php echo number_format($total_items); ?></span>
            </h2>
            <div class="wpal-d-flex wpal-gap-2">
                <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro-export'); ?>" class="wpal-btn wpal-btn-secondary wpal-btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <?php _e('Export Logs', 'wp-activity-logger-pro'); ?>
                </a>
                <button id="wpal-delete-all-logs" class="wpal-btn wpal-btn-danger wpal-btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    <?php _e('Delete All Logs', 'wp-activity-logger-pro'); ?>
                </button>
            </div>
        </div>
        <div class="wpal-card-body">
            <?php if (empty($logs)) : ?>
                <div class="wpal-alert wpal-alert-info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <?php _e('No logs found.', 'wp-activity-logger-pro'); ?>
                </div>
            <?php else : ?>
                <div class="wpal-table-responsive">
                    <table class="wpal-table wpal-table-striped" id="wpal-logs-table">
                        <thead>
                            <tr>
                                <th><?php _e('Time', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('User', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('Action', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('IP', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('Browser', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('Severity', 'wp-activity-logger-pro'); ?></th>
                                <th><?php _e('Actions', 'wp-activity-logger-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log) : ?>
                                <tr>
                                    <td><?php echo WPAL_Helpers::format_datetime($log->time); ?></td>
                                    <td>
                                        <?php if ($log->user_id > 0) : ?>
                                            <a href="<?php echo admin_url('user-edit.php?user_id=' . $log->user_id); ?>" class="wpal-user-link">
                                                <?php echo esc_html($log->username); ?>
                                            </a>
                                            <br><small class="wpal-text-muted"><?php echo esc_html($log->user_role); ?></small>
                                        <?php else : ?>
                                            <?php echo esc_html($log->username); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($log->action); ?></td>
                                    <td>
                                        <?php 
                                        // Fix for IP not showing
                                        $ip = !empty($log->ip) ? $log->ip : WPAL_Helpers::get_client_ip();
                                        echo esc_html($ip);
                                        ?>
                                    </td>
                                    <td><?php echo esc_html($log->browser); ?></td>
                                    <td>
                                        <?php 
                                        $severity_class = '';
                                        switch ($log->severity) {
                                            case 'info':
                                                $severity_class = 'success';
                                                break;
                                            case 'warning':
                                                $severity_class = 'warning';
                                                break;
                                            case 'error':
                                                $severity_class = 'danger';
                                                break;
                                            default:
                                                $severity_class = 'info';
                                        }
                                        ?>
                                        <span class="wpal-badge wpal-badge-<?php echo $severity_class; ?>"><?php echo esc_html(ucfirst($log->severity)); ?></span>
                                    </td>
                                    <td>
                                        <div class="wpal-d-flex wpal-gap-2">
                                            <button class="wpal-btn wpal-btn-info wpal-btn-sm wpal-view-details" data-id="<?php echo $log->id; ?>" data-context='<?php echo esc_attr($log->context); ?>' data-bs-toggle="tooltip" title="<?php _e('View Details', 'wp-activity-logger-pro'); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </button>
                                            <button class="wpal-btn wpal-btn-danger wpal-btn-sm wpal-delete-log" data-id="<?php echo $log->id; ?>" data-bs-toggle="tooltip" title="<?php _e('Delete Log', 'wp-activity-logger-pro'); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1) : ?>
                    <div class="wpal-pagination wpal-mt-3">
                        <ul class="wpal-pagination-list">
                            <?php if ($page > 1) : ?>
                                <li class="wpal-pagination-item">
                                    <a href="<?php echo esc_url($base_url . '&paged=' . ($page - 1)); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                    </a>
                                </li>
                            <?php else : ?>
                                <li class="wpal-pagination-item disabled">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                    </span>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $start_page + 4);
                            
                            if ($end_page - $start_page < 4 && $start_page > 1) {
                                $start_page = max(1, $end_page - 4);
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++) :
                            ?>
                                <li class="wpal-pagination-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                                    <?php if ($i === $page) : ?>
                                        <span><?php echo $i; ?></span>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url($base_url . '&paged=' . $i); ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages) : ?>
                                <li class="wpal-pagination-item">
                                    <a href="<?php echo esc_url($base_url . '&paged=' . ($page + 1)); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </li>
                            <?php else : ?>
                                <li class="wpal-pagination-item disabled">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (tooltipTriggerList.length) {
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
        
        // Initialize DataTable
        $('#wpal-logs-table').DataTable({
            "paging": false,
            "searching": false,
            "ordering": true,
            "order": [[0, 'desc']],
            "info": false,
            "responsive": true,
            "language": {
                "emptyTable": "No logs found",
                "zeroRecords": "No matching logs found"
            }
        });
        
        // View log details
        $('.wpal-view-details').on('click', function() {
            const context = $(this).data('context');
            let contextObj;
            
            try {
                contextObj = JSON.parse(context);
            } catch (e) {
                contextObj = {};
            }
            
            let html = '';
            
            if (Object.keys(contextObj).length === 0) {
                html = '<div class="wpal-alert wpal-alert-info">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>' +
                    'No additional details available for this log entry.' +
                    '</div>';
            } else {
                html = '<div class="wpal-log-details-table-container">' +
                    '<table class="wpal-log-details-table">';
                
                for (const key in contextObj) {
                    if (contextObj.hasOwnProperty(key)) {
                        html += '<tr>' +
                            '<th>' + key + '</th>' +
                            '<td>' + contextObj[key] + '</td>' +
                            '</tr>';
                    }
                }
                
                html += '</table></div>';
            }
            
            $('#wpal-log-details-content').html(html);
            const modal = new bootstrap.Modal(document.getElementById('wpal-log-details-modal'));
            modal.show();
        });
        
        // Delete log
        $('.wpal-delete-log').on('click', function() {
            if (confirm(wpal_admin_vars.confirm_delete)) {
                const logId = $(this).data('id');
                const row = $(this).closest('tr');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpal_delete_log',
                        nonce: wpal_admin_vars.delete_nonce,
                        log_id: logId
                    },
                    success: function(response) {
                        if (response.success) {
                            row.fadeOut(400, function() {
                                row.remove();
                                
                                // Show message if no logs left
                                if ($('#wpal-logs-table tbody tr').length === 0) {
                                    $('#wpal-logs-table').parent().html('<div class="wpal-alert wpal-alert-info">' +
                                        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>' +
                                        'No logs found.' +
                                        '</div>');
                                }
                            });
                        } else {
                            alert(response.data);
                        }
                    },
                    error: function() {
                        alert('Error deleting log entry.');
                    }
                });
            }
        });
        
        // Delete all logs
        $('#wpal-delete-all-logs').on('click', function() {
            if (confirm(wpal_admin_vars.confirm_delete_all)) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpal_delete_all_logs',
                        nonce: wpal_admin_vars.delete_nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data);
                        }
                    },
                    error: function() {
                        alert('Error deleting logs.');
                    }
                });
            }
        });
    });
</script>