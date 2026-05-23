<?php

namespace App\Http\Controllers;

use App\DTO\OrderDTO;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|integer|exists:products,id',
            'items.*.name'            => 'required|string',
            'items.*.price'           => 'required|integer|min:0',
            'items.*.quantity'        => 'required|integer|min:1',
            'payment_method'          => 'required|in:cash,card,qris',
        ]);

        try {
            $dto   = OrderDTO::fromRequest($validated, Auth::id());
            $order = $this->orderService->createOrder($dto);

            return response()->json([
                'success'      => true,
                'order_number' => $order->order_number,
                'total'        => $order->total,
                'redirect'     => route('receipt.show', $order->order_number),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan']);
            }
            
            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'success' => false, 
                    'message' => "Stok {$product->name} tidak cukup. Stok tersedia: {$product->stock}"
                ]);
            }
        }
    }

    public function receipt(string $orderNumber)
    {
        $order = $this->orderService->getOrderByNumber($orderNumber);

        if (!$order) {
            abort(404);
        }

        $businessName = \App\Support\BusinessSettings::businessName();
        $logoUrl      = \App\Support\BusinessSettings::logoUrl();

        return view('receipt.show', compact('order', 'businessName', 'logoUrl'));
    }
}
