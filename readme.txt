=== TracePilot for WordPress ===
Contributors: wprashed
Tags: activity log, audit log, security, diagnostics, monitoring, logging
Requires at least: 6.0
Tested up to: 6.9.4
Requires PHP: 7.4
Stable tag: 1.3.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track WordPress activity, investigate conflicts, review security signals, and export audit-ready logs from one modern admin dashboard.

== Description ==

TracePilot for WordPress helps site owners, agencies, and administrators understand what is happening inside WordPress.

The plugin records user and system activity, highlights suspicious patterns, offers diagnostics for common site issues, and provides a clear export workflow for compliance or troubleshooting.

= Features =

* 🧾 Activity audit log: Track key user and system actions such as logins, content edits, option changes, and software lifecycle events.
* ✍️ Content change tracking: Record post/page updates, publishes, unpublishes, trash/restore operations, and deletions with context.
* 🧩 Plugin and theme lifecycle: Log activation, deactivation, installs, deletes, and updates for faster change attribution.
* 🔎 Search and filters: Quickly find events by text search, severity, role, action key, date range, and site context (multisite).
* 📊 Admin dashboard: A modern dashboard view with summaries and quick access to recent activity.
* 🧰 Diagnostics scanner: Run site checks (PHP, cron, REST health, DB/table signals, server limits) and see a health score and issues.
* 🧪 Conflict detection signals: Identify potential hook collisions and generate a binary split-test plan.
* 🛡️ Safe mode debugging: Temporarily disable selected plugins only for your admin session so visitors are unaffected.
* 🔔 Real alert channels: Route alerts to Email, generic webhooks, Slack, Discord, and Telegram.
* 🧩 Threat review workflow: Flag suspicious patterns (failed logins, unusual logins, file integrity signals) and review them in admin.
* 🧬 File integrity: Build a baseline for core/plugin/theme files and scan for new, deleted, or modified files.
* 🧠 Vulnerability intelligence: Configure Wordfence, Patchstack, and WPScan lookups for installed plugins/themes/core.
* 📤 Exports: Download logs as CSV, JSON, XML, or a plain-text report for incident review.
* 🧹 Retention and suppression: Reduce noise with excluded actions, suppressed severities, and per-action retention rules.
* 🔐 Privacy and GDPR guardrails: IP anonymization, UI masking, context redaction keys, and per-user export/delete tools.
* 🌐 Multisite support: When used in multisite/network admin, aggregate logs across sites and filter by site.

= Built for administrators =

TracePilot for WordPress is designed for:

* site owners who need an audit trail
* agencies managing client sites
* support engineers investigating regressions
* administrators reviewing plugin conflicts and security signals
* teams handling compliance requests and log exports

= Developer-friendly foundation =

The plugin follows WordPress patterns for escaping, sanitization, AJAX nonce checks, and translatable strings. It also includes a helper API for custom activity entries.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install it from the WordPress admin plugins screen.
2. Activate the plugin through the `Plugins` screen in WordPress.
3. Open `TracePilot` in the admin menu.
4. Review the `Settings`, `Diagnostics`, and `Threat Detection` pages to configure the plugin for your workflow.

== Frequently Asked Questions ==

= What does the plugin log? =

It logs tracked user and system events such as authentication activity, settings changes, content updates, and selected plugin or theme operations.

= Can I filter the logs? =

Yes. The log stream supports filtering by text search, role, action, severity, date range, and site context on multisite installs.

= Does the plugin support multisite? =

Yes. The plugin includes multisite-aware log retrieval and site filters for supported admin views.

= Can I export data for privacy or compliance requests? =

Yes. You can export filtered logs or export/delete log history for a specific user from the settings tools.

= Does it support diagnostics and conflict testing? =

Yes. The diagnostics area includes system checks, issue explanations, change correlation, and admin-session safe mode for plugin conflict testing.

= Does the plugin include vulnerability scanning? =

It includes configuration for software vulnerability intelligence sources and combines that data with file-integrity checks when configured.

== Screenshots ==

1. Dashboard with activity summaries and charts.
2. Log stream with filters and event cards.
3. Detailed log modal with timeline context.
4. Diagnostics scanner with issue explanations and safe mode tools.
5. Threat Detection and vulnerability intelligence controls.
6. Export screen with filterable report generation.

== Changelog ==

= 1.3.3 =
* Fix: prevent “Exclude roles from logging” from accidentally excluding every role (which can make the logger appear broken).
* Add: post/page delete tracking and additional plugin/theme lifecycle events (install/delete signals via upgrader).
* Improve: log filtering reliability when user role context is missing.

= 1.3.2 =
* Improve: settings save flow reliability and admin asset loading.

= 1.3.1 =
* Rebranded plugin identity to TracePilot for WordPress in plugin metadata, docs, and admin menu labels.
* Updated admin menu icon to a security-focused shield icon.
* Refined naming language in key settings and export messages.

= 1.3.0 =
* Added WordPress.org-ready readme content and full repository documentation.
* Improved standards coverage for sanitization, escaping, and translatable strings in key admin flows.
* Added stricter Search Console option sanitization and export request handling.
* Refined logs and diagnostics UI behavior, including improved filters and control consistency.

= 1.2.9 =
* Added log stream action filters and refreshed checkbox styling.
* Improved diagnostics layout and card behavior.

= 1.2.8 =
* Reorganized settings into multiple tabs.
* Converted diagnostics into sub-tabs.

== Upgrade Notice ==

= 1.3.3 =
This release fixes a common configuration pitfall that could unintentionally disable logging and expands software lifecycle logging coverage.

= 1.3.1 =
This release introduces TracePilot branding updates and admin menu polish.
