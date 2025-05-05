/**
 * WP Activity Logger Pro - Admin JavaScript
 */
(function($) {
    'use strict';
    
    // Store chart instances
    const charts = {
        dailyActivity: null,
        userActivity: null,
        actionTypes: null
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        initDataTables();
        initDashboard();
        initExport();
        initSettings();
        initFilters();
    });
    
    // Initialize DataTables
    function initDataTables() {
        if ($('#wpal-logs-table').length) {
            $('#wpal-logs-table').DataTable({
                order: [[0, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                responsive: true,
                language: {
                    search: 'Quick Filter:',
                    lengthMenu: 'Show _MENU_ entries per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total entries)'
                }
            });
            
            // Delete log entry
            $('#wpal-logs-table').on('click', '.delete-log', function(e) {
                e.preventDefault();
                
                if (confirm(WPAL.confirm_delete)) {
                    const logId = $(this).data('id');
                    
                    $.ajax({
                        url: WPAL.ajax_url,
                        method: 'POST',
                        data: {
                            action: 'wpal_delete_log',
                            nonce: WPAL.delete_nonce,
                            log_id: logId
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert(response.data);
                            }
                        },
                        error: function() {
                            alert('An error occurred while deleting the log entry.');
                        }
                    });
                }
            });
            
            // Delete all logs
            $('#delete-all-logs').on('click', function(e) {
                e.preventDefault();
                
                if (confirm(WPAL.confirm_delete_all)) {
                    $.ajax({
                        url: WPAL.ajax_url,
                        method: 'POST',
                        data: {
                            action: 'wpal_delete_all_logs',
                            nonce: WPAL.delete_nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert(response.data);
                            }
                        },
                        error: function() {
                            alert('An error occurred while deleting all log entries.');
                        }
                    });
                }
            });
        }
    }
    
    // Initialize dashboard
    function initDashboard() {
        if ($('#wpal-dashboard').length) {
            loadDashboardData();
            
            // Refresh dashboard data
            $('#refresh-dashboard').on('click', function(e) {
                e.preventDefault();
                loadDashboardData();
            });
        }
    }
    
    // Load dashboard data
    function loadDashboardData() {
        // Show loading indicators
        $('#wpal-daily-activity-chart').closest('.card-body').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p>Loading chart data...</p></div>');
        $('#wpal-user-activity-chart').closest('.card-body').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p>Loading chart data...</p></div>');
        $('#wpal-action-types-chart').closest('.card-body').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p>Loading chart data...</p></div>');
        
        $.ajax({
            url: WPAL.rest_url + 'stats',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', WPAL.nonce);
            },
            success: function(response) {
                renderDashboardCharts(response);
            },
            error: function(xhr) {
                console.error('Error loading dashboard data:', xhr.responseText);
                
                // Show friendly error message with retry button
                const errorHtml = '<div class="text-center"><p class="text-danger">Error loading dashboard data.</p><button class="button retry-dashboard-load">Retry</button></div>';
                $('#wpal-daily-activity-chart').closest('.card-body').html(errorHtml);
                $('#wpal-user-activity-chart').closest('.card-body').html(errorHtml);
                $('#wpal-action-types-chart').closest('.card-body').html(errorHtml);
                
                // Add retry button handler
                $('.retry-dashboard-load').on('click', function() {
                    loadDashboardData();
                });
            }
        });
    }
    
    // Render dashboard charts
    function renderDashboardCharts(data) {
        // Recreate canvas elements to avoid Chart.js errors
        $('#wpal-daily-activity-chart').closest('.card-body').html('<canvas id="wpal-daily-activity-chart" height="250"></canvas>');
        $('#wpal-user-activity-chart').closest('.card-body').html('<canvas id="wpal-user-activity-chart" height="250"></canvas>');
        $('#wpal-action-types-chart').closest('.card-body').html('<canvas id="wpal-action-types-chart" height="250"></canvas>');
        
        // Daily activity chart
        if (data.daily_activity && data.daily_activity.length > 0) {
            const ctx1 = document.getElementById('wpal-daily-activity-chart');
            if (ctx1) {
                const labels = data.daily_activity.map(item => item.date);
                const values = data.daily_activity.map(item => parseInt(item.count));
                
                if (charts.dailyActivity) {
                    charts.dailyActivity.destroy();
                }
                
                charts.dailyActivity = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Daily Activity',
                            data: values,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        } else {
            $('#wpal-daily-activity-chart').closest('.card-body').html('<div class="text-center"><p>No activity data available</p></div>');
        }
        
        // User activity chart
        if (data.user_activity && data.user_activity.length > 0) {
            const ctx2 = document.getElementById('wpal-user-activity-chart');
            if (ctx2) {
                const labels = data.user_activity.map(item => item.username);
                const values = data.user_activity.map(item => parseInt(item.count));
                
                if (charts.userActivity) {
                    charts.userActivity.destroy();
                }
                
                charts.userActivity = new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Activity Count',
                            data: values,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        } else {
            $('#wpal-user-activity-chart').closest('.card-body').html('<div class="text-center"><p>No user activity data available</p></div>');
        }
        
        // Action types chart
        if (data.action_types && data.action_types.length > 0) {
            const ctx3 = document.getElementById('wpal-action-types-chart');
            if (ctx3) {
                const labels = data.action_types.map(item => item.action_type);
                const values = data.action_types.map(item => parseInt(item.count));
                
                if (charts.actionTypes) {
                    charts.actionTypes.destroy();
                }
                
                charts.actionTypes = new Chart(ctx3, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.6)',
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 206, 86, 0.6)',
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(153, 102, 255, 0.6)',
                                'rgba(255, 159, 64, 0.6)',
                                'rgba(199, 199, 199, 0.6)',
                                'rgba(83, 102, 255, 0.6)',
                                'rgba(40, 159, 64, 0.6)',
                                'rgba(210, 199, 199, 0.6)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)',
                                'rgba(199, 199, 199, 1)',
                                'rgba(83, 102, 255, 1)',
                                'rgba(40, 159, 64, 1)',
                                'rgba(210, 199, 199, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
            }
        } else {
            $('#wpal-action-types-chart').closest('.card-body').html('<div class="text-center"><p>No action type data available</p></div>');
        }
    }
    
    // Initialize export
    function initExport() {
        if ($('#wpal-export-form').length) {
            $('#wpal-export-form').on('submit', function(e) {
                const format = $('#export-format').val();
                const dateFrom = $('#date-from').val();
                const dateTo = $('#date-to').val();
                const severity = $('#export-severity').val();
                
                if (!dateFrom || !dateTo) {
                    e.preventDefault();
                    alert('Please select both start and end dates.');
                    return false;
                }
                
                return true;
            });
        }
    }
    
    // Initialize settings
    function initSettings() {
        if ($('#wpal-settings-form').length) {
            // Color picker
            if ($.fn.wpColorPicker) {
                $('.wpal-color-picker').wpColorPicker();
            }
            
            // Save settings
            $('#wpal-settings-form').on('submit', function(e) {
                e.preventDefault();
                
                const settings = {
                    retention_days: $('#retention-days').val(),
                    log_storage: $('input[name="log-storage"]:checked').val(),
                    notification_email: $('#notification-email').val(),
                    notification_events: [],
                    daily_report: $('#daily-report').is(':checked'),
                    webhook_url: $('#webhook-url').val(),
                    severity_colors: {
                        info: $('#info-color').val(),
                        warning: $('#warning-color').val(),
                        error: $('#error-color').val()
                    },
                    push_enabled: $('#push-enabled').is(':checked'),
                    slack_webhook: $('#slack-webhook').val(),
                    discord_webhook: $('#discord-webhook').val(),
                    telegram_bot_token: $('#telegram-bot-token').val(),
                    telegram_chat_id: $('#telegram-chat-id').val()
                };
                
                // Get notification events
                $('input[name="notification-events[]"]:checked').each(function() {
                    settings.notification_events.push($(this).val());
                });
                
                // Save settings via AJAX
                $.ajax({
                    url: WPAL.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'wpal_save_settings',
                        nonce: WPAL.settings_nonce,
                        settings: settings
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#settings-message').html('<div class="notice notice-success is-dismissible"><p>' + response.data + '</p></div>');
                        } else {
                            $('#settings-message').html('<div class="notice notice-error is-dismissible"><p>' + response.data + '</p></div>');
                        }
                        
                        // Scroll to message
                        $('html, body').animate({
                            scrollTop: $('#settings-message').offset().top - 50
                        }, 500);
                    },
                    error: function() {
                        $('#settings-message').html('<div class="notice notice-error is-dismissible"><p>An error occurred while saving settings.</p></div>');
                        
                        // Scroll to message
                        $('html, body').animate({
                            scrollTop: $('#settings-message').offset().top - 50
                        }, 500);
                    }
                });
            });
            
            // Toggle notification settings
            $('#push-enabled').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.push-settings').show();
                } else {
                    $('.push-settings').hide();
                }
            }).trigger('change');
            
            // Toggle integration settings
            $('.integration-toggle').on('change', function() {
                const target = $(this).data('target');
                if ($(this).is(':checked')) {
                    $(target).show();
                } else {
                    $(target).hide();
                }
            }).trigger('change');
        }
    }
    
    // Initialize filters
    function initFilters() {
        if ($('#wpal-filter-form').length) {
            // Date range picker
            if ($.fn.daterangepicker) {
                $('#filter-date-range').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear',
                        format: 'YYYY-MM-DD'
                    }
                });
                
                $('#filter-date-range').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                });
                
                $('#filter-date-range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                });
            }
            
            // Apply filters
            $('#wpal-filter-form').on('submit', function(e) {
                e.preventDefault();
                
                const filters = {
                    date_range: $('#filter-date-range').val(),
                    user: $('#filter-user').val(),
                    action: $('#filter-action').val(),
                    severity: $('#filter-severity').val()
                };
                
                // Apply filters to DataTable
                const table = $('#wpal-logs-table').DataTable();
                
                // Clear all filters
                table.search('').columns().search('').draw();
                
                // Apply each filter
                if (filters.user) {
                    table.column(2).search(filters.user).draw();
                }
                
                if (filters.action) {
                    table.column(3).search(filters.action).draw();
                }
                
                if (filters.severity) {
                    table.column(6).search(filters.severity).draw();
                }
                
                // Date range filtering is more complex and would require custom filtering
                if (filters.date_range) {
                    const dates = filters.date_range.split(' - ');
                    if (dates.length === 2) {
                        const startDate = new Date(dates[0]);
                        const endDate = new Date(dates[1]);
                        
                        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                            const date = new Date(data[0]);
                            return date >= startDate && date <= endDate;
                        });
                        
                        table.draw();
                        
                        // Remove the custom filter
                        $.fn.dataTable.ext.search.pop();
                    }
                }
            });
            
            // Reset filters
            $('#reset-filters').on('click', function() {
                $('#wpal-filter-form')[0].reset();
                $('#wpal-logs-table').DataTable().search('').columns().search('').draw();
            });
        }
    }
    
})(jQuery);