<?php

namespace App\Services;

use App\Models\Setting;

class CommissionService
{
    /**
     * Split a gross amount (cents) into platform commission and inspector share.
     * Commission is rounded to the nearest cent; the inspector receives the exact remainder
     * so the two parts always sum to the total.
     *
     * @return array{commission: int, inspector: int}
     */
    public function split(int $totalCents): array
    {
        $percent = Setting::commissionPercent();
        $commission = (int) round($totalCents * $percent / 100);

        return [
            'commission' => $commission,
            'inspector' => $totalCents - $commission,
        ];
    }
}
