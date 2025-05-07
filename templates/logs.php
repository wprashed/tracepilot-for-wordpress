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

// Get filters
$filter_user = isset($_GET['user']) ? sanitize_text_field($_GET['user']) : '';
$filter_action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$filter_severity = isset($_GET['severity']) ? sanitize_text_field($_GET['severity']) : '';
$filter_date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$filter_date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

// Build query
$query = "SELECT * FROM $table_name WHERE 1=1";
$query_args = array();

if ($filter_user) {
    $query .= " AND username LIKE %s";
    $query_args[] = '%' . $wpdb->esc_like($filter_user) . '%';
}

if ($filter_action) {
    $query .= " AND action LIKE %s";
    $query_args[] = '%' . $wpdb->esc_like($filter_action) . '%';
}

if ($filter_severity) {
    $query .= " AND severity = %s";
    $query_args[] = $filter_severity;
}

if ($filter_date_from) {
    $query .= " AND time >= %s";
    $query_args[] = $filter_date_from . ' 00:00:00';
}

if ($filter_date_to) {
    $query .= " AND time <= %s";
    $query_args[] = $filter_date_to . ' 23:59:59';
}

// Prepare query if there are arguments
if (!empty($query_args)) {
    $query = $wpdb->prepare($query, $query_args);
}

// Get logs
$logs = $wpdb->get_results($query);

