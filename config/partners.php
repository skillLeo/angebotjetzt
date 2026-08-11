<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External partner destinations
    |--------------------------------------------------------------------------
    |
    | Services whose flow_mode is 'external' are fulfilled by a partner rather
    | than through the AngebotJetzt request flow. The service page shows an
    | information block and links out to the partner instead of opening the
    | booking wizard.
    |
    | A service type may override this per row via its external_url column;
    | this array is the fallback used when that column is empty.
    |
    | If this is ever emptied, the call-to-action renders in a clearly
    | disabled state rather than sending customers to a dead address.
    |
    */

    'carspector_url' => env('CARSPECTOR_URL', 'https://www.carspector.de'),

];
