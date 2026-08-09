<?php

namespace App\Services;

use App\Models\Inspector;
use App\Models\ServiceRequest;
use Illuminate\Support\Collection;

class MatchingService
{
    /**
     * Every active, approved inspector whose service area genuinely covers
     * the request's location: an exact case-insensitive city name match, or
     * the request's PLZ falling inside a configured postal range (inclusive
     * of both boundary values). A previous version also matched any PLZ
     * merely sharing its first two digits with a provider's range — meant as
     * a coarse regional near-miss catch, but German first-two-digit postal
     * zones can span 50-150km, so it was routinely notifying providers whose
     * actual configured area did not cover the customer's location. Removed
     * in favor of matching only what a provider actually configured.
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
                            ->whereRaw('LOWER(TRIM(city_name)) = ?', [$ort]);
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
