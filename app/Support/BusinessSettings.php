<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class BusinessSettings
{
    public static function logoUrl(): ?string
    {
        try {
            if (StorageUrl::diskConfigured() && Storage::disk('s3')->exists('settings/logo.png')) {
                return StorageUrl::public('settings/logo.png');
            }
        } catch (\Throwable) {
            // skip
        }

        return null;
    }

    public static function businessName(): string
    {
        return config('app.name', 'Apipi Coffee');
    }
}
