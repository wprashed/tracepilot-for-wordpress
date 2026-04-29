<?php
/**
 * WP Activity Logger Geolocation
 *
 * @package WP Activity Logger
 * @since 1.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}

class TracePilot_Geolocation {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_tracepilot_get_ip_geolocation', array($this, 'ajax_get_ip_geolocation'));
    }

    /**
     * AJAX get IP geolocation
     */
    public function ajax_get_ip_geolocation() {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'tracepilot_nonce')) {
            wp_send_json_error(array('message' => __('Invalid security token.', 'tracepilot')));
        }
        
        // Check permissions
        if (!TracePilot_Helpers::current_user_can_manage()) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'tracepilot')));
        }
        
        // Get IP
        $ip = isset($_POST['ip']) ? sanitize_text_field(wp_unslash($_POST['ip'])) : '';
        
        if (empty($ip)) {
            wp_send_json_error(array('message' => __('IP address is required.', 'tracepilot')));
        }
        
        // Get geolocation data
        $geo_data = $this->get_ip_geolocation($ip);
        
        if (is_wp_error($geo_data)) {
            wp_send_json_error(array('message' => $geo_data->get_error_message()));
        }
        
        wp_send_json_success($geo_data);
    }
    
    /**
     * Get IP geolocation
     */
    public function get_ip_geolocation($ip) {
        $settings = TracePilot_Helpers::get_settings();
        if (empty($settings['enable_geolocation'])) {
            return array(
                'country' => __('Unavailable', 'tracepilot'),
                'country_code' => 'NA',
                'city' => __('Unavailable', 'tracepilot'),
                'region' => __('Unavailable', 'tracepilot'),
                'continent' => __('Unavailable', 'tracepilot'),
                'latitude' => 0,
                'longitude' => 0,
                'isp' => __('Unavailable', 'tracepilot'),
                'timezone' => __('Unavailable', 'tracepilot')
            );
        }

        // Check if IP is valid
        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
            return array(
                'country' => __('Local', 'tracepilot'),
                'country_code' => 'LO',
                'city' => __('Local', 'tracepilot'),
                'region' => __('Local', 'tracepilot'),
                'continent' => __('Local', 'tracepilot'),
                'latitude' => 0,
                'longitude' => 0,
                'isp' => __('Local', 'tracepilot'),
                'timezone' => __('Local', 'tracepilot')
            );
        }
        
        // Try to get from cache
        $cache_key = 'tracepilot_geo_' . md5($ip);
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Call geolocation API
        $response = wp_remote_get(
            'https://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,message,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,isp,org,as,continent,continentCode',
            array(
                'timeout' => 10,
            )
        );
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($data) || isset($data['status']) && $data['status'] === 'fail') {
            return new WP_Error('geolocation_error', isset($data['message']) ? $data['message'] : __('Failed to get geolocation data.', 'tracepilot'));
        }
        
        // Format data
        $geo_data = array(
            'country' => isset($data['country']) ? sanitize_text_field($data['country']) : __('Unknown', 'tracepilot'),
            'country_code' => isset($data['countryCode']) ? sanitize_text_field($data['countryCode']) : 'XX',
            'city' => isset($data['city']) ? sanitize_text_field($data['city']) : __('Unknown', 'tracepilot'),
            'region' => isset($data['regionName']) ? sanitize_text_field($data['regionName']) : __('Unknown', 'tracepilot'),
            'continent' => isset($data['continent']) ? sanitize_text_field($data['continent']) : __('Unknown', 'tracepilot'),
            'latitude' => isset($data['lat']) ? floatval($data['lat']) : 0,
            'longitude' => isset($data['lon']) ? floatval($data['lon']) : 0,
            'isp' => isset($data['isp']) ? sanitize_text_field($data['isp']) : __('Unknown', 'tracepilot'),
            'timezone' => isset($data['timezone']) ? sanitize_text_field($data['timezone']) : __('Unknown', 'tracepilot')
        );
        
        // Cache result for 1 week
        set_transient($cache_key, $geo_data, WEEK_IN_SECONDS);
        
        return $geo_data;
    }
}
