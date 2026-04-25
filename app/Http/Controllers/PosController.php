<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly OrderService   $orderService,
    ) {}

    public function index(Request $request): View
    {
        $category = $request->get('category', 'all');
        $search   = $request->get('search');

        $products = $this->productService->getAllForPos($category, $search);
        $stats    = $this->orderService->getDashboardStats();

        return view('pos.index', compact('products', 'category', 'search', 'stats'));
    }
}
