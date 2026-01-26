<?php
/**
 * Gallery image repository interface
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Interfaces;

use Zul\Gallery\Domain\Entities\GalleryImage;

interface GalleryImageRepositoryInterface
{
    /**
     * Find image by ID
     */
    public function findById(int $id): ?GalleryImage;

    /**
     * List images by gallery ID
     *
     * @param int $galleryId Gallery ID
     * @param array $filters Optional filters (status)
     * @param int $limit Number of results (-1 for all)
     * @param int $offset Offset for pagination
     * @return GalleryImage[]
     */
    public function listByGalleryId(int $galleryId, array $filters = [], int $limit = -1, int $offset = 0): array;

    /**
     * Count images in a gallery
     */
    public function countByGalleryId(int $galleryId, array $filters = []): int;

    /**
     * Insert a single image
     *
     * @return int The new image ID
     */
    public function insert(GalleryImage $image): int;

    /**
     * Insert multiple images
     *
     * @param GalleryImage[] $images
     * @return int[] Array of new image IDs
     */
    public function insertMany(array $images): array;

    /**
     * Update an existing image
     */
    public function update(GalleryImage $image): bool;

    /**
     * Delete an image by ID
     */
    public function delete(int $id): bool;

    /**
     * Delete all images for a gallery
     */
    public function deleteByGalleryId(int $galleryId): int;
}
