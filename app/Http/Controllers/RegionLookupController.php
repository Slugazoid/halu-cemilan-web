<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionLookupController extends Controller
{
    public function provinces(Request $request): JsonResponse
    {
        return $this->options(Province::query(), $request, ['code', 'name']);
    }

    public function regencies(Request $request, string $provinceCode): JsonResponse
    {
        return $this->options(
            Regency::query()->where('province_code', $provinceCode),
            $request,
            ['code', 'name'],
        );
    }

    public function districts(Request $request, string $regencyCode): JsonResponse
    {
        return $this->options(
            District::query()->where('regency_code', $regencyCode),
            $request,
            ['code', 'name'],
        );
    }

    public function villages(Request $request, string $districtCode): JsonResponse
    {
        return $this->options(
            Village::query()->where('district_code', $districtCode)
                ->orderByRaw("CASE WHEN code LIKE '%LNY' THEN 1 ELSE 0 END"),
            $request,
            ['code', 'name', 'postal_code'],
        );
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function options(Builder $query, Request $request, array $columns): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        if (filled($validated['q'] ?? null)) {
            $query->where('name', 'like', '%'.$validated['q'].'%');
        }

        return response()->json($query->orderBy('name')->get($columns));
    }
}
