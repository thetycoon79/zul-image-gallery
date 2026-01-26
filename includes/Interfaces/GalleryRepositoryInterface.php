<?php
/**
 * Gallery repository interface
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Interfaces;

use Zul\Gallery\Domain\Entities\Gallery;

interface GalleryRepositoryInterface
{
    /**
     * Find gallery by ID
     */
    public function findById(int $id): ?Gallery;

    /**
     * Find active gallery by ID
     */
    public function findActiveById(int $id): ?Gallery;

    /**
     * List galleries with optional filters
     *
     * @param array $filters Optional filters (status, source, search)
     * @param int $limit Number of results
     * @param int $offset Offset for pagination
     * @return Gallery[]
     */
    public function list(array $filters = [], int $limit = 20, int $offset = 0): array;

    /**
     * Count galleries matching filters
     */
    public function count(array $filters = []): int;

    /**
     * Insert a new gallery
     *
     * @return int The new gallery ID
     */
    public function insert(Gallery $gallery): int;

    /**
     * Update an existing gallery
     */
    public function update(Gallery $gallery): bool;

    /**
     * Delete a gallery by ID
     */
    public function delete(int $id): bool;
}
