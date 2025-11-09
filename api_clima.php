<?php


error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/config.php';
require __DIR__ . '/lib/weather.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
    $lon = isset($_GET['lon']) ? (float)$_GET['lon'] : null;

    if ($lat === null || $lon === null) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'MISSING_PARAMS']);
        exit;
    }

    
    if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'INVALID_COORDINATES']);
        exit;
    }

    
    $weather_data = get_weather_data($lat, $lon);

    if ($weather_data['ok']) {
        echo json_encode([
            'ok' => true,
            'data' => [
                'current' => $weather_data['current'],
                'forecast' => $weather_data['forecast']
            ],
            'duration_ms' => $weather_data['duration_ms']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(502);
        echo json_encode([
            'ok' => false,
            'error' => $weather_data['error'],
            'duration_ms' => $weather_data['duration_ms']
        ]);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'SERVER_ERROR',
        'message' => $e->getMessage()
    ]);
}
?>
