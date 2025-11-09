<?php
// Inicia ou retoma a sessão para acessar as informações do usuário logado
session_start();
// Inclui o arquivo que contém as credenciais de conexão com o banco de dados
require 'config.php';

// ======================================
// 1. VERIFICAÇÃO DE LOGIN
// ======================================

// Verifica se a variável de sessão 'usuario_id' existe (se o usuário está logado)
if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, redireciona para a página de login
    header('Location: index.php');
    exit; // Interrompe o script
}

// ======================================
// 2. VALIDAÇÃO DO ID DO FAVORITO
// ======================================

// Tenta obter o ID do favorito a ser removido da URL (GET) e o converte para inteiro
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Verifica se o ID do favorito é inválido (igual ou menor que zero)
if ($id <= 0) {
    // Se for inválido, redireciona o usuário de volta para a home
    header('Location: home.php');
    exit; // Interrompe o script
}

// ======================================
// 3. EXECUÇÃO DA REMOÇÃO SEGURA
// ======================================

// Prepara a consulta SQL para DELETAR um favorito.
// CRUCIAL: A exclusão é baseada no ID do favorito E no ID do usuário logado,
// garantindo que ele não remova o favorito de outra pessoa.
$stmt = $mysqli->prepare("DELETE FROM favoritos WHERE id = ? AND usuario_id = ?");
// Liga os dois parâmetros como inteiros (ii): ID do favorito e ID do usuário da sessão
$stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
// Executa a remoção no banco de dados
$stmt->execute();


// ======================================
// 4. REDIRECIONAMENTO FINAL
// ======================================

// Após tentar a exclusão (com sucesso ou não), redireciona o usuário para a home
header('Location: home.php');
exit; // Interrompe o script
?>