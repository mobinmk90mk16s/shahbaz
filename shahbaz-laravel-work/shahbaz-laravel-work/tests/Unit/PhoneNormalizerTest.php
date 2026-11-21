<?php

namespace Tests\Unit;

use App\Support\PhoneNormalizer;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PhoneNormalizerTest extends TestCase
{
    #[DataProvider('validNumbers')]
    public function test_it_normalizes_supported_iranian_mobile_formats(string $input, string $expected): void
    {
        $this->assertSame($expected, PhoneNormalizer::normalize($input));
    }

    public static function validNumbers(): array
    {
        return [
            'local' => ['09123456789', '09123456789'],
            'persian digits' => ['۰۹۱۲۳۴۵۶۷۸۹', '09123456789'],
            'country code' => ['+989123456789', '09123456789'],
            'international prefix' => ['00989123456789', '09123456789'],
        ];
    }

    public function test_it_rejects_invalid_numbers(): void
    {
        $this->expectException(ValidationException::class);
        PhoneNormalizer::normalize('12345');
    }
}
