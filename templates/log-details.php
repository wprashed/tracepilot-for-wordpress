<?php
/**
 * Log details template.
 */

if (!defined('ABSPATH')) {
    exit;
}

$log_id = isset($_POST['log_id']) ? absint($_POST['log_id']) : 0;
if (!$log_id) {
    echo '<p>' . esc_html__('Invalid log ID.', 'wp-activity-logger-pro') . '</p>';
    return;
}

global $wpdb;
WPAL_Helpers::init();
$table_name = WPAL_Helpers::$db_table;
$log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $log_id));

if (!$log) {
    echo '<p>' . esc_html__('Log not found.', 'wp-activity-logger-pro') . '</p>';
    return;
}

$context = !empty($log->context) ? json_decode($log->context, true) : array();
$settings = WPAL_Helpers::get_settings();
$window_hours = max(1, absint($settings['timeline_window_hours']));

$timeline = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT id, time, action, severity, description
        FROM $table_name
        WHERE id != %d
        AND (
            (user_id > 0 AND user_id = %d)
            OR
            (ip <> '' AND ip = %s)
        )
        AND time BETWEEN DATE_SUB(%s, INTERVAL %d HOUR) AND DATE_ADD(%s, INTERVAL %d HOUR)
        ORDER BY time ASC
        LIMIT 12",
        $log->id,
        absint($log->user_id),
        $log->ip,
        $log->time,
        $window_hours,
        $log->time,
        $window_hours
    )
);
?>

<div class="wpal-detail">
    <div class="wpal-detail-header">
        <div>
            <p class="wpal-eyebrow"><?php echo esc_html($log->action); ?></p>
            <h2><?php echo esc_html($log->description); ?></h2>
        </div>
        <div><?php echo WPAL_Helpers::get_severity_badge($log->severity); ?></div>
    </div>

    <div class="wpal-detail-grid">
        <div class="wpal-detail-card">
            <h3><?php esc_html_e('Event', 'wp-activity-logger-pro'); ?></h3>
            <dl>
                <dt><?php esc_html_e('Time', 'wp-activity-logger-pro'); ?></dt>
                <dd><?php echo esc_html(WPAL_Helpers::format_datetime($log->time)); ?></dd>
                <dt><?php esc_html_e('Action', 'wp-activity-logger-pro'); ?></dt>
                <dd><?php echo esc_html($log->action); ?></dd>
                <dt><?php esc_html_e('Object', 'wp-activity-logger-pro'); ?></dt>
                <dd><?php echo esc_html($log->object_name ? $log->object_name : '—'); ?></dd>
            </dl>
        </div>

        <div class="wpal-detail-card">
            <h3><?php esc_html_e('Actor', 'wp-activity-logger-pro'); ?></h3>
            <dl>
                <dt><?php esc_html_e('Username', 'wp-activity-logger-pro'); ?></dt>
                <dd><?php echo esc_html($log->username); ?></dd>
                <dt><?php esc_html_e('Role', 'wp-activity-logger-pro'); ?></dt>
                <dd><?php echo esc_html($log->user_role ? $log->user_role : '—'); ?></dd>
                <dt><?php esc_html_e('IP', 'wp-activity-logger-pro'); ?></dt>
                <dd><?php echo esc_html($log->ip ? $log->ip : '—'); ?></dd>
                <dt><?php esc_html_e('Browser', 'wp-activity-logger-pro'); ?></dt>
                <dd><?php echo esc_html($log->browser ? $log->browser : '—'); ?></dd>
            </dl>
            <div class="wpal-inline-actions" style="margin-top:14px;">
                <?php if (!empty($log->ip)) : ?>
                    <button type="button" class="wpal-btn wpal-btn-secondary wpal-block-ip" data-ip="<?php echo esc_attr($log->ip); ?>"><?php esc_html_e('Block IP', 'wp-activity-logger-pro'); ?></button>
                <?php endif; ?>
                <?php if (!empty($log->user_id)) : ?>
                    <button type="button" class="wpal-btn wpal-btn-secondary wpal-force-logout" data-user-id="<?php echo esc_attr($log->user_id); ?>"><?php esc_html_e('Force Logout', 'wp-activity-logger-pro'); ?></button>
                    <button type="button" class="wpal-btn wpal-btn-secondary wpal-reset-password" data-user-id="<?php echo esc_attr($log->user_id); ?>"><?php esc_html_e('Reset Password', 'wp-activity-logger-pro'); ?></button>
                    <button type="button" class="wpal-btn wpal-btn-danger wpal-delete-user-logs" data-user-id="<?php echo esc_attr($log->user_id); ?>"><?php esc_html_e('Delete User Logs', 'wp-activity-logger-pro'); ?></button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($context) && is_array($context)) : ?>
        <div class="wpal-detail-card">
            <h3><?php esc_html_e('Context', 'wp-activity-logger-pro'); ?></h3>
            <pre class="wpal-code-block"><?php echo esc_html(wp_json_encode($context, JSON_PRETTY_PRINT)); ?></pre>
        </div>
    <?php endif; ?>

    <div class="wpal-detail-card">
        <h3><?php echo esc_html(sprintf(__('Session timeline (%d hour window)', 'wp-activity-logger-pro'), $window_hours)); ?></h3>
        <?php if (empty($timeline)) : ?>
            <p><?php esc_html_e('No nearby events were found for this user or IP.', 'wp-activity-logger-pro'); ?></p>
        <?php else : ?>
            <div class="wpal-list">
                <?php foreach ($timeline as $entry) : ?>
                    <div class="wpal-list-row">
                        <div>
                            <strong><?php echo esc_html($entry->action); ?></strong>
                            <div class="wpal-list-subtext"><?php echo esc_html(WPAL_Helpers::format_datetime($entry->time)); ?></div>
                            <div class="wpal-list-subtext"><?php echo esc_html($entry->description); ?></div>
                        </div>
                        <div><?php echo WPAL_Helpers::get_severity_badge($entry->severity); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
