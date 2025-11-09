<?php
// Inicia a sessão para garantir que ela possa ser encerrada corretamente
session_start();

// Remove todas as variáveis de sessão (limpa os dados do usuário)
session_unset();

// Destroi a sessão completamente (encerra o login)
session_destroy();

// Redireciona o usuário de volta para a página de login (index.php)
header('Location: index.php');

// Encerra o script imediatamente após o redirecionamento
exit;
?>
