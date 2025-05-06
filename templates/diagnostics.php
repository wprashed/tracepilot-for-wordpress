<?php
/**
* Template for the diagnostics page
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get WordPress and PHP info
global $wpdb; // Make sure to declare $wpdb as global
$wp_version = get_bloginfo('version');
$php_version = phpversion();
$mysql_version = $wpdb->db_version();
$server_software = $_SERVER['SERVER_SOFTWARE'];
$memory_limit = ini_get('memory_limit');
$upload_max_filesize = ini_get('upload_max_filesize');
$post_max_size = ini_get('post_max_size');
$max_execution_time = ini_get('max_execution_time');

// Get plugin info
$plugin_data = get_plugin_data(WPAL_PLUGIN_FILE);
$plugin_version = $plugin_data['Version'];

// Get database table info
WPAL_Helpers::init();
$table_name = WPAL_Helpers::$db_table;
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
$logs_count = $table_exists ? $wpdb->get_var("SELECT COUNT(*) FROM $table_name") : 0;

// Get log directory info
$upload_dir = wp_upload_dir();
$log_dir = $upload_dir['basedir'] . '/wpal-logs';
$log_dir_exists = file_exists($log_dir);
$log_dir_writable = $log_dir_exists && is_writable($log_dir);

// Get active plugins
$active_plugins = get_option('active_plugins');
$active_plugins_count = count($active_plugins);
?>

<div class="wrap wpal-wrap">
    <h1 class="wp-heading-inline"><?php _e('Activity Logger Diagnostics', 'wp-activity-logger-pro'); ?></h1>
    
    <div class="wpal-diagnostics-container wpal-mt-4">
        <div class="wpal-diagnostics-header">
            <button id="wpal-run-diagnostics" class="wpal-btn wpal-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                <?php _e('Run Diagnostics', 'wp-activity-logger-pro'); ?>
            </button>
        </div>
        
        <div class="wpal-diagnostics-grid">
            <div class="wpal-diagnostics-card">
                <div class="wpal-diagnostics-card-header">
                    <h2 class="wpal-diagnostics-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <?php _e('System Information', 'wp-activity-logger-pro'); ?>
                    </h2>
                </div>
                <div class="wpal-diagnostics-card-body">
                    <table class="wpal-table wpal-table-bordered">
                        <tbody>
                            <tr>
                                <td class="wpal-table-label"><?php _e('WordPress Version', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo esc_html($wp_version); ?></td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('PHP Version', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo esc_html($php_version); ?></td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('MySQL Version', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo esc_html($mysql_version); ?></td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Server Software', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo esc_html($server_software); ?></td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Memory Limit', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo esc_html($memory_limit); ?></td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Upload Max Filesize', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo esc_html($upload_max_filesize); ?></td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Post Max Size', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo esc_html($post_max_size); ?></td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Max Execution Time', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo esc_html($max_execution_time); ?> <?php _e('seconds', 'wp-activity-logger-pro'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="wpal-diagnostics-card">
                <div class="wpal-diagnostics-card-header">
                    <h2 class="wpal-diagnostics-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-package"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        <?php _e('Plugin Information', 'wp-activity-logger-pro'); ?>
                    </h2>
                </div>
                <div class="wpal-diagnostics-card-body">
                    <table class="wpal-table wpal-table-bordered">
                        <tbody>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Plugin Version', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo esc_html($plugin_version); ?></td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Database Table', 'wp-activity-logger-pro'); ?></td>
                                <td>
                                    <?php if ($table_exists) : ?>
                                        <span class="wpal-badge wpal-badge-success">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <?php _e('Exists', 'wp-activity-logger-pro'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="wpal-badge wpal-badge-danger">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            <?php _e('Missing', 'wp-activity-logger-pro'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Logs Count', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo number_format($logs_count); ?></td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Log Directory', 'wp-activity-logger-pro'); ?></td>
                                <td>
                                    <?php if ($log_dir_exists) : ?>
                                        <span class="wpal-badge wpal-badge-success">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <?php _e('Exists', 'wp-activity-logger-pro'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="wpal-badge wpal-badge-danger">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            <?php _e('Missing', 'wp-activity-logger-pro'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Log Directory Writable', 'wp-activity-logger-pro'); ?></td>
                                <td>
                                    <?php if ($log_dir_writable) : ?>
                                        <span class="wpal-badge wpal-badge-success">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <?php _e('Yes', 'wp-activity-logger-pro'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="wpal-badge wpal-badge-danger">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            <?php _e('No', 'wp-activity-logger-pro'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="wpal-table-label"><?php _e('Active Plugins', 'wp-activity-logger-pro'); ?></td>
                                <td><?php echo number_format($active_plugins_count); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="wpal-diagnostics-results wpal-mt-4" id="wpal-diagnostics-results"></div>
    </div>
</div>