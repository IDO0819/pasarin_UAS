/**
 * home.js - Mengatur tampilan halaman beranda:
 * daftar kategori, grid produk, dan filter kategori/pencarian.
 */

const categoryIcons = {
    beauty: '💄', fragrances: '🧴', furniture: '🛋️', groceries: '🛒',
    'home-decoration': '🏠', 'kitchen-accessories': '🍳', laptops: '💻',
    'mens-shirts': '👔', 'mens-shoes': '👞', 'mens-watches': '⌚',
    'mobile-accessories': '🎧', motorcycle: '🏍️', skincare: '🧴',
    smartphones: '📱', 'sports-accessories': '⚽', sunglasses: '🕶️',
    tablets: '📱', tops: '👕', vehicle: '🚗', 'womens-bags': '👜',
    'womens-dresses': '👗', 'womens-jewellery': '💍', 'womens-shoes': '👠',
    'womens-watches': '⌚',
};

function getCategoryIcon(slug) {
    return categoryIcons[slug] || '🏷️';
}

let activeCategory = null;

document.addEventListener('DOMContentLoaded', () => {
    loadCategories();

    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('q');

    if (searchQuery) {
        loadProducts({ q: searchQuery });
    } else {
        loadProducts();
    }
});

/** Memuat daftar kategori dari REST API internal (yang mengambil dari DummyJSON) */
async function loadCategories() {
    const container = document.getElementById('category-grid');
    if (!container) return;

    try {
        const result = await apiRequest('categories.php');
        if (!result.success) throw new Error(result.message);

        container.innerHTML = result.data.map(cat => {
            const slug = typeof cat === 'string' ? cat : cat.slug;
            const name = typeof cat === 'string' ? cat : cat.name;
            return `
                <div class="category-card" data-slug="${slug}" onclick="filterByCategory('${slug}')">
                    <div class="category-icon">${getCategoryIcon(slug)}</div>
                    <div class="category-name">${name.replace(/-/g, ' ')}</div>
                </div>
            `;
        }).join('');
    } catch (err) {
        container.innerHTML = '<p>Gagal memuat kategori.</p>';
    }
}

/** Memuat produk: default list, atau filter kategori, atau hasil pencarian */
async function loadProducts({ category = null, q = null } = {}) {
    const grid = document.getElementById('product-grid');
    const titleEl = document.getElementById('product-section-title');
    if (!grid) return;

    grid.innerHTML = '<div class="loader">Memuat produk...</div>';

    try {
        let endpoint = 'products.php?limit=24';
        if (category) {
            endpoint = `products.php?category=${encodeURIComponent(category)}`;
            if (titleEl) titleEl.textContent = `Kategori: ${category.replace(/-/g, ' ')}`;
        } else if (q) {
            endpoint = `products.php?q=${encodeURIComponent(q)}`;
            if (titleEl) titleEl.textContent = `Hasil pencarian: "${q}"`;
        } else if (titleEl) {
            titleEl.textContent = 'Produk Pilihan Untukmu';
        }

        const result = await apiRequest(endpoint);
        if (!result.success) throw new Error(result.message);

        renderProductGrid(grid, result.data);
    } catch (err) {
        grid.innerHTML = '<div class="empty-state"><div class="icon">⚠️</div><p>Gagal memuat produk dari server.</p></div>';
    }
}

function renderProductGrid(grid, products) {
    if (!products || products.length === 0) {
        grid.innerHTML = '<div class="empty-state"><div class="icon">📭</div><p>Produk tidak ditemukan.</p></div>';
        return;
    }

    grid.innerHTML = products.map(p => `
        <div class="product-card">
            <a href="${BASE_URL}/product.php?id=${p.id}">
                <img class="product-thumb" src="${p.thumbnail}" alt="${p.title}" loading="lazy">
            </a>
            <div class="product-info">
                <a href="${BASE_URL}/product.php?id=${p.id}">
                    <div class="product-title">${p.title}</div>
                </a>
                <div class="product-price">${formatRupiah(p.price)}</div>
                <div class="product-meta">
                    <span>⭐ ${p.rating ?? '-'}</span>
                    <span>Stok: ${p.stock ?? 0}</span>
                </div>
            </div>
            <div class="product-actions">
                <a class="btn btn-outline" href="${BASE_URL}/product.php?id=${p.id}">Detail</a>
                <button class="btn btn-primary" onclick="addToCart(${p.id})">+ Keranjang</button>
            </div>
        </div>
    `).join('');
}

function filterByCategory(slug) {
    document.querySelectorAll('.category-card').forEach(el => el.classList.remove('active'));
    const active = document.querySelector(`.category-card[data-slug="${slug}"]`);
    if (active) active.classList.add('active');
    loadProducts({ category: slug });
}
