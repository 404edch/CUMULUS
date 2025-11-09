<?php
// search_proxy.php — proxy Nominatim → sempre retorna JSON

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__.'/config.php'; // opcional p/ logs

header('Content-Type: application/json; charset=utf-8');

try {
    $q   = trim($_GET['q']   ?? '');
    $lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
    $lon = isset($_GET['lon']) ? (float)$_GET['lon'] : null;

    if ($q === '' && ($lat === null || $lon === null)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'MISSING_PARAMS']);
        exit;
    }

    if ($q !== '') {
        $url = "https://nominatim.openstreetmap.org/search?"
             . http_build_query(['q' => $q, 'format' => 'json', 'addressdetails' => 1, 'limit' => 10]);
    } else {
        $url = "https://nominatim.openstreetmap.org/reverse?"
             . http_build_query(['lat' => $lat, 'lon' => $lon, 'format' => 'json', 'addressdetails' => 1]);
    }

    $opts = [
        'http' => [
            'method'  => 'GET',
            'header'  => "User-Agent: CumulusApp/1.0 (email@exemplo.com)\r\n",
            'timeout' => 5
        ]
    ];
    $ctx = stream_context_create($opts);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        http_response_code(502);
        echo json_encode(['ok' => false, 'error' => 'GEOCODER_DOWN']);
        exit;
    }

    echo json_encode(['ok' => true, 'data' => json_decode($raw, true)], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'SERVER_ERROR', 'message' => $e->getMessage()]);
}
