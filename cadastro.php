<?php
// Inicia a sessão para poder armazenar dados do usuário entre páginas
session_start();

// Inclui o arquivo de configuração que faz a conexão com o banco de dados ($mysqli)
require 'config.php';

// faz com que as páginas seja renderizadas com a navbar + bloqueio de acesso quando deslogado
 include 'partials/header.php';


// Cria um array para armazenar mensagens de erro
$errors = [];

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Captura e limpa os campos do formulário
    $nome   = trim($_POST['nome'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $senha  = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha2'] ?? '';

    // ===========================
    // VALIDAÇÕES BÁSICAS
    // ===========================

    // Verifica se as senhas são iguais
    if ($senha !== $senha2) {
        $errors[] = 'As senhas não conferem.';
    }

    // Verifica se o email é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido.';
    }

    // Verifica se a senha tem pelo menos 6 caracteres
    if (strlen($senha) < 6) {
        $errors[] = 'Senha deve ter ao menos 6 caracteres.';
    }

    // Só continua se não houver erros até aqui
    if (empty($errors)) {

        // ===========================
        // VERIFICA SE O EMAIL JÁ EXISTE
        // ===========================
        $check = $mysqli->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // Caso o email já exista, adiciona erro
            $errors[] = 'Email já cadastrado.';
        } else {
            // ===========================
            // INSERE NOVO USUÁRIO
            // ===========================
            
            // Cria um hash seguro da senha
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            // Prepara a query de inserção
            $stmt = $mysqli->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $hash);

            // Executa e redireciona se der certo
            if ($stmt->execute()) {
                header('Location: index.php');
                exit;
            } else {
                // Caso ocorra erro ao salvar no banco
                $errors[] = 'Erro no cadastro. Tente novamente.';
            }
        }

        // Fecha o statement de verificação
        $check->close();
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cumulus - Cadastro</title>

    <!-- Importa o CSS do Bootstrap para estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
        <div class="card p-4" style="max-width:480px; width:100%;">
            
            <!-- Título -->
            <h3 class="mb-3">Criar Conta</h3>

            <!-- Exibição de mensagens de erro -->
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulário de cadastro -->
            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" name="senha" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Repita a senha</label>
                    <input type="password" name="senha2" class="form-control" required>
                </div>

                <!-- Botões -->
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-success">Criar</button>
                    <a href="index.php">Voltar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php include 'partials/footer.php'; ?>
