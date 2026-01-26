<?php
/**
 * Gallery entity tests
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Unit\Domain\Entities;

use PHPUnit\Framework\TestCase;
use Zul\Gallery\Domain\Entities\Gallery;
use Zul\Gallery\Domain\ValueObjects\GallerySource;
use Zul\Gallery\Domain\ValueObjects\Status;

class GalleryTest extends TestCase
{
    public function testCanCreateGalleryWithMinimalData(): void
    {
        $gallery = new Gallery(
            title: 'Test Gallery',
            createdBy: 1
        );

        $this->assertSame('Test Gallery', $gallery->getTitle());
        $this->assertSame(1, $gallery->getCreatedBy());
        $this->assertNull($gallery->getId());
        $this->assertNull($gallery->getDescription());
        $this->assertSame(GallerySource::WP, $gallery->getSource());
        $this->assertSame(Status::ACTIVE, $gallery->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $gallery->getCreateDt());
        $this->assertNull($gallery->getModifiedDt());
    }

    public function testCanCreateGalleryWithFullData(): void
    {
        $createDt = new \DateTimeImmutable('2024-01-01 10:00:00');
        $modifiedDt = new \DateTimeImmutable('2024-01-02 10:00:00');

        $gallery = new Gallery(
            title: 'Full Gallery',
            createdBy: 5,
            description: 'A full description',
            source: GallerySource::EXTERNAL,
            status: Status::DRAFT,
            id: 42,
            createDt: $createDt,
            modifiedDt: $modifiedDt
        );

        $this->assertSame(42, $gallery->getId());
        $this->assertSame('Full Gallery', $gallery->getTitle());
        $this->assertSame('A full description', $gallery->getDescription());
        $this->assertSame(5, $gallery->getCreatedBy());
        $this->assertSame(GallerySource::EXTERNAL, $gallery->getSource());
        $this->assertSame(Status::DRAFT, $gallery->getStatus());
        $this->assertSame($createDt, $gallery->getCreateDt());
        $this->assertSame($modifiedDt, $gallery->getModifiedDt());
    }

    public function testWithTitleReturnsNewInstanceWithUpdatedTitle(): void
    {
        $gallery = new Gallery(title: 'Original', createdBy: 1);
        $updated = $gallery->withTitle('Updated');

        $this->assertSame('Original', $gallery->getTitle());
        $this->assertSame('Updated', $updated->getTitle());
        $this->assertNotSame($gallery, $updated);
        $this->assertNotNull($updated->getModifiedDt());
    }

    public function testWithDescriptionReturnsNewInstanceWithUpdatedDescription(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, description: 'Original');
        $updated = $gallery->withDescription('Updated description');

        $this->assertSame('Original', $gallery->getDescription());
        $this->assertSame('Updated description', $updated->getDescription());
        $this->assertNotSame($gallery, $updated);
    }

    public function testWithStatusReturnsNewInstanceWithUpdatedStatus(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1);
        $updated = $gallery->withStatus(Status::INACTIVE);

        $this->assertSame(Status::ACTIVE, $gallery->getStatus());
        $this->assertSame(Status::INACTIVE, $updated->getStatus());
        $this->assertNotSame($gallery, $updated);
    }

    public function testWithSourceReturnsNewInstanceWithUpdatedSource(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1);
        $updated = $gallery->withSource(GallerySource::EXTERNAL);

        $this->assertSame(GallerySource::WP, $gallery->getSource());
        $this->assertSame(GallerySource::EXTERNAL, $updated->getSource());
        $this->assertNotSame($gallery, $updated);
    }

    public function testWithIdReturnsNewInstanceWithId(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1);
        $updated = $gallery->withId(123);

        $this->assertNull($gallery->getId());
        $this->assertSame(123, $updated->getId());
    }

    public function testIsActiveReturnsTrueForActiveStatus(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, status: Status::ACTIVE);
        $this->assertTrue($gallery->isActive());
    }

    public function testIsActiveReturnsFalseForInactiveStatus(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, status: Status::INACTIVE);
        $this->assertFalse($gallery->isActive());
    }

    public function testIsWpSourceReturnsTrueForWpSource(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, source: GallerySource::WP);
        $this->assertTrue($gallery->isWpSource());
    }

    public function testIsWpSourceReturnsFalseForExternalSource(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, source: GallerySource::EXTERNAL);
        $this->assertFalse($gallery->isWpSource());
    }

    public function testToArrayReturnsCorrectData(): void
    {
        $createDt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $gallery = new Gallery(
            title: 'Test',
            createdBy: 1,
            description: 'Desc',
            source: GallerySource::WP,
            status: Status::ACTIVE,
            id: 1,
            createDt: $createDt
        );

        $array = $gallery->toArray();

        $this->assertSame(1, $array['id']);
        $this->assertSame('Test', $array['title']);
        $this->assertSame('Desc', $array['description']);
        $this->assertSame(1, $array['created_by']);
        $this->assertSame('WP', $array['source']);
        $this->assertSame('active', $array['status']);
        $this->assertSame('2024-01-01 10:00:00', $array['create_dt']);
        $this->assertNull($array['modified_dt']);
    }
}
