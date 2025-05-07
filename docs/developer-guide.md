# WP Activity Logger Pro - Developer Guide

## Introduction

This guide is intended for developers who want to integrate with WP Activity Logger Pro, either to log custom events or to extend the plugin's functionality.

## Logging Custom Events

### Basic Usage

To log a custom event, use the `WPAL_Helpers::log_activity()` method:

\`\`\`php
// Make sure the helper class is initialized
WPAL_Helpers::init();

// Log a simple activity
WPAL_Helpers::log_activity(
    'custom_action_name',    // Action identifier
    'Description of action', // Human-readable description
    'info'                   // Severity: 'info', 'warning', or 'error'
);
\`\`\`

### Advanced Usage

For more detailed logging, you can include additional context:

\`\`\`php
// Log activity with context
WPAL_Helpers::log_activity(
    'product_purchased',                  // Action identifier
    'User purchased Product X',           // Human-readable description
    'info',                               // Severity
    array(
        'object_type' => 'product',       // Type of object affected
        'object_id'   => 123,             // ID of the object
        'object_name' => 'Product X',     // Name of the object
        'context'     => array(           // Additional context (will be JSON encoded)
            'price'   => 49.99,
            'quantity' => 2,
            'total'   => 99.98
        )
    )
);
\`\`\`

### Available Severity Levels

- `info`: Normal activities, informational only
- `warning`: Activities that might require attention
- `error`: Critical activities that indicate problems

### Logging User Actions

When logging actions performed by users other than the current user:

\`\`\`php
// Log activity for a specific user
WPAL_Helpers::log_activity(
    'custom_user_action',
    'User performed a custom action',
    'info',
    array(
        'user_id' => 42  // Specify the user ID
    )
);
\`\`\`

## Hooks and Filters

### Actions

#### `wpal_before_log_activity`

Fired before an activity is logged.

\`\`\`php
add_action('wpal_before_log_activity', function($action, $description, $severity, $args) {
    // Do something before logging
}, 10, 4);
\`\`\`

#### `wpal_after_log_activity`

Fired after an activity has been logged.

\`\`\`php
add_action('wpal_after_log_activity', function($log_id, $action, $description, $severity, $args) {
    // Do something after logging
    // $log_id contains the ID of the newly created log entry
}, 10, 5);
\`\`\`

#### `wpal_log_deleted`

Fired when a log entry is deleted.

\`\`\`php
add_action('wpal_log_deleted', function($log_id) {
    // Do something when a log is deleted
}, 10, 1);
\`\`\`

### Filters

#### `wpal_log_data`

Filter the data before it's inserted into the database.

\`\`\`php
add_filter('wpal_log_data', function($log_data, $action, $description, $severity, $args) {
    // Modify $log_data before it's saved
    return $log_data;
}, 10, 5);
\`\`\`

#### `wpal_should_log_activity`

Determine whether an activity should be logged.

\`\`\`php
add_filter('wpal_should_log_activity', function($should_log, $action, $description, $severity, $args) {
    // Return false to prevent logging
    if ($action === 'some_action_to_ignore') {
        return false;
    }
    return $should_log;
}, 10, 5);
\`\`\`

#### `wpal_log_retention_days`

Filter the number of days to keep logs.

\`\`\`php
add_filter('wpal_log_retention_days', function($days) {
    // Change the retention period
    return 60; // Keep logs for 60 days
}, 10, 1);
\`\`\`

## Database Schema

The plugin stores logs in a custom table with the following structure:

\`\`\`sql
CREATE TABLE {$wpdb->prefix}wpal_activity_log (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    time datetime NOT NULL,
    user_id bigint(20) unsigned DEFAULT NULL,
    username varchar(60) DEFAULT NULL,
    user_role varchar(255) DEFAULT NULL,
    action varchar(255) NOT NULL,
    description text NOT NULL,
    severity varchar(20) NOT NULL DEFAULT 'info',
    object_type varchar(255) DEFAULT NULL,
    object_id varchar(255) DEFAULT NULL,
    object_name varchar(255) DEFAULT NULL,
    ip varchar(45) DEFAULT NULL,
    browser varchar(255) DEFAULT NULL,
    context longtext DEFAULT NULL,
    PRIMARY KEY (id),
    KEY time (time),
    KEY user_id (user_id),
    KEY action (action),
    KEY severity (severity)
);
\`\`\`

## Extending the Plugin

### Adding Custom Widgets

To add a custom widget to the dashboard:

1. Create a template file in your theme or plugin
2. Register it using the `wpal_dashboard_widgets` filter

\`\`\`php
add_filter('wpal_dashboard_widgets', function($widgets) {
    $widgets['my_custom_widget'] = array(
        'title' => 'My Custom Widget',
        'callback' => 'my_custom_widget_callback',
        'icon' => 'bar-chart-2', // Feather icon name
        'position' => 'column-1' // 'column-1' or 'column-2'
    );
    return $widgets;
});

function my_custom_widget_callback() {
    // Output your widget content
    echo '<div>Custom widget content</div>';
}
\`\`\`

### Adding Export Formats

To add a custom export format:

\`\`\`php
add_filter('wpal_export_formats', function($formats) {
    $formats['custom_format'] = array(
        'label' => 'My Custom Format',
        'callback' => 'my_custom_export_callback',
        'icon' => 'file-text' // Feather icon name
    );
    return $formats;
});

function my_custom_export_callback($logs, $args) {
    // Process logs and return the formatted content
    $output = ''; // Generate your formatted output
    return $output;
}
\`\`\`

### Adding Custom Notification Channels

To add a custom notification channel:

\`\`\`php
add_filter('wpal_notification_channels', function($channels) {
    $channels['custom_channel'] = array(
        'label' => 'My Custom Channel',
        'callback' => 'my_custom_notification_callback',
        'icon' => 'bell' // Feather icon name
    );
    return $channels;
});

function my_custom_notification_callback($event, $log_data) {
    // Send notification through your custom channel
    // Return true on success, false on failure
    return true;
}
\`\`\`

## Best Practices

### Performance Considerations

- Log only significant events to avoid database bloat
- Use appropriate severity levels
- Consider adding indexes if you're querying logs in custom ways
- Use the context field for detailed data rather than putting everything in the description

### Security Considerations

- Never log sensitive information like passwords or API keys
- Be mindful of personal data logging for GDPR compliance
- Sanitize all data before logging
- Use proper capability checks when displaying log data

### Compatibility

- Prefix all your custom actions with your plugin/theme slug to avoid conflicts
- Check if the WPAL_Helpers class exists before using it
- Use the provided hooks rather than modifying the plugin directly

## Example Implementations

### WooCommerce Integration

\`\`\`php
// Log product purchases
add_action('woocommerce_order_status_completed', 'log_woocommerce_purchase');

function log_woocommerce_purchase($order_id) {
    if (!class_exists('WPAL_Helpers')) {
        return;
    }
    
    WPAL_Helpers::init();
    
    $order = wc_get_order($order_id);
    $items = $order->get_items();
    $products = array();
    
    foreach ($items as $item) {
        $products[] = $item->get_name() . ' (x' . $item->get_quantity() . ')';
    }
    
    WPAL_Helpers::log_activity(
        'woocommerce_purchase',
        sprintf('Order #%s completed for %s', $order->get_order_number(), $order->get_formatted_billing_full_name()),
        'info',
        array(
            'object_type' => 'order',
            'object_id' => $order_id,
            'object_name' => 'Order #' . $order->get_order_number(),
            'context' => array(
                'total' => $order->get_total(),
                'products' => $products,
                'payment_method' => $order->get_payment_method_title()
            )
        )
    );
}
\`\`\`

### Custom Post Type Integration

\`\`\`php
// Log custom post type activities
add_action('save_post_my_custom_post', 'log_custom_post_save', 10, 3);

function log_custom_post_save($post_id, $post, $update) {
    if (!class_exists('WPAL_Helpers') || wp_is_post_revision($post_id)) {
        return;
    }
    
    WPAL_Helpers::init();
    
    $action = $update ? 'updated' : 'created';
    
    WPAL_Helpers::log_activity(
        'custom_post_' . $action,
        sprintf('Custom post "%s" was %s', get_the_title($post_id), $action),
        'info',
        array(
            'object_type' => 'post',
            'object_id' => $post_id,
            'object_name' => get_the_title($post_id)
        )
    );
}
\`\`\`

## Conclusion

WP Activity Logger Pro provides a robust framework for tracking activities in WordPress. By using the provided API and hooks, you can integrate your own plugins and themes with the logging system to provide comprehensive activity tracking for your users.

For additional support or to report issues, please contact our development team at dev-support@example.com.