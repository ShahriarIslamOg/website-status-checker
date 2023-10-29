<?php
/**
 * Plugin Name: Website Status Checker
 * Description: A WordPress plugin to check the status of websites and pages.
 * Author: Shahriar Islam
 * Author URI: https://shahriarislam.com/
 * Version: 1.0
 */

function website_status_form() {
    ob_start();
    ?>
    <form method="post" action="">
        <label for="url">URL:</label>
        <input type="text" name="url" id="url" />
        <label for="status-type">Status Type:</label>
        <select name="status-type" id="status-type">
            <option value="website">Website Status</option>
            <option value="page">Page Status</option>
        </select>
        <input type="submit" value="Check Status" />
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('website_status_form', 'website_status_form');

// Shortcode to display the status
function check_status_result($atts) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $url = sanitize_text_field($_POST['url']);
        $status_type = sanitize_text_field($_POST['status-type']);

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            if ($status_type === 'website') {
                $host = parse_url($url, PHP_URL_HOST);
                $host = str_replace('www.', '', $host); // Remove www if present

                $response = get_headers($url, 1);
                if ($response && isset($response[0])) {
                    if (strpos($response[0], '200') !== false) {
                        echo "Website is up. Host: $host";
                    } else {
                        echo "Website is down. Host: $host";
                    }
                } else {
                    echo "Website is down. Host: $host";
                }
            } elseif ($status_type === 'page') {
                $http_response = wp_safe_remote_head($url);
                if (is_wp_error($http_response)) {
                    echo "Failed to check page status.";
                } else {
                    $response_code = wp_remote_retrieve_response_code($http_response);
                    if ($response_code == 200) {
                        echo "Page exists. URL: $url";
                    } elseif ($response_code == 404) {
                        echo "Page not found. URL: $url";
                    } else {
                        echo "Page status: $response_code. URL: $url";
                    }
                }
            }
        } else {
            echo "Invalid URL.";
        }
    }
}
add_shortcode('check_status_result', 'check_status_result');

// Create a Dashboard Menu
function add_plugin_menu() {
    add_menu_page(
        'Website Status Checker',
        'Status Checker',
        'manage_options',
        'status-checker-menu',
        'status_checker_menu_page'
    );
}
add_action('admin_menu', 'add_plugin_menu');

// Dashboard Menu Page with Shortcode Instructions and Custom CSS Box
function status_checker_menu_page() {
    echo '<div class="wrap">';
    echo '<h2>Website Status Checker</h2>';
    echo '<h3>Shortcode Instructions:</h3>';
    echo '<p>Use the following shortcodes to check website or page status:</p>';
    echo '<p><strong>[website_status_form]</strong> - Display the status check form.</p>';
    echo '<p><strong>[check_status_result]</strong> - Display the status result.</p>';
}
