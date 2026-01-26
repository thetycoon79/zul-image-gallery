<?php
/**
 * Gallery image service - business logic for gallery images
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Services;

use Zul\Gallery\Interfaces\GalleryImageRepositoryInterface;
use Zul\Gallery\Interfaces\GalleryRepositoryInterface;
use Zul\Gallery\Domain\Entities\GalleryImage;
use Zul\Gallery\Domain\ValueObjects\Status;
use Zul\Gallery\Support\Validator;

class GalleryImageService
{
    private GalleryImageRepositoryInterface $imageRepository;
    private GalleryRepositoryInterface $galleryRepository;

    public function __construct(
        GalleryImageRepositoryInterface $imageRepository,
        GalleryRepositoryInterface $galleryRepository
    ) {
        $this->imageRepository = $imageRepository;
        $this->galleryRepository = $galleryRepository;
    }

    public function findById(int $id): ?GalleryImage
    {
        return $this->imageRepository->findById($id);
    }

    public function getImagesByGallery(int $galleryId, array $filters = [], int $limit = -1): array
    {
        return $this->imageRepository->listByGalleryId($galleryId, $filters, $limit);
    }

    public function getActiveImagesByGallery(int $galleryId, array $options = []): array
    {
        $filters = ['status' => 'active'];

        if (isset($options['orderby'])) {
            $filters['orderby'] = $options['orderby'];
        }

        if (isset($options['order'])) {
            $filters['order'] = $options['order'];
        }

        $limit = $options['limit'] ?? -1;

        return $this->imageRepository->listByGalleryId($galleryId, $filters, $limit);
    }

    public function countByGallery(int $galleryId, array $filters = []): int
    {
        return $this->imageRepository->countByGalleryId($galleryId, $filters);
    }

    public function addImage(int $galleryId, array $data, int $userId): GalleryImage
    {
        $this->validateGalleryExists($galleryId);
        $this->validateImageData($data);

        $image = new GalleryImage(
            galleryId: $galleryId,
            createdBy: $userId,
            title: $data['title'] ?? null,
            attachmentId: $data['attachment_id'] ?? null,
            attachmentUrl: $data['attachment_url'] ?? null,
            description: $data['description'] ?? null,
            status: isset($data['status']) ? Status::fromString($data['status']) : Status::ACTIVE
        );

        $id = $this->imageRepository->insert($image);

        return $this->imageRepository->findById($id);
    }

    public function addImagesFromAttachments(int $galleryId, array $attachmentIds, int $userId): array
    {
        $this->validateGalleryExists($galleryId);

        $images = [];

        foreach ($attachmentIds as $attachmentId) {
            $attachmentId = absint($attachmentId);

            if (!$this->isValidAttachment($attachmentId)) {
                continue;
            }

            // Get attachment title
            $attachment = get_post($attachmentId);
            $title = $attachment ? $attachment->post_title : null;

            $image = new GalleryImage(
                galleryId: $galleryId,
                createdBy: $userId,
                title: $title,
                attachmentId: $attachmentId,
                status: Status::ACTIVE
            );

            $id = $this->imageRepository->insert($image);
            $images[] = $this->imageRepository->findById($id);
        }

        return $images;
    }

    public function updateImage(int $id, array $data): GalleryImage
    {
        $image = $this->imageRepository->findById($id);

        if (!$image) {
            throw new \InvalidArgumentException('Image not found');
        }

        if (isset($data['title'])) {
            $image = $image->withTitle($data['title']);
        }

        if (array_key_exists('description', $data)) {
            $image = $image->withDescription($data['description']);
        }

        if (isset($data['status'])) {
            $image = $image->withStatus(Status::fromString($data['status']));
        }

        $this->imageRepository->update($image);

        return $this->imageRepository->findById($id);
    }

    public function deleteImage(int $id): bool
    {
        $image = $this->imageRepository->findById($id);

        if (!$image) {
            throw new \InvalidArgumentException('Image not found');
        }

        return $this->imageRepository->delete($id);
    }

    public function deleteAllByGallery(int $galleryId): int
    {
        return $this->imageRepository->deleteByGalleryId($galleryId);
    }

    private function validateGalleryExists(int $galleryId): void
    {
        $gallery = $this->galleryRepository->findById($galleryId);

        if (!$gallery) {
            throw new \InvalidArgumentException('Gallery not found');
        }
    }

    private function validateImageData(array $data): void
    {
        // Either attachment_id or attachment_url must exist
        $hasAttachmentId = !empty($data['attachment_id']);
        $hasAttachmentUrl = !empty($data['attachment_url']);

        if (!$hasAttachmentId && !$hasAttachmentUrl) {
            throw new \InvalidArgumentException('Either attachment_id or attachment_url is required');
        }

        if ($hasAttachmentId && !$this->isValidAttachment($data['attachment_id'])) {
            throw new \InvalidArgumentException('Invalid attachment ID');
        }

        if ($hasAttachmentUrl) {
            $validator = new Validator();
            $validator->url('attachment_url', $data['attachment_url']);

            if ($validator->hasErrors()) {
                throw new \InvalidArgumentException('Invalid attachment URL');
            }
        }
    }

    private function isValidAttachment(int $attachmentId): bool
    {
        $attachment = get_post($attachmentId);

        if (!$attachment || $attachment->post_type !== 'attachment') {
            return false;
        }

        // Check if it's an image
        $mimeType = get_post_mime_type($attachmentId);
        return strpos($mimeType, 'image/') === 0;
    }
}
