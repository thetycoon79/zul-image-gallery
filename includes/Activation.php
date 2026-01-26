<?php
/**
 * Plugin activation/deactivation handling
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery;

class Activation
{
    public static function activate(): void
    {
        self::createTables();
        self::addCapabilities();
        self::setVersion();
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        // No data removal per spec
        flush_rewrite_rules();
    }

    private static function createTables(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charsetCollate = $wpdb->get_charset_collate();
        $galleryTable = $wpdb->prefix . 'zul_image_gallery';
        $imagesTable = $wpdb->prefix . 'zul_image_gallery_images';

        // Gallery table
        $gallerySql = "CREATE TABLE {$galleryTable} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            created_by BIGINT UNSIGNED NOT NULL,
            source ENUM('WP','External') NOT NULL DEFAULT 'WP',
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            create_dt DATETIME NOT NULL,
            modified_dt DATETIME NULL,
            PRIMARY KEY (id),
            KEY idx_status (status),
            KEY idx_source (source),
            KEY idx_created_by (created_by)
        ) {$charsetCollate};";

        // Images table
        $imagesSql = "CREATE TABLE {$imagesTable} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            gallery_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NULL,
            attachment_id BIGINT UNSIGNED NULL,
            attachment_url TEXT NULL,
            description TEXT NULL,
            created_by BIGINT UNSIGNED NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            create_dt DATETIME NOT NULL,
            modified_dt DATETIME NULL,
            PRIMARY KEY (id),
            KEY idx_gallery_id (gallery_id),
            KEY idx_gallery_status (gallery_id, status),
            KEY idx_attachment_id (attachment_id)
        ) {$charsetCollate};";

        dbDelta($gallerySql);
        dbDelta($imagesSql);
    }

    private static function addCapabilities(): void
    {
        Capabilities::addToRole('administrator');
    }

    private static function setVersion(): void
    {
        update_option('zul_gallery_version', ZUL_GALLERY_VERSION);
    }

    public static function maybeUpgrade(): void
    {
        $installedVersion = get_option('zul_gallery_version', '0');

        if (version_compare($installedVersion, ZUL_GALLERY_VERSION, '<')) {
            self::createTables();
            self::setVersion();
        }
    }
}
