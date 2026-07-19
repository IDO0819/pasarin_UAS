/**
 * product.js - Mengatur halaman detail produk: memuat data dari API,
 * qty stepper, tambah ke keranjang, dan beli sekarang.
 */

let currentProduct = null;

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');

    if (!id) {
        window.location.href = `${BASE_URL}/index.php`;
        return;
    }

    loadProductDetail(id);
    setupQtyStepper();
});

async function loadProductDetail(id) {
    const container = document.getElementById('product-detail-container');
    container.innerHTML = '<div class="loader">Memuat detail produk...</div>';

    try {
        const result = await apiRequest(`products.php?id=${id}`);
        if (!result.success) throw new Error(result.message);

        currentProduct = result.data;
        renderProductDetail(currentProduct);
    } catch (err) {
        container.innerHTML = '<div class="empty-state"><div class="icon">⚠️</div><p>Produk tidak ditemukan.</p></div>';
    }
}

function renderProductDetail(p) {
    document.title = `${p.title} - Pasarin`;
    const container = document.getElementById('product-detail-container');

    container.innerHTML = `
        <div class="product-detail">
            <div class="product-detail-img">
                <img src="${p.images?.[0] || p.thumbnail}" alt="${p.title}" id="main-product-image">
            </div>
            <div>
                <span class="badge">${p.brand || 'Tanpa Brand'}</span>
                <span class="badge">${(p.category || '').replace(/-/g, ' ')}</span>
                <h1>${p.title}</h1>
                <div>⭐ ${p.rating} &nbsp;|&nbsp; Stok: ${p.stock}</div>
                <div class="price">${formatRupiah(p.price)}</div>

                <div class="qty-stepper">
                    <button type="button" id="qty-minus">-</button>
                    <input type="number" id="qty-input" value="1" min="1" max="${p.stock}">
                    <button type="button" id="qty-plus">+</button>
                </div>

                <div class="detail-actions">
                    <button class="btn btn-outline" onclick="handleAddToCart(${p.id})">🛒 Tambah Keranjang</button>
                    <button class="btn btn-primary" onclick="handleBuyNow(${p.id})">Beli Sekarang</button>
                </div>

                <p class="description">${p.description}</p>
            </div>
        </div>
    `;
}

function setupQtyStepper() {
    document.addEventListener('click', (e) => {
        if (e.target.id === 'qty-plus') {
            const input = document.getElementById('qty-input');
            const max = parseInt(input.max || 999, 10);
            input.value = Math.min(max, parseInt(input.value, 10) + 1);
        }
        if (e.target.id === 'qty-minus') {
            const input = document.getElementById('qty-input');
            input.value = Math.max(1, parseInt(input.value, 10) - 1);
        }
    });
}

function getSelectedQty() {
    const input = document.getElementById('qty-input');
    return input ? Math.max(1, parseInt(input.value, 10)) : 1;
}

async function handleAddToCart(productId) {
    await addToCart(productId, getSelectedQty());
}

async function handleBuyNow(productId) {
    await addToCart(productId, getSelectedQty());
    window.location.href = `${BASE_URL}/cart.php`;
}
