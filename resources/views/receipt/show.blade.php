<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: { DEFAULT: '#F97316', hover: '#EA580C' } } } }
        };
    </script>
    <style>body { font-family: 'Inter', sans-serif; background: rgba(0,0,0,0.4); }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative">
        <div class="flex items-center justify-end gap-3 p-4 pb-0">
            <button onclick="window.print()" class="w-8 h-8 flex items-center justify-center text-[#78716C] hover:text-[#1C1917] rounded-lg hover:bg-stone-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
            </button>
            <a href="{{ route('pos.index') }}" class="w-8 h-8 flex items-center justify-center text-[#78716C] hover:text-[#1C1917] rounded-lg hover:bg-stone-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        </div>

        <div class="px-7 pb-7 pt-3">
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center mx-auto mb-3 overflow-hidden">
                    @if($logoUrl ?? null)
                        <img src="{{ $logoUrl }}" alt="Logo" class="w-full h-full object-cover">
                    @else
                        <svg class="w-6 h-6 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    @endif
                </div>
                <h2 class="text-lg font-bold text-[#1C1917]">{{ $businessName ?? config('app.name', 'Apipi Coffee') }}</h2>
                <p class="text-xs text-[#78716C]">Thank you for your visit!</p>

                <div class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 bg-green-100 rounded-full">
                    <svg class="w-3 h-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-xs font-bold text-green-600 uppercase tracking-wider">Payment Successful</span>
                </div>

                <p class="text-xs text-[#78716C] mt-3">
                    ID: #{{ $order->order_number }} &nbsp;|&nbsp; {{ $order->order_date->format('M d, Y, H:i') }}
                </p>
            </div>

            <div class="relative border-t border-dashed border-stone-200 mb-4">
                <span class="absolute -left-7 top-1/2 -translate-y-1/2 w-5 h-5 bg-stone-100 rounded-full"></span>
                <span class="absolute -right-7 top-1/2 -translate-y-1/2 w-5 h-5 bg-stone-100 rounded-full"></span>
            </div>

            <div class="space-y-3 mb-5">
                @foreach($order->items as $item)
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[#1C1917]">
                            <span class="text-[#F97316] font-bold">{{ $item->quantity }}x</span>
                            {{ $item->product_name }}
                        </p>
                    </div>
                    <span class="text-sm font-semibold text-[#1C1917]">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </span>
                </div>
                @endforeach
            </div>

            <div class="space-y-2 pt-4 border-t border-stone-100 mb-4">
                <div class="flex justify-between text-sm text-[#78716C]">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm text-[#78716C]">
                    <span>Tax (10%)</span>
                    <span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="bg-orange-50 rounded-2xl p-4 flex items-center justify-between mb-4">
                <span class="text-sm font-bold text-[#1C1917]">Total Amount</span>
                <span class="text-xl font-bold text-[#F97316]">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>

            <p class="text-center text-xs text-[#78716C] mb-6">
                Paid via {{ ucfirst($order->payment_method) }}
            </p>

            <a href="{{ route('pos.index') }}"
               class="flex w-full items-center justify-center py-3.5 bg-[#F97316] hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors shadow-lg shadow-orange-200">
                New Order
            </a>

            <p class="text-center text-xs text-[#78716C] uppercase tracking-widest mt-6">Visit us again at apipicoffee.com</p>
        </div>
    </div>
</body>
</html>
