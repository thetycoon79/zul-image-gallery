<?php
/**
 * Gallery source enum
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Domain\ValueObjects;

enum GallerySource: string
{
    case WP = 'WP';
    case EXTERNAL = 'External';

    public static function fromString(string $value): self
    {
        return match (strtoupper($value)) {
            'WP', 'WORDPRESS' => self::WP,
            'EXTERNAL' => self::EXTERNAL,
            default => self::WP,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::WP => 'WordPress Media Library',
            self::EXTERNAL => 'External Source',
        };
    }
}
