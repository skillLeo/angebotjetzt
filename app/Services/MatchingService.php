<?php

namespace App\Services;

use App\Models\Inspector;
use App\Models\ServiceRequest;
use Illuminate\Support\Collection;

class MatchingService
{
    /**
     * Every active inspector whose service area covers the request's region:
     * city name matches case-insensitively OR the PLZ falls inside a postal range.
     *
     * @return Collection<int, Inspector>
     */
    public function match(ServiceRequest $request): Collection
    {
        $plz = (int) $request->plz;
        $ort = mb_strtolower(trim($request->ort));

        return Inspector::query()
            ->where('is_active', true)
            ->where('is_approved', true)
            ->whereHas('serviceAreas', function ($query) use ($plz, $ort) {
                $query->where(function ($q) use ($plz, $ort) {
                    $q->where(function ($city) use ($ort) {
                        $city->where('type', 'city')
                            ->whereRaw('LOWER(city_name) = ?', [$ort]);
                    })->orWhere(function ($range) use ($plz) {
                        $range->where('type', 'postal_range')
                            ->where('postal_from', '<=', $plz)
                            ->where('postal_to', '>=', $plz);
                    });
                });
            })
            ->get();
    }
}
