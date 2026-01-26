<?php
/**
 * Gallery image repository implementation
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Repositories;

use Zul\Gallery\Interfaces\GalleryImageRepositoryInterface;
use Zul\Gallery\Domain\Entities\GalleryImage;
use Zul\Gallery\Domain\ValueObjects\Status;
use Zul\Gallery\Support\Db;

class GalleryImageRepository implements GalleryImageRepositoryInterface
{
    private Db $db;
    private string $table;

    public function __construct(?Db $db = null)
    {
        $this->db = $db ?? new Db();
        $this->table = $this->db->getTableName('zul_image_gallery_images');
    }

    public function findById(int $id): ?GalleryImage
    {
        $sql = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id = %d",
            $id
        );

        $row = $this->db->getRow($sql);
        return $row ? $this->hydrate($row) : null;
    }

    public function listByGalleryId(int $galleryId, array $filters = [], int $limit = -1, int $offset = 0): array
    {
        $where = $this->buildWhereClause($galleryId, $filters);
        $orderBy = $this->buildOrderByClause($filters);

        $sql = "SELECT * FROM {$this->table} {$where} {$orderBy}";

        if ($limit > 0) {
            $sql = $this->db->prepare("{$sql} LIMIT %d OFFSET %d", $limit, $offset);
        }

        $rows = $this->db->getResults($sql);
        return array_map([$this, 'hydrate'], $rows);
    }

    public function countByGalleryId(int $galleryId, array $filters = []): int
    {
        $where = $this->buildWhereClause($galleryId, $filters);
        $sql = "SELECT COUNT(*) FROM {$this->table} {$where}";

        return (int) $this->db->getVar($sql);
    }

    public function insert(GalleryImage $image): int
    {
        $data = [
            'gallery_id' => $image->getGalleryId(),
            'title' => $image->getTitle(),
            'attachment_id' => $image->getAttachmentId(),
            'attachment_url' => $image->getAttachmentUrl(),
            'description' => $image->getDescription(),
            'created_by' => $image->getCreatedBy(),
            'status' => $image->getStatus()->value,
            'create_dt' => $image->getCreateDt()->format('Y-m-d H:i:s'),
            'modified_dt' => null,
        ];

        $result = $this->db->insert($this->table, $data);

        if ($result === false) {
            throw new \RuntimeException('Failed to insert image: ' . $this->db->lastError());
        }

        return $result;
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
        if (!$image->getId()) {
            throw new \InvalidArgumentException('Cannot update image without ID');
        }

        $data = [
            'title' => $image->getTitle(),
            'description' => $image->getDescription(),
            'status' => $image->getStatus()->value,
            'modified_dt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];

        $result = $this->db->update(
            $this->table,
            $data,
            ['id' => $image->getId()]
        );

        return $result !== false;
    }

    public function delete(int $id): bool
    {
        $result = $this->db->delete($this->table, ['id' => $id]);
        return $result !== false;
    }

    public function deleteByGalleryId(int $galleryId): int
    {
        $sql = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE gallery_id = %d",
            $galleryId
        );

        $result = $this->db->query($sql);
        return $result !== false ? (int) $result : 0;
    }

    private function hydrate(object $row): GalleryImage
    {
        return new GalleryImage(
            galleryId: (int) $row->gallery_id,
            createdBy: (int) $row->created_by,
            title: $row->title,
            attachmentId: $row->attachment_id ? (int) $row->attachment_id : null,
            attachmentUrl: $row->attachment_url,
            description: $row->description,
            status: Status::from($row->status),
            id: (int) $row->id,
            createDt: new \DateTimeImmutable($row->create_dt),
            modifiedDt: $row->modified_dt ? new \DateTimeImmutable($row->modified_dt) : null
        );
    }

    private function buildWhereClause(int $galleryId, array $filters): string
    {
        $conditions = [];
        $conditions[] = $this->db->prepare('gallery_id = %d', $galleryId);

        if (!empty($filters['status'])) {
            $conditions[] = $this->db->prepare('status = %s', $filters['status']);
        }

        if (!empty($filters['attachment_id'])) {
            $conditions[] = $this->db->prepare('attachment_id = %d', $filters['attachment_id']);
        }

        return 'WHERE ' . implode(' AND ', $conditions);
    }

    private function buildOrderByClause(array $filters): string
    {
        $orderBy = $filters['orderby'] ?? 'create_dt';
        $order = strtoupper($filters['order'] ?? 'ASC');

        $allowedColumns = ['id', 'title', 'status', 'create_dt', 'modified_dt'];
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($orderBy, $allowedColumns, true)) {
            $orderBy = 'create_dt';
        }

        if (!in_array($order, $allowedOrder, true)) {
            $order = 'ASC';
        }

        return "ORDER BY {$orderBy} {$order}";
    }
}
