<?php
/**
 * Gallery renderer interface
 *
 * Defines the contract for gallery rendering implementations.
 * Allows different UI renderers (Fancybox, Masonry, etc.) to be used interchangeably.
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Interfaces;

use Zul\Gallery\Domain\Entities\Gallery;

interface GalleryRendererInterface
{
    /**
     * Render the gallery HTML
     *
     * @param Gallery $gallery The gallery entity
     * @param array $images Array of image data with resolved URLs
     *                      Each item contains: 'image' => GalleryImage, 'url' => string, 'thumbnail' => string, 'alt' => string
     * @param array $options Rendering options (columns, class, etc.)
     * @return string The rendered HTML
     */
    public function render(Gallery $gallery, array $images, array $options = []): string;

    /**
     * Get the assets required by this renderer
     *
     * @return array Array with 'styles' and 'scripts' keys containing asset handles
     */
    public function getRequiredAssets(): array;

    /**
     * Get the renderer identifier
     *
     * @return string Unique renderer identifier
     */
    public function getId(): string;
}
