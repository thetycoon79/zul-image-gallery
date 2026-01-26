<?php
/**
 * External URL image source (stub for future implementation)
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Sources;

use Zul\Gallery\Interfaces\GalleryImageSourceInterface;
use Zul\Gallery\Domain\Entities\GalleryImage;

class ExternalImageSource implements GalleryImageSourceInterface
{
    public function getImageUrl(GalleryImage $image, string $size = 'full'): string
    {
        return $image->getAttachmentUrl() ?? '';
    }

    public function getThumbnailUrl(GalleryImage $image, string $size = 'thumbnail'): string
    {
        // External sources don't have separate thumbnails by default
        // Future: Could implement thumbnail service or URL manipulation
        return $image->getAttachmentUrl() ?? '';
    }

    public function getAltText(GalleryImage $image): string
    {
        return $image->getTitle() ?? '';
    }

    public function supports(GalleryImage $image): bool
    {
        return $image->getAttachmentId() === null && $image->getAttachmentUrl() !== null;
    }
}
