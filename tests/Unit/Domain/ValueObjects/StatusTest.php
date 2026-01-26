<?php
/**
 * Status value object tests
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Zul\Gallery\Domain\ValueObjects\Status;

class StatusTest extends TestCase
{
    public function testActiveStatusHasCorrectValue(): void
    {
        $this->assertSame('active', Status::ACTIVE->value);
    }

    public function testInactiveStatusHasCorrectValue(): void
    {
        $this->assertSame('inactive', Status::INACTIVE->value);
    }

    public function testDraftStatusHasCorrectValue(): void
    {
        $this->assertSame('draft', Status::DRAFT->value);
    }

    public function testFromStringReturnsCorrectStatus(): void
    {
        $this->assertSame(Status::ACTIVE, Status::fromString('active'));
        $this->assertSame(Status::INACTIVE, Status::fromString('inactive'));
        $this->assertSame(Status::DRAFT, Status::fromString('draft'));
    }

    public function testFromStringIsCaseInsensitive(): void
    {
        $this->assertSame(Status::ACTIVE, Status::fromString('ACTIVE'));
        $this->assertSame(Status::INACTIVE, Status::fromString('InActive'));
    }

    public function testFromStringDefaultsToActiveForUnknownValue(): void
    {
        $this->assertSame(Status::ACTIVE, Status::fromString('unknown'));
        $this->assertSame(Status::ACTIVE, Status::fromString(''));
    }

    public function testLabelReturnsHumanReadableLabel(): void
    {
        $this->assertSame('Active', Status::ACTIVE->label());
        $this->assertSame('Inactive', Status::INACTIVE->label());
        $this->assertSame('Draft', Status::DRAFT->label());
    }

    public function testIsPublicReturnsTrueOnlyForActive(): void
    {
        $this->assertTrue(Status::ACTIVE->isPublic());
        $this->assertFalse(Status::INACTIVE->isPublic());
        $this->assertFalse(Status::DRAFT->isPublic());
    }
}
