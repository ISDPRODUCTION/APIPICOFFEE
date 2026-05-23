<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\BusinessSettings;
use App\Support\StorageUrl;
use Illuminate\Support\Facades\Storage;

class UnifiedApiController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly OrderService   $orderService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        // 1. Categories
        $categories = Category::where('is_active', true)->get();

        // 2. Products (All for POS)
        $products = $this->productService->getAllForPos();

        // 3. Stats
        $stats = $this->orderService->getDashboardStats();

        // 4. Business Settings
        $logoUrl = cache()->remember('settings_logo_url', 86400, function () {
            try {
                if (StorageUrl::diskConfigured() && Storage::disk('s3')->exists('settings/logo.png')) {
                    return StorageUrl::public('settings/logo.png');
                }
            } catch (\Exception $e) {}
            return null;
        });

        $settings = [
            'business_name' => BusinessSettings::businessName(),
            'logo'          => $logoUrl,
        ];

        // 5. User Profile
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'products' => $products,
                'stats' => $stats,
                'settings' => $settings,
                'user' => $user,
            ]
        ]);
    }
}
