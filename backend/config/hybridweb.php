<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SIMRS Khanza Hybrid Web Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration untuk mengakses berkas digital dari SIMRS Khanza
    | Hybrid Web Server seperti gambar radiologi, hasil lab PA, dll.
    |
    */

    'host' => env('HYBRIDWEB_HOST', 'localhost'),
    'port' => env('HYBRIDWEB_PORT', '80'),
    'path' => env('HYBRIDWEB_PATH', 'webapps'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL untuk mengakses berkas digital
    | Bisa di-override dengan env HYBRIDWEB_BASE_URL
    |
    */

    'base_url' => env('HYBRIDWEB_BASE_URL', null),

];
