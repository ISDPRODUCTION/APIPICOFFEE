<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BusinessSettings
{
    private const CONFIG_PATH = 'settings/business.json';

    private const CACHE_KEY = 'settings.business_name';

    public static function logoUrl(): ?string
    {
        return StorageUrl::publicForPath('settings/logo.png');
    }

    public static function businessName(): string
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $fromFile = self::readNameFromStorage();
            if ($fromFile !== null) {
                return $fromFile;
            }

            return config('app.name', 'Apipi Coffee');
        });
    }

    public static function setBusinessName(string $name): void
    {
        $name = trim($name);
        $payload = json_encode(['name' => $name], JSON_UNESCAPED_UNICODE);

        foreach (self::writableDisks() as $disk) {
            try {
                Storage::disk($disk)->put(self::CONFIG_PATH, $payload);
            } catch (\Throwable) {
                // coba disk berikutnya
            }
        }

        Cache::forget(self::CACHE_KEY);
        Cache::put(self::CACHE_KEY, $name, now()->addYear());
    }

    /** @return array{0: string, 1: string} [teks utama, aksen oranye] untuk sidebar */
    public static function brandParts(): array
    {
        $name = self::businessName();
        $parts = preg_split('/\s+/', trim($name), 2);

        return [
            $parts[0] ?? $name,
            $parts[1] ?? '',
        ];
    }

    private static function readNameFromStorage(): ?string
    {
        foreach (array_unique([StorageUrl::uploadDisk(), 's3', 'public']) as $disk) {
            try {
                if (! Storage::disk($disk)->exists(self::CONFIG_PATH)) {
                    continue;
                }
                $data = json_decode(Storage::disk($disk)->get(self::CONFIG_PATH), true);
                if (is_array($data) && ! empty($data['name'])) {
                    return (string) $data['name'];
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    /** @return list<string> */
    private static function writableDisks(): array
    {
        $disks = [StorageUrl::uploadDisk()];
        if (! in_array('public', $disks, true)) {
            $disks[] = 'public';
        }
        if (StorageUrl::diskConfigured('s3') && ! in_array('s3', $disks, true)) {
            $disks[] = 's3';
        }

        return $disks;
    }
}
