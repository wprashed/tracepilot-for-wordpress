<?php
/**
 * Template for the Google Search Console integration page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get Google Search Console instance
$gsc = new WPAL_Google_Search_Console();
$is_connected = $gsc->is_connected();
$sites = $is_connected ? $gsc->get_sites() : array();

// Get options
$options = get_option('wpal_gsc_options', array());
?>

<div class="wrap wpal-wrap">
    <div class="wpal-dashboard-header">
        <h1 class="wpal-dashboard-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <?php _e('Google Search Console Integration', 'wp-activity-logger-pro'); ?>
        </h1>
        <div class="wpal-dashboard-actions">
            <?php if ($is_connected) : ?>
                <button id="wpal-gsc-disconnect" class="wpal-btn wpal-btn-outline-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    <?php _e('Disconnect', 'wp-activity-logger-pro'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!$is_connected) : ?>
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                    <?php _e('Connect to Google Search Console', 'wp-activity-logger-pro'); ?>
                </h3>
            </div>
            <div class="wpal-widget-body">
                <p><?php _e('Connect your Google Search Console account to analyze search performance data alongside your activity logs.', 'wp-activity-logger-pro'); ?></p>
                
                <form method="post" action="options.php">
                    <?php settings_fields('wpal_gsc_options'); ?>
                    
                    <div class="wpal-form-group">
                        <label class="wpal-form-label" for="wpal_gsc_client_id"><?php _e('Google API Client ID', 'wp-activity-logger-pro'); ?></label>
                        <input type="text" id="wpal_gsc_client_id" name="wpal_gsc_options[client_id]" class="wpal-form-control" value="<?php echo esc_attr(isset($options['client_id']) ? $options['client_id'] : ''); ?>" required>
                        <p class="description"><?php _e('Enter your Google API Client ID from the Google Cloud Console.', 'wp-activity-logger-pro'); ?></p>
                    </div>
                    
                    <div class="wpal-form-group">
                        <label class="wpal-form-label" for="wpal_gsc_client_secret"><?php _e('Google API Client Secret', 'wp-activity-logger-pro'); ?></label>
                        <input type="password" id="wpal_gsc_client_secret" name="wpal_gsc_options[client_secret]" class="wpal-form-control" value="<?php echo esc_attr(isset($options['client_secret']) ? $options['client_secret'] : ''); ?>" required>
                        <p class="description"><?php _e('Enter your Google API Client Secret from the Google Cloud Console.', 'wp-activity-logger-pro'); ?></p>
                    </div>
                    
                    <div class="wpal-form-group">
                        <p><?php _e('To create Google API credentials:', 'wp-activity-logger-pro'); ?></p>
                        <ol>
                            <li><?php _e('Go to the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>', 'wp-activity-logger-pro'); ?></li>
                            <li><?php _e('Create a new project or select an existing one', 'wp-activity-logger-pro'); ?></li>
                            <li><?php _e('Navigate to "APIs & Services > Library"', 'wp-activity-logger-pro'); ?></li>
                            <li><?php _e('Search for and enable the "Google Search Console API"', 'wp-activity-logger-pro'); ?></li>
                            <li><?php _e('Go to "APIs & Services > Credentials"', 'wp-activity-logger-pro'); ?></li>
                            <li><?php _e('Click "Create Credentials" and select "OAuth client ID"', 'wp-activity-logger-pro'); ?></li>
                            <li><?php _e('Set the application type to "Web application"', 'wp-activity-logger-pro'); ?></li>
                            <li><?php _e('Add the following as an authorized redirect URI:', 'wp-activity-logger-pro'); ?><br>
                                <code><?php echo admin_url('admin.php?page=wp-activity-logger-pro-search-console&oauth=callback'); ?></code>
                            </li>
                            <li><?php _e('Click "Create" and copy the Client ID and Client Secret', 'wp-activity-logger-pro'); ?></li>
                        </ol>
                    </div>
                    
                    <div class="wpal-form-group">
                        <button type="submit" class="wpal-btn wpal-btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            <?php _e('Save Credentials', 'wp-activity-logger-pro'); ?>
                        </button>
                        
                        <?php if (!empty($options['client_id']) && !empty($options['client_secret'])) : ?>
                            <a href="<?php echo esc_url($gsc->get_auth_url()); ?>" class="wpal-btn wpal-btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-in"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                <?php _e('Connect to Google', 'wp-activity-logger-pro'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    <?php else : ?>
        <div class="wpal-widget">
            <div class="wpal-widget-header">
                <h3 class="wpal-widget-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-globe"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                    <?php _e('Search Console Data', 'wp-activity-logger-pro'); ?>
                </h3>
            </div>
            <div class="wpal-widget-body">
                <form id="wpal-gsc-form">
                    <div class="wpal-form-group">
                        <label class="wpal-form-label" for="wpal_gsc_site"><?php _e('Select Site', 'wp-activity-logger-pro'); ?></label>
                        <select id="wpal_gsc_site" name="site_url" class="wpal-form-control" required>
                            <option value=""><?php _e('-- Select a site --', 'wp-activity-logger-pro'); ?></option>
                            <?php foreach ($sites as $site) : ?>
                                <option value="<?php echo esc_attr($site->getSiteUrl()); ?>"><?php echo esc_html($site->getSiteUrl()); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="wpal-form-group">
                        <label class="wpal-form-label" for="wpal_gsc_date_range"><?php _e('Date Range', 'wp-activity-logger-pro'); ?></label>
                        <div class="wpal-d-flex">
                            <input type="date" id="wpal_gsc_start_date" name="start_date" class="wpal-form-control" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>" required>
                            <span style="margin: 0 10px; line-height: 38px;"><?php _e('to', 'wp-activity-logger-pro'); ?></span>
                            <input type="date" id="wpal_gsc_end_date" name="end_date" class="wpal-form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="wpal-form-group">
                        <label class="wpal-form-label"><?php _e('Dimensions', 'wp-activity-logger-pro'); ?></label>
                        <div class="wpal-d-flex wpal-flex-wrap">
                            <label style="margin-right: 20px;">
                                <input type="checkbox" name="dimensions[]" value="date" checked> <?php _e('Date', 'wp-activity-logger-pro'); ?>
                            </label>
                            <label style="margin-right: 20px;">
                                <input type="checkbox" name="dimensions[]" value="query"> <?php _e('Query', 'wp-activity-logger-pro'); ?>
                            </label>
                            <label style="margin-right: 20px;">
                                <input type="checkbox" name="dimensions[]" value="page"> <?php _e('Page', 'wp-activity-logger-pro'); ?>
                            </label>
                            <label style="margin-right: 20px;">
                                <input type="checkbox" name="dimensions[]" value="device"> <?php _e('Device', 'wp-activity-logger-pro'); ?>
                            </label>
                            <label style="margin-right: 20px;">
                                <input type="checkbox" name="dimensions[]" value="country"> <?php _e('Country', 'wp-activity-logger-pro'); ?>
                            </label>
                        </div>
                    </div>
                    
                    <div class="wpal-form-group">
                        <button type="submit" id="wpal-gsc-fetch" class="wpal-btn wpal-btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <?php _e('Fetch Data', 'wp-activity-logger-pro'); ?>
                        </button>
                    </div>
                </form>
                
                <div id="wpal-gsc-loading" style="display: none;" class="wpal-text-center wpal-mt-4">
                    <div class="wpal-spinner"></div>
                    <p><?php _e('Loading data from Google Search Console...', 'wp-activity-logger-pro'); ?></p>
                </div>
                
                <div id="wpal-gsc-results" style="display: none;" class="wpal-mt-4">
                    <ul class="wpal-settings-tabs" id="wpal-gsc-tabs">
                        <li><a href="#wpal-gsc-tab-overview" class="wpal-settings-tab wpal-active"><?php _e('Overview', 'wp-activity-logger-pro'); ?></a></li>
                        <li><a href="#wpal-gsc-tab-queries" class="wpal-settings-tab"><?php _e('Top Queries', 'wp-activity-logger-pro'); ?></a></li>
                        <li><a href="#wpal-gsc-tab-pages" class="wpal-settings-tab"><?php _e('Top Pages', 'wp-activity-logger-pro'); ?></a></li>
                        <li><a href="#wpal-gsc-tab-correlation" class="wpal-settings-tab"><?php _e('Activity Correlation', 'wp-activity-logger-pro'); ?></a></li>
                    </ul>
                    
                    <div id="wpal-gsc-tab-overview" class="wpal-settings-content wpal-active">
                        <div class="wpal-chart-container">
                            <canvas id="wpal-gsc-overview-chart"></canvas>
                        </div>
                        
                        <div class="wpal-stats-grid">
                            <div class="wpal-stat-card">
                                <div class="wpal-stat-card-header">
                                    <div class="wpal-stat-card-icon info">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mouse-pointer"><path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"></path><path d="M13 13l6 6"></path></svg>
                                    </div>
                                    <div class="wpal-stat-card-title"><?php _e('Total Clicks', 'wp-activity-logger-pro'); ?></div>
                                </div>
                                <p class="wpal-stat-card-value" id="wpal-gsc-total-clicks">0</p>
                            </div>
                            
                            <div class="wpal-stat-card">
                                <div class="wpal-stat-card-header">
                                    <div class="wpal-stat-card-icon info">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </div>
                                    <div class="wpal-stat-card-title"><?php _e('Total Impressions', 'wp-activity-logger-pro'); ?></div>
                                </div>
                                <p class="wpal-stat-card-value" id="wpal-gsc-total-impressions">0</p>
                            </div>
                            
                            <div class="wpal-stat-card">
                                <div class="wpal-stat-card-header">
                                    <div class="wpal-stat-card-icon info">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-percent"><line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>
                                    </div>
                                    <div class="wpal-stat-card-title"><?php _e('Average CTR', 'wp-activity-logger-pro'); ?></div>
                                </div>
                                <p class="wpal-stat-card-value" id="wpal-gsc-avg-ctr">0%</p>
                            </div>
                            
                            <div class="wpal-stat-card">
                                <div class="wpal-stat-card-header">
                                    <div class="wpal-stat-card-icon info">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
                                    </div>
                                    <div class="wpal-stat-card-title"><?php _e('Average Position', 'wp-activity-logger-pro'); ?></div>
                                </div>
                                <p class="wpal-stat-card-value" id="wpal-gsc-avg-position">0</p>
                            </div>
                        </div>
                    </div>
                    
                    <div id="wpal-gsc-tab-queries" class="wpal-settings-content">
                        <div class="wpal-table-responsive">
                            <table class="wpal-table wpal-table-striped">
                                <thead>
                                    <tr>
                                        <th><?php _e('Query', 'wp-activity-logger-pro'); ?></th>
                                        <th><?php _e('Clicks', 'wp-activity-logger-pro'); ?></th>
                                        <th><?php _e('Impressions', 'wp-activity-logger-pro'); ?></th>
                                        <th><?php _e('CTR', 'wp-activity-logger-pro'); ?></th>
                                        <th><?php _e('Position', 'wp-activity-logger-pro'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="wpal-gsc-queries-table">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div id="wpal-gsc-tab-pages" class="wpal-settings-content">
                        <div class="wpal-table-responsive">
                            <table class="wpal-table wpal-table-striped">
                                <thead>
                                    <tr>
                                        <th><?php _e('Page', 'wp-activity-logger-pro'); ?></th>
                                        <th><?php _e('Clicks', 'wp-activity-logger-pro'); ?></th>
                                        <th><?php _e('Impressions', 'wp-activity-logger-pro'); ?></th>
                                        <th><?php _e('CTR', 'wp-activity-logger-pro'); ?></th>
                                        <th><?php _e('Position', 'wp-activity-logger-pro'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="wpal-gsc-pages-table">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div id="wpal-gsc-tab-correlation" class="wpal-settings-content">
                        <div class="wpal-chart-container">
                            <canvas id="wpal-gsc-correlation-chart"></canvas>
                        </div>
                        
                        <div class="wpal-alert wpal-alert-info wpal-mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                            <div>
                                <?php _e('This chart shows the correlation between your site\'s search performance and user activity. Use this data to understand how search traffic impacts user behavior on your site.', 'wp-activity-logger-pro'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize tabs
    $('#wpal-gsc-tabs a').on('click', function(e) {
        e.preventDefault();
        
        const tabId = $(this).attr('href');
        
        $('#wpal-gsc-tabs a').removeClass('wpal-active');
        $('.wpal-settings-content').removeClass('wpal-active');
        
        $(this).addClass('wpal-active');
        $(tabId).addClass('wpal-active');
    });
    
    // Disconnect from Google Search Console
    $('#wpal-gsc-disconnect').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to disconnect from Google Search Console?', 'wp-activity-logger-pro'); ?>')) {
            const $button = $(this);
            
            // Disable button and show loading
            $button.prop('disabled', true).html('<div class="wpal-spinner"></div> <?php _e('Disconnecting...', 'wp-activity-logger-pro'); ?>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpal_gsc_disconnect',
                    nonce: '<?php echo wp_create_nonce('wpal_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page
                        location.reload();
                    } else {
                        alert(response.data.message);
                        
                        // Re-enable button
                        $button.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg> <?php _e('Disconnect', 'wp-activity-logger-pro'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred while disconnecting.', 'wp-activity-logger-pro'); ?>');
                    
                    // Re-enable button
                    $button.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg> <?php _e('Disconnect', 'wp-activity-logger-pro'); ?>');
                }
            });
        }
    });
    
    // Fetch data from Google Search Console
    $('#wpal-gsc-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $button = $('#wpal-gsc-fetch');
        
        // Validate form
        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }
        
        // Check if at least one dimension is selected
        if (!$('input[name="dimensions[]"]:checked').length) {
            alert('<?php _e('Please select at least one dimension.', 'wp-activity-logger-pro'); ?>');
            return;
        }
        
        // Disable button and show loading
        $button.prop('disabled', true).html('<div class="wpal-spinner"></div> <?php _e('Fetching...', 'wp-activity-logger-pro'); ?>');
        $('#wpal-gsc-loading').show();
        $('#wpal-gsc-results').hide();
        
        // Get form data
        const formData = new FormData($form[0]);
        formData.append('action', 'wpal_gsc_fetch_data');
        formData.append('nonce', '<?php echo wp_create_nonce('wpal_nonce'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Process and display data
                    processSearchData(response.data);
                    
                    // Show results
                    $('#wpal-gsc-results').show();
                } else {
                    alert(response.data.message);
                }
                
                // Hide loading
                $('#wpal-gsc-loading').hide();
                
                // Re-enable button
                $button.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> <?php _e('Fetch Data', 'wp-activity-logger-pro'); ?>');
            },
            error: function() {
                alert('<?php _e('An error occurred while fetching data.', 'wp-activity-logger-pro'); ?>');
                
                // Hide loading
                $('#wpal-gsc-loading').hide();
                
                // Re-enable button
                $button.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> <?php _e('Fetch Data', 'wp-activity-logger-pro'); ?>');
            }
        });
    });
    
    // Process and display search data
    function processSearchData(data) {
        const searchData = data.search_data;
        const logData = data.log_data;
        const correlation = data.correlation;
        
        // Process overview data
        let totalClicks = 0;
        let totalImpressions = 0;
        let totalCtr = 0;
        let totalPosition = 0;
        
        // Group data by date for overview chart
        const dateData = {};
        const queryData = {};
        const pageData = {};
        
        searchData.forEach(function(row) {
            totalClicks += row.clicks;
            totalImpressions += row.impressions;
            
            // Group by date
            if (row.keys && row.keys[0] && row.keys[0].match(/\d{4}-\d{2}-\d{2}/)) {
                const date = row.keys[0];
                
                if (!dateData[date]) {
                    dateData[date] = {
                        clicks: 0,
                        impressions: 0,
                        ctr: 0,
                        position: 0
                    };
                }
                
                dateData[date].clicks += row.clicks;
                dateData[date].impressions += row.impressions;
                dateData[date].ctr = (dateData[date].impressions > 0) ? 
                    (dateData[date].clicks / dateData[date].impressions) * 100 : 0;
                dateData[date].position += row.position;
            }
            
            // Group by query
            if (row.keys && row.keys.length > 1 && row.keys[1]) {
                const query = row.keys[1];
                
                if (!queryData[query]) {
                    queryData[query] = {
                        clicks: 0,
                        impressions: 0,
                        ctr: 0,
                        position: 0
                    };
                }
                
                queryData[query].clicks += row.clicks;
                queryData[query].impressions += row.impressions;
                queryData[query].ctr = (queryData[query].impressions > 0) ? 
                    (queryData[query].clicks / queryData[query].impressions) * 100 : 0;
                queryData[query].position += row.position;
            }
            
            // Group by page
            if (row.keys && row.keys.length > 1 && row.keys[1] && row.keys[1].startsWith('http')) {
                const page = row.keys[1];
                
                if (!pageData[page]) {
                    pageData[page] = {
                        clicks: 0,
                        impressions: 0,
                        ctr: 0,
                        position: 0
                    };
                }
                
                pageData[page].clicks += row.clicks;
                pageData[page].impressions += row.impressions;
                pageData[page].ctr = (pageData[page].impressions > 0) ? 
                    (pageData[page].clicks / pageData[page].impressions) * 100 : 0;
                pageData[page].position += row.position;
            }
        });
        
        // Calculate averages
        const avgCtr = (totalImpressions > 0) ? (totalClicks / totalImpressions) * 100 : 0;
        const avgPosition = (searchData.length > 0) ? totalPosition / searchData.length : 0;
        
        // Update overview stats
        $('#wpal-gsc-total-clicks').text(totalClicks.toLocaleString());
        $('#wpal-gsc-total-impressions').text(totalImpressions.toLocaleString());
        $('#wpal-gsc-avg-ctr').text(avgCtr.toFixed(2) + '%');
        $('#wpal-gsc-avg-position').text(avgPosition.toFixed(2));
        
        // Create overview chart
        createOverviewChart(dateData);
        
        // Create correlation chart
        createCorrelationChart(correlation);
        
        // Populate queries table
        const queriesHtml = [];
        Object.entries(queryData)
            .sort((a, b) => b[1].clicks - a[1].clicks)
            .slice(0, 20)
            .forEach(function([query, data]) {
                queriesHtml.push(`
                    <tr>
                        <td>${query}</td>
                        <td>${data.clicks.toLocaleString()}</td>
                        <td>${data.impressions.toLocaleString()}</td>
                        <td>${data.ctr.toFixed(2)}%</td>
                        <td>${(data.position / Object.keys(dateData).length).toFixed(1)}</td>
                    </tr>
                `);
            });
        
        $('#wpal-gsc-queries-table').html(queriesHtml.join(''));
        
        // Populate pages table
        const pagesHtml = [];
        Object.entries(pageData)
            .sort((a, b) => b[1].clicks - a[1].clicks)
            .slice(0, 20)
            .forEach(function([page, data]) {
                pagesHtml.push(`
                    <tr>
                        <td>${page}</td>
                        <td>${data.clicks.toLocaleString()}</td>
                        <td>${data.impressions.toLocaleString()}</td>
                        <td>${data.ctr.toFixed(2)}%</td>
                        <td>${(data.position / Object.keys(dateData).length).toFixed(1)}</td>
                    </tr>
                `);
            });
        
        $('#wpal-gsc-pages-table').html(pagesHtml.join(''));
    }
    
    // Create overview chart
    function createOverviewChart(dateData) {
        const ctx = document.getElementById('wpal-gsc-overview-chart').getContext('2d');
        
        // Prepare data
        const labels = Object.keys(dateData).sort();
        const clicksData = [];
        const impressionsData = [];
        
        labels.forEach(function(date) {
            clicksData.push(dateData[date].clicks);
            impressionsData.push(dateData[date].impressions);
        });
        
        // Destroy existing chart if it exists
        if (window.overviewChart) {
            window.overviewChart.destroy();
        }
        
        // Create new chart
        window.overviewChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '<?php _e('Clicks', 'wp-activity-logger-pro'); ?>',
                        data: clicksData,
                        backgroundColor: 'rgba(34, 113, 177, 0.2)',
                        borderColor: 'rgba(34, 113, 177, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: '<?php _e('Impressions', 'wp-activity-logger-pro'); ?>',
                        data: impressionsData,
                        backgroundColor: 'rgba(240, 184, 73, 0.2)',
                        borderColor: 'rgba(240, 184, 73, 1)',
                        borderWidth: 1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: '<?php _e('Clicks', 'wp-activity-logger-pro'); ?>'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: '<?php _e('Impressions', 'wp-activity-logger-pro'); ?>'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }
    
    // Create correlation chart
    function createCorrelationChart(correlation) {
        const ctx = document.getElementById('wpal-gsc-correlation-chart').getContext('2d');
        
        // Prepare data
        const labels = Object.keys(correlation).sort();
        const clicksData = [];
        const logsData = [];
        
        labels.forEach(function(date) {
            clicksData.push(correlation[date].search.clicks);
            logsData.push(correlation[date].logs.count);
        });
        
        // Destroy existing chart if it exists
        if (window.correlationChart) {
            window.correlationChart.destroy();
        }
        
        // Create new chart
        window.correlationChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '<?php _e('Search Clicks', 'wp-activity-logger-pro'); ?>',
                        data: clicksData,
                        backgroundColor: 'rgba(34, 113, 177, 0.2)',
                        borderColor: 'rgba(34, 113, 177, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: '<?php _e('Site Activities', 'wp-activity-logger-pro'); ?>',
                        data: logsData,
                        backgroundColor: 'rgba(0, 163, 42, 0.2)',
                        borderColor: 'rgba(0, 163, 42, 1)',
                        borderWidth: 1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: '<?php _e('Search Clicks', 'wp-activity-logger-pro'); ?>'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: '<?php _e('Site Activities', 'wp-activity-logger-pro'); ?>'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }
});
</script>