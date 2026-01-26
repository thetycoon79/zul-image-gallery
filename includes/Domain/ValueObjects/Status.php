<?php
/**
 * Status enum for galleries and images
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Domain\ValueObjects;

enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DRAFT = 'draft';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'active' => self::ACTIVE,
            'inactive' => self::INACTIVE,
            'draft' => self::DRAFT,
            default => self::ACTIVE,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::DRAFT => 'Draft',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::ACTIVE;
    }
}
