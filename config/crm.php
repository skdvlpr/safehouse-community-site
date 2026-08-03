<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CRM → site sync token
    |--------------------------------------------------------------------------
    |
    | Shared secret for authenticated internal calls from EspoCRM (e.g.
    | PrimaNota «Aggiorna da Stripe»). Prefer DB site_settings override.
    |
    */

    'sync_token' => env('CRM_SYNC_TOKEN', ''),

];
