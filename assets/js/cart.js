/**
 * cart.js - Mengatur halaman keranjang: menampilkan item,
 * ubah qty, hapus item, dan menghitung subtotal.
 */

document.addEventListener('DOMContentLoaded', loadCart);

async function loadCart() {
    const container = document.getElementById('cart-items-container');
    const summaryEl = document.getElementById('cart-summary-subtotal');
    const checkoutBtn = document.getElementById('checkout-btn');

    container.innerHTML = '<div class="loader">Memuat keranjang...</div>';

    try {
        const result = await apiRequest('cart.php');
        if (!result.success) throw new Error(result.message);

        if (result.data.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="icon">🛒</div>
                    <p>Keranjang kamu masih kosong.</p>
                    <a href="${BASE_URL}/index.php" class="btn btn-primary" style="margin-top:12px;">Mulai Belanja</a>
                </div>`;
            if (checkoutBtn) {
                checkoutBtn.style.pointerEvents = 'none';
                checkoutBtn.style.opacity = '0.5';
            }
        } else {
            container.innerHTML = result.data.map(renderCartItem).join('');
            if (checkoutBtn) {
                checkoutBtn.style.pointerEvents = 'auto';
                checkoutBtn.style.opacity = '1';
            }
        }

        if (summaryEl) summaryEl.textContent = formatRupiah(result.subtotal);
        window.cartSubtotal = result.subtotal;
    } catch (err) {
        container.innerHTML = '<p>Gagal memuat keranjang.</p>';
    }
}

function renderCartItem(item) {
    return `
        <div class="cart-item" data-product-id="${item.product_id}">
            <img src="${item.thumbnail}" alt="${item.title}">
            <div class="cart-item-info">
                <div class="cart-item-title">${item.title}</div>
                <div class="cart-item-price">${formatRupiah(item.price)}</div>
            </div>
            <div class="qty-stepper">
                <button type="button" onclick="changeQty(${item.product_id}, ${item.qty - 1})">-</button>
                <input type="number" value="${item.qty}" min="1" max="${item.stock}"
                    onchange="changeQty(${item.product_id}, this.value)">
                <button type="button" onclick="changeQty(${item.product_id}, ${item.qty + 1})">+</button>
            </div>
            <div style="font-weight:700;">${formatRupiah(item.line_total)}</div>
            <button class="btn btn-outline" onclick="removeItem(${item.product_id})">🗑️</button>
        </div>
    `;
}

async function changeQty(productId, newQty) {
    newQty = parseInt(newQty, 10);
    if (isNaN(newQty) || newQty < 1) return;

    await apiRequest('cart.php', {
        method: 'PUT',
        body: JSON.stringify({ product_id: productId, qty: newQty }),
    });
    loadCart();
}

async function removeItem(productId) {
    await apiRequest('cart.php', {
        method: 'DELETE',
        body: JSON.stringify({ product_id: productId }),
    });
    showToast('Item dihapus dari keranjang');
    loadCart();
}
