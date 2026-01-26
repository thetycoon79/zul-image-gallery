<?php
/**
 * Gallery service - business logic for galleries
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Services;

use Zul\Gallery\Interfaces\GalleryRepositoryInterface;
use Zul\Gallery\Domain\Entities\Gallery;
use Zul\Gallery\Domain\ValueObjects\GallerySource;
use Zul\Gallery\Domain\ValueObjects\Status;
use Zul\Gallery\Support\Validator;

class GalleryService
{
    private GalleryRepositoryInterface $repository;

    public function __construct(GalleryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function findById(int $id): ?Gallery
    {
        return $this->repository->findById($id);
    }

    public function getActiveGallery(int $id): ?Gallery
    {
        return $this->repository->findActiveById($id);
    }

    public function list(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        return $this->repository->list($filters, $limit, $offset);
    }

    public function count(array $filters = []): int
    {
        return $this->repository->count($filters);
    }

    public function create(array $data, int $userId): Gallery
    {
        $this->validateGalleryData($data);

        $source = isset($data['source'])
            ? GallerySource::fromString($data['source'])
            : GallerySource::WP;

        $status = isset($data['status'])
            ? Status::fromString($data['status'])
            : Status::ACTIVE;

        $gallery = new Gallery(
            title: $data['title'],
            createdBy: $userId,
            description: $data['description'] ?? null,
            source: $source,
            status: $status
        );

        $id = $this->repository->insert($gallery);

        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): Gallery
    {
        $gallery = $this->repository->findById($id);

        if (!$gallery) {
            throw new \InvalidArgumentException('Gallery not found');
        }

        $this->validateGalleryData($data, true);

        if (isset($data['title'])) {
            $gallery = $gallery->withTitle($data['title']);
        }

        if (array_key_exists('description', $data)) {
            $gallery = $gallery->withDescription($data['description']);
        }

        if (isset($data['status'])) {
            $gallery = $gallery->withStatus(Status::fromString($data['status']));
        }

        if (isset($data['source'])) {
            $gallery = $gallery->withSource(GallerySource::fromString($data['source']));
        }

        $this->repository->update($gallery);

        return $this->repository->findById($id);
    }

    public function updateStatus(int $id, string $status): Gallery
    {
        return $this->update($id, ['status' => $status]);
    }

    public function delete(int $id): bool
    {
        $gallery = $this->repository->findById($id);

        if (!$gallery) {
            throw new \InvalidArgumentException('Gallery not found');
        }

        return $this->repository->delete($id);
    }

    private function validateGalleryData(array $data, bool $isUpdate = false): void
    {
        $validator = new Validator();

        if (!$isUpdate || isset($data['title'])) {
            $validator->required('title', $data['title'] ?? null, 'Gallery title is required.');
            $validator->maxLength('title', $data['title'] ?? '', 255);
        }

        if (isset($data['status'])) {
            $validator->inArray('status', $data['status'], ['active', 'inactive', 'draft']);
        }

        if (isset($data['source'])) {
            $validator->inArray('source', $data['source'], ['WP', 'External']);
        }

        if ($validator->hasErrors()) {
            $errors = $validator->getErrors();
            throw new \InvalidArgumentException(reset($errors));
        }
    }
}
