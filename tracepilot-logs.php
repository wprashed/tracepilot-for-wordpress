<?php
/**
 * Logs template.
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
TracePilot_Helpers::init();
$table_name = TracePilot_Helpers::$db_table;

$role_filter = isset($_GET['role_filter']) ? sanitize_text_field(wp_unslash($_GET['role_filter'])) : '';
$severity_filter = isset($_GET['severity_filter']) ? sanitize_text_field(wp_unslash($_GET['severity_filter'])) : '';
$action_filter = isset($_GET['action_filter']) ? sanitize_text_field(wp_unslash($_GET['action_filter'])) : '';
$date_from = isset($_GET['date_from']) ? sanitize_text_field(wp_unslash($_GET['date_from'])) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field(wp_unslash($_GET['date_to'])) : '';
$search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$site_filter = isset($_GET['site_id']) ? absint($_GET['site_id']) : 0;
$severity_display = array(
    'info' => __('Info', 'tracepilot'),
    'warning' => __('Warning', 'tracepilot'),
    'error' => __('Error', 'tracepilot'),
);
$logs = TracePilot_Helpers::get_logs(
    array(
        'role_filter' => $role_filter,
        'severity_filter' => $severity_filter,
        'action_filter' => $action_filter,
        'date_from' => $date_from,
        'date_to' => $date_to,
        'search' => $search,
        'site_id' => $site_filter,
    ),
    500
);
$roles = $wpdb->get_col("SELECT DISTINCT user_role FROM $table_name WHERE user_role <> '' ORDER BY user_role ASC");
$actions = $wpdb->get_col("SELECT DISTINCT action FROM $table_name WHERE action <> '' ORDER BY action ASC");
$sites = is_multisite() ? get_sites(array('number' => 200)) : array();

$severity_rows = $wpdb->get_results("SELECT severity, COUNT(*) AS total FROM $table_name GROUP BY severity");
$severity_labels = array();
$severity_values = array();
foreach ($severity_rows as $row) {
    $severity_labels[] = ucfirst($row->severity);
    $severity_values[] = (int) $row->total;
}
?>

<div class="wrap tracepilot-wrap">
    <section class="tracepilot-hero tracepilot-hero-compact">
        <div>
            <p class="tracepilot-eyebrow"><?php esc_html_e('Audit trail', 'tracepilot'); ?></p>
            <h1 class="tracepilot-page-title"><?php esc_html_e('Activity Logs', 'tracepilot'); ?></h1>
            <p class="tracepilot-hero-copy"><?php esc_html_e('Filter recent events, inspect full context, archive old entries, and remove noisy records when needed.', 'tracepilot'); ?></p>
        </div>
        <div class="tracepilot-hero-actions">
            <a class="tracepilot-btn tracepilot-btn-secondary" href="<?php echo esc_url(admin_url('admin.php?page=tracepilot-export')); ?>"><?php esc_html_e('Export', 'tracepilot'); ?></a>
            <a class="tracepilot-btn tracepilot-btn-secondary" href="<?php echo esc_url(admin_url('admin.php?page=tracepilot-archive')); ?>"><?php esc_html_e('Archive', 'tracepilot'); ?></a>
            <button type="button" id="tracepilot-delete-all-logs" class="tracepilot-btn tracepilot-btn-danger"><?php esc_html_e('Delete All', 'tracepilot'); ?></button>
        </div>
    </section>

    <section class="tracepilot-grid">
        <!-- Disable Group panel 
        <article class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Disable Group', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('A quick view of how noisy the event stream is.', 'tracepilot'); ?></p>
                </div>
            </div>
            <div class="tracepilot-chart-shell tracepilot-chart-shell-sm">
                <canvas id="tracepilot-logs-severity"></canvas>
            </div>
        </article>
-->
        <article class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Filters', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Narrow down the event stream quickly.', 'tracepilot'); ?></p>
                </div>
            </div>
            <form method="get" class="tracepilot-filter-grid tracepilot-filter-grid-logs">
                <input type="hidden" name="page" value="tracepilot-logs">
                <label class="tracepilot-field-span-2">
                    <span><?php esc_html_e('Search', 'tracepilot'); ?></span>
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" class="tracepilot-input" placeholder="<?php esc_attr_e('Username, action, description', 'tracepilot'); ?>">
                </label>
                <label>
                    <span><?php esc_html_e('Role', 'tracepilot'); ?></span>
                    <select name="role_filter" class="tracepilot-input">
                        <option value=""><?php esc_html_e('All roles', 'tracepilot'); ?></option>
                        <?php foreach ($roles as $role) : ?>
                            <option value="<?php echo esc_attr($role); ?>" <?php selected($role_filter, $role); ?>><?php echo esc_html($role); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e('Severity', 'tracepilot'); ?></span>
                    <select name="severity_filter" class="tracepilot-input">
                        <option value=""><?php esc_html_e('All severities', 'tracepilot'); ?></option>
                        <option value="info" <?php selected($severity_filter, 'info'); ?>><?php esc_html_e('Info', 'tracepilot'); ?></option>
                        <option value="warning" <?php selected($severity_filter, 'warning'); ?>><?php esc_html_e('Warning', 'tracepilot'); ?></option>
                        <option value="error" <?php selected($severity_filter, 'error'); ?>><?php esc_html_e('Error', 'tracepilot'); ?></option>
                    </select>
                </label>
                <label>
                    <span><?php esc_html_e('Action', 'tracepilot'); ?></span>
                    <select name="action_filter" class="tracepilot-input">
                        <option value=""><?php esc_html_e('All actions', 'tracepilot'); ?></option>
                        <?php foreach ($actions as $action) : ?>
                            <option value="<?php echo esc_attr($action); ?>" <?php selected($action_filter, $action); ?>><?php echo esc_html($action); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <?php if (is_multisite() && is_network_admin()) : ?>
                    <label>
                        <span><?php esc_html_e('Site', 'tracepilot'); ?></span>
                        <select name="site_id" class="tracepilot-input">
                            <option value="0"><?php esc_html_e('All sites', 'tracepilot'); ?></option>
                            <?php foreach ($sites as $site) : ?>
                                <option value="<?php echo esc_attr($site->blog_id); ?>" <?php selected($site_filter, (int) $site->blog_id); ?>><?php echo esc_html($site->blogname ? $site->blogname : $site->domain); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>
                <div class="tracepilot-date-grid tracepilot-field-span-2">
                    <label>
                        <span><?php esc_html_e('From', 'tracepilot'); ?></span>
                        <input type="text" name="date_from" value="<?php echo esc_attr($date_from); ?>" class="tracepilot-input tracepilot-datepicker" placeholder="<?php esc_attr_e('YYYY-MM-DD', 'tracepilot'); ?>">
                    </label>
                    <label>
                        <span><?php esc_html_e('To', 'tracepilot'); ?></span>
                        <input type="text" name="date_to" value="<?php echo esc_attr($date_to); ?>" class="tracepilot-input tracepilot-datepicker" placeholder="<?php esc_attr_e('YYYY-MM-DD', 'tracepilot'); ?>">
                    </label>
                </div>
                <div class="tracepilot-filter-actions">
                    <button type="submit" class="tracepilot-btn tracepilot-btn-primary"><?php esc_html_e('Apply Filters', 'tracepilot'); ?></button>
                    <a class="tracepilot-btn tracepilot-btn-secondary" href="<?php echo esc_url(admin_url('admin.php?page=tracepilot-logs')); ?>"><?php esc_html_e('Reset', 'tracepilot'); ?></a>
                </div>
            </form>
        </article>
        
    </section>

    <section class="tracepilot-panel">
        <div class="tracepilot-panel-head">
            <div>
                <h2><?php esc_html_e('Log Stream', 'tracepilot'); ?></h2>
                <p><?php echo esc_html(sprintf(_n('%d log shown', '%d logs shown', count($logs), 'tracepilot'), count($logs))); ?></p>
            </div>
        </div>


        <?php if ($search || $severity_filter || $action_filter || $role_filter || $date_from || $date_to || $site_filter) : ?>
            <div class="tracepilot-toolbar-pills">
                <?php if ($search) : ?><span class="tracepilot-meta-pill"><?php echo esc_html(sprintf(__('Search: %s', 'tracepilot'), $search)); ?></span><?php endif; ?>
                <?php if ($severity_filter) : ?><span class="tracepilot-meta-pill"><?php echo esc_html(sprintf(__('Severity: %s', 'tracepilot'), isset($severity_display[ $severity_filter ]) ? $severity_display[ $severity_filter ] : $severity_filter)); ?></span><?php endif; ?>
                <?php if ($action_filter) : ?><span class="tracepilot-meta-pill"><?php echo esc_html(sprintf(__('Action: %s', 'tracepilot'), $action_filter)); ?></span><?php endif; ?>
                <?php if ($role_filter) : ?><span class="tracepilot-meta-pill"><?php echo esc_html(sprintf(__('Role: %s', 'tracepilot'), $role_filter)); ?></span><?php endif; ?>
                <?php if ($date_from) : ?><span class="tracepilot-meta-pill"><?php echo esc_html(sprintf(__('From: %s', 'tracepilot'), $date_from)); ?></span><?php endif; ?>
                <?php if ($date_to) : ?><span class="tracepilot-meta-pill"><?php echo esc_html(sprintf(__('To: %s', 'tracepilot'), $date_to)); ?></span><?php endif; ?>
                <?php if ($site_filter && is_multisite() && is_network_admin()) : ?><span class="tracepilot-meta-pill"><?php echo esc_html(sprintf(__('Site ID: %d', 'tracepilot'), $site_filter)); ?></span><?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($logs)) : ?>
            <p><?php esc_html_e('No logs match the current filters.', 'tracepilot'); ?></p>
        <?php else : ?>
            <div class="tracepilot-stream">
                <?php foreach ($logs as $log) : ?>
                    <article class="tracepilot-stream-card">
                        <div class="tracepilot-stream-head">
                            <div class="tracepilot-stream-time">
                                <span class="tracepilot-stream-kicker"><?php esc_html_e('Recorded', 'tracepilot'); ?></span>
                                <strong><?php echo esc_html(TracePilot_Helpers::format_datetime($log->time)); ?></strong>
                            </div>
                            <div class="tracepilot-stream-head-meta">
                                <?php echo TracePilot_Helpers::get_severity_badge($log->severity); ?>
                                <?php if (!empty($log->site_label)) : ?>
                                    <span class="tracepilot-meta-pill"><?php echo esc_html($log->site_label); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="tracepilot-stream-body">
                            <div class="tracepilot-stream-primary">
                                <h3><?php echo esc_html($log->description); ?></h3>
                                <p class="tracepilot-stream-action"><?php echo esc_html($log->action); ?></p>
                            </div>

                            <aside class="tracepilot-stream-sidebar">
                                <div class="tracepilot-stream-meta-grid">
                                    <div class="tracepilot-stream-meta-item">
                                        <span><?php esc_html_e('User', 'tracepilot'); ?></span>
                                        <strong><?php echo esc_html($log->username ? $log->username : __('Guest', 'tracepilot')); ?></strong>
                                        <?php if (!empty($log->user_role)) : ?>
                                            <em><?php echo esc_html($log->user_role); ?></em>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tracepilot-stream-meta-item">
                                        <span><?php esc_html_e('IP Address', 'tracepilot'); ?></span>
                                        <strong><?php echo esc_html($log->ip ? TracePilot_Helpers::format_ip_for_display($log->ip) : '—'); ?></strong>
                                    </div>
                                    <div class="tracepilot-stream-meta-item">
                                        <span><?php esc_html_e('Event ID', 'tracepilot'); ?></span>
                                        <strong>#<?php echo esc_html($log->id); ?></strong>
                                    </div>
                                </div>

                                <div class="tracepilot-stream-actions">
                                    <button type="button" class="tracepilot-btn tracepilot-btn-secondary tracepilot-view-log" data-log-id="<?php echo esc_attr($log->id); ?>" data-site-id="<?php echo esc_attr(isset($log->site_id) ? $log->site_id : 0); ?>"><?php esc_html_e('View Details', 'tracepilot'); ?></button>
                                    <button type="button" class="tracepilot-btn tracepilot-btn-secondary tracepilot-archive-log" data-log-id="<?php echo esc_attr($log->id); ?>" data-site-id="<?php echo esc_attr(isset($log->site_id) ? $log->site_id : 0); ?>"><?php esc_html_e('Archive', 'tracepilot'); ?></button>
                                    <button type="button" class="tracepilot-btn tracepilot-btn-danger tracepilot-delete-log" data-log-id="<?php echo esc_attr($log->id); ?>" data-site-id="<?php echo esc_attr(isset($log->site_id) ? $log->site_id : 0); ?>"><?php esc_html_e('Delete', 'tracepilot'); ?></button>
                                </div>
                            </aside>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<div id="tracepilot-log-details-modal" class="tracepilot-modal">
    <div class="tracepilot-modal-dialog">
        <button type="button" class="tracepilot-modal-close" aria-label="<?php esc_attr_e('Close', 'tracepilot'); ?>">×</button>
        <div class="tracepilot-modal-body"></div>
    </div>
</div>
<div
    id="tracepilot-logs-severity-data"
    data-labels="<?php echo esc_attr(wp_json_encode($severity_labels)); ?>"
    data-values="<?php echo esc_attr(wp_json_encode($severity_values)); ?>"
    hidden
></div>
