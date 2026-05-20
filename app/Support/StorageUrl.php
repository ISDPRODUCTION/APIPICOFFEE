<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StorageUrl
{
    public static function public(?string $path, string $disk = 's3'): ?string
    {
        if (! $path) {
            return null;
        }

        $base = config("filesystems.disks.{$disk}.url");
        if ($base) {
            return rtrim($base, '/').'/'.ltrim($path, '/');
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
