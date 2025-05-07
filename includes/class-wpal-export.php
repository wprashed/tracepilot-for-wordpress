<?php
/**
 * Export class for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Export {
    /**
     * Constructor
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Export logs
     */
    public function export_logs() {
        // Check nonce
        check_ajax_referer('wpal_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to export logs.', 'wp-activity-logger-pro'));
        }
        
        // Get export format
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';
        
        // Get filters
        $filter_user = isset($_POST['user']) ? sanitize_text_field($_POST['user']) : '';
        $filter_action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
        $filter_severity = isset($_POST['severity']) ? sanitize_text_field($_POST['severity']) : '';
        $filter_date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $filter_date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
        
        // Get logs
        global $wpdb;
        WPAL_Helpers::init();
        
        // Build query
        $query = "SELECT * FROM " . WPAL_Helpers::$db_table . " WHERE 1=1";
        $query_args = array();
        
        if ($filter_user) {
            $query .= " AND username LIKE %s";
            $query_args[] = '%' . $wpdb->esc_like($filter_user) . '%';
        }
        
        if ($filter_action) {
            $query .= " AND action LIKE %s";
            $query_args[] = '%' . $wpdb->esc_like($filter_action) . '%';
        }
        
        if ($filter_severity) {
            $query .= " AND severity = %s";
            $query_args[] = $filter_severity;
        }
        
        if ($filter_date_from) {
            $query .= " AND time >= %s";
            $query_args[] = $filter_date_from . ' 00:00:00';
        }
        
        if ($filter_date_to) {
            $query .= " AND time <= %s";
            $query_args[] = $filter_date_to . ' 23:59:59';
        }
        
        $query .= " ORDER BY time DESC";
        
        // Prepare query if there are arguments
        if (!empty($query_args)) {
            $query = $wpdb->prepare($query, $query_args);
        }
        
        // Get logs
        $logs = $wpdb->get_results($query, ARRAY_A);
        
        // Export based on format
        switch ($format) {
            case 'csv':
                $this->export_csv($logs);
                break;
            case 'json':
                $this->export_json($logs);
                break;
            case 'xml':
                $this->export_xml($logs);
                break;
            case 'pdf':
                $this->export_pdf($logs);
                break;
            default:
                $this->export_csv($logs);
        }
        
        wp_die();
    }

    /**
     * Export logs as CSV
     */
    private function export_csv($logs) {
        // Set headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=activity-logs-' . date('Y-m-d') . '.csv');
        
        // Create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        
        // Output the column headings
        fputcsv($output, array(
            'ID',
            'Time',
            'User ID',
            'Username',
            'User Role',
            'Action',
            'Object Type',
            'Object ID',
            'Object Name',
            'Context',
            'IP',
            'Browser',
            'Severity'
        ));
        
        // Output each row of the data
        foreach ($logs as $log) {
            fputcsv($output, $log);
        }
        
        // Close the file pointer
        fclose($output);
        exit;
    }

    /**
     * Export logs as JSON
     */
    private function export_json($logs) {
        // Set headers
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=activity-logs-' . date('Y-m-d') . '.json');
        
        // Output JSON
        echo json_encode($logs);
        exit;
    }

    /**
     * Export logs as XML
     */
    private function export_xml($logs) {
        // Set headers
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename=activity-logs-' . date('Y-m-d') . '.xml');
        
        // Create XML document
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><logs></logs>');
        
        // Add logs to XML
        foreach ($logs as $log) {
            $log_xml = $xml->addChild('log');
            
            foreach ($log as $key => $value) {
                if ($key === 'context') {
                    // Handle context as CDATA
                    $context = $log_xml->addChild($key);
                    $context_node = dom_import_simplexml($context);
                    $context_owner = $context_node->ownerDocument;
                    $context_node->appendChild($context_owner->createCDATASection($value));
                } else {
                    $log_xml->addChild($key, htmlspecialchars($value));
                }
            }
        }
        
        // Output XML
        echo $xml->asXML();
        exit;
    }

    /**
     * Export logs as PDF
     */
    private function export_pdf($logs) {
        // Check if TCPDF is available
        if (!class_exists('TCPDF')) {
            // If TCPDF is not available, fallback to CSV
            $this->export_csv($logs);
            return;
        }
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('WP Activity Logger Pro');
        $pdf->SetTitle('Activity Logs');
        $pdf->SetSubject('Activity Logs Export');
        $pdf->SetKeywords('WordPress, Activity, Logs, Export');
        
        // Set default header data
        $pdf->SetHeaderData('', 0, 'Activity Logs', 'Generated on ' . date('Y-m-d H:i:s'));
        
        // Set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Add a page
        $pdf->AddPage();
        
        // Create table header
        $html = '<table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Time</th>
                    <th>Username</th>
                    <th>Action</th>
                    <th>Severity</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>';
        
        // Add logs to table
        foreach ($logs as $log) {
            $html .= '<tr>
                <td>' . $log['id'] . '</td>
                <td>' . $log['time'] . '</td>
                <td>' . $log['username'] . '</td>
                <td>' . $log['action'] . '</td>
                <td>' . $log['severity'] . '</td>
                <td>' . $log['ip'] . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
        
        // Output HTML
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output('activity-logs-' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
}