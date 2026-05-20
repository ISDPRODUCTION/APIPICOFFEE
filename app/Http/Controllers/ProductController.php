<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function index(Request $request): View
    {
        $category = $request->get('category', 'all');
        $search   = $request->get('search');
        $products = $this->productService->getPaginated($category, $search, 8);
        $allCategories = Category::where('is_active', true)->orderBy('name')->get();

        return view('menu.index', compact('products', 'category', 'search', 'allCategories'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'category' => 'required|exists:categories,slug',
            'price'    => 'required|integer|min:0',
            'stock'    => 'required|integer|min:0',
            'image'    => 'nullable|image|max:5120',
        ]);

        try {
            $product = $this->productService->create($validated, $request->file('image'));
            return response()->json(['success' => true, 'product' => $product]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'category' => 'required|exists:categories,slug',
            'price'    => 'required|integer|min:0',
            'stock'    => 'required|integer|min:0',
            'image'    => 'nullable|image|max:5120',
        ]);

        try {
            $this->productService->update($id, $validated, $request->file('image'));
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productService->delete($id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}