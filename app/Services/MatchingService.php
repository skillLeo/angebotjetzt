<?php

namespace App\Services;

use App\Models\Inspector;
use App\Models\ServiceRequest;
use Illuminate\Support\Collection;

class MatchingService
{
    /**
     * Every active inspector whose service area covers the request's region:
     * city name matches case-insensitively, OR the PLZ falls inside a postal
     * range, OR the PLZ shares its first two digits with that range — German
     * postal codes' first two digits denote a coarse delivery region
     * (roughly 50-150km), so this catches near-misses at a range's edge
     * without needing real geocoding. The exact-match case is a subset of
     * this broader band check, so a single condition covers both.
     *
     * @return Collection<int, Inspector>
     */
    public function match(ServiceRequest $request): Collection
    {
        $plz = (int) $request->plz;
        $ort = mb_strtolower(trim($request->ort));
        $bandLow = intdiv($plz, 1000) * 1000;
        $bandHigh = $bandLow + 999;

        return Inspector::query()
            ->where('is_active', true)
            ->where('is_approved', true)
            ->whereHas('serviceAreas', function ($query) use ($bandLow, $bandHigh, $ort) {
                $query->where(function ($q) use ($bandLow, $bandHigh, $ort) {
                    $q->where(function ($city) use ($ort) {
                        $city->where('type', 'city')
                            ->whereRaw('LOWER(city_name) = ?', [$ort]);
                    })->orWhere(function ($range) use ($bandLow, $bandHigh) {
                        $range->where('type', 'postal_range')
                            ->where('postal_from', '<=', $bandHigh)
                            ->where('postal_to', '>=', $bandLow);
                    });
                });
            })
            ->get();
    }
}
