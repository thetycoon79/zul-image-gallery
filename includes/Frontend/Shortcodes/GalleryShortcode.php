<?php
/**
 * Gallery shortcode handler
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Frontend\Shortcodes;

use Zul\Gallery\Services\GalleryService;
use Zul\Gallery\Services\GalleryImageService;
use Zul\Gallery\Services\SourceResolver;
use Zul\Gallery\Services\RendererResolver;

class GalleryShortcode
{
    private GalleryService $galleryService;
    private GalleryImageService $imageService;
    private SourceResolver $sourceResolver;
    private RendererResolver $rendererResolver;
    private bool $assetsEnqueued = false;

    public function __construct(
        GalleryService $galleryService,
        GalleryImageService $imageService,
        SourceResolver $sourceResolver,
        RendererResolver $rendererResolver
    ) {
        $this->galleryService = $galleryService;
        $this->imageService = $imageService;
        $this->sourceResolver = $sourceResolver;
        $this->rendererResolver = $rendererResolver;
    }

    public function register(): void
    {
        add_shortcode('zul_gallery', [$this, 'render']);
    }

    public function render($atts): string
    {
        $atts = shortcode_atts([
            'id' => 0,
            'columns' => 3,
            'limit' => -1,
            'class' => '',
            'orderby' => 'create_dt',
            'order' => 'ASC',
            'size' => 'full',
            'thumbnail_size' => 'medium',
            'show_captions' => 'yes',
            'renderer' => null,
        ], $atts, 'zul_gallery');

        $galleryId = absint($atts['id']);

        if (!$galleryId) {
            return $this->renderError(__('Gallery ID is required.', 'zul-gallery'));
        }

        // Fetch active gallery only
        $gallery = $this->galleryService->getActiveGallery($galleryId);

        if (!$gallery) {
            // Inactive or missing gallery produces no output
            return '';
        }

        // Fetch active images only
        $images = $this->imageService->getActiveImagesByGallery($galleryId, [
            'limit' => (int) $atts['limit'],
            'orderby' => sanitize_text_field($atts['orderby']),
            'order' => sanitize_text_field($atts['order']),
        ]);

        if (empty($images)) {
            return '';
        }

        // Build image data with resolved URLs
        $imageData = $this->sourceResolver->resolveMultiple(
            $images,
            sanitize_text_field($atts['size']),
            sanitize_text_field($atts['thumbnail_size'])
        );

        // Enqueue frontend assets
        $this->enqueueAssets();

        // Resolve renderer
        $renderer = $this->rendererResolver->resolve($atts['renderer']);

        // Render HTML
        return $renderer->render($gallery, $imageData, [
            'columns' => absint($atts['columns']),
            'class' => sanitize_html_class($atts['class']),
            'show_captions' => $atts['show_captions'] === 'yes',
        ]);
    }

    private function enqueueAssets(): void
    {
        if ($this->assetsEnqueued) {
            return;
        }

        wp_enqueue_style('fancybox');
        wp_enqueue_style('zul-gallery-frontend');
        wp_enqueue_script('fancybox');
        wp_enqueue_script('zul-gallery-frontend');

        $this->assetsEnqueued = true;
    }

    private function renderError(string $message): string
    {
        if (!current_user_can('manage_options')) {
            return '';
        }

        return sprintf(
            '<div class="zul-gallery-error">%s</div>',
            esc_html($message)
        );
    }
}
