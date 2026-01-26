<?php
/**
 * Main plugin orchestrator
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery;

use Zul\Gallery\Assets\AssetManager;
use Zul\Gallery\Admin\Menu;
use Zul\Gallery\Admin\Controllers\GalleryController;
use Zul\Gallery\Admin\Controllers\GalleryImageController;
use Zul\Gallery\Frontend\Shortcodes\GalleryShortcode;
use Zul\Gallery\Repositories\GalleryRepository;
use Zul\Gallery\Repositories\GalleryImageRepository;
use Zul\Gallery\Services\GalleryService;
use Zul\Gallery\Services\GalleryImageService;
use Zul\Gallery\Services\SourceResolver;
use Zul\Gallery\Services\RendererResolver;
use Zul\Gallery\Support\Db;

class Plugin
{
    private static ?Plugin $instance = null;

    // Core dependencies
    private Db $db;
    private GalleryRepository $galleryRepository;
    private GalleryImageRepository $imageRepository;
    private GalleryService $galleryService;
    private GalleryImageService $imageService;
    private SourceResolver $sourceResolver;
    private RendererResolver $rendererResolver;

    // Components
    private AssetManager $assetManager;
    private ?Menu $adminMenu = null;
    private ?GalleryController $galleryController = null;
    private ?GalleryImageController $imageController = null;
    private ?GalleryShortcode $shortcode = null;

    public function __construct()
    {
        $this->initializeDependencies();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void
    {
        // Check for database upgrades
        Activation::maybeUpgrade();

        // Initialize components
        $this->initializeAssets();
        $this->initializeAdmin();
        $this->initializeFrontend();

        // Allow extensions
        do_action('zul_gallery_loaded', $this);
    }

    private function initializeDependencies(): void
    {
        // Database layer
        $this->db = new Db();

        // Repositories
        $this->galleryRepository = new GalleryRepository($this->db);
        $this->imageRepository = new GalleryImageRepository($this->db);

        // Services
        $this->galleryService = new GalleryService($this->galleryRepository);
        $this->imageService = new GalleryImageService($this->imageRepository, $this->galleryRepository);

        // Resolvers
        $this->sourceResolver = new SourceResolver();
        $this->rendererResolver = new RendererResolver();
    }

    private function initializeAssets(): void
    {
        $this->assetManager = new AssetManager();
        $this->assetManager->register();
    }

    private function initializeAdmin(): void
    {
        if (!is_admin()) {
            return;
        }

        // Gallery controller
        $this->galleryController = new GalleryController(
            $this->galleryService,
            $this->imageService
        );

        // Image controller (AJAX)
        $this->imageController = new GalleryImageController(
            $this->imageService,
            $this->sourceResolver
        );
        $this->imageController->registerAjaxHandlers();

        // Admin menu
        $this->adminMenu = new Menu($this->galleryController);
        $this->adminMenu->register();
    }

    private function initializeFrontend(): void
    {
        // Shortcode
        $this->shortcode = new GalleryShortcode(
            $this->galleryService,
            $this->imageService,
            $this->sourceResolver,
            $this->rendererResolver
        );

        add_action('init', [$this->shortcode, 'register']);
    }

    // Getters for external access
    public function getGalleryService(): GalleryService
    {
        return $this->galleryService;
    }

    public function getImageService(): GalleryImageService
    {
        return $this->imageService;
    }

    public function getSourceResolver(): SourceResolver
    {
        return $this->sourceResolver;
    }

    public function getRendererResolver(): RendererResolver
    {
        return $this->rendererResolver;
    }

    public function getGalleryRepository(): GalleryRepository
    {
        return $this->galleryRepository;
    }

    public function getImageRepository(): GalleryImageRepository
    {
        return $this->imageRepository;
    }
}
