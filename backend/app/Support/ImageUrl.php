<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ImageUrl
{
    /**
     * Convert a storage path into a public URL.
     */
    public static function make(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Already a full URL
        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://')
        ) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}