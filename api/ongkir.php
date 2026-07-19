<?php
/**
 * REST API: Ongkos Kirim
 *
 * POST /api/ongkir.php  { destination_city_id, weight, courier }
 * Menggunakan API Ongkir eksternal (contoh: RajaOngkir Starter).
 * Jika API key belum diisi di config, sistem memakai estimasi fallback
 * supaya aplikasi tetap bisa didemokan tanpa API key aktif.
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$input       = getJsonInput();
$destination = sanitize($input['destination_city_id'] ?? '');
$weight      = (int) ($input['weight'] ?? 1000); // dalam gram
$courier     = sanitize($input['courier'] ?? 'jne');

if (!$destination) {
    jsonResponse(['success' => false, 'message' => 'Kota tujuan wajib diisi'], 422);
}

/**
 * Memanggil API RajaOngkir. Jika gagal / API key belum diisi,
 * gunakan estimasi lokal agar checkout tetap bisa berjalan saat demo.
 */
function getOngkirFromApi(string $destination, int $weight, string $courier): ?array
{
    if (ONGKIR_API_KEY === 'GANTI_DENGAN_API_KEY_ANDA') {
        return null;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => ONGKIR_BASE_URL . '/cost',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['key: ' . ONGKIR_API_KEY],
        CURLOPT_POSTFIELDS     => http_build_query([
            'origin'      => ONGKIR_ORIGIN_CITY_ID,
            'destination' => $destination,
            'weight'      => $weight,
            'courier'     => $courier,
        ]),
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return null;
    }

    $decoded = json_decode($response, true);
    return $decoded['rajaongkir']['results'][0]['costs'] ?? null;
}

$apiResult = getOngkirFromApi($destination, $weight, $courier);

if ($apiResult !== null) {
    jsonResponse(['success' => true, 'source' => 'api', 'data' => $apiResult]);
}

// Fallback estimasi lokal (dipakai saat API key belum dikonfigurasi)
$baseRates = [
    'jne'    => ['label' => 'JNE',    'services' => [['service' => 'REG', 'etd' => '2-3 hari', 'cost' => 15000], ['service' => 'YES', 'etd' => '1-2 hari', 'cost' => 25000]]],
    'jnt'    => ['label' => 'J&T',    'services' => [['service' => 'EZ',  'etd' => '2-4 hari', 'cost' => 13000]]],
    'sicepat'=> ['label' => 'SiCepat','services' => [['service' => 'REG', 'etd' => '2-3 hari', 'cost' => 14000], ['service' => 'BEST', 'etd' => '1 hari', 'cost' => 22000]]],
];

$courierKey = strtolower($courier);
$rate = $baseRates[$courierKey] ?? $baseRates['jne'];

// Sesuaikan biaya sedikit berdasarkan berat (per 1000 gram)
$weightFactor = max(1, ceil($weight / 1000));
$services = array_map(function ($s) use ($weightFactor) {
    $s['cost'] = $s['cost'] * $weightFactor;
    return $s;
}, $rate['services']);

jsonResponse([
    'success' => true,
    'source'  => 'fallback_estimate',
    'data'    => [[
        'courier'  => $rate['label'],
        'costs'    => $services,
    ]],
]);
