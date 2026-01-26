<?php
/**
 * Source resolver - resolves appropriate image source for an image
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Services;

use Zul\Gallery\Interfaces\GalleryImageSourceInterface;
use Zul\Gallery\Domain\Entities\GalleryImage;
use Zul\Gallery\Sources\WpMediaImageSource;
use Zul\Gallery\Sources\ExternalImageSource;

class SourceResolver
{
    /** @var GalleryImageSourceInterface[] */
    private array $sources = [];

    public function __construct()
    {
        // Register default sources in priority order
        $this->register(new WpMediaImageSource());
        $this->register(new ExternalImageSource());
    }

    public function register(GalleryImageSourceInterface $source): void
    {
        $this->sources[] = $source;
    }

    public function resolve(GalleryImage $image): GalleryImageSourceInterface
    {
        foreach ($this->sources as $source) {
            if ($source->supports($image)) {
                return $source;
            }
        }

        throw new \RuntimeException('No suitable image source found for image');
    }

    public function resolveImageData(GalleryImage $image, string $size = 'full', string $thumbnailSize = 'medium'): array
    {
        $source = $this->resolve($image);

        return [
            'image' => $image,
            'url' => $source->getImageUrl($image, $size),
            'thumbnail' => $source->getThumbnailUrl($image, $thumbnailSize),
            'alt' => $source->getAltText($image),
        ];
    }

    public function resolveMultiple(array $images, string $size = 'full', string $thumbnailSize = 'medium'): array
    {
        $result = [];

        foreach ($images as $image) {
            $result[] = $this->resolveImageData($image, $size, $thumbnailSize);
        }

        return $result;
    }
}
