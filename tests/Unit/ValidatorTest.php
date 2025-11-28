<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Exceptions\ValidationException;
use App\Infrastructure\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testRequiredRule(): void
    {
        $this->expectException(ValidationException::class);

        Validator::validate(
            ['name' => ''],
            ['name' => 'required']
        );
    }

    public function testStringRule(): void
    {
        $result = Validator::validate(
            ['name' => 'Product Name'],
            ['name' => 'required|string']
        );

        $this->assertEquals('Product Name', $result['name']);
    }

    public function testNumericRule(): void
    {
        $result = Validator::validate(
            ['price' => '99.99'],
            ['price' => 'numeric']
        );

        $this->assertEquals(99.99, $result['price']);
    }

    public function testMinRule(): void
    {
        $this->expectException(ValidationException::class);

        Validator::validate(
            ['price' => '5'],
            ['price' => 'numeric|min:10']
        );
    }

    public function testInRule(): void
    {
        $result = Validator::validate(
            ['status' => 'PUBLISHED'],
            ['status' => 'in:DRAFT,PUBLISHED,ARCHIVED']
        );

        $this->assertEquals('PUBLISHED', $result['status']);
    }
}

