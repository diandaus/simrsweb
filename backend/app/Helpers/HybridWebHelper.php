<?php

namespace App\Helpers;

class HybridWebHelper
{
    /**
     * Generate URL untuk berkas digital di Hybrid Web Server
     *
     * @param string $path Path relatif dari folder webapps (contoh: 'radiologi/gambar.jpg')
     * @return string Full URL ke berkas
     */
    public static function getFileUrl($path)
    {
        // Gunakan base_url jika sudah di-set di env
        $baseUrl = config('hybridweb.base_url');
        
        if ($baseUrl) {
            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }

        // Generate dari host, port, dan path jika base_url tidak di-set
        $host = config('hybridweb.host');
        $port = config('hybridweb.port');
        $basePath = config('hybridweb.path');

        $url = 'http://' . $host;

        // Tambahkan port jika bukan port default (80)
        if ($port && $port != '80') {
            $url .= ':' . $port;
        }

        // Tambahkan base path
        if ($basePath) {
            $url .= '/' . trim($basePath, '/');
        }

        // Tambahkan path file
        $url .= '/' . ltrim($path, '/');

        return $url;
    }

    /**
     * Generate URL untuk gambar radiologi
     *
     * @param string $filename Nama file gambar
     * @return string Full URL ke gambar radiologi
     */
    public static function getRadiologiImageUrl($filename)
    {
        return self::getFileUrl('radiologi/' . $filename);
    }

    /**
     * Generate URL untuk gambar laboratorium PA
     *
     * @param string $filename Nama file gambar
     * @return string Full URL ke gambar lab PA
     */
    public static function getLabPAImageUrl($filename)
    {
        return self::getFileUrl('labpa/' . $filename);
    }

    /**
     * Generate URL untuk berkas lainnya
     *
     * @param string $folder Nama folder (contoh: 'radiologi', 'labpa', dll)
     * @param string $filename Nama file
     * @return string Full URL ke berkas
     */
    public static function getCustomFileUrl($folder, $filename)
    {
        return self::getFileUrl($folder . '/' . $filename);
    }
}
