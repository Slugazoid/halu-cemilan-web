<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    #[DataProvider('validInputs')]
    public function test_normalizes_to_628_format(string $input): void
    {
        $this->assertSame('6281234567890', PhoneNumber::normalize($input));
    }

    public static function validInputs(): array
    {
        return [
            'awalan 08' => ['081234567890'],
            'awalan +62' => ['+6281234567890'],
            'awalan 62' => ['6281234567890'],
            'dengan strip' => ['0812-3456-7890'],
            'dengan spasi' => ['0812 3456 7890'],
            'tanpa nol' => ['81234567890'],
        ];
    }

    #[DataProvider('invalidInputs')]
    public function test_rejects_invalid_numbers(?string $input): void
    {
        $this->assertNull(PhoneNumber::normalize($input));
        $this->assertFalse(PhoneNumber::isValid($input));
    }

    public static function invalidInputs(): array
    {
        return [
            'null' => [null],
            'kosong' => [''],
            'huruf' => ['abc'],
            'bukan Indonesia' => ['+14155552671'],
            'terlalu pendek' => ['08123'],
            'terlalu panjang' => ['0812345678901234567'],
            'telepon rumah' => ['0221234567'],
        ];
    }

    public function test_display_format(): void
    {
        $this->assertSame('0812-3456-7890', PhoneNumber::display('6281234567890'));
    }
}
