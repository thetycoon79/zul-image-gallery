<?php
/**
 * Renderer resolver - resolves appropriate gallery renderer
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Services;

use Zul\Gallery\Interfaces\GalleryRendererInterface;
use Zul\Gallery\Renderers\FancyboxGalleryRenderer;

class RendererResolver
{
    /** @var GalleryRendererInterface[] */
    private array $renderers = [];
    private string $defaultRendererId = 'fancybox';

    public function __construct()
    {
        // Register default renderer
        $this->register(new FancyboxGalleryRenderer());
    }

    public function register(GalleryRendererInterface $renderer): void
    {
        $this->renderers[$renderer->getId()] = $renderer;
    }

    public function setDefault(string $rendererId): void
    {
        if (!isset($this->renderers[$rendererId])) {
            throw new \InvalidArgumentException("Renderer '{$rendererId}' not found");
        }

        $this->defaultRendererId = $rendererId;
    }

    public function resolve(?string $rendererId = null): GalleryRendererInterface
    {
        $rendererId = $rendererId ?? $this->defaultRendererId;

        // Allow filtering the renderer choice
        $rendererId = apply_filters('zul_gallery_renderer', $rendererId);

        if (isset($this->renderers[$rendererId])) {
            return $this->renderers[$rendererId];
        }

        // Fall back to default
        if (isset($this->renderers[$this->defaultRendererId])) {
            return $this->renderers[$this->defaultRendererId];
        }

        throw new \RuntimeException('No suitable renderer found');
    }

    public function getAvailableRenderers(): array
    {
        return array_keys($this->renderers);
    }

    public function hasRenderer(string $rendererId): bool
    {
        return isset($this->renderers[$rendererId]);
    }
}
