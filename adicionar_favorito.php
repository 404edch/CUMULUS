<?php
// adicionar_favorito.php — endpoint JSON (sem HTML/footer/header)

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

require __DIR__.'/config.php';        // cria $mysqli
require __DIR__.'/check_login.php';   // define require_login()
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'METHOD_NOT_ALLOWED']);
        exit;
    }

    $usuario_id = (int)($_SESSION['usuario_id'] ?? 0);
    $nome       = trim($_POST['nome_local'] ?? $_POST['nome'] ?? '');
    $lat        = isset($_POST['lat']) ? (float)$_POST['lat'] : null;
    $lon        = isset($_POST['lon']) ? (float)$_POST['lon'] : null;
    $apelido    = trim($_POST['apelido'] ?? '');

    if (!$usuario_id || $nome === '' || $lat === null || $lon === null) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'MISSING_PARAMS']);
        exit;
    }

    $chk = $mysqli->prepare(
        "SELECT id FROM favoritos WHERE usuario_id = ? AND latitude = ? AND longitude = ? LIMIT 1"
    );
    $chk->bind_param('idd', $usuario_id, $lat, $lon);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) {
        echo json_encode(['ok' => false, 'error' => 'ALREADY_EXISTS']);
        exit;
    }

    // Se sua tabela NÃO tiver 'apelido', troque a linha abaixo por:
    // $stmt = $mysqli->prepare("INSERT INTO favoritos (usuario_id, nome_local, latitude, longitude) VALUES (?, ?, ?, ?)");
    // $stmt->bind_param('isdd', $usuario_id, $nome, $lat, $lon);
    $stmt = $mysqli->prepare(
        "INSERT INTO favoritos (usuario_id, nome_local, latitude, longitude, apelido)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('isdds', $usuario_id, $nome, $lat, $lon, $apelido);

    if ($stmt->execute()) {
        echo json_encode(['ok' => true]);
    } else {
        $err = ($mysqli->errno === 1062) ? 'ALREADY_EXISTS' : 'DB_ERROR';
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $err]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'SERVER_ERROR', 'message' => $e->getMessage()]);
}
