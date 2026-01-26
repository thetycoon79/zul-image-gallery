<?php
/**
 * Asset manager - handles script and style enqueueing
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Assets;

class AssetManager
{
    public function register(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('wp_enqueue_scripts', [$this, 'registerFrontendAssets']);
    }

    public function enqueueAdminAssets(string $hook): void
    {
        // Only load on our admin pages
        if (strpos($hook, 'zul-galler') === false && strpos($hook, 'zul_galler') === false) {
            return;
        }

        // WordPress media uploader
        wp_enqueue_media();

        // Admin styles
        wp_enqueue_style(
            'zul-gallery-admin',
            ZUL_GALLERY_PLUGIN_URL . 'assets/css/admin-gallery.css',
            [],
            ZUL_GALLERY_VERSION
        );

        // Admin scripts
        wp_enqueue_script(
            'zul-gallery-admin',
            ZUL_GALLERY_PLUGIN_URL . 'assets/js/admin-gallery.js',
            ['jquery', 'wp-util', 'underscore'],
            ZUL_GALLERY_VERSION,
            true
        );

        // Localize script
        wp_localize_script('zul-gallery-admin', 'zulGalleryAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('zul_gallery_images'),
            'i18n' => [
                'selectImages' => __('Select Images', 'zul-gallery'),
                'addToGallery' => __('Add to Gallery', 'zul-gallery'),
                'removeImage' => __('Remove Image', 'zul-gallery'),
                'confirmRemove' => __('Are you sure you want to remove this image?', 'zul-gallery'),
                'saving' => __('Saving...', 'zul-gallery'),
                'saved' => __('Saved', 'zul-gallery'),
                'error' => __('An error occurred. Please try again.', 'zul-gallery'),
            ],
        ]);
    }

    public function registerFrontendAssets(): void
    {
        // Fancybox from CDN
        wp_register_style(
            'fancybox',
            'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css',
            [],
            '5.0'
        );

        wp_register_script(
            'fancybox',
            'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js',
            [],
            '5.0',
            true
        );

        // Plugin frontend styles
        wp_register_style(
            'zul-gallery-frontend',
            ZUL_GALLERY_PLUGIN_URL . 'assets/css/frontend-gallery.css',
            [],
            ZUL_GALLERY_VERSION
        );

        // Plugin frontend scripts
        wp_register_script(
            'zul-gallery-frontend',
            ZUL_GALLERY_PLUGIN_URL . 'assets/js/frontend-gallery.js',
            ['fancybox'],
            ZUL_GALLERY_VERSION,
            true
        );
    }
}
