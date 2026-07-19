# Pasarin v2

Aplikasi marketplace sederhana (mirip Shopee/Tokopedia) yang dibangun dengan **PHP Native**, **MySQL**, **Vanilla JavaScript**, dan **REST API**, dibuat untuk keperluan Tugas UAS.

Produk yang ditampilkan **100% berasal dari [DummyJSON](https://dummyjson.com)** (Platform API eksternal). Database MySQL hanya dipakai untuk data internal aplikasi: user, keranjang, dan order.

---

## 1. Teknologi yang Digunakan

| Komponen        | Teknologi                          |
|------------------|-------------------------------------|
| Backend          | PHP Native (tanpa framework)        |
| Database         | MySQL (PDO + Prepared Statement)    |
| Frontend         | HTML5, CSS3 Native, Vanilla JS      |
| Data Produk      | DummyJSON REST API                  |
| Ongkos Kirim     | RajaOngkir API (dengan fallback estimasi lokal) |
| Server           | XAMPP (Apache + MySQL)              |

Tidak menggunakan Composer, NodeJS, React/Vue/Angular, Bootstrap, Tailwind, maupun jQuery.

---

## 2. Struktur Folder

```
pasarin/
├── admin/                  # Panel admin (dashboard, order, user)
│   ├── includes/
│   ├── index.php
│   ├── orders.php
│   └── users.php
├── api/                    # REST API (JSON response)
│   ├── auth.php
│   ├── cart.php
│   ├── categories.php
│   ├── ongkir.php
│   ├── orders.php
│   └── products.php
├── assets/
│   ├── css/style.css
│   ├── js/                 # app.js, home.js, product.js, cart.js, checkout.js, auth.js
│   └── images/
├── config/
│   ├── config.php          # Konfigurasi umum & session
│   └── database.php        # Koneksi PDO ke MySQL
├── includes/
│   ├── auth.php            # Helper login/CSRF
│   ├── functions.php       # Helper umum (sanitize, format rupiah, dll)
│   ├── header.php
│   └── footer.php
├── services/
│   └── DummyJsonService.php # Satu-satunya pintu ke API DummyJSON
├── index.php                # Halaman Home
├── product.php               # Detail Produk
├── cart.php                  # Keranjang
├── checkout.php               # Checkout + Ongkir
├── orders.php                 # Riwayat Pesanan
├── login.php / register.php / logout.php
├── database.sql               # Skema database
└── README.md
```

---

## 3. Cara Instalasi (XAMPP)

### Langkah 1 — Salin Project
Salin folder `pasarin/` ke dalam folder `htdocs` XAMPP, contoh:

```
C:\xampp\htdocs\pasarin
```

atau di Linux/Mac:

```
/opt/lampp/htdocs/pasarin
```

### Langkah 2 — Jalankan Apache & MySQL
Buka **XAMPP Control Panel**, lalu klik **Start** pada modul **Apache** dan **MySQL**.

### Langkah 3 — Buat Database
1. Buka **phpMyAdmin** di `http://localhost/phpmyadmin`
2. Klik tab **Import**
3. Pilih file `database.sql` dari folder project
4. Klik **Go** — database `pasarin_db` beserta seluruh tabel akan otomatis dibuat, lengkap dengan 1 akun admin default.

### Langkah 4 — Sesuaikan Konfigurasi Database
Buka `config/database.php`, sesuaikan jika kredensial MySQL Anda berbeda dari default XAMPP:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pasarin');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Langkah 5 (Opsional) — Konfigurasi API Ongkos Kirim
Buka `config/config.php`, isi API key ongkir Anda:

```php
define('ONGKIR_API_KEY', 'API_KEY_ANDA');
```

> Jika API key belum diisi, sistem otomatis memakai **estimasi ongkir lokal** (fallback) agar checkout tetap bisa didemokan tanpa API key aktif.

### Langkah 6 — Jalankan Aplikasi
Buka browser dan akses:

```
http://localhost/pasarin/index.php
```

---

## 4. Akun Default

| Role     | Email                  | Password    |
|----------|-------------------------|-------------|
| Admin    | admin@pasarin.local     | admin1234   |

Panel admin dapat diakses di:
```
http://localhost/pasarin/admin/index.php
```

Untuk akun customer, silakan daftar sendiri melalui halaman **Register**.

---

## 5. Alur Data Produk

```
Browser  →  index.php / product.php  →  fetch() JS
         →  api/products.php (REST API)
         →  services/DummyJsonService.php (cURL)
         →  https://dummyjson.com
```

Produk **tidak pernah** disimpan ke tabel MySQL. Setiap kali halaman dibuka, data selalu segar (real-time) dari DummyJSON.

## 6. Alur Keranjang & Checkout

- Saat "Tambah Keranjang" ditekan → hanya `product_id` dan `qty` yang disimpan ke tabel `cart_items`.
- Saat halaman keranjang dibuka → sistem mengambil ulang nama, harga, thumbnail dari DummyJSON berdasarkan `product_id` yang tersimpan.
- Saat checkout → harga disinkronkan sekali lagi dari DummyJSON, ongkir dihitung via API ongkir, lalu order & order_items disimpan permanen sebagai snapshot riwayat transaksi.

---

## 7. Keamanan yang Diterapkan

- Prepared Statement (PDO) di seluruh query database
- `password_hash()` & `password_verify()` untuk password
- `htmlspecialchars()` di semua output dinamis
- Validasi CSRF token sederhana pada form checkout & update status order
- Validasi input di sisi server (bukan hanya client-side)
- Session-based authentication dengan pemisahan role `customer` dan `admin`

---

## 8. Catatan untuk Presentasi UAS

Poin yang bisa disampaikan saat presentasi:
1. Aplikasi menggunakan **2 sumber data**: DummyJSON (produk) dan MySQL (data internal).
2. Semua komunikasi ke DummyJSON melalui satu service class (`DummyJsonService.php`) — clean architecture.
3. Aplikasi memiliki REST API sendiri (`/api/*.php`) yang dikonsumsi oleh frontend menggunakan `fetch()`.
4. Tidak ada duplikasi data produk — cart & order selalu sinkron dengan harga terbaru dari API eksternal.
5. Desain UI modern terinspirasi Shopee/Tokopedia, dibangun murni dengan CSS Native (tanpa framework).
