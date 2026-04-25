<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $logoUrl = null;
        try {
            if (Storage::disk('s3')->exists('settings/logo.png')) {
                $logoUrl = Storage::disk('s3')->url('settings/logo.png');
            }
        } catch (\Exception $e) {}

        $settings = [
            'business_name' => cache('business_name') ?? config('app.name', 'Apipi Coffee'),
            'logo' => $logoUrl,
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
