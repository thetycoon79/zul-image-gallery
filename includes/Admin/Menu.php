<?php
/**
 * Admin menu registration
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Admin;

use Zul\Gallery\Capabilities;
use Zul\Gallery\Admin\Controllers\GalleryController;

class Menu
{
    private GalleryController $galleryController;

    public function __construct(GalleryController $galleryController)
    {
        $this->galleryController = $galleryController;
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenuPages']);
    }

    public function addMenuPages(): void
    {
        // Main menu
        add_menu_page(
            __('ZUL Image Galleries', 'zul-gallery'),
            __('ZUL Galleries', 'zul-gallery'),
            Capabilities::VIEW_ADMIN,
            'zul-galleries',
            [$this->galleryController, 'listAction'],
            'dashicons-format-gallery',
            30
        );

        // All Galleries submenu (same as main)
        add_submenu_page(
            'zul-galleries',
            __('All Galleries', 'zul-gallery'),
            __('All Galleries', 'zul-gallery'),
            Capabilities::VIEW_ADMIN,
            'zul-galleries',
            [$this->galleryController, 'listAction']
        );

        // Add New Gallery submenu
        add_submenu_page(
            'zul-galleries',
            __('Add New Gallery', 'zul-gallery'),
            __('Add New', 'zul-gallery'),
            Capabilities::MANAGE,
            'zul-gallery-new',
            [$this->galleryController, 'createAction']
        );
    }

    public function getCurrentScreen(): string
    {
        $page = $_GET['page'] ?? '';

        if (strpos($page, 'zul-galler') === false) {
            return '';
        }

        $action = $_GET['action'] ?? 'list';

        return match ($page) {
            'zul-galleries' => match ($action) {
                'edit' => 'edit',
                'delete' => 'delete',
                default => 'list',
            },
            'zul-gallery-new' => 'create',
            default => '',
        };
    }
}
