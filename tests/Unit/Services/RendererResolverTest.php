<?php
/**
 * RendererResolver tests
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Unit\Services;

use Zul\Gallery\Tests\TestCase;
use Zul\Gallery\Services\RendererResolver;
use Zul\Gallery\Renderers\FancyboxGalleryRenderer;
use Zul\Gallery\Interfaces\GalleryRendererInterface;

class RendererResolverTest extends TestCase
{
    private RendererResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock apply_filters to return the input
        \Brain\Monkey\Functions\when('apply_filters')->returnArg(2);

        $this->resolver = new RendererResolver();
    }

    public function testResolveReturnsDefaultFancyboxRenderer(): void
    {
        $renderer = $this->resolver->resolve();

        $this->assertInstanceOf(FancyboxGalleryRenderer::class, $renderer);
        $this->assertSame('fancybox', $renderer->getId());
    }

    public function testResolveReturnsSpecificRenderer(): void
    {
        $renderer = $this->resolver->resolve('fancybox');

        $this->assertInstanceOf(FancyboxGalleryRenderer::class, $renderer);
    }

    public function testResolveReturnsDefaultForUnknownRenderer(): void
    {
        $renderer = $this->resolver->resolve('unknown');

        $this->assertInstanceOf(FancyboxGalleryRenderer::class, $renderer);
    }

    public function testRegisterAddsNewRenderer(): void
    {
        $mockRenderer = $this->createMock(GalleryRendererInterface::class);
        $mockRenderer->method('getId')->willReturn('custom');

        $this->resolver->register($mockRenderer);

        $this->assertTrue($this->resolver->hasRenderer('custom'));
    }

    public function testSetDefaultChangesDefaultRenderer(): void
    {
        $mockRenderer = $this->createMock(GalleryRendererInterface::class);
        $mockRenderer->method('getId')->willReturn('custom');

        $this->resolver->register($mockRenderer);
        $this->resolver->setDefault('custom');

        $renderer = $this->resolver->resolve();

        $this->assertSame('custom', $renderer->getId());
    }

    public function testSetDefaultThrowsExceptionForUnknownRenderer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Renderer 'unknown' not found");

        $this->resolver->setDefault('unknown');
    }

    public function testGetAvailableRenderersReturnsRegisteredIds(): void
    {
        $renderers = $this->resolver->getAvailableRenderers();

        $this->assertContains('fancybox', $renderers);
    }

    public function testHasRendererReturnsTrueForRegistered(): void
    {
        $this->assertTrue($this->resolver->hasRenderer('fancybox'));
    }

    public function testHasRendererReturnsFalseForUnregistered(): void
    {
        $this->assertFalse($this->resolver->hasRenderer('unknown'));
    }
}
