<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    private const ALLOWED_PREFIXES = [
        'products/',
        'settings/',
        'avatars/',
    ];

    public function show(Request $request, string $path): StreamedResponse
    {
        $path = str_replace(['..', '\\'], '', $path);
        $path = ltrim($path, '/');

        if (! $this->isAllowedPath($path)) {
            abort(403);
        }

        $diskName = \App\Support\StorageUrl::diskConfigured('s3') ? 's3' : 'public';
        $disk = Storage::disk($diskName);

        if (! $disk->exists($path) && $diskName === 's3') {
            $disk = Storage::disk('public');
        }

        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function isAllowedPath(string $path): bool
    {
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
