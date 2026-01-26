<?php
/**
 * Gallery image entity
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Domain\Entities;

use Zul\Gallery\Domain\ValueObjects\Status;

class GalleryImage
{
    private ?int $id;
    private int $galleryId;
    private ?string $title;
    private ?int $attachmentId;
    private ?string $attachmentUrl;
    private ?string $description;
    private int $createdBy;
    private Status $status;
    private \DateTimeImmutable $createDt;
    private ?\DateTimeImmutable $modifiedDt;

    public function __construct(
        int $galleryId,
        int $createdBy,
        ?string $title = null,
        ?int $attachmentId = null,
        ?string $attachmentUrl = null,
        ?string $description = null,
        ?Status $status = null,
        ?int $id = null,
        ?\DateTimeImmutable $createDt = null,
        ?\DateTimeImmutable $modifiedDt = null
    ) {
        $this->id = $id;
        $this->galleryId = $galleryId;
        $this->title = $title;
        $this->attachmentId = $attachmentId;
        $this->attachmentUrl = $attachmentUrl;
        $this->description = $description;
        $this->createdBy = $createdBy;
        $this->status = $status ?? Status::ACTIVE;
        $this->createDt = $createDt ?? new \DateTimeImmutable();
        $this->modifiedDt = $modifiedDt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGalleryId(): int
    {
        return $this->galleryId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getAttachmentId(): ?int
    {
        return $this->attachmentId;
    }

    public function getAttachmentUrl(): ?string
    {
        return $this->attachmentUrl;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getCreateDt(): \DateTimeImmutable
    {
        return $this->createDt;
    }

    public function getModifiedDt(): ?\DateTimeImmutable
    {
        return $this->modifiedDt;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withTitle(?string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;
        $clone->modifiedDt = new \DateTimeImmutable();
        return $clone;
    }

    public function withDescription(?string $description): self
    {
        $clone = clone $this;
        $clone->description = $description;
        $clone->modifiedDt = new \DateTimeImmutable();
        return $clone;
    }

    public function withStatus(Status $status): self
    {
        $clone = clone $this;
        $clone->status = $status;
        $clone->modifiedDt = new \DateTimeImmutable();
        return $clone;
    }

    public function isActive(): bool
    {
        return $this->status === Status::ACTIVE;
    }

    public function isWpAttachment(): bool
    {
        return $this->attachmentId !== null;
    }

    public function isExternalSource(): bool
    {
        return $this->attachmentId === null && $this->attachmentUrl !== null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'gallery_id' => $this->galleryId,
            'title' => $this->title,
            'attachment_id' => $this->attachmentId,
            'attachment_url' => $this->attachmentUrl,
            'description' => $this->description,
            'created_by' => $this->createdBy,
            'status' => $this->status->value,
            'create_dt' => $this->createDt->format('Y-m-d H:i:s'),
            'modified_dt' => $this->modifiedDt?->format('Y-m-d H:i:s'),
        ];
    }
}
