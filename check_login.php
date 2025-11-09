<?php
// check_login.php
// Se ainda não existe sessão ele inicia, necesário para ler/gravar $_SESSION
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Garante que o usuário esteja autenticado.
 * - Se NÃO estiver logado (não existe $_SESSION['usuario_id']), redireciona para login.php
 *   e envia o parâmetro ?next=<URL-atual> para, depois do login, voltar para a página pedida.
 * - Se estiver logado, não faz nada (segue a execução da página).
 */
function require_login(): void {
  if (empty($_SESSION['usuario_id'])) {
    $back = urlencode($_SERVER['REQUEST_URI'] ?? 'home.php');
    header("Location: login.php?next={$back}");
    exit;
  }
}


/**
 * Exige perfil admin
 */

/**
 * Exige perfil de administrador.
 * - Primeiro chama require_login() (garante que tem sessão).
 * - Depois verifica $_SESSION['role']; se não for 'admin', devolve 403.
 */
function require_admin(): void {
  require_login();
  $role = $_SESSION['role'] ?? 'user';
  if ($role !== 'admin') {
    http_response_code(403);
    echo 'Acesso restrito.';
    exit;
  }
}

/**
 * Retorna o ID do usuário logado
 */
function get_user_id() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Retorna o nome do usuário logado
 */
function get_user_name() {
    return $_SESSION['usuario_nome'] ?? 'Usuário';
}

/**
 * Retorna o role do usuário logado
 */
function get_user_role() {
    return $_SESSION['role'] ?? 'user';
}

/**
 * Verifica se o usuário é admin
 */
function is_admin() {
    return get_user_role() === 'admin';
}

/**
 * Verifica se o usuário está logado
 */
function is_logged_in() {
    return !empty($_SESSION['usuario_id']);
}