// Get unique users and actions for filters
$users = $wpdb->get_col("SELECT DISTINCT username FROM $table_name ORDER BY username ASC");
$actions = $wpdb->get_col("SELECT DISTINCT action FROM $table_name ORDER BY action ASC");
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
            <button id="wpal-delete-all-logs" class="wpal-btn wpal-btn-outline-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                <?php _e('Delete All', 'wp-activity-logger-pro'); ?>
            </button>
        </div>
    </div>
    
    <div class="wpal-widget wpal-mb-4">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <?php _e('Filter Logs', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <form method="get" action="<?php echo admin_url('admin.php'); ?>" class="wpal-d-flex wpal-flex-wrap">
                <input type="hidden" name="page" value="wp-activity-logger-pro">
                
                <div class="wpal-form-group" style="margin-right: 1rem;">
                    <label for="user" class="wpal-form-label"><?php _e('User', 'wp-activity-logger-pro'); ?></label>
                    <select name="user" id="user" class="wpal-form-control">
                        <option value=""><?php _e('All Users', 'wp-activity-logger-pro'); ?></option>
                        <?php foreach ($users as $user) : ?>
                            <option value="<?php echo esc_attr($user); ?>" <?php selected($filter_user, $user); ?>><?php echo esc_html($user); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="wpal-form-group" style="margin-right: 1rem;">
                    <label for="action" class="wpal-form-label"><?php _e('Action', 'wp-activity-logger-pro'); ?></label>
                    <select name="action" id="action" class="wpal-form-control">
                        <option value=""><?php _e('All Actions', 'wp-activity-logger-pro'); ?></option>
                        <?php foreach ($actions as $action) : ?>
                            <option value="<?php echo esc_attr($action); ?>" <?php selected($filter_action, $action); ?>><?php echo esc_html($action); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="wpal-form-group" style="margin-right: 1rem;">
                    <label for="severity" class="wpal-form-label"><?php _e('Severity', 'wp-activity-logger-pro'); ?></label>
                    <select name="severity" id="severity" class="wpal-form-control">
                        <option value=""><?php _e('All Severities', 'wp-activity-logger-pro'); ?></option>
                        <option value="info" <?php selected($filter_severity, 'info'); ?>><?php _e('Info', 'wp-activity-logger-pro'); ?></option>
                        <option value="warning" <?php selected($filter_severity, 'warning'); ?>><?php _e('Warning', 'wp-activity-logger-pro'); ?></option>
                        <option value="error" <?php selected($filter_severity, 'error'); ?>><?php _e('Error', 'wp-activity-logger-pro'); ?></option>
                    </select>
                </div>
                
                <div class="wpal-form-group" style="margin-right: 1rem;">
                    <label for="date_from" class="wpal-form-label"><?php _e('Date From', 'wp-activity-logger-pro'); ?></label>
                    <input type="text" name="date_from" id="date_from" class="wpal-form-control wpal-datepicker" value="<?php echo esc_attr($filter_date_from); ?>" placeholder="YYYY-MM-DD">
                </div>
                
                <div class="wpal-form-group" style="margin-right: 1rem;">
                    <label for="date_to" class="wpal-form-label"><?php _e('Date To', 'wp-activity-logger-pro'); ?></label>
                    <input type="text" name="date_to" id="date_to" class="wpal-form-control wpal-datepicker" value="<?php echo esc_attr($filter_date_to); ?>" placeholder="YYYY-MM-DD">
                </div>
                
                <div class="wpal-form-group" style="align-self: flex-end;">
                    <button type="submit" class="wpal-btn wpal-btn-sm wpal-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <?php _e('Filter', 'wp-activity-logger-pro'); ?>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro'); ?>" class="wpal-btn wpal-btn-sm wpal-btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        <?php _e('Reset', 'wp-activity-logger-pro'); ?>
                    </a>
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
            <div class="wpal-table-responsive">
                <table id="wpal-logs-table" class="wpal-table wpal-table-striped">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('User', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('Action', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('Severity', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('Time', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('IP', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('Actions', 'wp-activity-logger-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)) : ?>
                            <tr>
                                <td colspan="7" class="wpal-text-center"><?php _e('No logs found.', 'wp-activity-logger-pro'); ?></td>
                            </tr>
                        <?php else : ?>
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
                                    <td><?php echo esc_html($log->id); ?></td>
                                    <td><?php echo esc_html($log->username); ?></td>
                                    <td><?php echo esc_html($log->action); ?></td>
                                    <td><?php echo $severity_badge; ?></td>
                                    <td><?php echo WPAL_Helpers::format_datetime($log->time); ?></td>
                                    <td><?php echo esc_html($log->ip); ?></td>
                                    <td>
                                        <div class="wpal-d-flex">
                                            <button type="button" class="wpal-btn wpal-btn-sm wpal-btn-icon wpal-btn-secondary wpal-view-log" data-log-id="<?php echo esc_attr($log->id); ?>" title="<?php _e('View Details', 'wp-activity-logger-pro'); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </button>
                                            <button type="button" class="wpal-btn wpal-btn-sm wpal-btn-icon wpal-btn-danger wpal-delete-log" data-log-id="<?php echo esc_attr($log->id); ?>" data-nonce="<?php echo wp_create_nonce('wpal_delete_log_' . $log->id); ?>" title="<?php _e('Delete', 'wp-activity-logger-pro'); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div id="wpal-log-details-modal" class="wpal-modal">
    <div class="wpal-modal-content">
        <div class="wpal-modal-header">
            <h3><?php _e('Log Details', 'wp-activity-logger-pro'); ?></h3>
            <button type="button" class="wpal-modal-close">&times;</button>
        </div>
        <div class="wpal-modal-body"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize DataTables
    if ($.fn.dataTable && !$.fn.dataTable.isDataTable('#wpal-logs-table')) {
        $('#wpal-logs-table').DataTable({
            order: [[4, 'desc']], // Sort by time column (index 4) in descending order
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            language: {
                search: '<span class="screen-reader-text">Search logs:</span> ',
                searchPlaceholder: 'Search logs...',
                info: 'Showing _START_ to _END_ of _TOTAL_ logs',
                lengthMenu: 'Show _MENU_ logs per page'
            }
        });
    }
    
    // View log details
    $(document).on('click', '.wpal-view-log', function(e) {
        e.preventDefault();
        
        const logId = $(this).data('log-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpal_get_log_details',
                nonce: wpal_admin_vars.nonce,
                log_id: logId
            },
            success: function(response) {
                // Set modal content and show
                $('#wpal-log-details-modal .wpal-modal-body').html(response);
                $('#wpal-log-details-modal').addClass('wpal-modal-show');
            }
        });
    });
    
    // Close modal on click
    $(document).on('click', '.wpal-modal-close', function() {
        $('#wpal-log-details-modal').removeClass('wpal-modal-show');
    });
    
    // Close modal on outside click
    $(window).on('click', function(e) {
        if ($(e.target).is('#wpal-log-details-modal')) {
            $('#wpal-log-details-modal').removeClass('wpal-modal-show');
        }
    });
    
    // Delete log entry
    $(document).on('click', '.wpal-delete-log', function(e) {
        e.preventDefault();
        
        if (!confirm(wpal_admin_vars.confirm_delete)) {
            return;
        }
        
        const $row = $(this).closest('tr');
        const logId = $(this).data('log-id');
        const nonce = $(this).data('nonce');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpal_delete_log',
                log_id: logId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(300, function() {
                        // If using DataTables, we need to remove the row properly
                        if ($.fn.dataTable && $.fn.dataTable.isDataTable('#wpal-logs-table')) {
                            const table = $('#wpal-logs-table').DataTable();
                            table.row($row).remove().draw();
                        } else {
                            $row.remove();
                        }
                    });
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('An error occurred while deleting the log.');
            }
        });
    });
    
    // Delete all logs
    $('#wpal-delete-all-logs').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(wpal_admin_vars.confirm_delete_all)) {
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpal_delete_all_logs',
                nonce: wpal_admin_vars.delete_nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('An error occurred while deleting all logs.');
            }
        });
    });
    
    // Initialize date pickers
    if ($.fn.datepicker) {
        $('.wpal-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: '0'
        });
    }
});
</script>