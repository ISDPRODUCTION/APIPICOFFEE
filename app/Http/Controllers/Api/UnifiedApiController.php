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
        $settings = [
            'business_name' => BusinessSettings::businessName(),
            'logo'          => BusinessSettings::logoUrl(),
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
