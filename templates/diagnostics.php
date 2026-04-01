<?php
/**
 * Diagnostics template.
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$wp_version = get_bloginfo('version');
$php_version = phpversion();
$mysql_version = $wpdb->db_version();
$server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '';
$memory_limit = ini_get('memory_limit');
$max_execution_time = ini_get('max_execution_time');
$post_max_size = ini_get('post_max_size');
$upload_max_filesize = ini_get('upload_max_filesize');
$max_input_vars = ini_get('max_input_vars');

$plugin_data = get_plugin_data(WPAL_PLUGIN_FILE);
WPAL_Helpers::init();
$table_name = WPAL_Helpers::$db_table;
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
$table_count = $table_exists ? (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name") : 0;
$table_size = $table_exists ? $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = '$table_name'") : 0;

$ip_details = WPAL_Helpers::get_ip_details();
$proxy_chain = !empty($ip_details['raw_headers']['HTTP_X_FORWARDED_FOR']) ? $ip_details['raw_headers']['HTTP_X_FORWARDED_FOR'] : '';

$wp_debug = defined('WP_DEBUG') && WP_DEBUG;
$wp_debug_log = defined('WP_DEBUG_LOG') ? WP_DEBUG_LOG : false;
$debug_log_enabled = (true === $wp_debug_log || (is_string($wp_debug_log) && '' !== $wp_debug_log));
$debug_log_path = WP_CONTENT_DIR . '/debug.log';
if (is_string($wp_debug_log) && '' !== $wp_debug_log) {
    $debug_log_path = $wp_debug_log;
}

$debug_log_exists = file_exists($debug_log_path);
$debug_log_readable = $debug_log_exists && is_readable($debug_log_path);
$debug_log_size = $debug_log_exists ? filesize($debug_log_path) : 0;

$read_debug_tail = static function($path, $max_bytes = 65536, $max_lines = 40) {
    if (!file_exists($path) || !is_readable($path)) {
        return '';
    }

    $size = filesize($path);
    if (false === $size || 0 === $size) {
        return '';
    }

    $handle = fopen($path, 'rb');
    if (!$handle) {
        return '';
    }

    $seek = min($max_bytes, $size);
    fseek($handle, -$seek, SEEK_END);
    $contents = fread($handle, $seek);
    fclose($handle);

    if (false === $contents) {
        return '';
    }

    $contents = ltrim($contents);
    $lines = preg_split('/\r\n|\r|\n/', $contents);
    $lines = array_slice(array_filter($lines, 'strlen'), -$max_lines);

    return implode("\n", $lines);
};

$debug_log_tail = $debug_log_readable ? $read_debug_tail($debug_log_path) : '';

$issues = array();

if (version_compare($php_version, '7.4', '<')) {
    $issues[] = array(
        'title' => __('Old PHP Version', 'wp-activity-logger-pro'),
        'summary' => __('Your server is running an older PHP version, which can cause compatibility and performance problems.', 'wp-activity-logger-pro'),
        'solution' => __('Ask your hosting provider to upgrade PHP to 8.0 or newer.', 'wp-activity-logger-pro'),
    );
}

if (!$table_exists) {
    $issues[] = array(
        'title' => __('Missing Log Table', 'wp-activity-logger-pro'),
        'summary' => __('The activity log table is missing, so new events may not be saved at all.', 'wp-activity-logger-pro'),
        'solution' => __('Deactivate and reactivate the plugin once to recreate its database tables.', 'wp-activity-logger-pro'),
    );
}

if (wp_convert_hr_to_bytes($memory_limit) < 64 * 1024 * 1024) {
    $issues[] = array(
        'title' => __('Low PHP Memory', 'wp-activity-logger-pro'),
        'summary' => __('Your PHP memory limit is low for charts, exports, and heavier admin screens.', 'wp-activity-logger-pro'),
        'solution' => __('Raise the WordPress memory limit to at least 128M if your host allows it.', 'wp-activity-logger-pro'),
    );
}

if (empty($ip_details['ip'])) {
    $issues[] = array(
        'title' => __('Visitor IP Not Detected', 'wp-activity-logger-pro'),
        'summary' => __('The plugin could not find a valid client IP from the current request headers.', 'wp-activity-logger-pro'),
        'solution' => __('If you use Cloudflare, a proxy, or a load balancer, make sure it forwards a real client IP header such as CF-Connecting-IP, X-Forwarded-For, or X-Real-IP.', 'wp-activity-logger-pro'),
    );
} elseif ('Remote Address' === $ip_details['source'] && !$ip_details['is_public']) {
    $issues[] = array(
        'title' => __('Only Server or Private IP Found', 'wp-activity-logger-pro'),
        'summary' => __('The plugin only sees a private or server-side address, which usually means the real client IP is being hidden by a proxy.', 'wp-activity-logger-pro'),
        'solution' => __('Configure your proxy or CDN to pass the original visitor IP to PHP. Most setups use CF-Connecting-IP, True-Client-IP, X-Forwarded-For, or X-Real-IP.', 'wp-activity-logger-pro'),
    );
}

if (!$wp_debug) {
    $issues[] = array(
        'title' => __('WordPress Debug Mode Is Off', 'wp-activity-logger-pro'),
        'summary' => __('Debug logging is disabled, so PHP notices and plugin errors will not be collected in a WordPress log file.', 'wp-activity-logger-pro'),
        'solution' => __('Add define(\'WP_DEBUG\', true); and define(\'WP_DEBUG_LOG\', true); to wp-config.php while troubleshooting.', 'wp-activity-logger-pro'),
    );
} elseif (!$debug_log_enabled) {
    $issues[] = array(
        'title' => __('Debug Log Output Is Off', 'wp-activity-logger-pro'),
        'summary' => __('WordPress debug mode is enabled, but writing to debug.log is disabled.', 'wp-activity-logger-pro'),
        'solution' => __('Set define(\'WP_DEBUG_LOG\', true); in wp-config.php to store errors in wp-content/debug.log.', 'wp-activity-logger-pro'),
    );
} elseif ($debug_log_enabled && !$debug_log_exists) {
    $issues[] = array(
        'title' => __('Debug Log File Not Found Yet', 'wp-activity-logger-pro'),
        'summary' => __('Debug logging is enabled, but the log file does not exist yet. This often means no PHP warning has been written yet, or the file path is not writable.', 'wp-activity-logger-pro'),
        'solution' => __('Trigger the issue again, then recheck this screen. If the file still does not appear, verify that WordPress can write to the configured log path.', 'wp-activity-logger-pro'),
    );
} elseif ($debug_log_exists && !$debug_log_readable) {
    $issues[] = array(
        'title' => __('Debug Log Is Not Readable', 'wp-activity-logger-pro'),
        'summary' => __('The debug log file exists, but PHP cannot read it from this admin screen.', 'wp-activity-logger-pro'),
        'solution' => __('Fix file permissions so the web server user can read the debug log path.', 'wp-activity-logger-pro'),
    );
}

if (isset($_POST['wpal_run_diagnostics'])) {
    check_admin_referer('wpal_diagnostics_nonce');
    WPAL_Helpers::log_activity('diagnostics_test', __('Diagnostics test log entry', 'wp-activity-logger-pro'), 'info');
}
?>

<div class="wrap wpal-wrap">
    <section class="wpal-hero wpal-hero-compact">
        <div>
            <p class="wpal-eyebrow"><?php esc_html_e('System checks', 'wp-activity-logger-pro'); ?></p>
            <h1 class="wpal-page-title"><?php esc_html_e('Diagnostics', 'wp-activity-logger-pro'); ?></h1>
            <p class="wpal-hero-copy"><?php esc_html_e('This page shows the easiest way to understand what the plugin can currently detect, what is failing, and how to fix each issue.', 'wp-activity-logger-pro'); ?></p>
        </div>
        <div class="wpal-hero-actions">
            <form method="post">
                <?php wp_nonce_field('wpal_diagnostics_nonce'); ?>
                <button type="submit" name="wpal_run_diagnostics" class="wpal-btn wpal-btn-primary"><?php esc_html_e('Run Diagnostics', 'wp-activity-logger-pro'); ?></button>
            </form>
        </div>
    </section>

    <?php if (!empty($issues)) : ?>
        <section class="wpal-panel">
            <div class="wpal-panel-head">
                <div>
                    <h2><?php esc_html_e('Easy Fix Guide', 'wp-activity-logger-pro'); ?></h2>
                    <p><?php esc_html_e('Each item below explains the issue in plain language and gives the most likely fix.', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
            <div class="wpal-stack">
                <?php foreach ($issues as $issue) : ?>
                    <div class="wpal-detail-card">
                        <h3><?php echo esc_html($issue['title']); ?></h3>
                        <p><?php echo esc_html($issue['summary']); ?></p>
                        <div class="wpal-note">
                            <strong><?php esc_html_e('Possible solution:', 'wp-activity-logger-pro'); ?></strong>
                            <?php echo ' ' . esc_html($issue['solution']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else : ?>
        <section class="wpal-panel">
            <div class="wpal-empty-panel">
                <h2><?php esc_html_e('No active diagnostics issues found', 'wp-activity-logger-pro'); ?></h2>
                <p><?php esc_html_e('The main environment checks, IP detection, and debug log settings all look healthy right now.', 'wp-activity-logger-pro'); ?></p>
            </div>
        </section>
    <?php endif; ?>

    <section class="wpal-grid wpal-grid-2">
        <article class="wpal-panel">
            <div class="wpal-panel-head">
                <div>
                    <h2><?php esc_html_e('IP Detection', 'wp-activity-logger-pro'); ?></h2>
                    <p><?php esc_html_e('See exactly which header produced the current visitor IP and how trustworthy it looks.', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
            <div class="wpal-stack">
                <div class="wpal-detail-card">
                    <dl>
                        <dt><?php esc_html_e('Detected IP', 'wp-activity-logger-pro'); ?></dt>
                        <dd><?php echo esc_html($ip_details['ip'] ? $ip_details['ip'] : __('Unavailable', 'wp-activity-logger-pro')); ?></dd>
                        <dt><?php esc_html_e('Source', 'wp-activity-logger-pro'); ?></dt>
                        <dd><?php echo esc_html($ip_details['source'] ? $ip_details['source'] : __('None', 'wp-activity-logger-pro')); ?></dd>
                        <dt><?php esc_html_e('Public IP', 'wp-activity-logger-pro'); ?></dt>
                        <dd><?php echo esc_html($ip_details['ip'] ? ($ip_details['is_public'] ? __('Yes', 'wp-activity-logger-pro') : __('No', 'wp-activity-logger-pro')) : __('Unknown', 'wp-activity-logger-pro')); ?></dd>
                        <dt><?php esc_html_e('Proxy chain', 'wp-activity-logger-pro'); ?></dt>
                        <dd><?php echo esc_html($proxy_chain ? $proxy_chain : __('No X-Forwarded-For header found on this request.', 'wp-activity-logger-pro')); ?></dd>
                    </dl>
                </div>

                <div class="wpal-note">
                    <?php
                    if (empty($ip_details['ip'])) {
                        esc_html_e('Easy explanation: the request did not include a usable client IP header, so the plugin cannot store the real visitor address yet.', 'wp-activity-logger-pro');
                    } elseif ($ip_details['is_public']) {
                        printf(
                            /* translators: %s: header source label */
                            esc_html__('Easy explanation: the plugin successfully resolved a public visitor IP using %s.', 'wp-activity-logger-pro'),
                            esc_html($ip_details['source'])
                        );
                    } else {
                        printf(
                            /* translators: %s: header source label */
                            esc_html__('Easy explanation: the plugin only found a private or local IP from %s. This usually happens on local sites, reverse proxies, or load balancers when the real client IP is not forwarded.', 'wp-activity-logger-pro'),
                            esc_html($ip_details['source'])
                        );
                    }
                    ?>
                </div>

                <?php if (!empty($ip_details['raw_headers'])) : ?>
                    <div>
                        <h3><?php esc_html_e('Raw IP Headers Seen By PHP', 'wp-activity-logger-pro'); ?></h3>
                        <pre class="wpal-code-block"><?php echo esc_html(wp_json_encode($ip_details['raw_headers'], JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </article>

        <article class="wpal-panel">
            <div class="wpal-panel-head">
                <div>
                    <h2><?php esc_html_e('Debug Log', 'wp-activity-logger-pro'); ?></h2>
                    <p><?php esc_html_e('Check whether WordPress debugging is enabled and preview the latest lines from the log file.', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
            <div class="wpal-stack">
                <div class="wpal-detail-card">
                    <dl>
                        <dt><?php esc_html_e('WP_DEBUG', 'wp-activity-logger-pro'); ?></dt>
                        <dd><?php echo esc_html($wp_debug ? __('Enabled', 'wp-activity-logger-pro') : __('Disabled', 'wp-activity-logger-pro')); ?></dd>
                        <dt><?php esc_html_e('WP_DEBUG_LOG', 'wp-activity-logger-pro'); ?></dt>
                        <dd><?php echo esc_html($debug_log_enabled ? __('Enabled', 'wp-activity-logger-pro') : __('Disabled', 'wp-activity-logger-pro')); ?></dd>
                        <dt><?php esc_html_e('Log path', 'wp-activity-logger-pro'); ?></dt>
                        <dd><span class="wpal-code-inline"><?php echo esc_html($debug_log_path); ?></span></dd>
                        <dt><?php esc_html_e('File exists', 'wp-activity-logger-pro'); ?></dt>
                        <dd><?php echo esc_html($debug_log_exists ? __('Yes', 'wp-activity-logger-pro') : __('No', 'wp-activity-logger-pro')); ?></dd>
                        <dt><?php esc_html_e('Readable', 'wp-activity-logger-pro'); ?></dt>
                        <dd><?php echo esc_html($debug_log_exists ? ($debug_log_readable ? __('Yes', 'wp-activity-logger-pro') : __('No', 'wp-activity-logger-pro')) : __('Not applicable', 'wp-activity-logger-pro')); ?></dd>
                        <dt><?php esc_html_e('File size', 'wp-activity-logger-pro'); ?></dt>
                        <dd><?php echo esc_html($debug_log_exists && false !== $debug_log_size ? size_format($debug_log_size) : __('N/A', 'wp-activity-logger-pro')); ?></dd>
                    </dl>
                </div>

                <div class="wpal-note">
                    <?php
                    if (!$wp_debug) {
                        esc_html_e('Easy explanation: WordPress debugging is turned off, so no debug log will be generated for plugin or PHP issues.', 'wp-activity-logger-pro');
                    } elseif (!$debug_log_enabled) {
                        esc_html_e('Easy explanation: WordPress is in debug mode, but it is not writing errors to a log file yet.', 'wp-activity-logger-pro');
                    } elseif (!$debug_log_exists) {
                        esc_html_e('Easy explanation: debug logging is enabled, but the file has not been created yet. That usually means no error has been written yet, or WordPress cannot write to the configured location.', 'wp-activity-logger-pro');
                    } elseif (!$debug_log_readable) {
                        esc_html_e('Easy explanation: the log file exists, but the server user cannot read it from this screen.', 'wp-activity-logger-pro');
                    } elseif ('' === $debug_log_tail) {
                        esc_html_e('Easy explanation: the debug log is available, but it is currently empty.', 'wp-activity-logger-pro');
                    } else {
                        esc_html_e('Easy explanation: the debug log is active and the latest lines are shown below for quick troubleshooting.', 'wp-activity-logger-pro');
                    }
                    ?>
                </div>

                <?php if ('' !== $debug_log_tail) : ?>
                    <div>
                        <h3><?php esc_html_e('Latest Debug Log Lines', 'wp-activity-logger-pro'); ?></h3>
                        <pre class="wpal-code-block"><?php echo esc_html($debug_log_tail); ?></pre>
                    </div>
                <?php else : ?>
                    <div class="wpal-empty-panel">
                        <h3><?php esc_html_e('No debug lines to show', 'wp-activity-logger-pro'); ?></h3>
                        <p><?php esc_html_e('Once WordPress writes notices, warnings, or errors to the configured log file, they will appear here.', 'wp-activity-logger-pro'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    </section>

    <section class="wpal-grid wpal-grid-2">
        <article class="wpal-panel">
            <div class="wpal-panel-head">
                <div>
                    <h2><?php esc_html_e('System Information', 'wp-activity-logger-pro'); ?></h2>
                    <p><?php esc_html_e('Core environment values currently detected by WordPress and PHP.', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
            <div class="wpal-table-wrap">
                <table class="wpal-table wpal-kv-table">
                    <tbody>
                        <tr><th><?php esc_html_e('WordPress version', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($wp_version); ?></td></tr>
                        <tr><th><?php esc_html_e('PHP version', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($php_version); ?></td></tr>
                        <tr><th><?php esc_html_e('MySQL version', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($mysql_version); ?></td></tr>
                        <tr><th><?php esc_html_e('Server software', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($server_software); ?></td></tr>
                        <tr><th><?php esc_html_e('Memory limit', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($memory_limit); ?></td></tr>
                        <tr><th><?php esc_html_e('Max execution time', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($max_execution_time); ?>s</td></tr>
                        <tr><th><?php esc_html_e('Post max size', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($post_max_size); ?></td></tr>
                        <tr><th><?php esc_html_e('Upload max filesize', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($upload_max_filesize); ?></td></tr>
                        <tr><th><?php esc_html_e('Max input vars', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($max_input_vars); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="wpal-panel">
            <div class="wpal-panel-head">
                <div>
                    <h2><?php esc_html_e('Plugin Information', 'wp-activity-logger-pro'); ?></h2>
                    <p><?php esc_html_e('Useful metadata for support and debugging.', 'wp-activity-logger-pro'); ?></p>
                </div>
            </div>
            <div class="wpal-table-wrap">
                <table class="wpal-table wpal-kv-table">
                    <tbody>
                        <tr><th><?php esc_html_e('Plugin version', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($plugin_data['Version']); ?></td></tr>
                        <tr><th><?php esc_html_e('Author', 'wp-activity-logger-pro'); ?></th><td><?php echo wp_kses_post($plugin_data['Author']); ?></td></tr>
                        <tr><th><?php esc_html_e('Plugin URI', 'wp-activity-logger-pro'); ?></th><td><a href="<?php echo esc_url($plugin_data['PluginURI']); ?>" target="_blank" rel="noreferrer"><?php echo esc_html($plugin_data['PluginURI']); ?></a></td></tr>
                        <tr><th><?php esc_html_e('Database table', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($table_name); ?> <?php echo $table_exists ? esc_html__('(exists)', 'wp-activity-logger-pro') : esc_html__('(missing)', 'wp-activity-logger-pro'); ?></td></tr>
                        <tr><th><?php esc_html_e('Log count', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html(number_format_i18n($table_count)); ?></td></tr>
                        <tr><th><?php esc_html_e('Database size', 'wp-activity-logger-pro'); ?></th><td><?php echo esc_html($table_size ? size_format($table_size) : 'N/A'); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</div>
