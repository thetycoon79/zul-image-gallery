<?php
/**
 * GalleryService tests
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Unit\Services;

use Zul\Gallery\Tests\TestCase;
use Zul\Gallery\Tests\Mocks\MockGalleryRepository;
use Zul\Gallery\Services\GalleryService;
use Zul\Gallery\Domain\Entities\Gallery;
use Zul\Gallery\Domain\ValueObjects\Status;
use Zul\Gallery\Domain\ValueObjects\GallerySource;

class GalleryServiceTest extends TestCase
{
    private MockGalleryRepository $repository;
    private GalleryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MockGalleryRepository();
        $this->service = new GalleryService($this->repository);
    }

    public function testCreateGalleryWithValidData(): void
    {
        $data = [
            'title' => 'Test Gallery',
            'description' => 'A test description',
            'status' => 'active',
            'source' => 'WP',
        ];

        $gallery = $this->service->create($data, 1);

        $this->assertInstanceOf(Gallery::class, $gallery);
        $this->assertSame('Test Gallery', $gallery->getTitle());
        $this->assertSame('A test description', $gallery->getDescription());
        $this->assertSame(Status::ACTIVE, $gallery->getStatus());
        $this->assertSame(GallerySource::WP, $gallery->getSource());
        $this->assertSame(1, $gallery->getCreatedBy());
        $this->assertNotNull($gallery->getId());
    }

    public function testCreateGalleryWithMinimalData(): void
    {
        $data = ['title' => 'Minimal Gallery'];

        $gallery = $this->service->create($data, 5);

        $this->assertSame('Minimal Gallery', $gallery->getTitle());
        $this->assertNull($gallery->getDescription());
        $this->assertSame(Status::ACTIVE, $gallery->getStatus());
        $this->assertSame(GallerySource::WP, $gallery->getSource());
    }

    public function testCreateGalleryThrowsExceptionWithoutTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gallery title is required');

        $this->service->create(['description' => 'No title'], 1);
    }

    public function testCreateGalleryThrowsExceptionWithEmptyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->create(['title' => ''], 1);
    }

    public function testFindByIdReturnsGallery(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, id: 1);
        $this->repository->addGallery($gallery);

        $found = $this->service->findById(1);

        $this->assertInstanceOf(Gallery::class, $found);
        $this->assertSame('Test', $found->getTitle());
    }

    public function testFindByIdReturnsNullForNonexistent(): void
    {
        $found = $this->service->findById(999);

        $this->assertNull($found);
    }

    public function testGetActiveGalleryReturnsOnlyActive(): void
    {
        $activeGallery = new Gallery(title: 'Active', createdBy: 1, status: Status::ACTIVE, id: 1);
        $inactiveGallery = new Gallery(title: 'Inactive', createdBy: 1, status: Status::INACTIVE, id: 2);

        $this->repository->addGallery($activeGallery);
        $this->repository->addGallery($inactiveGallery);

        $this->assertNotNull($this->service->getActiveGallery(1));
        $this->assertNull($this->service->getActiveGallery(2));
    }

    public function testUpdateGalleryTitle(): void
    {
        $gallery = new Gallery(title: 'Original', createdBy: 1, id: 1);
        $this->repository->addGallery($gallery);

        $updated = $this->service->update(1, ['title' => 'Updated']);

        $this->assertSame('Updated', $updated->getTitle());
    }

    public function testUpdateGalleryDescription(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, description: 'Old', id: 1);
        $this->repository->addGallery($gallery);

        $updated = $this->service->update(1, ['description' => 'New description']);

        $this->assertSame('New description', $updated->getDescription());
    }

    public function testUpdateGalleryStatus(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, status: Status::ACTIVE, id: 1);
        $this->repository->addGallery($gallery);

        $updated = $this->service->update(1, ['status' => 'inactive']);

        $this->assertSame(Status::INACTIVE, $updated->getStatus());
    }

    public function testUpdateGalleryThrowsExceptionForNonexistent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gallery not found');

        $this->service->update(999, ['title' => 'Test']);
    }

    public function testUpdateStatusShortcut(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, status: Status::ACTIVE, id: 1);
        $this->repository->addGallery($gallery);

        $updated = $this->service->updateStatus(1, 'draft');

        $this->assertSame(Status::DRAFT, $updated->getStatus());
    }

    public function testDeleteGallery(): void
    {
        $gallery = new Gallery(title: 'Test', createdBy: 1, id: 1);
        $this->repository->addGallery($gallery);

        $result = $this->service->delete(1);

        $this->assertTrue($result);
        $this->assertNull($this->service->findById(1));
    }

    public function testDeleteThrowsExceptionForNonexistent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gallery not found');

        $this->service->delete(999);
    }

    public function testListGalleries(): void
    {
        $this->repository->addGallery(new Gallery(title: 'Gallery 1', createdBy: 1, id: 1));
        $this->repository->addGallery(new Gallery(title: 'Gallery 2', createdBy: 1, id: 2));
        $this->repository->addGallery(new Gallery(title: 'Gallery 3', createdBy: 1, id: 3));

        $galleries = $this->service->list();

        $this->assertCount(3, $galleries);
    }

    public function testListGalleriesWithStatusFilter(): void
    {
        $this->repository->addGallery(new Gallery(title: 'Active 1', createdBy: 1, status: Status::ACTIVE, id: 1));
        $this->repository->addGallery(new Gallery(title: 'Active 2', createdBy: 1, status: Status::ACTIVE, id: 2));
        $this->repository->addGallery(new Gallery(title: 'Inactive', createdBy: 1, status: Status::INACTIVE, id: 3));

        $galleries = $this->service->list(['status' => 'active']);

        $this->assertCount(2, $galleries);
    }

    public function testListGalleriesWithPagination(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->repository->addGallery(new Gallery(title: "Gallery $i", createdBy: 1, id: $i));
        }

        $page1 = $this->service->list([], 3, 0);
        $page2 = $this->service->list([], 3, 3);

        $this->assertCount(3, $page1);
        $this->assertCount(3, $page2);
    }

    public function testCountGalleries(): void
    {
        $this->repository->addGallery(new Gallery(title: 'Gallery 1', createdBy: 1, id: 1));
        $this->repository->addGallery(new Gallery(title: 'Gallery 2', createdBy: 1, id: 2));

        $count = $this->service->count();

        $this->assertSame(2, $count);
    }

    public function testCountGalleriesWithFilter(): void
    {
        $this->repository->addGallery(new Gallery(title: 'Active', createdBy: 1, status: Status::ACTIVE, id: 1));
        $this->repository->addGallery(new Gallery(title: 'Inactive', createdBy: 1, status: Status::INACTIVE, id: 2));

        $count = $this->service->count(['status' => 'active']);

        $this->assertSame(1, $count);
    }
}
