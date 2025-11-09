<?php
/**
 * detalhes_clima.php
 * Mostra os detalhes do clima para uma coordenada
 */

require __DIR__ . '/config.php';
require 'check_login.php';
require_login();
require __DIR__ . '/lib/weather.php';
require __DIR__ . '/lib/analytics.php';

header('Content-Type: text/html; charset=utf-8');

$lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
$lon = isset($_GET['lon']) ? (float)$_GET['lon'] : null;

if ($lat === null || $lon === null) {
    echo '<div class="alert alert-warning">Coordenadas inválidas.</div>';
    exit;
}

$inicio_ms = microtime(true);

// Obter dados de clima
$weather_data = get_weather_data($lat, $lon);
$duracao_ms = $weather_data['duration_ms'] ?? 0;

// Log da pesquisa
log_pesquisa($mysqli, [
    'usuario_id' => $_SESSION['usuario_id'],
    'termo' => 'coords',
    'origem' => 'manual',
    'lat' => $lat,
    'lon' => $lon,
    'resultados' => $weather_data['ok'] ? 1 : 0,
    'sucesso' => $weather_data['ok'] ? 1 : 0,
    'duracao_ms' => $duracao_ms
]);

// Exibir dados
if ($weather_data['ok']) {
    echo format_weather_display($weather_data);
} else {
    echo '<div class="alert alert-danger">Erro ao obter dados de clima: ' . htmlspecialchars($weather_data['error']) . '</div>';
}
?>
