<?php
/**
 * FancyboxGalleryRenderer tests
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Unit\Renderers;

use Zul\Gallery\Tests\TestCase;
use Zul\Gallery\Renderers\FancyboxGalleryRenderer;
use Zul\Gallery\Domain\Entities\Gallery;
use Zul\Gallery\Domain\Entities\GalleryImage;

class FancyboxGalleryRendererTest extends TestCase
{
    private FancyboxGalleryRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock WordPress escaping functions
        \Brain\Monkey\Functions\when('esc_attr')->returnArg(1);
        \Brain\Monkey\Functions\when('esc_url')->returnArg(1);
        \Brain\Monkey\Functions\when('esc_html')->returnArg(1);
        \Brain\Monkey\Functions\when('absint')->alias(function ($val) {
            return abs((int) $val);
        });

        $this->renderer = new FancyboxGalleryRenderer();
    }

    public function testGetIdReturnsFancybox(): void
    {
        $this->assertSame('fancybox', $this->renderer->getId());
    }

    public function testGetRequiredAssetsReturnsCorrectAssets(): void
    {
        $assets = $this->renderer->getRequiredAssets();

        $this->assertArrayHasKey('styles', $assets);
        $this->assertArrayHasKey('scripts', $assets);
        $this->assertContains('fancybox', $assets['styles']);
        $this->assertContains('zul-gallery-frontend', $assets['styles']);
        $this->assertContains('fancybox', $assets['scripts']);
        $this->assertContains('zul-gallery-frontend', $assets['scripts']);
    }

    public function testRenderReturnsEmptyStringForEmptyImages(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, id: 1);

        $html = $this->renderer->render($gallery, []);

        $this->assertSame('', $html);
    }

    public function testRenderOutputsGalleryContainer(): void
    {
        $gallery = new Gallery(title: 'Test Gallery', createdBy: 1, id: 42);
        $images = $this->createImageData();

        $html = $this->renderer->render($gallery, $images);

        $this->assertStringContainsString('id="zul-gallery-42"', $html);
        $this->assertStringContainsString('class="zul-gallery', $html);
        $this->assertStringContainsString('data-gallery-id="42"', $html);
    }

    public function testRenderOutputsCorrectColumnClass(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, id: 1);
        $images = $this->createImageData();

        $html = $this->renderer->render($gallery, $images, ['columns' => 4]);

        $this->assertStringContainsString('zul-gallery-columns-4', $html);
    }

    public function testRenderClampsColumnsToValidRange(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, id: 1);
        $images = $this->createImageData();

        // Test max clamping
        $html = $this->renderer->render($gallery, $images, ['columns' => 10]);
        $this->assertStringContainsString('zul-gallery-columns-6', $html);

        // Test min clamping
        $html = $this->renderer->render($gallery, $images, ['columns' => 0]);
        $this->assertStringContainsString('zul-gallery-columns-1', $html);
    }

    public function testRenderOutputsCustomClass(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, id: 1);
        $images = $this->createImageData();

        $html = $this->renderer->render($gallery, $images, ['class' => 'my-custom-class']);

        $this->assertStringContainsString('my-custom-class', $html);
    }

    public function testRenderOutputsImageLinks(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, id: 1);
        $images = $this->createImageData();

        $html = $this->renderer->render($gallery, $images);

        $this->assertStringContainsString('href="https://example.com/full.jpg"', $html);
        $this->assertStringContainsString('data-fancybox="zul-gallery-1"', $html);
        $this->assertStringContainsString('data-caption="Test description"', $html);
    }

    public function testRenderOutputsThumbnailImages(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, id: 1);
        $images = $this->createImageData();

        $html = $this->renderer->render($gallery, $images);

        $this->assertStringContainsString('src="https://example.com/thumb.jpg"', $html);
        $this->assertStringContainsString('alt="Test alt"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
    }

    public function testRenderOutputsCaptionsWhenEnabled(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, id: 1);
        $images = $this->createImageData();

        $html = $this->renderer->render($gallery, $images, ['show_captions' => true]);

        $this->assertStringContainsString('class="zul-gallery-caption"', $html);
        $this->assertStringContainsString('Test Image', $html);
    }

    public function testRenderHidesCaptionsWhenDisabled(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, id: 1);
        $images = $this->createImageData();

        $html = $this->renderer->render($gallery, $images, ['show_captions' => false]);

        $this->assertStringNotContainsString('class="zul-gallery-caption"', $html);
    }

    private function createImageData(): array
    {
        $image = new GalleryImage(
            galleryId: 1,
            createdBy: 1,
            title: 'Test Image',
            attachmentId: 123,
            description: 'Test description',
            id: 1
        );

        return [
            [
                'image' => $image,
                'url' => 'https://example.com/full.jpg',
                'thumbnail' => 'https://example.com/thumb.jpg',
                'alt' => 'Test alt',
            ],
        ];
    }
}
