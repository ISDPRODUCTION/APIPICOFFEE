<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StorageUrl
{
    /**
     * URL untuk ditampilkan di browser.
     * Default: proxy lewat APP_URL (/media/...) agar hindari masalah SSL/timeout pub-*.r2.dev.
     */
    public static function public(?string $path, string $disk = 's3'): ?string
    {
        if (! $path) {
            return null;
        }

        $path = ltrim($path, '/');

        if (! self::diskConfigured($disk)) {
            return null;
        }

        if (config('filesystems.proxy_media', true)) {
            return url('/media/'.$path);
        }

        $base = config("filesystems.disks.{$disk}.url");
        if ($base) {
            $base = rtrim($base, '/');
            // Hindari duplikasi nama bucket di URL publik R2
            $bucket = config("filesystems.disks.{$disk}.bucket");
            if ($bucket && str_ends_with($base, '/'.$bucket)) {
                $base = substr($base, 0, -strlen('/'.$bucket));
            }

            return $base.'/'.$path;
        }

        try {
            return Storage::disk($disk)->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function diskConfigured(string $disk = 's3'): bool
    {
        return (bool) config("filesystems.disks.{$disk}.bucket")
            && (bool) config("filesystems.disks.{$disk}.key");
    }
}
