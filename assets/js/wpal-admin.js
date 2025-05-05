/**
 * WP Activity Logger Pro - Admin JavaScript
 */
(function($) {
    'use strict';
    
    // Global variables
    let currentOffset = 0;
    let currentLimit = 50;
    let totalLogs = 0;
    let currentFilters = {};
    let liveFeedInterval = null;
    let charts = {};
    
    // Initialize on document ready
    $(document).ready(function() {
        // Initialize date pickers
        if ($.fn.flatpickr) {
            $('#wpal-date-range, #wpal-export-date-range').flatpickr({
                mode: 'range',
                dateFormat: 'Y-m-d',
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        const from = formatDate(selectedDates[0]);
                        const to = formatDate(selectedDates[1]);
                        
                        if (instance.element.id === 'wpal-export-date-range') {
                            $('#wpal-export-from').val(from);
                            $('#wpal-export-to').val(to);
                        }
                    }
                }
            });
        }
        
        // Initialize logs page
        if ($('#wpal-logs-table').length) {
            loadLogs();
            
            // Apply filters
            $('#wpal-apply-filters').on('click', function() {
                currentOffset = 0;
                applyFilters();
                loadLogs();
            });
            
            // Reset filters
            $('#wpal-reset-filters').on('click', function() {
                $('#wpal-date-range').val('');
                $('#wpal-user-filter').val('');
                $('#wpal-action-filter').val('');
                $('#wpal-severity-filter').val('');
                
                currentOffset = 0;
                currentFilters = {};
                loadLogs();
            });
            
            // Load more logs
            $('#wpal-load-more').on('click', function() {
                currentOffset += currentLimit;
                loadLogs(true);
            });
            
            // Export filtered results
            $('#wpal-export-filtered').on('click', function() {
                exportFilteredLogs();
            });
            
            // Clear logs
            $('#wpal-clear-logs').on('click', function() {
                if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
                    clearLogs();
                }
            });
        }
        
        // Initialize dashboard page
        if ($('#wpal-daily-activity-chart').length) {
            loadDashboardData();
            startLiveFeed();
        }
        
        // Initialize export page
        if ($('#wpal-export-form').length) {
            // Update hidden fields on form submission
            $('#wpal-export-form').on('submit', function() {
                $('#wpal-export-user-value').val($('#wpal-export-user').val());
                $('#wpal-export-action-value').val($('#wpal-export-action').val());
                $('#wpal-export-severity-value').val($('#wpal-export-severity').val());
                $('#wpal-export-format-value').val($('#wpal-export-format').val());
                $('#wpal-export-limit-value').val($('#wpal-export-limit').val());
            });
            
            // Show/hide custom filters
            $('#wpal-schedule-filter').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#wpal-schedule-custom-filters').removeClass('d-none');
                } else {
                    $('#wpal-schedule-custom-filters').addClass('d-none');
                }
            });
            
            // Handle scheduled export form
            $('#wpal-scheduled-export-form').on('submit', function(e) {
                e.preventDefault();
                saveScheduledExport();
            });
            
            // Load scheduled exports
            loadScheduledExports();
        }
    });
    
    // Load logs from the API
    function loadLogs(append = false) {
        const params = {
            limit: currentLimit,
            offset: currentOffset,
            ...currentFilters
        };
        
        $.ajax({
            url: WPAL.rest_url + 'logs',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', WPAL.nonce);
            },
            data: params,
            success: function(response) {
                renderLogs(response, append);
                
                // Update showing count
                if (response.length < currentLimit) {
                    totalLogs = currentOffset + response.length;
                    $('#wpal-load-more').prop('disabled', true);
                } else {
                    $('#wpal-load-more').prop('disabled', false);
                }
                
                $('#wpal-showing-count').text('Showing ' + (currentOffset + response.length) + ' of ' + (totalLogs || 'many') + ' logs');
            },
            error: function(xhr) {
                console.error('Error loading logs:', xhr.responseText);
                alert('Error loading logs. Please try again.');
            }
        });
    }
    
    // Render logs in the table
    function renderLogs(logs, append = false) {
        if (!append) {
            $('#wpal-logs-body').empty();
        }
        
        if (logs.length === 0) {
            if (!append) {
                $('#wpal-logs-body').html('<tr><td colspan="8" class="text-center">No logs found</td></tr>');
            }
            return;
        }
        
        let html = '';
        
        logs.forEach(function(log) {
            const severityClass = getSeverityClass(log.severity);
            
            html += '<tr>';
            html += '<td>' + formatDateTime(log.time) + '</td>';
            html += '<td>' + escapeHtml(log.username) + '</td>';
            html += '<td>' + escapeHtml(log.user_role || 'N/A') + '</td>';
            html += '<td>' + escapeHtml(log.action) + '</td>';
            html += '<td>' + escapeHtml(log.ip) + '</td>';
            html += '<td>' + escapeHtml(log.browser || 'Unknown') + '</td>';
            html += '<td><span class="badge ' + severityClass + '">' + (log.severity || 'info').toUpperCase() + '</span></td>';
            
            // Details button
            html += '<td>';
            if (log.context) {
                html += '<button class="button button-small view-details" data-log=\'' + JSON.stringify(log) + '\'>Details</button>';
            } else {
                html += 'N/A';
            }
            html += '</td>';
            
            html += '</tr>';
        });
        
        $('#wpal-logs-body').append(html);
        
        // Attach event handlers for detail buttons
        $('.view-details').off('click').on('click', function() {
            const log = $(this).data('log');
            showLogDetails(log);
        });
    }
    
    // Show log details in modal
    function showLogDetails(log) {
        let html = '<table class="table table-bordered">';
        
        // Add all log properties
        for (const key in log) {
            if (key === 'context') continue;
            html += '<tr>';
            html += '<th>' + key.charAt(0).toUpperCase() + key.slice(1) + '</th>';
            html += '<td>' + escapeHtml(log[key]) + '</td>';
            html += '</tr>';
        }
        
        // Add context if available
        if (log.context) {
            html += '<tr>';
            html += '<th>Context</th>';
            html += '<td><pre>' + JSON.stringify(log.context, null, 2) + '</pre></td>';
            html += '</tr>';
        }
        
        html += '</table>';
        
        $('#wpal-log-details-content').html(html);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('wpal-log-details-modal'));
        modal.show();
    }
    
    // Apply filters from form
    function applyFilters() {
        currentFilters = {};
        
        const dateRange = $('#wpal-date-range').val();
        if (dateRange) {
            const dates = dateRange.split(' to ');
            if (dates.length === 2) {
                currentFilters.from = dates[0] + ' 00:00:00';
                currentFilters.to = dates[1] + ' 23:59:59';
            }
        }
        
        const user = $('#wpal-user-filter').val();
        if (user) {
            currentFilters.user = user;
        }
        
        const action = $('#wpal-action-filter').val();
        if (action) {
            currentFilters.action_type = action;
        }
        
        const severity = $('#wpal-severity-filter').val();
        if (severity) {
            currentFilters.severity = severity;
        }
    }
    
    // Export filtered logs
    function exportFilteredLogs() {
        applyFilters();
        
        let url = WPAL.rest_url + 'export?_wpnonce=' + WPAL.nonce;
        
        for (const key in currentFilters) {
            url += '&' + key + '=' + encodeURIComponent(currentFilters[key]);
        }
        
        window.location.href = url;
    }
    
    // Clear all logs
    function clearLogs() {
        $.ajax({
            url: WPAL.ajax_url,
            method: 'POST',
            data: {
                action: 'wpal_clear_logs',
                nonce: WPAL.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Logs cleared successfully.');
                    currentOffset = 0;
                    loadLogs();
                } else {
                    alert('Error clearing logs: ' + response.data);
                }
            },
            error: function() {
                alert('Error clearing logs. Please try again.');
            }
        });
    }
    
    // Load dashboard data
    function loadDashboardData() {
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
                alert('Error loading dashboard data. Please try again.');
            }
        });
    }
    
    // Render dashboard charts
    function renderDashboardCharts(data) {
        // Daily activity chart
        if (data.daily_activity && data.daily_activity.length > 0) {
            const ctx1 = document.getElementById('wpal-daily-activity-chart').getContext('2d');
            
            const labels = data.daily_activity.map(item => item.date);
            const values = data.daily_activity.map(item => item.count);
            
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
        
        // User activity chart
        if (data.user_activity && data.user_activity.length > 0) {
            const ctx2 = document.getElementById('wpal-user-activity-chart').getContext('2d');
            
            const labels = data.user_activity.map(item => item.username);
            const values = data.user_activity.map(item => item.count);
            
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
        
        // Action types chart
        if (data.action_types && data.action_types.length > 0) {
            const ctx3 = document.getElementById('wpal-action-types-chart').getContext('2d');
            
            const labels = data.action_types.map(item => item.action_type);
            const values = data.action_types.map(item => item.count);
            
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
    }
    
    // Start live feed
    function startLiveFeed() {
        // Initial load
        loadLiveFeed();
        
        // Set interval for updates
        liveFeedInterval = setInterval(loadLiveFeed, 30000); // Update every 30 seconds
        
        // Clean up on page unload
        $(window).on('beforeunload', function() {
            if (liveFeedInterval) {
                clearInterval(liveFeedInterval);
            }
        });
    }
    
    // Load live feed data
    function loadLiveFeed() {
        $.ajax({
            url: WPAL.ajax_url,
            method: 'POST',
            data: {
                action: 'wpal_live_feed',
                nonce: WPAL.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderLiveFeed(response.data);
                }
            },
            error: function() {
                console.error('Error loading live feed');
            }
        });
    }
    
    // Render live feed
    function renderLiveFeed(logs) {
        if (!logs || logs.length === 0) {
            $('#wpal-live-feed').html('<p class="text-center">No recent activity</p>');
            return;
        }
        
        let html = '';
        
        logs.forEach(function(log) {
            const severityClass = getSeverityClass(log.severity);
            
            html += '<div class="live-feed-item mb-2 p-2 border-bottom">';
            html += '<div class="d-flex justify-content-between">';
            html += '<span class="fw-bold">' + escapeHtml(log.username) + '</span>';
            html += '<span class="text-muted small">' + formatDateTime(log.time) + '</span>';
            html += '</div>';
            html += '<div>' + escapeHtml(log.action) + '</div>';
            html += '<div class="d-flex justify-content-between align-items-center mt-1">';
            html += '<span class="badge ' + severityClass + '">' + (log.severity || 'info').toUpperCase() + '</span>';
            html += '<span class="text-muted small">' + escapeHtml(log.ip) + '</span>';
            html += '</div>';
            html += '</div>';
        });
        
        $('#wpal-live-feed').html(html);
    }
    
    // Load scheduled exports
    function loadScheduledExports() {
        // This would be implemented with an AJAX call to get the scheduled exports
        // For now, we'll just show a placeholder
        $('#wpal-scheduled-exports-list').html('<tr><td colspan="5" class="text-center">No scheduled exports configured</td></tr>');
    }
    
    // Save scheduled export
    function saveScheduledExport() {
        // This would be implemented with an AJAX call to save the scheduled export
        alert('This feature will be implemented in the next version.');
    }
    
    // Helper function to format date
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Helper function to format date and time
    function formatDateTime(dateTimeStr) {
        const date = new Date(dateTimeStr);
        return date.toLocaleString();
    }
    
    // Helper function to get severity class
    function getSeverityClass(severity) {
        switch (severity) {
            case 'error':
                return 'bg-danger';
            case 'warning':
                return 'bg-warning text-dark';
            case 'info':
            default:
                return 'bg-success';
        }
    }
    
    // Helper function to escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        
        return str
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
})(jQuery);