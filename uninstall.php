<?php
/**
 * Uninstall handler for ZUL Image Gallery
 *
 * @package Zul\Gallery
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Remove custom tables
$gallery_table = $wpdb->prefix . 'zul_image_gallery';
$images_table = $wpdb->prefix . 'zul_image_gallery_images';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query("DROP TABLE IF EXISTS {$images_table}");
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query("DROP TABLE IF EXISTS {$gallery_table}");

// Remove capabilities from all roles
$capabilities = ['zul_gallery_manage', 'zul_gallery_view_admin'];

foreach (wp_roles()->roles as $role_name => $role_info) {
    $role = get_role($role_name);
    if ($role) {
        foreach ($capabilities as $cap) {
            $role->remove_cap($cap);
        }
    }
}

// Clean up options if any
delete_option('zul_gallery_version');
