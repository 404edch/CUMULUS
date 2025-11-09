<?php
// Inicia a sessão para armazenar dados do usuário logado
session_start();

// Inclui o arquivo de configuração com a conexão ao banco de dados
require 'config.php';

// Cria uma variável para armazenar mensagens de erro de login
$login_error = '';

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtém os valores enviados pelo formulário ou define como string vazia se não existir
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Prepara uma consulta SQL segura para buscar o usuário pelo email
    $stmt = $mysqli->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
    // Faz a ligação do parâmetro (?) com o valor da variável $email (tipo string "s")
    $stmt->bind_param("s", $email);
    // Executa a consulta
    $stmt->execute();
    // Obtém o resultado da consulta
    $result = $stmt->get_result();
    // Converte o resultado em um array associativo
    $user = $result->fetch_assoc();

    // Verifica se o usuário existe e se a senha informada corresponde à senha criptografada no banco
    if ($user && password_verify($senha, $user['senha'])) {
        // Se estiver correto, cria variáveis de sessão para manter o login ativo
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];

        // Redireciona o usuário para a página inicial (home.php)
        header('Location: home.php');
        exit;
    } else {
        // Caso o email ou a senha estejam incorretos, define uma mensagem de erro
        $login_error = 'Email ou senha inválidos.';
    }
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cumulus - Login</title>
    <!-- Importa o CSS do Bootstrap para estilização -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Importa o CSS personalizado -->
    <link rel="stylesheet" href="Home.css">
</head>

<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
        <div class="card p-4" style="max-width:420px; width:100%;">
            <h3 class="mb-3">Login</h3>

            <!-- Exibe mensagem de erro, se existir -->
            <?php if ($login_error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulário de login -->
            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <!-- Campo de email obrigatório -->
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <!-- Campo de senha obrigatório -->
                    <input type="password" name="senha" class="form-control" required>
                </div>
                
                <!-- Área dos botões e imagem -->
                <div class="sla d-flex flex-column align-items-center">
                    <div class="login-actions">
                        <!-- Botão de login -->
                        <button id="dormir" class="btn btn-primary">Entrar</button>
                        <!-- Link para criar nova conta -->
                        <a href="cadastro.php">Criar conta</a>
                    </div>
                    
                    <!-- Imagem exibida abaixo do botão -->
                    <img class="engracadinho" 
                         src="https://i.pinimg.com/736x/a3/8d/6e/a38d6ec6b3e355722d0c985c0c030d79.jpg" 
                         alt="Imagem Engraçada"
                         width="100px">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
