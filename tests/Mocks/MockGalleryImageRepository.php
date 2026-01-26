<?php
/**
 * Mock gallery image repository for testing
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Mocks;

use Zul\Gallery\Interfaces\GalleryImageRepositoryInterface;
use Zul\Gallery\Domain\Entities\GalleryImage;

class MockGalleryImageRepository implements GalleryImageRepositoryInterface
{
    private array $images = [];
    private int $nextId = 1;

    public function findById(int $id): ?GalleryImage
    {
        return $this->images[$id] ?? null;
    }

    public function listByGalleryId(int $galleryId, array $filters = [], int $limit = -1, int $offset = 0): array
    {
        $results = array_filter(
            $this->images,
            fn($img) => $img->getGalleryId() === $galleryId
        );

        // Apply filters
        if (!empty($filters['status'])) {
            $results = array_filter($results, fn($img) => $img->getStatus()->value === $filters['status']);
        }

        $results = array_values($results);

        // Apply pagination
        if ($limit > 0) {
            $results = array_slice($results, $offset, $limit);
        }

        return $results;
    }

    public function countByGalleryId(int $galleryId, array $filters = []): int
    {
        return count($this->listByGalleryId($galleryId, $filters, -1, 0));
    }

    public function insert(GalleryImage $image): int
    {
        $id = $this->nextId++;
        $this->images[$id] = $image->withId($id);
        return $id;
    }

    public function insertMany(array $images): array
    {
        $ids = [];
        foreach ($images as $image) {
            $ids[] = $this->insert($image);
        }
        return $ids;
    }

    public function update(GalleryImage $image): bool
    {
        if (!$image->getId() || !isset($this->images[$image->getId()])) {
            return false;
        }

        $this->images[$image->getId()] = $image;
        return true;
    }

    public function delete(int $id): bool
    {
        if (!isset($this->images[$id])) {
            return false;
        }

        unset($this->images[$id]);
        return true;
    }

    public function deleteByGalleryId(int $galleryId): int
    {
        $count = 0;
        foreach ($this->images as $id => $image) {
            if ($image->getGalleryId() === $galleryId) {
                unset($this->images[$id]);
                $count++;
            }
        }
        return $count;
    }

    // Test helpers
    public function addImage(GalleryImage $image): void
    {
        $id = $image->getId() ?? $this->nextId++;
        $this->images[$id] = $image->withId($id);
    }

    public function clear(): void
    {
        $this->images = [];
        $this->nextId = 1;
    }

    public function getAll(): array
    {
        return $this->images;
    }
}
