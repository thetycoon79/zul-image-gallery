<?php
/**
 * GallerySource value object tests
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Zul\Gallery\Domain\ValueObjects\GallerySource;

class GallerySourceTest extends TestCase
{
    public function testWpSourceHasCorrectValue(): void
    {
        $this->assertSame('WP', GallerySource::WP->value);
    }

    public function testExternalSourceHasCorrectValue(): void
    {
        $this->assertSame('External', GallerySource::EXTERNAL->value);
    }

    public function testFromStringReturnsCorrectSource(): void
    {
        $this->assertSame(GallerySource::WP, GallerySource::fromString('WP'));
        $this->assertSame(GallerySource::EXTERNAL, GallerySource::fromString('External'));
    }

    public function testFromStringIsCaseInsensitive(): void
    {
        $this->assertSame(GallerySource::WP, GallerySource::fromString('wp'));
        $this->assertSame(GallerySource::WP, GallerySource::fromString('WordPress'));
        $this->assertSame(GallerySource::EXTERNAL, GallerySource::fromString('EXTERNAL'));
    }

    public function testFromStringDefaultsToWpForUnknownValue(): void
    {
        $this->assertSame(GallerySource::WP, GallerySource::fromString('unknown'));
        $this->assertSame(GallerySource::WP, GallerySource::fromString(''));
    }

    public function testLabelReturnsHumanReadableLabel(): void
    {
        $this->assertSame('WordPress Media Library', GallerySource::WP->label());
        $this->assertSame('External Source', GallerySource::EXTERNAL->label());
    }
}
