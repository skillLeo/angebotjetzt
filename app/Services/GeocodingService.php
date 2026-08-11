<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Resolve a German place name to coordinates via OpenStreetMap Nominatim
     * (already the map's tile provider, so no new vendor relationship).
     * Returns null when the place can't be resolved so the caller can put a
     * validation error in front of the admin rather than storing a bad pin.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function locate(string $city): ?array
    {
        try {
            $response = Http::withHeaders([
                // Nominatim rejects requests without an identifying UA.
                'User-Agent' => 'AngebotJetzt/1.0 (+'.config('app.url').')',
            ])->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $city,
                'countrycodes' => 'de',
                'format' => 'json',
                'limit' => 1,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $hit = $response->json()[0] ?? null;

            if (! $hit || ! isset($hit['lat'], $hit['lon'])) {
                return null;
            }

            return ['lat' => (float) $hit['lat'], 'lng' => (float) $hit['lon']];
        } catch (\Throwable $e) {
            Log::warning('Geocoding failed', ['city' => $city, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * The reverse trip, for when an admin drops a pin on the map instead of
     * typing: turn coordinates back into the nearest place name so the entry
     * gets a sensible label without them having to guess it.
     */
    public function nameFor(float $lat, float $lng): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'AngebotJetzt/1.0 (+'.config('app.url').')',
            ])->timeout(10)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
                'zoom' => 10,
                'addressdetails' => 1,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $address = $response->json()['address'] ?? [];

            foreach (['city', 'town', 'village', 'municipality', 'county', 'state'] as $key) {
                if (! empty($address[$key])) {
                    return $address[$key];
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('Reverse geocoding failed', ['lat' => $lat, 'lng' => $lng, 'error' => $e->getMessage()]);

            return null;
        }
    }
}
