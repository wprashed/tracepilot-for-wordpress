# TracePilot

TracePilot is a modern WordPress activity logging, diagnostics, and threat review plugin for administrators who need clarity when something changes, breaks, or looks suspicious.

It captures important site events, explains issues in plain language, helps isolate plugin conflicts safely, and supports export and privacy workflows for audits and compliance.

## What TracePilot Helps You Do

- See what changed before a problem appeared.
- Track content, plugin, theme, and settings activity in one place.
- Review site health with diagnostics, conflict detection, and safe mode tools.
- Send alerts through multiple channels when high-signal events happen.
- Manage privacy requests with export/delete tools and redaction controls.

## Key Features

- 🧾 **Activity audit log**  
  Track important user and system actions such as logins, content edits, option changes, and software lifecycle events.

- ✍️ **Content change tracking**  
  Record post and page updates, publishes, unpublishes, trash/restore actions, and deletions with useful context.

- 🧩 **Plugin and theme lifecycle**  
  Log activation, deactivation, installs, deletes, and updates so changes are easier to trace.

- 🔎 **Search and filters**  
  Quickly find events by text, role, action, severity, date range, and site context in multisite setups.

- 📊 **Modern admin dashboard**  
  Review summaries, recent activity, and visual insights from one central screen.

- 🧰 **System diagnostics scanner**  
  Run checks across PHP, cron, REST health, database signals, and server limits with a health score and issue list.

- 🧪 **Conflict detection**  
  Identify possible hook collisions and build a safe split-test plan before making risky changes.

- 🛡️ **Safe mode debugging**  
  Temporarily disable selected plugins only for your admin session so visitors are not affected.

- 🔔 **Real alert channels**  
  Route alerts to Email, webhooks, Slack, Discord, or Telegram.

- 🧩 **Threat review workflow**  
  Flag suspicious patterns such as failed logins, unusual logins, and file integrity signals for admin review.

- 🧬 **File integrity monitoring**  
  Build a baseline for core, plugin, and theme files, then scan for new, deleted, or modified files.

- 🧠 **Vulnerability intelligence**  
  Configure optional lookups from Wordfence, Patchstack, and WPScan for installed plugins, themes, and WordPress core.

- 📤 **Export tools**  
  Download logs as CSV, JSON, XML, or plain text for incident review and reporting.

- 🧹 **Retention and suppression**  
  Reduce noise with excluded actions, suppressed severities, and per-action retention rules.

- 🔐 **Privacy and GDPR guardrails**  
  Use IP anonymization, UI masking, context redaction keys, and per-user export/delete tools.

- 🌐 **Multisite support**  
  Aggregate logs across sites and filter by site or blog ID in supported admin views.

## What Gets Logged

TracePilot focuses on practical, high-value events rather than endless noise.

- Content events: publish, update, unpublish, trash, restore, delete
- Authentication events: login, logout, failed login attempts
- Settings changes: options and configuration updates
- Plugin and theme changes: activation, deactivation, install, delete, update
- Diagnostics signals: scan findings, conflict hints, and change correlation entries

## Quick Start

1. Install and activate TracePilot.
2. Open `TracePilot` from the WordPress admin menu.
3. Go to `Activity Logs`.
4. Make a visible change such as updating a page or activating a plugin.
5. Refresh the log stream and open `View Details` on the newest entry.
6. Optionally enable alerts, privacy controls, and diagnostics based on your workflow.

## Built for Real Workflows

- Site owners who need an audit trail
- Agencies managing client sites
- Support engineers investigating regressions
- Administrators reviewing plugin conflicts and security signals
- Teams handling compliance requests and log exports

## Developer Notes

TracePilot follows WordPress standards for escaping, sanitization, nonce checks, and translatable strings.

If you extend the plugin or add custom logging from another plugin/theme, the helper API is available:

