<?php

/*
|--------------------------------------------------------------------------
| Predefined provider avatars
|--------------------------------------------------------------------------
| Providers pick one of these instead of uploading a free-form photo, so an
| avatar can never itself reveal an identity before a customer accepts an
| offer (see CompareOffers.vue's pre-acceptance anonymization). Each key
| pairs a lucide-vue-next icon name (resolved client-side) with a brand
| background color.
*/

return [
    'shield' => ['icon' => 'Shield', 'color' => '#14375E'],
    'wrench' => ['icon' => 'Wrench', 'color' => '#3EAE2B'],
    'badge-check' => ['icon' => 'BadgeCheck', 'color' => '#0B8457'],
    'car' => ['icon' => 'Car', 'color' => '#1F5FA8'],
    'clipboard-check' => ['icon' => 'ClipboardCheck', 'color' => '#B8860B'],
    'user-round' => ['icon' => 'UserRound', 'color' => '#6E747E'],
];
