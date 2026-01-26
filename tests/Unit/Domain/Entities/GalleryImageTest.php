<?php
/**
 * GalleryImage entity tests
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Unit\Domain\Entities;

use PHPUnit\Framework\TestCase;
use Zul\Gallery\Domain\Entities\GalleryImage;
use Zul\Gallery\Domain\ValueObjects\Status;

class GalleryImageTest extends TestCase
{
    public function testCanCreateImageWithMinimalData(): void
    {
        $image = new GalleryImage(
            galleryId: 1,
            createdBy: 1
        );

        $this->assertSame(1, $image->getGalleryId());
        $this->assertSame(1, $image->getCreatedBy());
        $this->assertNull($image->getId());
        $this->assertNull($image->getTitle());
        $this->assertNull($image->getAttachmentId());
        $this->assertNull($image->getAttachmentUrl());
        $this->assertNull($image->getDescription());
        $this->assertSame(Status::ACTIVE, $image->getStatus());
    }

    public function testCanCreateImageWithWpAttachment(): void
    {
        $image = new GalleryImage(
            galleryId: 1,
            createdBy: 1,
            title: 'Test Image',
            attachmentId: 123,
            description: 'A test image'
        );

        $this->assertSame('Test Image', $image->getTitle());
        $this->assertSame(123, $image->getAttachmentId());
        $this->assertNull($image->getAttachmentUrl());
        $this->assertSame('A test image', $image->getDescription());
    }

    public function testCanCreateImageWithExternalUrl(): void
    {
        $image = new GalleryImage(
            galleryId: 1,
            createdBy: 1,
            title: 'External Image',
            attachmentUrl: 'https://example.com/image.jpg'
        );

        $this->assertNull($image->getAttachmentId());
        $this->assertSame('https://example.com/image.jpg', $image->getAttachmentUrl());
    }

    public function testWithTitleReturnsNewInstance(): void
    {
        $image = new GalleryImage(galleryId: 1, createdBy: 1, title: 'Original');
        $updated = $image->withTitle('Updated');

        $this->assertSame('Original', $image->getTitle());
        $this->assertSame('Updated', $updated->getTitle());
        $this->assertNotSame($image, $updated);
        $this->assertNotNull($updated->getModifiedDt());
    }

    public function testWithDescriptionReturnsNewInstance(): void
    {
        $image = new GalleryImage(galleryId: 1, createdBy: 1);
        $updated = $image->withDescription('New description');

        $this->assertNull($image->getDescription());
        $this->assertSame('New description', $updated->getDescription());
    }

    public function testWithStatusReturnsNewInstance(): void
    {
        $image = new GalleryImage(galleryId: 1, createdBy: 1);
        $updated = $image->withStatus(Status::INACTIVE);

        $this->assertSame(Status::ACTIVE, $image->getStatus());
        $this->assertSame(Status::INACTIVE, $updated->getStatus());
    }

    public function testWithIdReturnsNewInstance(): void
    {
        $image = new GalleryImage(galleryId: 1, createdBy: 1);
        $updated = $image->withId(99);

        $this->assertNull($image->getId());
        $this->assertSame(99, $updated->getId());
    }

    public function testIsActiveReturnsTrueForActiveStatus(): void
    {
        $image = new GalleryImage(galleryId: 1, createdBy: 1, status: Status::ACTIVE);
        $this->assertTrue($image->isActive());
    }

    public function testIsActiveReturnsFalseForInactiveStatus(): void
    {
        $image = new GalleryImage(galleryId: 1, createdBy: 1, status: Status::INACTIVE);
        $this->assertFalse($image->isActive());
    }

    public function testIsWpAttachmentReturnsTrueWhenAttachmentIdSet(): void
    {
        $image = new GalleryImage(galleryId: 1, createdBy: 1, attachmentId: 123);
        $this->assertTrue($image->isWpAttachment());
    }

    public function testIsWpAttachmentReturnsFalseWhenNoAttachmentId(): void
    {
        $image = new GalleryImage(galleryId: 1, createdBy: 1, attachmentUrl: 'https://example.com/img.jpg');
        $this->assertFalse($image->isWpAttachment());
    }

    public function testIsExternalSourceReturnsTrueForExternalUrl(): void
    {
        $image = new GalleryImage(galleryId: 1, createdBy: 1, attachmentUrl: 'https://example.com/img.jpg');
        $this->assertTrue($image->isExternalSource());
    }

    public function testIsExternalSourceReturnsFalseForWpAttachment(): void
    {
        $image = new GalleryImage(galleryId: 1, createdBy: 1, attachmentId: 123);
        $this->assertFalse($image->isExternalSource());
    }

    public function testToArrayReturnsCorrectData(): void
    {
        $createDt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $image = new GalleryImage(
            galleryId: 5,
            createdBy: 2,
            title: 'Test',
            attachmentId: 123,
            description: 'Desc',
            status: Status::ACTIVE,
            id: 1,
            createDt: $createDt
        );

        $array = $image->toArray();

        $this->assertSame(1, $array['id']);
        $this->assertSame(5, $array['gallery_id']);
        $this->assertSame('Test', $array['title']);
        $this->assertSame(123, $array['attachment_id']);
        $this->assertNull($array['attachment_url']);
        $this->assertSame('Desc', $array['description']);
        $this->assertSame(2, $array['created_by']);
        $this->assertSame('active', $array['status']);
    }
}
