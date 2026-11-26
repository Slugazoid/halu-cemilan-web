<?php

namespace App\Support;

final class CustomerStreetAddress
{
    private const MANUAL_VILLAGE_SEPARATOR = "\nKelurahan manual: ";

    public static function combine(string $streetAddress, ?string $manualVillageName): string
    {
        if ($manualVillageName === null || trim($manualVillageName) === '') {
            return $streetAddress;
        }

        return $streetAddress.self::MANUAL_VILLAGE_SEPARATOR.trim($manualVillageName);
    }

    /**
     * @return array{street_address: string, manual_village_name: ?string}
     */
    public static function split(string $streetAddress): array
    {
        $parts = explode(self::MANUAL_VILLAGE_SEPARATOR, $streetAddress, 2);

        return [
            'street_address' => $parts[0],
            'manual_village_name' => $parts[1] ?? null,
        ];
    }
}
