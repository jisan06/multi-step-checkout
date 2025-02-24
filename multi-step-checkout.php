<?php
/**
 * Plugin Name: Multi Step Checkout
 * Description: A WooCommerce multi-step checkout with mobile OTP or email login.
 * Version: 1.0
 * Author: Jisan
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define plugin paths
define('MSC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MSC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once MSC_PLUGIN_PATH . 'includes/init.php';

// Enqueue scripts and styles
function msc_enqueue_scripts() {
        wp_enqueue_style('msc-style', MSC_PLUGIN_URL . 'assets/css/style.css');
        wp_enqueue_script('msc-script', MSC_PLUGIN_URL . 'assets/js/script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'msc_enqueue_scripts');

// Activation & Deactivation hooks
function msc_activate() {
    // Any setup needed on activation
}
register_activation_hook(__FILE__, 'msc_activate');

function msc_deactivate() {
    // Cleanup on deactivation
}
register_deactivation_hook(__FILE__, 'msc_deactivate');
