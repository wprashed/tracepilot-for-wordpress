<?php
/**
 * Template for the diagnostics page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;

// Get system info
$wp_version = get_bloginfo('version');
$php_version = phpversion();
$mysql_version = $wpdb->db_version();
$server_software = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
$memory_limit = ini_get('memory_limit');
$max_execution_time = ini_get('max_execution_time');
$post_max_size = ini_get('post_max_size');
$upload_max_filesize = ini_get('upload_max_filesize');
$max_input_vars = ini_get('max_input_vars');

// Get plugin info
$plugin_data = get_plugin_data(WPAL_PLUGIN_FILE);
$plugin_version = $plugin_data['Version'];
$plugin_author = $plugin_data['Author'];
$plugin_uri = $plugin_data['PluginURI'];

// Get database info
global $wpdb;
WPAL_Helpers::init();
$table_name = WPAL_Helpers::$db_table;
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
$table_count = $table_exists ? $wpdb->get_var("SELECT COUNT(*) FROM $table_name") : 0;
$table_size = $table_exists ? $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = '$table_name'") : 0;
$table_size_formatted = $table_size ? size_format($table_size) : 'N/A';

// Check for issues
$issues = array();

// Check PHP version
if (version_compare($php_version, '7.0', '<')) {
    $issues[] = array(
        'type' => 'error',
        'message' => sprintf(__('PHP version %s is outdated. We recommend using PHP 7.0 or higher.', 'wp-activity-logger-pro'), $php_version)
    );
}

// Check MySQL version
if (version_compare($mysql_version, '5.6', '<')) {
    $issues[] = array(
        'type' => 'warning',
        'message' => sprintf(__('MySQL version %s is outdated. We recommend using MySQL 5.6 or higher.', 'wp-activity-logger-pro'), $mysql_version)
    );
}

// Check memory limit
$memory_limit_bytes = wp_convert_hr_to_bytes($memory_limit);
if ($memory_limit_bytes < 64 * 1024 * 1024) {
    $issues[] = array(
        'type' => 'warning',
        'message' => sprintf(__('Memory limit is set to %s. We recommend at least 64M.', 'wp-activity-logger-pro'), $memory_limit)
    );
}

// Check max execution time
if ($max_execution_time < 30 && $max_execution_time != 0) {
    $issues[] = array(
        'type' => 'warning',
        'message' => sprintf(__('Max execution time is set to %s seconds. We recommend at least 30 seconds.', 'wp-activity-logger-pro'), $max_execution_time)
    );
}

// Check if table exists
if (!$table_exists) {
    $issues[] = array(
        'type' => 'error',
        'message' => sprintf(__('Database table %s does not exist. Please deactivate and reactivate the plugin.', 'wp-activity-logger-pro'), $table_name)
    );
}

// Run diagnostics test
if (isset($_POST['wpal_run_diagnostics'])) {
    // Verify nonce
    check_admin_referer('wpal_diagnostics_nonce');
    
    // Test database connection
    $db_test = $wpdb->query("SELECT 1");
    if ($db_test === false) {
        $issues[] = array(
            'type' => 'error',
            'message' => __('Database connection test failed.', 'wp-activity-logger-pro')
        );
    }
    
    // Test log writing
    $log_test = WPAL_Helpers::log_activity(
        'diagnostics_test',
        __('Diagnostics test log entry', 'wp-activity-logger-pro'),
        'info'
    );
    if (!$log_test) {
        $issues[] = array(
            'type' => 'error',
            'message' => __('Log writing test failed.', 'wp-activity-logger-pro')
        );
    }
    
    // Show success message
    if (empty($issues)) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Diagnostics tests completed successfully.', 'wp-activity-logger-pro') . '</p></div>';
    }
}
?>

<div class="wrap wpal-wrap">
    <div class="wpal-dashboard-header">
        <h1 class="wpal-dashboard-title"><?php _e('Diagnostics', 'wp-activity-logger-pro'); ?></h1>
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
    
    <?php if (!empty($issues)) : ?>
        <div class="wpal-widget wpal-mb-4">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <?php _e('Issues Detected', 'wp-activity-logger-pro'); ?>
                </h3>
            </div>
            <div class="wpal-widget-body">
                <?php foreach ($issues as $issue) : ?>
                    <div class="wpal-alert wpal-alert-<?php echo $issue['type'] === 'error' ? 'danger' : $issue['type']; ?>">
                        <?php if ($issue['type'] === 'error') : ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-octagon"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <?php else : ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <?php endif; ?>
                        <div><?php echo $issue['message']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="wpal-widget wpal-mb-4">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                <?php _e('Run Diagnostics Tests', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <p><?php _e('Run diagnostics tests to check for common issues with the plugin.', 'wp-activity-logger-pro'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('wpal_diagnostics_nonce'); ?>
                <button type="submit" name="wpal_run_diagnostics" class="wpal-btn wpal-btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-play"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                    <?php _e('Run Diagnostics', 'wp-activity-logger-pro'); ?>
                </button>
            </form>
        </div>
    </div>
    
    <div class="wpal-widget">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <?php _e('System Information', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <div class="wpal-table-responsive">
                <table class="wpal-table">
                    <tbody>
                        <tr>
                            <th><?php _e('WordPress Version', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($wp_version); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('PHP Version', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($php_version); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('MySQL Version', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($mysql_version); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Server Software', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($server_software); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Memory Limit', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($memory_limit); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Max Execution Time', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($max_execution_time); ?> <?php _e('seconds', 'wp-activity-logger-pro'); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Post Max Size', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($post_max_size); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Upload Max Filesize', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($upload_max_filesize); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Max Input Vars', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($max_input_vars); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="wpal-widget wpal-mt-4">
        <div class="wpal-widget-header">
            <h3 class="wpal-widget-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-package"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                <?php _e('Plugin Information', 'wp-activity-logger-pro'); ?>
            </h3>
        </div>
        <div class="wpal-widget-body">
            <div class="wpal-table-responsive">
                <table class="wpal-table">
                    <tbody>
                        <tr>
                            <th><?php _e('Plugin Version', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($plugin_version); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Plugin Author', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($plugin_author); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Plugin URI', 'wp-activity-logger-pro'); ?></th>
                            <td><a href="<?php echo esc_url($plugin_uri); ?>" target="_blank"><?php echo esc_html($plugin_uri); ?></a></td>
                        </tr>
                        <tr>
                            <th><?php _e('Database Table', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($table_name); ?> (<?php echo $table_exists ? __('Exists', 'wp-activity-logger-pro') : __('Does Not Exist', 'wp-activity-logger-pro'); ?>)</td>
                        </tr>
                        <tr>
                            <th><?php _e('Log Count', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo number_format($table_count); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Database Size', 'wp-activity-logger-pro'); ?></th>
                            <td><?php echo esc_html($table_size_formatted); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>