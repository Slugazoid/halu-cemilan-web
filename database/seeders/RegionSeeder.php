<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegionSeeder extends Seeder
{
    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        $this->seedCsv('provinsi.csv', fn (array $row): array => [
            'code' => $this->normalizeCode($row[0], 2),
            'name' => $row[1],
        ], 'provinces', ['code'], ['name']);

        $this->seedCsv('kabupaten.csv', fn (array $row): array => [
            'code' => $this->normalizeCode($row[0], 4),
            'province_code' => $this->normalizeCode($row[1], 2),
            'name' => $row[2],
        ], 'regencies', ['code'], ['province_code', 'name']);

        $this->seedCsv('kecamatan.csv', fn (array $row): array => [
            'code' => $this->normalizeCode($row[0], 6),
            'regency_code' => $this->normalizeCode($row[1], 4),
            'name' => $row[2],
        ], 'districts', ['code'], ['regency_code', 'name']);

        $this->seedCsv('desa.csv', fn (array $row): array => [
            'code' => $this->normalizeCode($row[0], 10),
            'district_code' => $this->normalizeCode($row[1], 6),
            'name' => $row[2],
            'postal_code' => $this->normalizePostalCode($row[4] ?? null),
        ], 'villages', ['code'], ['district_code', 'name', 'postal_code']);

        DB::table('districts')->orderBy('code')->chunk(self::CHUNK_SIZE, function ($districts): void {
            $rows = $districts->map(fn (object $district): array => [
                'code' => $district->code.'LNY',
                'district_code' => $district->code,
                'name' => 'Lainnya',
                'postal_code' => null,
            ])->all();

            DB::table('villages')->upsert($rows, ['code'], ['district_code', 'name', 'postal_code']);
        });
    }

    private function seedCsv(string $filename, callable $map, string $table, array $uniqueBy, array $update): void
    {
        $path = database_path('data/regions/'.$filename);
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Data wilayah tidak dapat dibaca: {$filename}");
        }

        try {
            fgetcsv($handle, null, ',', '"', '');
            $rows = [];

            while (($fields = fgetcsv($handle, null, ',', '"', '')) !== false) {
                if ($fields === [null]) {
                    continue;
                }

                $rows[] = $map($fields);

                if (count($rows) === self::CHUNK_SIZE) {
                    DB::table($table)->upsert($rows, $uniqueBy, $update);
                    $rows = [];
                }
            }

            if ($rows !== []) {
                DB::table($table)->upsert($rows, $uniqueBy, $update);
            }
        } finally {
            fclose($handle);
        }
    }

    private function normalizeCode(string $code, int $length): string
    {
        $normalized = str_replace('.', '', trim($code));

        if (strlen($normalized) !== $length || ! ctype_digit($normalized)) {
            throw new RuntimeException("Kode wilayah tidak valid: {$code}");
        }

        return $normalized;
    }

    private function normalizePostalCode(?string $postalCode): ?string
    {
        $postalCode = trim((string) $postalCode);

        if ($postalCode === '') {
            return null;
        }

        if (! preg_match('/^\d{5}$/D', $postalCode)) {
            throw new RuntimeException("Kode pos tidak valid: {$postalCode}");
        }

        return $postalCode;
    }
}
