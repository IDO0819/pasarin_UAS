/**
 * app.js - fungsi bantu JavaScript yang dipakai di banyak halaman.
 * Murni Vanilla JS, menggunakan fetch() dan async/await (tanpa jQuery).
 */

const BASE_URL = window.PASARIN_BASE_URL || '/pasarin';

/**
 * Wrapper fetch untuk memanggil REST API internal (bukan DummyJSON langsung).
 */
async function apiRequest(endpoint, options = {}) {
    const response = await fetch(`${BASE_URL}/api/${endpoint}`, {
        headers: { 'Content-Type': 'application/json' },
        ...options,
    });
    return response.json();
}

/** Format angka menjadi Rupiah, contoh: Rp 150.000 */
function formatRupiah(number) {
    return 'Rp ' + Number(number).toLocaleString('id-ID');
}

/** Menampilkan notifikasi kecil (toast) di pojok layar */
function showToast(message, type = 'success') {
    let toast = document.getElementById('app-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'app-toast';
        toast.style.cssText = `
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            padding: 14px 20px; border-radius: 10px; color: #fff;
            font-size: 14px; font-weight: 600; box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            transition: opacity 0.3s ease; opacity: 0;
        `;
        document.body.appendChild(toast);
    }
    toast.style.background = type === 'error' ? '#dc2626' : '#16a34a';
    toast.textContent = message;
    toast.style.opacity = '1';

    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => { toast.style.opacity = '0'; }, 2500);
}

/**
 * Menambahkan produk ke keranjang. Dipanggil dari tombol "Tambah Keranjang".
 */
async function addToCart(productId, qty = 1) {
    try {
        const result = await apiRequest('cart.php', {
            method: 'POST',
            body: JSON.stringify({ product_id: productId, qty }),
        });

        if (result.success) {
            showToast('Produk ditambahkan ke keranjang');
        } else if (result.message && result.message.includes('login')) {
            showToast('Silakan login terlebih dahulu', 'error');
            setTimeout(() => { window.location.href = `${BASE_URL}/login.php`; }, 1200);
        } else {
            showToast(result.message || 'Gagal menambahkan produk', 'error');
        }
    } catch (err) {
        showToast('Terjadi kesalahan koneksi', 'error');
    }
}
