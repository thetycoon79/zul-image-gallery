<?php
/**
 * Plugin Name: ZUL Image Gallery
 * Plugin URI: https://example.com/zul-gallery
 * Description: Image gallery management with extensible architecture for WordPress
 * Version: 1.0.0
 * Author: ZUL
 * Author URI: https://example.com
 * Text Domain: zul-gallery
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package Zul\Gallery
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('ZUL_GALLERY_VERSION', '1.0.0');
define('ZUL_GALLERY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ZUL_GALLERY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ZUL_GALLERY_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
require_once ZUL_GALLERY_PLUGIN_DIR . 'includes/Autoloader.php';
\Zul\Gallery\Autoloader::register();

// Activation/Deactivation hooks
register_activation_hook(__FILE__, ['Zul\\Gallery\\Activation', 'activate']);
register_deactivation_hook(__FILE__, ['Zul\\Gallery\\Activation', 'deactivate']);

// Initialize plugin on plugins_loaded
add_action('plugins_loaded', function () {
    $plugin = new \Zul\Gallery\Plugin();
    $plugin->init();
});
