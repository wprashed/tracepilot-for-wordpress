/**
 * Admin JavaScript for WP Activity Logger Pro
 */
(function($) {
    'use strict';

    // Initialize DataTables only if not already initialized
    function initDataTables() {
        if ($.fn.dataTable.isDataTable('#wpal-logs-table')) {
            // Table already initialized, no need to reinitialize
            return;
        }
        
        // Initialize DataTables
        $('#wpal-logs-table').DataTable({
            order: [[4, 'desc']], // Sort by time column (index 4) in descending order
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            language: {
                search: '<span class="screen-reader-text">Search logs:</span> ',
                searchPlaceholder: 'Search logs...',
                info: 'Showing _START_ to _END_ of _TOTAL_ logs',
                lengthMenu: 'Show _MENU_ logs per page'
            }
        });
    }

    // Initialize charts
    function initCharts() {
        // Activity chart
        const activityChart = document.getElementById('wpal-activity-chart');
        if (activityChart) {
            const chartData = JSON.parse(activityChart.getAttribute('data-chart'));
            new Chart(activityChart, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Activity Logs',
                        data: chartData.data,
                        backgroundColor: 'rgba(78, 115, 223, 0.2)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }

        // Severity chart
        const severityChart = document.getElementById('wpal-severity-chart');
        if (severityChart) {
            const chartData = JSON.parse(severityChart.getAttribute('data-chart'));
            new Chart(severityChart, {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.data,
                        backgroundColor: [
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(246, 194, 62, 0.8)',
                            'rgba(231, 74, 59, 0.8)',
                            'rgba(78, 115, 223, 0.8)'
                        ],
                        borderColor: [
                            'rgba(28, 200, 138, 1)',
                            'rgba(246, 194, 62, 1)',
                            'rgba(231, 74, 59, 1)',
                            'rgba(78, 115, 223, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }

    // Load dashboard widgets via AJAX
    function loadDashboardWidgets() {
        // Recent logs widget
        if ($('#wpal-recent-logs-widget').length) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpal_get_recent_logs',
                    nonce: wpal_admin_vars.nonce
                },
                success: function(response) {
                    $('#wpal-recent-logs-widget').html(response);
                }
            });
        }

        // Activity chart widget
        if ($('#wpal-activity-chart-widget').length) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpal_get_activity_chart',
                    nonce: wpal_admin_vars.nonce
                },
                success: function(response) {
                    $('#wpal-activity-chart-widget').html(response);
                    initCharts();
                }
            });
        }

        // Top users widget
        if ($('#wpal-top-users-widget').length) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpal_get_top_users',
                    nonce: wpal_admin_vars.nonce
                },
                success: function(response) {
                    $('#wpal-top-users-widget').html(response);
                }
            });
        }

        // Severity breakdown widget
        if ($('#wpal-severity-breakdown-widget').length) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpal_get_severity_breakdown',
                    nonce: wpal_admin_vars.nonce
                },
                success: function(response) {
                    $('#wpal-severity-breakdown-widget').html(response);
                    initCharts();
                }
            });
        }
    }

    // Run diagnostics
    function runDiagnostics() {
        $('#wpal-run-diagnostics').on('click', function() {
            const $button = $(this);
            const $results = $('#wpal-diagnostics-results');
            
            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Running...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpal_run_diagnostics',
                    nonce: wpal_admin_vars.nonce
                },
                success: function(response) {
                    $results.html(response);
                    $button.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg> Run Diagnostics');
                },
                error: function() {
                    $results.html('<div class="wpal-alert wpal-alert-danger">An error occurred while running diagnostics.</div>');
                    $button.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg> Run Diagnostics');
                }
            });
        });
    }

    // View log details
    function viewLogDetails() {
        $('.wpal-view-log').on('click', function(e) {
            e.preventDefault();
            
            const logId = $(this).data('log-id');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpal_get_log_details',
                    nonce: wpal_admin_vars.nonce,
                    log_id: logId
                },
                success: function(response) {
                    // Create modal if it doesn't exist
                    if (!$('#wpal-log-details-modal').length) {
                        $('body').append('<div id="wpal-log-details-modal" class="wpal-modal"><div class="wpal-modal-content"><div class="wpal-modal-header"><h3>Log Details</h3><button type="button" class="wpal-modal-close">&times;</button></div><div class="wpal-modal-body"></div><div class="wpal-modal-footer"><button type="button" class="wpal-btn wpal-btn-secondary wpal-modal-close">Close</button></div></div></div>');
                    }
                    
                    // Set modal content and show
                    $('#wpal-log-details-modal .wpal-modal-body').html(response);
                    $('#wpal-log-details-modal').addClass('wpal-modal-show');
                    
                    // Close modal on click
                    $('.wpal-modal-close').on('click', function() {
                        $('#wpal-log-details-modal').removeClass('wpal-modal-show');
                    });
                    
                    // Close modal on outside click
                    $(window).on('click', function(e) {
                        if ($(e.target).is('#wpal-log-details-modal')) {
                            $('#wpal-log-details-modal').removeClass('wpal-modal-show');
                        }
                    });
                }
            });
        });
    }

    // Delete log entry
    function deleteLogEntry() {
        $('.wpal-delete-log').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm(wpal_admin_vars.confirm_delete)) {
                return;
            }
            
            const $row = $(this).closest('tr');
            const logId = $(this).data('log-id');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpal_delete_log',
                    nonce: wpal_admin_vars.delete_nonce,
                    log_id: logId
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data);
                    }
                }
            });
        });
    }

    // Delete all logs
    function deleteAllLogs() {
        $('#wpal-delete-all-logs').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm(wpal_admin_vars.confirm_delete_all)) {
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpal_delete_all_logs',
                    nonce: wpal_admin_vars.delete_nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert(response.data);
                    }
                }
            });
        });
    }

    // Export logs
    function exportLogs() {
        $('#wpal-export-form').on('submit', function(e) {
            const format = $('#wpal-export-format').val();
            const dateFrom = $('#wpal-export-date-from').val();
            const dateTo = $('#wpal-export-date-to').val();
            
            if (!format) {
                e.preventDefault();
                alert('Please select an export format.');
                return false;
            }
            
            if (!dateFrom || !dateTo) {
                e.preventDefault();
                alert('Please select a date range.');
                return false;
            }
            
            return true;
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        // Initialize DataTables
        if ($('#wpal-logs-table').length) {
            initDataTables();
        }
        
        // Load dashboard widgets
        loadDashboardWidgets();
        
        // Run diagnostics
        runDiagnostics();
        
        // View log details
        viewLogDetails();
        
        // Delete log entry
        deleteLogEntry();
        
        // Delete all logs
        deleteAllLogs();
        
        // Export logs
        exportLogs();
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize date pickers
        if ($.fn.datepicker) {
            $('.wpal-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                maxDate: '0'
            });
        }
    });

})(jQuery);