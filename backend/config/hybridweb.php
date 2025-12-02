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
    | Base URL Generator
    |--------------------------------------------------------------------------
    |
    | Generate base URL untuk mengakses berkas digital
    |
    */

    'base_url' => function() {
        $host = config('hybridweb.host');
        $port = config('hybridweb.port');
        $path = config('hybridweb.path');

        $url = 'http://' . $host;

        // Tambahkan port jika bukan port default (80)
        if ($port && $port != '80') {
            $url .= ':' . $port;
        }

        // Tambahkan path
        if ($path) {
            $url .= '/' . trim($path, '/');
        }

        return $url;
    },

];
