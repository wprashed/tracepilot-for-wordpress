/**
 * TracePilot page-specific interactions.
 */
(function($) {
    'use strict';

    if (typeof window.tracepilot_admin_vars === 'undefined') {
        return;
    }

    const vars = window.tracepilot_admin_vars;
    const i18n = vars.i18n || {};

    function t(key, fallback) {
        return i18n[key] || fallback || '';
    }

    function request(data) {
        return $.ajax({
            url: vars.ajax_url,
            type: 'POST',
            data: Object.assign({ nonce: vars.nonce }, data)
        });
    }

    function parseJsonAttribute(value, fallback) {
        if (!value) {
            return fallback;
        }
        try {
            return JSON.parse(value);
        } catch (error) {
            return fallback;
        }
    }

    function initDashboardChart() {
        const holder = document.getElementById('tracepilot-dashboard-trend-data');
        if (!holder || typeof window.tracepilotRenderLineChart !== 'function') {
            return;
        }

        const labels = parseJsonAttribute(holder.getAttribute('data-labels'), []);
        const values = parseJsonAttribute(holder.getAttribute('data-values'), []);
        window.tracepilotRenderLineChart('tracepilot-dashboard-trend', labels, values);
    }

    function initDynamicChartCanvases(root) {
        const context = root || document;
        const charts = context.querySelectorAll('.tracepilot-dynamic-chart[data-chart-type]');
        charts.forEach(function(canvas) {
            if (canvas.getAttribute('data-chart-rendered') === '1') {
                return;
            }

            const chartType = canvas.getAttribute('data-chart-type');
            const labels = parseJsonAttribute(canvas.getAttribute('data-chart-labels'), []);
            const values = parseJsonAttribute(canvas.getAttribute('data-chart-values'), []);

            if (chartType === 'line' && typeof window.tracepilotRenderLineChart === 'function') {
                window.tracepilotRenderLineChart(canvas.id, labels, values);
                canvas.setAttribute('data-chart-rendered', '1');
            } else if (chartType === 'doughnut' && typeof window.tracepilotRenderDoughnutChart === 'function') {
                window.tracepilotRenderDoughnutChart(canvas.id, labels, values);
                canvas.setAttribute('data-chart-rendered', '1');
            }
        });
    }

    function initLogSeverityChart() {
        const holder = document.getElementById('tracepilot-logs-severity-data');
        if (!holder || typeof window.tracepilotRenderDoughnutChart !== 'function') {
            return;
        }

        const labels = parseJsonAttribute(holder.getAttribute('data-labels'), []);
        const values = parseJsonAttribute(holder.getAttribute('data-values'), []);
        window.tracepilotRenderDoughnutChart('tracepilot-logs-severity', labels, values);
    }

    function initAnalyticsPage() {
        if (!$('#tracepilot-analytics-form').length) {
            return;
        }

        let chart = null;

        function renderAnalytics(config, title, insights) {
            const canvas = document.getElementById('tracepilot-analytics-chart');
            if (!canvas || typeof window.Chart === 'undefined') {
                return;
            }

            if (chart) {
                chart.destroy();
            }

            chart = new window.Chart(canvas, config);
            $('#tracepilot-analytics-title').text(title);

            const box = $('#tracepilot-analytics-insights').empty();
            if (Array.isArray(insights) && insights.length) {
                insights.forEach(function(line) {
                    box.append('<div class="tracepilot-list-row"><div>' + $('<div>').text(line).html() + '</div></div>');
                });
            }
        }

        function loadAnalytics() {
            const chartType = $('#tracepilot-chart-type').val();
            const dateRange = $('#tracepilot-date-range').val();
            const groupBy = $('#tracepilot-group-by').val();

            request({
                action: 'tracepilot_get_analytics_data',
                chart_type: chartType,
                date_range: dateRange,
                group_by: groupBy
            }).done(function(response) {
                if (!response.success) {
                    return;
                }

                let type = 'bar';
                const options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: chartType === 'severity_distribution',
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                };

                if (chartType === 'activity_over_time') {
                    type = 'line';
                    options.plugins.legend.display = false;
                } else if (chartType === 'severity_distribution') {
                    type = 'doughnut';
                    delete options.scales;
                }

                renderAnalytics(
                    {
                        type: type,
                        data: response.data,
                        options: options
                    },
                    $('#tracepilot-chart-type option:selected').text(),
                    response.data.insights || []
                );
            });
        }

        $('#tracepilot-chart-type').on('change', function() {
            $('#tracepilot-group-by-wrap').toggle($(this).val() === 'activity_over_time');
        });

        $('#tracepilot-analytics-form').on('submit', function(event) {
            event.preventDefault();
            loadAnalytics();
        });

        loadAnalytics();
    }

    function initServerRecommendationsPage() {
        const button = $('#tracepilot-analyze-server');
        if (!button.length) {
            return;
        }

        button.on('click', function() {
            const trigger = $(this);
            trigger.prop('disabled', true).text(t('analyzing', 'Analyzing...'));

            request({ action: 'tracepilot_analyze_server_needs' }).done(function(response) {
                if (!response.success || !response.data) {
                    window.alert((response.data && response.data.message) ? response.data.message : t('unable_analyze_server', 'Unable to analyze server needs.'));
                    return;
                }

                const data = response.data;
                $('#tracepilot-stat-total-logs').text(Number(data.stats.total_logs || 0).toLocaleString());
                $('#tracepilot-stat-logs-per-day').text(Number(data.stats.logs_per_day || 0).toLocaleString());
                $('#tracepilot-stat-peak-logs').text(Number(data.stats.peak_logs_per_hour || 0).toLocaleString());
                $('#tracepilot-stat-db-size').text(data.stats.db_size || '');

                $('#tracepilot-server-software').text(data.current_server.server_software || '');
                $('#tracepilot-php-version').text(data.current_server.php_version || '');
                $('#tracepilot-mysql-version').text(data.current_server.mysql_version || '');
                $('#tracepilot-memory-limit').text(data.current_server.memory_limit || '');
                $('#tracepilot-max-execution-time').text((data.current_server.max_execution_time || 0) + 's');
                $('#tracepilot-post-max-size').text(data.current_server.post_max_size || '');
                $('#tracepilot-upload-max-filesize').text(data.current_server.upload_max_filesize || '');

                $('#tracepilot-rec-storage').text((data.recommendations.storage || 0) + ' GB storage');
                $('#tracepilot-rec-cpu').text((data.recommendations.cpu || 0) + ' CPU cores');
                $('#tracepilot-rec-ram').text((data.recommendations.ram || 0) + ' GB RAM');
                $('#tracepilot-rec-bandwidth').text((data.recommendations.bandwidth || 0) + ' GB/month');
                $('#tracepilot-rec-hosting-type').text('Recommended hosting type: ' + (data.recommendations.hosting_type || ''));
                $('#tracepilot-rec-explanation').html(($('<div>').text(data.recommendations.explanation || '').html() || '').replace(/\n/g, '<br>'));
            }).always(function() {
                trigger.prop('disabled', false).text(t('analyze_server_needs', 'Analyze Server Needs'));
            });
        });
    }

    function renderThreatRows(threats) {
        return threats.map(function(threat) {
            const severity = threat.severity || 'info';
            const badgeClass = severity === 'high' ? 'danger' : (severity === 'medium' ? 'warning' : 'info');
            const badge = '<span class="tracepilot-badge tracepilot-badge-' + badgeClass + '">' + severity.charAt(0).toUpperCase() + severity.slice(1) + '</span>';
            const label = $('<div>').text((threat.type || '').replace(/_/g, ' ')).html();
            const description = $('<div>').text(threat.description || '').html();
            const threatIp = $('<div>').text(threat.ip || '—').html();
            const threatTime = $('<div>').text(threat.last_attempt || threat.login_time || threat.time || '—').html();
            const userAction = threat.user_id ? '<button type="button" class="tracepilot-btn tracepilot-btn-secondary tracepilot-force-logout" data-user-id="' + Number(threat.user_id) + '">' + t('force_logout', 'Force Logout') + '</button>' : '';
            const ipAction = threat.raw_ip ? '<button type="button" class="tracepilot-btn tracepilot-btn-secondary tracepilot-block-ip" data-ip="' + $('<div>').text(threat.raw_ip).html() + '">' + t('block_ip', 'Block IP') + '</button>' : '';

            return '<tr>' +
                '<td data-label="' + $('<div>').text(t('severity', 'Severity')).html() + '">' + badge + '</td>' +
                '<td data-label="' + $('<div>').text(t('type', 'Type')).html() + '">' + label + '</td>' +
                '<td data-label="' + $('<div>').text(t('description', 'Description')).html() + '">' + description + '</td>' +
                '<td data-label="' + $('<div>').text(t('ip', 'IP')).html() + '">' + threatIp + '</td>' +
                '<td data-label="' + $('<div>').text(t('time', 'Time')).html() + '">' + threatTime + '</td>' +
                '<td data-label="' + $('<div>').text(t('actions', 'Actions')).html() + '" class="tracepilot-table-actions">' + ipAction + userAction + '</td>' +
            '</tr>';
        });
    }

    function severityBadge(severity) {
        const badgeClass = (severity === 'critical' || severity === 'high') ? 'danger' : (severity === 'medium' ? 'warning' : 'info');
        return '<span class="tracepilot-badge tracepilot-badge-' + badgeClass + '">' + severity.charAt(0).toUpperCase() + severity.slice(1) + '</span>';
    }

    function renderVulnerabilityReport(data) {
        $('#tracepilot-vulnerability-status').text('Latest report generated ' + (data.generated_at || '') + '.');
        $('#tracepilot-vulnerability-summary').show();
        $('#tracepilot-vuln-affected').text(data.summary.affected || 0);
        $('#tracepilot-vuln-critical').text(data.summary.critical || 0);
        $('#tracepilot-vuln-high').text(data.summary.high || 0);
        $('#tracepilot-vuln-clean').text(data.summary.clean || 0);

        const notesBox = $('#tracepilot-vulnerability-notes').empty();
        if (data.notes && data.notes.length) {
            data.notes.forEach(function(note) {
                notesBox.append('<div class="tracepilot-list-row"><div>' + $('<div>').text(note).html() + '</div></div>');
            });
            notesBox.show();
        } else {
            notesBox.hide();
        }

        const rows = (data.items || []).map(function(item) {
            let recommendation = t('no_action_needed', 'No action needed right now.');
            if (item.findings && item.findings.length && item.findings[0].fixed_in) {
                recommendation = t('update_to', 'Update to') + ' ' + item.findings[0].fixed_in + ' ' + t('or_newer', 'or newer.');
            } else if (item.local_change_count) {
                recommendation = t('review_integrity', 'Review recent file changes against the integrity baseline.');
            } else if (item.findings && item.findings.length) {
                recommendation = t('review_advisory', 'Review the linked advisory and update or replace this component.');
            }

            const findings = [];
            (item.findings || []).slice(0, 2).forEach(function(finding) {
                findings.push('<div class="tracepilot-list-subtext"><strong>' + $('<div>').text(finding.provider).html() + ':</strong> ' + $('<div>').text(finding.title).html() + '</div>');
            });

            const localPill = item.local_change_count ? '<span class="tracepilot-meta-pill">' + item.local_change_count + ' ' + $('<div>').text(t('local_file_changes', 'local file changes')).html() + '</span>' : '';

            return '<tr>' +
                '<td data-label="Component"><strong>' + $('<div>').text(item.name || '').html() + '</strong><div class="tracepilot-list-subtext">' + $('<div>').text(item.slug || '').html() + '</div></td>' +
                '<td data-label="Type">' + $('<div>').text((item.type || '').charAt(0).toUpperCase() + (item.type || '').slice(1)).html() + '</td>' +
                '<td data-label="Version">' + $('<div>').text(item.version || '').html() + '</td>' +
                '<td data-label="' + $('<div>').text(t('severity', 'Severity')).html() + '">' + severityBadge(item.severity || 'low') + '</td>' +
                '<td data-label="Findings">' + (item.finding_count || 0) + localPill + findings.join('') + '</td>' +
                '<td data-label="Recommended fix">' + $('<div>').text(recommendation).html() + '</td>' +
            '</tr>';
        });

        $('#tracepilot-vulnerability-table').html(rows.length ? rows.join('') : '<tr><td data-label="Status" colspan="6">' + $('<div>').text(t('no_components_in_scope', 'No installed components were found in the current scan scope.')).html() + '</td></tr>');
    }

    function renderIntegrity(response) {
        const list = $('#tracepilot-integrity-results').empty();
        const changes = response && response.data && Array.isArray(response.data.changes) ? response.data.changes : [];
        if (!changes.length) {
            list.append('<div class="tracepilot-list-row"><div>' + $('<div>').text(response.data.message || '').html() + '</div></div>');
            return;
        }

        changes.forEach(function(change) {
            list.append('<div class="tracepilot-list-row"><div><strong>' + $('<div>').text(change.type || '').html() + '</strong><div class="tracepilot-list-subtext">' + $('<div>').text(change.path || '').html() + '</div></div><div class="tracepilot-meta-pill">' + $('<div>').text(change.group || '').html() + '</div></div>');
        });
    }

    function initThreatDetectionPage() {
        if (!$('#tracepilot-analyze-threats').length) {
            return;
        }

        $('#tracepilot-save-threat-settings').on('click', function() {
            const feedback = $('#tracepilot-threat-settings-feedback');
            feedback.text(t('saving', 'Saving...'));

            const options = {
                enable_threat_detection: 0,
                enable_threat_notifications: 0,
                monitor_failed_logins: 0,
                monitor_unusual_logins: 0,
                monitor_file_changes: 0,
                monitor_privilege_escalation: 0
            };

            new window.FormData(document.getElementById('tracepilot-threat-settings-form')).forEach(function(value, key) {
                const match = key.match(/^wpal_options\[([^\]]+)\]$/);
                if (match) {
                    options[match[1]] = value;
                }
            });

            request({
                action: 'tracepilot_save_settings',
                wpal_options: options
            }).done(function(response) {
                feedback.text(response.success ? response.data.message : t('unable_save_settings', 'Unable to save settings.'));
            });
        });

        $('#tracepilot-analyze-threats').on('click', function() {
            const button = $(this);
            button.prop('disabled', true).text(t('analyzing', 'Analyzing...'));
            $('#tracepilot-threat-loading').text(t('analyzing_logs', 'Analyzing activity logs for potential threats...')).show();
            $('#tracepilot-threat-results, #tracepilot-no-threats, #tracepilot-threat-summary').hide();

            request({ action: 'tracepilot_analyze_threats' }).done(function(response) {
                if (!response.success || !response.data) {
                    window.alert((response.data && response.data.message) ? response.data.message : t('unable_analyze_threats', 'Unable to analyze threats.'));
                    return;
                }

                const data = response.data;
                $('#tracepilot-total-threats').text(data.summary.total || 0);
                $('#tracepilot-high-threats').text(data.summary.high || 0);
                $('#tracepilot-medium-threats').text(data.summary.medium || 0);
                $('#tracepilot-low-threats').text(data.summary.low || 0);
                $('#tracepilot-threat-summary').show();

                if (!Array.isArray(data.threats) || !data.threats.length) {
                    $('#tracepilot-no-threats').show();
                    return;
                }

                $('#tracepilot-threats-table').html(renderThreatRows(data.threats).join(''));
                $('#tracepilot-threat-results').show();
            }).always(function() {
                $('#tracepilot-threat-loading').hide();
                button.prop('disabled', false).text(t('analyze_threats', 'Analyze Threats'));
            });
        });

        $('#tracepilot-scan-vulnerabilities').on('click', function() {
            const button = $(this);
            button.prop('disabled', true).text(t('scanning', 'Scanning...'));
            $('#tracepilot-vulnerability-status').text(t('checking_software', 'Checking installed software against vulnerability intelligence providers...'));

            request({ action: 'tracepilot_scan_vulnerabilities' }).done(function(response) {
                if (!response.success || !response.data) {
                    window.alert((response.data && response.data.message) ? response.data.message : t('unable_scan_software', 'Unable to scan software.'));
                    return;
                }

                renderVulnerabilityReport(response.data);
            }).always(function() {
                button.prop('disabled', false).text(t('scan_software', 'Scan Software'));
            });
        });

        $('#tracepilot-build-baseline').on('click', function() {
            request({ action: 'tracepilot_build_file_baseline' }).done(function(response) {
                if (response.success && response.data) {
                    $('#tracepilot-integrity-status').text(t('baseline_created_with', 'Baseline created with %d files.').replace('%d', response.data.count || 0));
                }
            });
        });

        $('#tracepilot-scan-integrity').on('click', function() {
            request({ action: 'tracepilot_scan_file_integrity' }).done(function(response) {
                if (response.success) {
                    renderIntegrity(response);
                }
            });
        });
    }

    $(document).ready(function() {
        initDashboardChart();
        initLogSeverityChart();
        initDynamicChartCanvases();
        initAnalyticsPage();
        initServerRecommendationsPage();
        initThreatDetectionPage();
    });
})(jQuery);
