<?php

namespace App\Support;

class Assets
{
    /**
     * asset() plus a version taken from the file's modification time, so a replaced logo or icon
     * is fetched again instead of the browser (or WhatsApp / Facebook) showing a cached old one.
     */
    public static function url(string $path): string
    {
        $file = public_path($path);

        return asset($path) . (is_file($file) ? '?v=' . filemtime($file) : '');
    }
}
