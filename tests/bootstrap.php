<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Zul\Gallery\Tests
 */

// Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define constants needed by the plugin
define('ABSPATH', '/tmp/wordpress/');
define('ZUL_GALLERY_VERSION', '1.0.0');
define('ZUL_GALLERY_PLUGIN_DIR', dirname(__DIR__) . '/');
define('ZUL_GALLERY_PLUGIN_URL', 'http://example.com/wp-content/plugins/zul-gallery-plugin/');
define('ZUL_GALLERY_PLUGIN_BASENAME', 'zul-gallery-plugin/zul-gallery-plugin.php');

// Initialize Brain\Monkey
\Brain\Monkey\setUp();

// Register shutdown function to tear down Brain\Monkey
register_shutdown_function(function () {
    \Brain\Monkey\tearDown();
});
