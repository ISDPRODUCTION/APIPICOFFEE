/**
 * checkoutModule.js
 * Manages checkout modal UI and order submission flow.
 */

const checkoutModule = (() => {

    let selectedPayment = 'cash';

    // ── Open / Close ──────────────────────────────────────────────────────────
    function openCheckout() {
        const state = cartStore.getState();
        if (state.items.length === 0) return;

        _renderCheckoutItems(state);
        _renderTotal(state);

        document.getElementById('checkout-modal').classList.remove('hidden');
    }

    function closeCheckout() {
        document.getElementById('checkout-modal').classList.add('hidden');
    }

    // ── Payment selection ──────────────────────────────────────────────────────
    function selectPayment(method) {
        selectedPayment = method;

        document.querySelectorAll('.payment-btn').forEach(btn => {
            const isSelected = btn.dataset.payment === method;
            btn.classList.toggle('border-primary', isSelected);
            btn.classList.toggle('border-stone-200', !isSelected);

            const icon = btn.querySelector('svg');
            if (icon) {
                icon.classList.toggle('text-primary', isSelected);
                icon.classList.toggle('text-stone-500', !isSelected);
            }
        });
    }

    // ── Render helpers ─────────────────────────────────────────────────────────
    function _renderCheckoutItems(state) {
        const list = document.getElementById('checkout-items-list');
        if (!list) return;

        list.innerHTML = state.items.map(item => `
            <div class="flex items-center gap-3 p-3 bg-stone-50 rounded-xl">
                <div class="w-10 h-10 rounded-xl overflow-hidden bg-stone-200 flex-shrink-0">
                    <img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-[#1C1917] truncate">${item.name}</p>
                    <p class="text-xs text-[#78716C]">Qty: ${item.quantity}</p>
                </div>
                <span class="text-sm font-semibold text-[#1C1917]">
                    ${_formatRp(item.price * item.quantity)}
                </span>
            </div>
        `).join('');
    }

    function _renderTotal(state) {
        const totalEl = document.getElementById('checkout-total');
        if (totalEl) totalEl.textContent = _formatRp(state.total);
    }

    function _formatRp(amount) {
        return 'Rp' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // ── Submit order ───────────────────────────────────────────────────────────
    async function submitOrder() {
        const state = cartStore.getState();

        if (state.items.length === 0) return;

        const btn = document.getElementById('confirm-pay-btn');
        if (btn) {
            btn.disabled   = true;
            btn.textContent = 'Processing...';
        }

        try {
            // Simulated payment delay of 2 seconds
            await new Promise(r => setTimeout(r, 2000));

            const payload = {
                items:          state.items,
                payment_method: selectedPayment,
            };

            const response = await apiService.post('/orders', payload);

            if (response.success) {
                // Close checkout modal
                closeCheckout();

                // Show success modal
                _showSuccessModal(response);

                // Reset cart
                cartStore.reset();
            }
        } catch (err) {
            alert('Order failed: ' + err.message);
        } finally {
            if (btn) {
                btn.disabled    = false;
                btn.innerHTML = `
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Confirm & Pay
                `;
            }
        }
    }

    function _showSuccessModal(response) {
        const modal = document.getElementById('success-modal');
        if (!modal) return;

        const orderNumEl = document.getElementById('success-order-number');
        const totalEl    = document.getElementById('success-total');
        const printBtn   = document.getElementById('print-receipt-btn');

        if (orderNumEl) orderNumEl.textContent = `Order #${response.order_number} has been processed.`;
        if (totalEl)    totalEl.textContent    = _formatRp(response.total);
        if (printBtn)   printBtn.href          = response.redirect;

        modal.classList.remove('hidden');
    }

    return { openCheckout, closeCheckout, selectPayment, submitOrder };
})();

window.checkoutModule = checkoutModule;
