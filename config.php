<?php
// config.php

// Configurações do Banco de Dados
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'cumulus';

// Conexão com o Banco de Dados
$mysqli = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Verifica a conexão
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Define o charset para UTF-8
$mysqli->set_charset("utf8mb4");
?>
