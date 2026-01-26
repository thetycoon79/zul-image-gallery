<?php
/**
 * WordPress Media Library image source
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Sources;

use Zul\Gallery\Interfaces\GalleryImageSourceInterface;
use Zul\Gallery\Domain\Entities\GalleryImage;

class WpMediaImageSource implements GalleryImageSourceInterface
{
    public function getImageUrl(GalleryImage $image, string $size = 'full'): string
    {
        if (!$image->getAttachmentId()) {
            return '';
        }

        $url = wp_get_attachment_image_url($image->getAttachmentId(), $size);
        return $url ?: '';
    }

    public function getThumbnailUrl(GalleryImage $image, string $size = 'thumbnail'): string
    {
        return $this->getImageUrl($image, $size);
    }

    public function getAltText(GalleryImage $image): string
    {
        if (!$image->getAttachmentId()) {
            return $image->getTitle() ?? '';
        }

        $alt = get_post_meta($image->getAttachmentId(), '_wp_attachment_image_alt', true);
        return $alt ?: ($image->getTitle() ?? '');
    }

    public function supports(GalleryImage $image): bool
    {
        return $image->getAttachmentId() !== null;
    }

    public function getImageSrcset(GalleryImage $image, string $size = 'full'): string
    {
        if (!$image->getAttachmentId()) {
            return '';
        }

        return wp_get_attachment_image_srcset($image->getAttachmentId(), $size) ?: '';
    }

    public function getImageSizes(GalleryImage $image, string $size = 'full'): string
    {
        if (!$image->getAttachmentId()) {
            return '';
        }

        return wp_get_attachment_image_sizes($image->getAttachmentId(), $size) ?: '';
    }

    public function getImageMeta(GalleryImage $image): array
    {
        if (!$image->getAttachmentId()) {
            return [];
        }

        $meta = wp_get_attachment_metadata($image->getAttachmentId());
        return $meta ?: [];
    }
}
