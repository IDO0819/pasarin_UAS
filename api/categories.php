<?php
/**
 * REST API: Kategori
 * GET /api/categories.php -> daftar kategori dari DummyJSON
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/DummyJsonService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$service = new DummyJsonService();
$categories = $service->getCategories();

$icons = [
    'beauty' => '💄',
    'fragrances' => '🌸',
    'furniture' => '🪑',
    'groceries' => '🛒',
    'home-decoration' => '🏠',
    'kitchen-accessories' => '🍳',
    'laptops' => '💻',
    'mens-shirts' => '👕',
    'mens-shoes' => '👟',
    'mens-watches' => '⌚',
    'mobile-accessories' => '📱',
    'motorcycle' => '🏍️',
    'skin-care' => '🧴',
    'smartphones' => '📱',
    'sports-accessories' => '⚽',
    'sunglasses' => '🕶️',
    'tablets' => '📱',
    'tops' => '👚',
    'vehicle' => '🚗',
    'womens-bags' => '👜',
    'womens-dresses' => '👗',
    'womens-jewellery' => '💍',
    'womens-shoes' => '👠',
    'womens-watches' => '⌚'
];

$result = [];

foreach ($categories as $c) {

    $slug = is_array($c) ? $c['slug'] : $c;
    $name = is_array($c)
        ? $c['name']
        : ucwords(str_replace('-', ' ', $slug));

    $result[] = [
        'slug' => $slug,
        'name' => $name,
        'icon' => $icons[$slug] ?? '🛍️'
    ];
}

jsonResponse([
    'success'=>true,
    'data'=>$result
]);