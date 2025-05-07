<?php
/**
 * Dashboard class for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Dashboard {
    /**
     * Constructor
     */
    public function __construct() {
        // Schedule cleanup
        if (!wp_next_scheduled('wpal_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'wpal_cleanup_logs');
        }
        
        // Add cleanup action
        add_action('wpal_cleanup_logs', array('WPAL_Helpers', 'clean_old_logs'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Activity Logger', 'wp-activity-logger-pro'),
            __('Activity Logger', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro',
            array($this, 'render_logs_page'),
            'dashicons-list-view',
            30
        );
        
        // Logs submenu
        add_submenu_page(
            'wp-activity-logger-pro',
            __('Activity Logs', 'wp-activity-logger-pro'),
            __('Activity Logs', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro',
            array($this, 'render_logs_page')
        );
        
        // Dashboard submenu
        add_submenu_page(
            'wp-activity-logger-pro',
            __('Dashboard', 'wp-activity-logger-pro'),
            __('Dashboard', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro-dashboard',
            array($this, 'render_dashboard_page')
        );
        
        // Export submenu
        add_submenu_page(
            'wp-activity-logger-pro',
            __('Export', 'wp-activity-logger-pro'),
            __('Export', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro-export',
            array($this, 'render_export_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'wp-activity-logger-pro',
            __('Settings', 'wp-activity-logger-pro'),
            __('Settings', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro-settings',
            array($this, 'render_settings_page')
        );
        
        // Diagnostics submenu
        add_submenu_page(
            'wp-activity-logger-pro',
            __('Diagnostics', 'wp-activity-logger-pro'),
            __('Diagnostics', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro-diagnostics',
            array($this, 'render_diagnostics_page')
        );
    }

    /**
     * Render logs page
     */
    public function render_logs_page() {
        include WPAL_PLUGIN_DIR . 'templates/logs.php';
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        include WPAL_PLUGIN_DIR . 'templates/dashboard.php';
    }

    /**
     * Render export page
     */
    public function render_export_page() {
        include WPAL_PLUGIN_DIR . 'templates/export.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        include WPAL_PLUGIN_DIR . 'templates/settings.php';
    }

    /**
     * Render diagnostics page
     */
    public function render_diagnostics_page() {
        include WPAL_PLUGIN_DIR . 'templates/diagnostics.php';
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wp-activity-logger-pro') === false) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style('wpal-admin', WPAL_PLUGIN_URL . 'assets/css/wpal-admin.css', array(), WPAL_VERSION);
        
        // Enqueue scripts
        wp_enqueue_script('wpal-admin', WPAL_PLUGIN_URL . 'assets/js/wpal-admin.js', array('jquery'), WPAL_VERSION, true);
        
        // Localize script
        wp_localize_script('wpal-admin', 'wpal_admin_vars', array(
            'nonce' => wp_create_nonce('wpal_nonce'),
            'delete_nonce' => wp_create_nonce('wpal_delete_nonce'),
            'confirm_delete' => __('Are you sure you want to delete this log entry?', 'wp-activity-logger-pro'),
            'confirm_delete_all' => __('Are you sure you want to delete all log entries? This action cannot be undone.', 'wp-activity-logger-pro'),
            'ajax_url' => admin_url('admin-ajax.php')
        ));
        
        // Enqueue jQuery UI for datepicker
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        
        // Enqueue DataTables
        wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css');
        wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), '1.11.5', true);
        
        // Enqueue DataTables Buttons
        wp_enqueue_style('datatables-buttons', 'https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css');
        wp_enqueue_script('datatables-buttons', 'https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js', array('datatables'), '2.2.2', true);
        wp_enqueue_script('datatables-buttons-html5', 'https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js', array('datatables-buttons'), '2.2.2', true);
        wp_enqueue_script('datatables-buttons-print', 'https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js', array('datatables-buttons'), '2.2.2', true);
        wp_enqueue_script('jszip', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', array(), '3.1.3', true);
        wp_enqueue_script('pdfmake', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', array(), '0.1.53', true);
        wp_enqueue_script('pdfmake-fonts', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', array('pdfmake'), '0.1.53', true);
        
        // Enqueue Chart.js
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js', array(), '3.7.1', true);
    }
}