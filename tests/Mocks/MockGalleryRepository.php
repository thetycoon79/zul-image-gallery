<?php
/**
 * Mock gallery repository for testing
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Mocks;

use Zul\Gallery\Interfaces\GalleryRepositoryInterface;
use Zul\Gallery\Domain\Entities\Gallery;

class MockGalleryRepository implements GalleryRepositoryInterface
{
    private array $galleries = [];
    private int $nextId = 1;

    public function findById(int $id): ?Gallery
    {
        return $this->galleries[$id] ?? null;
    }

    public function findActiveById(int $id): ?Gallery
    {
        $gallery = $this->galleries[$id] ?? null;

        if ($gallery && $gallery->isActive()) {
            return $gallery;
        }

        return null;
    }

    public function list(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $results = array_values($this->galleries);

        // Apply filters
        if (!empty($filters['status'])) {
            $results = array_filter($results, fn($g) => $g->getStatus()->value === $filters['status']);
        }

        if (!empty($filters['source'])) {
            $results = array_filter($results, fn($g) => $g->getSource()->value === $filters['source']);
        }

        // Apply pagination
        if ($limit > 0) {
            $results = array_slice($results, $offset, $limit);
        }

        return array_values($results);
    }

    public function count(array $filters = []): int
    {
        return count($this->list($filters, -1, 0));
    }

    public function insert(Gallery $gallery): int
    {
        $id = $this->nextId++;
        $this->galleries[$id] = $gallery->withId($id);
        return $id;
    }

    public function update(Gallery $gallery): bool
    {
        if (!$gallery->getId() || !isset($this->galleries[$gallery->getId()])) {
            return false;
        }

        $this->galleries[$gallery->getId()] = $gallery;
        return true;
    }

    public function delete(int $id): bool
    {
        if (!isset($this->galleries[$id])) {
            return false;
        }

        unset($this->galleries[$id]);
        return true;
    }

    // Test helpers
    public function addGallery(Gallery $gallery): void
    {
        $id = $gallery->getId() ?? $this->nextId++;
        $this->galleries[$id] = $gallery->withId($id);
    }

    public function clear(): void
    {
        $this->galleries = [];
        $this->nextId = 1;
    }

    public function getAll(): array
    {
        return $this->galleries;
    }
}
