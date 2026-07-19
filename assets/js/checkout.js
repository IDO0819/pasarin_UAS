/**
 * checkout.js - Mengatur halaman checkout: memuat ringkasan cart,
 * menghitung ongkir via API, dan submit order.
 */

let selectedOngkir = 0;

document.addEventListener('DOMContentLoaded', () => {
    loadCheckoutSummary();

    const courierForm = document.getElementById('courier-form');
    if (courierForm) {
        courierForm.addEventListener('submit', (e) => {
            e.preventDefault();
            calculateOngkir();
        });
    }

    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', submitCheckout);
    }
});

async function loadCheckoutSummary() {
    const container = document.getElementById('checkout-items');
    try {
        const result = await apiRequest('cart.php');
        if (!result.success || result.data.length === 0) {
            window.location.href = `${BASE_URL}/cart.php`;
            return;
        }

        container.innerHTML = result.data.map(item => `
            <div class="summary-row">
                <span>${item.title} x${item.qty}</span>
                <span>${formatRupiah(item.line_total)}</span>
            </div>
        `).join('');

        window.checkoutSubtotal = result.subtotal;
        updateTotalDisplay();
    } catch (err) {
        container.innerHTML = '<p>Gagal memuat ringkasan pesanan.</p>';
    }
}

async function calculateOngkir() {
    const destination = document.getElementById('destination_city_id').value;
    const courier = document.getElementById('kurir').value;
    const resultBox = document.getElementById('ongkir-result');

    resultBox.innerHTML = '<div class="loader">Menghitung ongkos kirim...</div>';

    try {
        const result = await apiRequest('ongkir.php', {
            method: 'POST',
            body: JSON.stringify({ destination_city_id: destination, courier, weight: 1000 }),
        });

        if (!result.success) throw new Error(result.message);

        const costs = result.data[0]?.costs || [];
        resultBox.innerHTML = costs.map((c, i) => `
            <label class="form-group" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                <input type="radio" name="ongkir_option" value="${c.cost}" data-layanan="${c.service}"
                    onchange="selectOngkir(${c.cost}, '${c.service}')" ${i === 0 ? 'checked' : ''}>
                ${c.service} - ${formatRupiah(c.cost)} (estimasi ${c.etd})
            </label>
        `).join('');

        if (costs.length > 0) selectOngkir(costs[0].cost, costs[0].service);
    } catch (err) {
        resultBox.innerHTML = '<p>Gagal menghitung ongkir. Coba lagi.</p>';
    }
}

function selectOngkir(cost, layanan) {
    selectedOngkir = Number(cost);
    document.getElementById('layanan').value = layanan;
    document.getElementById('ongkir_value').value = selectedOngkir;

    const kurirSelect = document.getElementById('kurir');
    const kurirHidden = document.getElementById('kurir_final_hidden');
    if (kurirSelect && kurirHidden) kurirHidden.value = kurirSelect.value;

    updateTotalDisplay();
}

function updateTotalDisplay() {
    const totalEl = document.getElementById('checkout-total');
    const ongkirEl = document.getElementById('checkout-ongkir-display');
    const subtotal = window.checkoutSubtotal || 0;

    if (ongkirEl) ongkirEl.textContent = formatRupiah(selectedOngkir);
    if (totalEl) totalEl.textContent = formatRupiah(subtotal + selectedOngkir);
}

async function submitCheckout(e) {
    e.preventDefault();
    const form = e.target;

    if (!form.kurir_final.value) {
        showToast('Silakan hitung ongkir terlebih dahulu', 'error');
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Memproses...';

    const payload = {
        alamat: form.alamat.value,
        kurir: form.kurir_final.value,
        layanan: form.layanan.value,
        ongkir: Number(form.ongkir_value.value || 0),
        csrf_token: form.csrf_token.value,
    };

    try {
        const result = await apiRequest('orders.php', {
            method: 'POST',
            body: JSON.stringify(payload),
        });

        if (result.success) {
            showToast('Checkout berhasil!');
            setTimeout(() => { window.location.href = `${BASE_URL}/orders.php`; }, 1000);
        } else {
            showToast(result.message || 'Checkout gagal', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Buat Pesanan';
        }
    } catch (err) {
        showToast('Terjadi kesalahan koneksi', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Buat Pesanan';
    }
}
