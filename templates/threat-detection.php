<?php
/**
 * Threat detection template.
 */

if (!defined('ABSPATH')) {
    exit;
}

$options = WPAL_Helpers::get_settings();
$integrity = wp_activity_logger_pro()->file_integrity->get_baseline_status();
?>

<div class="wrap wpal-wrap">
    <section class="wpal-hero wpal-hero-compact">
        <div>
            <p class="wpal-eyebrow"><?php esc_html_e('Security monitoring', 'wp-activity-logger-pro'); ?></p>
            <h1 class="wpal-page-title"><?php esc_html_e('Threat Detection', 'wp-activity-logger-pro'); ?></h1>
            <p class="wpal-hero-copy"><?php esc_html_e('Analyze your audit trail for brute-force activity, unusual login patterns, suspicious file changes, and privilege escalation events.', 'wp-activity-logger-pro'); ?></p>
        </div>
        <div class="wpal-hero-actions">
            <button id="wpal-analyze-threats" class="wpal-btn wpal-btn-primary"><?php esc_html_e('Analyze Threats', 'wp-activity-logger-pro'); ?></button>
        </div>
    </section>

    <section class="wpal-grid wpal-grid-2">
        <article class="wpal-panel">
            <div class="wpal-panel-head">
                <div>
                    <h2><?php esc_html_e('Detection Rules', 'wp-activity-logger-pro'); ?></h2>
                    <p><?php esc_html_e('Choose which automated checks stay active as new logs are recorded.', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>

            <form id="wpal-threat-settings-form" class="wpal-form-stack">
                <label class="wpal-check-card">
                    <input type="checkbox" name="wpal_options[enable_threat_detection]" value="1" <?php checked($options['enable_threat_detection'], 1); ?>>
                    <span>
                        <strong><?php esc_html_e('Enable threat detection', 'wp-activity-logger-pro'); ?></strong>
                        <small><?php esc_html_e('Analyze new log entries as they are written.', 'wp-activity-logger-pro'); ?></small>
                    </span>
                </label>

                <label class="wpal-check-card">
                    <input type="checkbox" name="wpal_options[enable_threat_notifications]" value="1" <?php checked($options['enable_threat_notifications'], 1); ?>>
                    <span>
                        <strong><?php esc_html_e('Send threat notifications', 'wp-activity-logger-pro'); ?></strong>
                        <small><?php esc_html_e('Use your configured notification channels for serious detections.', 'wp-activity-logger-pro'); ?></small>
                    </span>
                </label>

                <div>
                    <span><?php esc_html_e('Threat types to monitor', 'wp-activity-logger-pro'); ?></span>
                    <div class="wpal-check-grid" style="margin-top:8px;">
                        <label class="wpal-check-card">
                            <input type="checkbox" name="wpal_options[monitor_failed_logins]" value="1" <?php checked($options['monitor_failed_logins'], 1); ?>>
                            <span><strong><?php esc_html_e('Failed login attacks', 'wp-activity-logger-pro'); ?></strong></span>
                        </label>
                        <label class="wpal-check-card">
                            <input type="checkbox" name="wpal_options[monitor_unusual_logins]" value="1" <?php checked($options['monitor_unusual_logins'], 1); ?>>
                            <span><strong><?php esc_html_e('Unusual login patterns', 'wp-activity-logger-pro'); ?></strong></span>
                        </label>
                        <label class="wpal-check-card">
                            <input type="checkbox" name="wpal_options[monitor_file_changes]" value="1" <?php checked($options['monitor_file_changes'], 1); ?>>
                            <span><strong><?php esc_html_e('Suspicious file changes', 'wp-activity-logger-pro'); ?></strong></span>
                        </label>
                        <label class="wpal-check-card">
                            <input type="checkbox" name="wpal_options[monitor_privilege_escalation]" value="1" <?php checked($options['monitor_privilege_escalation'], 1); ?>>
                            <span><strong><?php esc_html_e('Privilege escalation', 'wp-activity-logger-pro'); ?></strong></span>
                        </label>
                    </div>
                </div>

                <div class="wpal-inline-actions">
                    <button type="button" id="wpal-save-threat-settings" class="wpal-btn wpal-btn-secondary"><?php esc_html_e('Save Detection Settings', 'wp-activity-logger-pro'); ?></button>
                    <span id="wpal-threat-settings-feedback" class="wpal-form-feedback"></span>
                </div>
            </form>
        </article>

        <article class="wpal-panel">
            <div class="wpal-panel-head">
                <div>
                    <h2><?php esc_html_e('Threat Summary', 'wp-activity-logger-pro'); ?></h2>
                    <p><?php esc_html_e('Run an on-demand scan to populate the live results panel.', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>

            <div id="wpal-threat-summary" class="wpal-stats-grid" style="display:none; margin-bottom:14px;">
                <article class="wpal-stat-card">
                    <span class="wpal-stat-label"><?php esc_html_e('Total', 'wp-activity-logger-pro'); ?></span>
                    <strong id="wpal-total-threats" class="wpal-stat-value">0</strong>
                </article>
                <article class="wpal-stat-card">
                    <span class="wpal-stat-label"><?php esc_html_e('High', 'wp-activity-logger-pro'); ?></span>
                    <strong id="wpal-high-threats" class="wpal-stat-value">0</strong>
                </article>
                <article class="wpal-stat-card">
                    <span class="wpal-stat-label"><?php esc_html_e('Medium', 'wp-activity-logger-pro'); ?></span>
                    <strong id="wpal-medium-threats" class="wpal-stat-value">0</strong>
                </article>
                <article class="wpal-stat-card">
                    <span class="wpal-stat-label"><?php esc_html_e('Low', 'wp-activity-logger-pro'); ?></span>
                    <strong id="wpal-low-threats" class="wpal-stat-value">0</strong>
                </article>
            </div>

            <div id="wpal-threat-loading" class="wpal-note" style="display:none;">
                <?php esc_html_e('Analyzing activity logs for potential threats...', 'wp-activity-logger-pro'); ?>
            </div>

            <div id="wpal-no-threats" class="wpal-empty-panel" style="display:none;">
                <strong><?php esc_html_e('No threats detected', 'wp-activity-logger-pro'); ?></strong>
                <p><?php esc_html_e('The current log sample does not match any active detection rules.', 'wp-activity-logger-pro'); ?></p>
            </div>

            <div id="wpal-threat-results" style="display:none;">
                <div class="wpal-table-wrap">
                    <table class="wpal-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Severity', 'wp-activity-logger-pro'); ?></th>
                                <th><?php esc_html_e('Type', 'wp-activity-logger-pro'); ?></th>
                                <th><?php esc_html_e('Description', 'wp-activity-logger-pro'); ?></th>
                                <th><?php esc_html_e('IP', 'wp-activity-logger-pro'); ?></th>
                                <th><?php esc_html_e('Time', 'wp-activity-logger-pro'); ?></th>
                                <th><?php esc_html_e('Actions', 'wp-activity-logger-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="wpal-threats-table"></tbody>
                    </table>
                </div>
            </div>
        </article>
    </section>

    <section class="wpal-panel">
        <div class="wpal-panel-head">
            <div>
                <h2><?php esc_html_e('File Integrity', 'wp-activity-logger-pro'); ?></h2>
                <p><?php esc_html_e('Create a baseline of core, plugin, and theme files, then scan for new, deleted, or modified files.', 'wp-activity-logger-pro'); ?></p>
            </div>
            <div class="wpal-hero-actions">
                <button type="button" id="wpal-build-baseline" class="wpal-btn wpal-btn-secondary"><?php esc_html_e('Build Baseline', 'wp-activity-logger-pro'); ?></button>
                <button type="button" id="wpal-scan-integrity" class="wpal-btn wpal-btn-primary"><?php esc_html_e('Scan Integrity', 'wp-activity-logger-pro'); ?></button>
            </div>
        </div>
        <div class="wpal-note" id="wpal-integrity-status">
            <?php
            echo $integrity['exists']
                ? esc_html(sprintf(__('Baseline created %1$s with %2$d files.', 'wp-activity-logger-pro'), $integrity['created_at'], $integrity['count']))
                : esc_html__('No baseline exists yet.', 'wp-activity-logger-pro');
            ?>
        </div>
        <div id="wpal-integrity-results" class="wpal-list" style="margin-top:16px;"></div>
    </section>
</div>

<script>
jQuery(function($) {
    $('#wpal-save-threat-settings').on('click', function() {
        const feedback = $('#wpal-threat-settings-feedback');
        feedback.text('Saving...');

        const options = {
            enable_threat_detection: 0,
            enable_threat_notifications: 0,
            monitor_failed_logins: 0,
            monitor_unusual_logins: 0,
            monitor_file_changes: 0,
            monitor_privilege_escalation: 0
        };
        new window.FormData(document.getElementById('wpal-threat-settings-form')).forEach(function(value, key) {
            const match = key.match(/^wpal_options\[([^\]]+)\]$/);
            if (match) {
                options[match[1]] = value;
            }
        });

        $.post(ajaxurl, {
            action: 'wpal_save_settings',
            nonce: '<?php echo esc_js(wp_create_nonce('wpal_nonce')); ?>',
            wpal_options: options
        }).done(function(response) {
            feedback.text(response.success ? response.data.message : 'Unable to save settings.');
        });
    });

    $('#wpal-analyze-threats').on('click', function() {
        const button = $(this);
        button.prop('disabled', true).text('<?php echo esc_js(__('Analyzing...', 'wp-activity-logger-pro')); ?>');
        $('#wpal-threat-loading').show();
        $('#wpal-threat-results, #wpal-no-threats, #wpal-threat-summary').hide();

        $.post(ajaxurl, {
            action: 'wpal_analyze_threats',
            nonce: '<?php echo esc_js(wp_create_nonce('wpal_nonce')); ?>'
        }).done(function(response) {
            if (!response.success) {
                window.alert(response.data.message || 'Unable to analyze threats.');
                return;
            }

            const data = response.data;
            $('#wpal-total-threats').text(data.summary.total);
            $('#wpal-high-threats').text(data.summary.high);
            $('#wpal-medium-threats').text(data.summary.medium);
            $('#wpal-low-threats').text(data.summary.low);
            $('#wpal-threat-summary').show();

            if (!data.threats.length) {
                $('#wpal-no-threats').show();
                return;
            }

            const rows = data.threats.map(function(threat) {
                const badgeClass = threat.severity === 'high' ? 'danger' : (threat.severity === 'medium' ? 'warning' : 'info');
                const badge = '<span class="wpal-badge wpal-badge-' + badgeClass + '">' + threat.severity.charAt(0).toUpperCase() + threat.severity.slice(1) + '</span>';
                const label = threat.type.replace(/_/g, ' ');
                const userAction = threat.user_id ? '<button type="button" class="wpal-btn wpal-btn-secondary wpal-force-logout" data-user-id="' + threat.user_id + '"><?php echo esc_js(__('Force Logout', 'wp-activity-logger-pro')); ?></button>' : '';
                const ipAction = threat.ip ? '<button type="button" class="wpal-btn wpal-btn-secondary wpal-block-ip" data-ip="' + threat.ip + '"><?php echo esc_js(__('Block IP', 'wp-activity-logger-pro')); ?></button>' : '';
                return '<tr>' +
                    '<td>' + badge + '</td>' +
                    '<td>' + label + '</td>' +
                    '<td>' + threat.description + '</td>' +
                    '<td>' + (threat.ip || '—') + '</td>' +
                    '<td>' + (threat.last_attempt || threat.login_time || threat.time || '—') + '</td>' +
                    '<td class="wpal-table-actions">' + ipAction + userAction + '</td>' +
                '</tr>';
            });

            $('#wpal-threats-table').html(rows.join(''));
            $('#wpal-threat-results').show();
        }).always(function() {
            $('#wpal-threat-loading').hide();
            button.prop('disabled', false).text('<?php echo esc_js(__('Analyze Threats', 'wp-activity-logger-pro')); ?>');
        });
    });

    function renderIntegrity(response) {
        const list = $('#wpal-integrity-results').empty();
        if (!response.data.changes.length) {
            list.append('<div class="wpal-list-row"><div>' + response.data.message + '</div></div>');
            return;
        }

        response.data.changes.forEach(function(change) {
            list.append('<div class="wpal-list-row"><div><strong>' + change.type + '</strong><div class="wpal-list-subtext">' + change.path + '</div></div><div class="wpal-meta-pill">' + change.group + '</div></div>');
        });
    }

    $('#wpal-build-baseline').on('click', function() {
        $.post(ajaxurl, {
            action: 'wpal_build_file_baseline',
            nonce: '<?php echo esc_js(wp_create_nonce('wpal_nonce')); ?>'
        }).done(function(response) {
            if (response.success) {
                $('#wpal-integrity-status').text('Baseline created with ' + response.data.count + ' files.');
            }
        });
    });

    $('#wpal-scan-integrity').on('click', function() {
        $.post(ajaxurl, {
            action: 'wpal_scan_file_integrity',
            nonce: '<?php echo esc_js(wp_create_nonce('wpal_nonce')); ?>'
        }).done(function(response) {
            if (response.success) {
                renderIntegrity(response);
            }
        });
    });
});
</script>
