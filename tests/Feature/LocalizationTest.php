<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_app_uses_indonesian_locale_and_wib_timezone(): void
    {
        $this->assertSame('id', app()->getLocale());
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('Asia/Jakarta', date_default_timezone_get());
        $this->assertSame('+07:00', Carbon::parse('2026-12-18 10:00')->format('P'));
    }

    public function test_dates_are_formatted_in_indonesian(): void
    {
        $this->assertSame('Jumat, 18 Desember 2026', Carbon::parse('2026-12-18')->translatedFormat('l, j F Y'));
    }

    public function test_validation_messages_are_in_indonesian(): void
    {
        $v = Validator::make(['name' => ''], ['name' => 'required']);

        $this->assertSame('Nama wajib diisi.', $v->errors()->first('name'));
    }

    public function test_untranslated_validation_keys_fall_back_to_english_not_raw_keys(): void
    {
        $v = Validator::make(['x' => 'abc'], ['x' => 'ip']);

        $this->assertStringNotContainsString('validation.', $v->errors()->first('x'));
    }
}
