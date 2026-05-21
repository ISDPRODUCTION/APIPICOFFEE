<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class BusinessSettings
{
    public static function logoUrl(): ?string
    {
        return StorageUrl::publicForPath('settings/logo.png');
    }

    public static function businessName(): string
    {
        return \Illuminate\Support\Facades\Cache::get('settings.business_name')
            ?? config('app.name', 'Apipi Coffee');
    }
}
