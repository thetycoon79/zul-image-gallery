<?php
/**
 * Validator tests
 *
 * @package Zul\Gallery\Tests
 */

namespace Zul\Gallery\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Zul\Gallery\Support\Validator;

class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    public function testRequiredPassesForNonEmptyValue(): void
    {
        $this->validator->required('field', 'value');

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testRequiredFailsForEmptyValue(): void
    {
        $this->validator->required('field', '');

        $this->assertTrue($this->validator->hasErrors());
        $this->assertNotNull($this->validator->getError('field'));
    }

    public function testRequiredFailsForNull(): void
    {
        $this->validator->required('field', null);

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testRequiredPassesForZeroString(): void
    {
        $this->validator->required('field', '0');

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testRequiredPassesForZeroInt(): void
    {
        $this->validator->required('field', 0);

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testRequiredUsesCustomMessage(): void
    {
        $this->validator->required('field', '', 'Custom error message');

        $this->assertSame('Custom error message', $this->validator->getError('field'));
    }

    public function testMinLengthPassesForValidLength(): void
    {
        $this->validator->minLength('field', 'hello', 3);

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testMinLengthFailsForShortValue(): void
    {
        $this->validator->minLength('field', 'hi', 3);

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testMinLengthSkipsEmptyValue(): void
    {
        $this->validator->minLength('field', '', 3);

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testMaxLengthPassesForValidLength(): void
    {
        $this->validator->maxLength('field', 'hello', 10);

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testMaxLengthFailsForLongValue(): void
    {
        $this->validator->maxLength('field', 'hello world', 5);

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testNumericPassesForNumbers(): void
    {
        $this->validator->numeric('field', '123');

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testNumericFailsForNonNumbers(): void
    {
        $this->validator->numeric('field', 'abc');

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testPositiveIntPassesForPositiveIntegers(): void
    {
        $this->validator->positiveInt('field', 5);

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testPositiveIntFailsForZero(): void
    {
        $this->validator->positiveInt('field', 0);

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testPositiveIntFailsForNegative(): void
    {
        $this->validator->positiveInt('field', -1);

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testInArrayPassesForValidValue(): void
    {
        $this->validator->inArray('field', 'b', ['a', 'b', 'c']);

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testInArrayFailsForInvalidValue(): void
    {
        $this->validator->inArray('field', 'd', ['a', 'b', 'c']);

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testUrlPassesForValidUrl(): void
    {
        $this->validator->url('field', 'https://example.com');

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testUrlFailsForInvalidUrl(): void
    {
        $this->validator->url('field', 'not-a-url');

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testAddErrorAddsCustomError(): void
    {
        $this->validator->addError('custom', 'Custom error');

        $this->assertTrue($this->validator->hasErrors());
        $this->assertSame('Custom error', $this->validator->getError('custom'));
    }

    public function testGetErrorsReturnsAllErrors(): void
    {
        $this->validator->required('field1', '');
        $this->validator->required('field2', '');

        $errors = $this->validator->getErrors();

        $this->assertCount(2, $errors);
        $this->assertArrayHasKey('field1', $errors);
        $this->assertArrayHasKey('field2', $errors);
    }

    public function testResetClearsErrors(): void
    {
        $this->validator->required('field', '');
        $this->assertTrue($this->validator->hasErrors());

        $this->validator->reset();

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testValidateReturnsTrueWhenNoErrors(): void
    {
        $this->validator->required('field', 'value');

        $this->assertTrue($this->validator->validate());
    }

    public function testValidateReturnsFalseWhenErrors(): void
    {
        $this->validator->required('field', '');

        $this->assertFalse($this->validator->validate());
    }

    public function testChainableValidation(): void
    {
        $result = $this->validator
            ->required('title', 'My Title')
            ->minLength('title', 'My Title', 3)
            ->maxLength('title', 'My Title', 100)
            ->validate();

        $this->assertTrue($result);
    }
}
