{{-- Checkout Modal --}}
<div id="checkout-modal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="checkoutModule.closeCheckout()"></div>

    {{-- Modal --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between p-6 pb-4">
            <h2 class="text-xl font-bold text-[#1C1917]">Confirm Order</h2>
            <button onclick="checkoutModule.closeCheckout()" class="w-7 h-7 flex items-center justify-center text-stone-400 hover:text-[#1C1917] rounded-lg hover:bg-stone-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-6 pb-6 space-y-5">
            {{-- Order Summary --}}
            <div>
                <p class="text-xs font-bold text-primary uppercase tracking-wider mb-3">Order Summary</p>
                <div id="checkout-items-list" class="space-y-2 overflow-y-auto max-h-[35vh] pr-1">
                    {{-- Populated by checkoutModule --}}
                </div>
            </div>

            {{-- Payment Method --}}
            <div>
                <p class="text-xs font-bold text-primary uppercase tracking-wider mb-3">Payment Method</p>
                <div class="grid grid-cols-3 gap-3">
                    <button onclick="checkoutModule.selectPayment('cash')"
                            data-payment="cash"
                            class="payment-btn flex flex-col items-center gap-1.5 p-3 rounded-2xl border-2 border-primary bg-white transition-all">
                        <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="text-xs font-semibold text-[#1C1917]">Cash</span>
                    </button>
                    <button onclick="checkoutModule.selectPayment('card')"
                            data-payment="card"
                            class="payment-btn flex flex-col items-center gap-1.5 p-3 rounded-2xl border-2 border-stone-200 bg-white transition-all hover:border-primary/50">
                        <svg class="w-6 h-6 text-stone-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span class="text-xs font-semibold text-[#1C1917]">Card</span>
                    </button>
                    <button onclick="checkoutModule.selectPayment('qris')"
                            data-payment="qris"
                            class="payment-btn flex flex-col items-center gap-1.5 p-3 rounded-2xl border-2 border-stone-200 bg-white transition-all hover:border-primary/50">
                        <svg class="w-6 h-6 text-stone-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <span class="text-xs font-semibold text-[#1C1917]">QRIS</span>
                    </button>
                </div>
            </div>

            {{-- Total --}}
            <div class="bg-orange-50 rounded-2xl p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-primary">Final Total</p>
                    <p class="text-xs text-[#78716C]">Including tax (10%)</p>
                </div>
                <span id="checkout-total" class="text-2xl font-bold text-primary">Rp0</span>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button onclick="checkoutModule.closeCheckout()"
                        class="flex-1 py-3.5 rounded-2xl border-2 border-stone-200 text-sm font-semibold text-[#78716C] hover:border-stone-300 transition-colors">
                    Cancel
                </button>
                <button id="confirm-pay-btn"
                        onclick="checkoutModule.submitOrder()"
                        class="flex-1 py-3.5 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors flex items-center justify-center gap-2 shadow-lg shadow-orange-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Confirm & Pay
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div id="success-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-8 text-center">
        {{-- Success Icon --}}
        <div class="relative inline-flex items-center justify-center mb-6">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="absolute -top-1 -right-2 w-4 h-4 bg-primary rounded-full"></span>
            <span class="absolute bottom-0 -left-3 w-3 h-3 bg-blue-400 rounded-full"></span>
            <span class="absolute -bottom-1 right-0 w-3 h-3 bg-yellow-400 rounded-full"></span>
        </div>

        <h2 class="text-2xl font-bold text-[#1C1917] mb-2">Payment Successful!</h2>
        <p id="success-order-number" class="text-sm text-[#78716C] mb-6"></p>

        <div class="bg-stone-50 rounded-2xl p-4 flex items-center justify-between mb-6">
            <span class="text-sm text-[#78716C]">Total Amount Paid</span>
            <span id="success-total" class="text-xl font-bold text-primary"></span>
        </div>

        <div class="space-y-3">
            <button id="new-order-btn" onclick="document.getElementById('success-modal').classList.add('hidden')"
               class="flex w-full items-center justify-center gap-2 py-3.5 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors shadow-lg shadow-orange-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5h15M17 21a1 1 0 100-2 1 1 0 000 2zM7 21a1 1 0 100-2 1 1 0 000 2z"/>
                </svg>
                New Order
            </button>
            <a id="print-receipt-btn" href="#"
               class="flex w-full items-center justify-center gap-2 py-3.5 border-2 border-primary text-primary rounded-2xl text-sm font-semibold hover:bg-orange-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Receipt
            </a>
            <button class="text-sm text-[#78716C] hover:text-[#1C1917] transition-colors">Email Receipt to Customer</button>
        </div>
    </div>
</div>
