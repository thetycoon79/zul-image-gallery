<?php
/**
 * Gallery image source interface
 *
 * Defines the contract for image source implementations.
 * Allows different image sources (WP Media, External URLs) to be used interchangeably.
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Interfaces;

use Zul\Gallery\Domain\Entities\GalleryImage;

interface GalleryImageSourceInterface
{
    /**
     * Get the full image URL
     *
     * @param GalleryImage $image The image entity
     * @param string $size Image size (e.g., 'full', 'large', 'medium', 'thumbnail')
     * @return string The image URL
     */
    public function getImageUrl(GalleryImage $image, string $size = 'full'): string;

    /**
     * Get the thumbnail URL
     *
     * @param GalleryImage $image The image entity
     * @param string $size Thumbnail size
     * @return string The thumbnail URL
     */
    public function getThumbnailUrl(GalleryImage $image, string $size = 'thumbnail'): string;

    /**
     * Get the alt text for the image
     *
     * @param GalleryImage $image The image entity
     * @return string The alt text
     */
    public function getAltText(GalleryImage $image): string;

    /**
     * Check if this source supports the given image
     *
     * @param GalleryImage $image The image entity
     * @return bool True if this source can handle the image
     */
    public function supports(GalleryImage $image): bool;
}
