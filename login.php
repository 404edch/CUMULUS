<?php
// login.php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/config.php'; // precisa expor $mysqli (MySQLi)

$next = $_GET['next'] ?? $_POST['next'] ?? 'home.php';

// se já estiver logado, pula direto
if (!empty($_SESSION['usuario_id'])) {
  header('Location: ' . $next);
  exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $senha = $_POST['senha'] ?? '';

  // validações simples
  if ($email === '' || $senha === '') {
    $erro = 'Informe email e senha.';
  } else {
    // busca usuário
    $stmt = $mysqli->prepare('SELECT id, nome, role, senha FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if (!$user) {
      $erro = 'Usuário não encontrado.';
    } elseif (!password_verify($senha, $user['senha'])) {
      // senha inválida
      $erro = 'Credenciais inválidas.';
    } else {
      // sucesso de login
      session_regenerate_id(true);
      $_SESSION['usuario_id']   = (int)$user['id'];
      $_SESSION['usuario_nome'] = $user['nome'] ?? 'Usuário';
      $_SESSION['role']         = $user['role'] ?? 'usuario';

      // redireciona com segurança (aceita só caminhos relativos)
      $destino = (strpos($next, '/') === 0 || preg_match('#^[a-zA-Z0-9_\-]+\.php#', $next))
        ? $next
        : 'home.php';

      header('Location: ' . $destino);
      exit;
    }
  }
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h3 mb-4">Login</h1>

        <?php if ($erro): ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
          <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" required autocomplete="email">
          </div>

          <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input id="senha" name="senha" type="password" class="form-control" required autocomplete="current-password">
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <button class="btn btn-primary" type="submit">Entrar</button>
            <a class="link-secondary" href="cadastro.php">Criar conta</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
