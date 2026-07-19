<?php
/**
 * DummyJsonService
 *
 * Kelas ini adalah SATU-SATUNYA pintu untuk mengambil data produk
 * dari Platform API eksternal https://dummyjson.com
 *
 * Semua halaman/API lain WAJIB memanggil produk melalui service ini,
 * tidak boleh memanggil cURL ke DummyJSON secara langsung dari file lain.
 */
class DummyJsonService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = DUMMYJSON_BASE_URL;
    }

    /**
     * Fungsi inti untuk melakukan request GET ke DummyJSON menggunakan cURL.
     */
    private function request(string $endpoint): ?array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            return null;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Mengambil daftar produk dengan limit & skip (untuk pagination).
     */
    public function getProducts(int $limit = 20, int $skip = 0): ?array
    {
        return $this->request("/products?limit={$limit}&skip={$skip}");
    }

    /**
     * Mengambil detail satu produk berdasarkan ID.
     */
    public function getProduct(int $id): ?array
    {
        return $this->request("/products/{$id}");
    }

    /**
     * Mencari produk berdasarkan keyword (menggunakan endpoint search bawaan DummyJSON).
     */
    public function searchProducts(string $keyword, int $limit = 20): ?array
    {
        $query = urlencode($keyword);
        return $this->request("/products/search?q={$query}&limit={$limit}");
    }

    /**
     * Mengambil daftar semua kategori produk.
     */
    public function getCategories(): ?array
    {
        return $this->request('/products/categories');
    }

    /**
     * Mengambil produk berdasarkan slug kategori.
     */
    public function getProductsByCategory(string $slug, int $limit = 20): ?array
    {
        return $this->request("/products/category/{$slug}?limit={$limit}");
    }
}
