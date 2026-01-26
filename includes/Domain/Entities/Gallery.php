<?php
/**
 * Gallery entity
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Domain\Entities;

use Zul\Gallery\Domain\ValueObjects\GallerySource;
use Zul\Gallery\Domain\ValueObjects\Status;

class Gallery
{
    private ?int $id;
    private string $title;
    private ?string $description;
    private int $createdBy;
    private GallerySource $source;
    private Status $status;
    private \DateTimeImmutable $createDt;
    private ?\DateTimeImmutable $modifiedDt;

    public function __construct(
        string $title,
        int $createdBy,
        ?string $description = null,
        ?GallerySource $source = null,
        ?Status $status = null,
        ?int $id = null,
        ?\DateTimeImmutable $createDt = null,
        ?\DateTimeImmutable $modifiedDt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->createdBy = $createdBy;
        $this->source = $source ?? GallerySource::WP;
        $this->status = $status ?? Status::ACTIVE;
        $this->createDt = $createDt ?? new \DateTimeImmutable();
        $this->modifiedDt = $modifiedDt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getSource(): GallerySource
    {
        return $this->source;
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

    public function withTitle(string $title): self
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

    public function withSource(GallerySource $source): self
    {
        $clone = clone $this;
        $clone->source = $source;
        $clone->modifiedDt = new \DateTimeImmutable();
        return $clone;
    }

    public function isActive(): bool
    {
        return $this->status === Status::ACTIVE;
    }

    public function isWpSource(): bool
    {
        return $this->source === GallerySource::WP;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'created_by' => $this->createdBy,
            'source' => $this->source->value,
            'status' => $this->status->value,
            'create_dt' => $this->createDt->format('Y-m-d H:i:s'),
            'modified_dt' => $this->modifiedDt?->format('Y-m-d H:i:s'),
        ];
    }
}