```php
TracePilot_Helpers::log_activity( $action, $description, $severity, $args );
```

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`, or install it from the WordPress admin Plugins screen.
2. Activate the plugin.
3. Open `TracePilot` in the admin menu.
4. Review the `Settings`, `Diagnostics`, and `Threat Detection` areas to configure the plugin for your workflow.

## FAQ

### What does TracePilot log?

It logs tracked user and system events such as authentication activity, settings changes, content updates, and selected plugin or theme operations.

### Can I filter the logs?

Yes. The log stream supports filtering by search text, role, action, severity, date range, and multisite context.

### Why don’t I see new logs?

Usually the event is being filtered by settings.

1. Check `Settings` -> `Privacy` and make sure your role is not excluded.
2. Check `Settings` -> `Retention` and `Suppression` to confirm the event is not being filtered out.
3. Try a high-signal event such as a failed login or plugin activation.

### Does TracePilot support multisite?

Yes. It includes multisite-aware log retrieval and site filters in supported admin views.

### Can I export data for privacy or compliance requests?

Yes. You can export filtered logs or export/delete log history for a specific user from the settings tools.

### Does it support diagnostics and conflict testing?

Yes. The diagnostics area includes system checks, issue explanations, change correlation, and admin-session safe mode for plugin conflict testing.

### Does it include vulnerability scanning?

Yes. It can combine optional vulnerability intelligence sources with file integrity signals when configured.

## External Services

TracePilot can connect to optional third-party services. These requests are feature-driven and only happen when the related feature is enabled and configured by an administrator.

### Telegram Bot API

- Purpose: Deliver threat and summary alerts to a Telegram chat.
- Data sent: Alert title, message text, site name, severity, and event metadata included in the alert body.
- When sent: Only when Telegram bot token and chat ID are configured and alert delivery is enabled.
- Terms: https://telegram.org/tos
- Privacy: https://telegram.org/privacy

### Wordfence Vulnerability Intelligence API

- Purpose: Check installed plugin, theme, and core versions against known vulnerabilities.
- Data sent: API key in the Authorization header if configured, plus request metadata for the vulnerability feed.
- When sent: Only when software vulnerability scans are run and Wordfence is selected.
- Terms: https://www.wordfence.com/terms-of-service/
- Privacy: https://www.wordfence.com/privacy-policy/

### Patchstack Vulnerability Database API

- Purpose: Enrich local software inventory checks with Patchstack vulnerability data.
- Data sent: HTTPS request metadata and optional API key if configured.
- When sent: Only when software vulnerability scans are run and Patchstack is selected.
- Terms: https://patchstack.com/terms-of-service/
- Privacy: https://patchstack.com/privacy-policy/

### WPScan API

- Purpose: Check WordPress core, plugins, and themes against WPScan vulnerability records.
- Data sent: API token if configured and queried software version/slug identifiers.
- When sent: Only when software vulnerability scans are run and WPScan is selected.
- Terms: https://wpscan.com/terms-of-service/
- Privacy: https://wpscan.com/privacy-policy/

### ip-api Geolocation

- Purpose: Resolve IP geolocation context for log and threat metadata.
- Data sent: The IP address being enriched.
- When sent: Only when geolocation is explicitly enabled by an administrator.
- Terms and privacy: https://ip-api.com/docs/legal

### Google Search Console API

- Purpose: Fetch search performance metrics for the optional Search Console page.
- Data sent: OAuth tokens, selected property URL, date range, and requested dimensions.
- When sent: Only after an administrator connects Google Search Console and requests analytics data.
- Terms: https://policies.google.com/terms
- Privacy: https://policies.google.com/privacy
- Additional user data policy: https://developers.google.com/terms/api-services-user-data-policy

## Screens

- Dashboard with activity summaries and charts
- Log stream with filters and event cards
- Detailed log view with timeline context
- Diagnostics scanner with safe mode tools
- Threat detection and vulnerability intelligence controls
- Export tools with filterable report generation

## Version

Current package version: `1.0.0`
