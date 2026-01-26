<?php
/**
 * SourceResolver tests
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Zul\Gallery\Services\SourceResolver;
use Zul\Gallery\Domain\Entities\GalleryImage;
use Zul\Gallery\Sources\WpMediaImageSource;
use Zul\Gallery\Sources\ExternalImageSource;

class SourceResolverTest extends TestCase
{
    private SourceResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new SourceResolver();
    }

    public function testResolveReturnsWpSourceForAttachmentId(): void
    {
        $image = new GalleryImage(
            galleryId: 1,
            createdBy: 1,
            attachmentId: 123
        );

        $source = $this->resolver->resolve($image);

        $this->assertInstanceOf(WpMediaImageSource::class, $source);
    }

    public function testResolveReturnsExternalSourceForAttachmentUrl(): void
    {
        $image = new GalleryImage(
            galleryId: 1,
            createdBy: 1,
            attachmentUrl: 'https://example.com/image.jpg'
        );

        $source = $this->resolver->resolve($image);

        $this->assertInstanceOf(ExternalImageSource::class, $source);
    }

    public function testResolveThrowsExceptionForNoSource(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No suitable image source found');

        $image = new GalleryImage(
            galleryId: 1,
            createdBy: 1
            // No attachment_id or attachment_url
        );

        $this->resolver->resolve($image);
    }

    public function testResolveMultipleReturnsArrayOfImageData(): void
    {
        // Mock WordPress functions for this test
        \Brain\Monkey\Functions\when('wp_get_attachment_image_url')->justReturn('https://example.com/full.jpg');
        \Brain\Monkey\Functions\when('get_post_meta')->justReturn('Alt text');

        $images = [
            new GalleryImage(galleryId: 1, createdBy: 1, attachmentId: 123, title: 'Image 1'),
            new GalleryImage(galleryId: 1, createdBy: 1, attachmentId: 456, title: 'Image 2'),
        ];

        $result = $this->resolver->resolveMultiple($images);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('image', $result[0]);
        $this->assertArrayHasKey('url', $result[0]);
        $this->assertArrayHasKey('thumbnail', $result[0]);
        $this->assertArrayHasKey('alt', $result[0]);
    }
}
