<?php
// editar_favorito.php — editar apelido e ordem de um favorito do usuário
require __DIR__ . '/config.php';
require __DIR__ . '/check_login.php';
require_login();

include __DIR__ . '/partials/header.php';

$uid = (int)$_SESSION['usuario_id'];
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
  echo '<div class="alert alert-danger">ID inválido.</div>';
  include __DIR__ . '/partials/footer.php';
  exit;
}

// Carrega o favorito do usuário (garante propriedade)
$stmt = $mysqli->prepare("
  SELECT id, nome_local, latitude, longitude, apelido, ordem
  FROM favoritos
  WHERE id = ? AND usuario_id = ?
  LIMIT 1
");
$stmt->bind_param('ii', $id, $uid);
$stmt->execute();
$fav = $stmt->get_result()->fetch_assoc();

if (!$fav) {
  echo '<div class="alert alert-danger">Favorito não encontrado.</div>';
  include __DIR__ . '/partials/footer.php';
  exit;
}

$erro = null; $ok = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $apelido = trim($_POST['apelido'] ?? '');
  $ordem   = ($_POST['ordem'] ?? '') === '' ? null : (int)$_POST['ordem'];

  // validações simples
  if ($apelido !== '' && mb_strlen($apelido) > 60) {
    $erro = 'Apelido pode ter no máximo 60 caracteres.';
  } elseif ($ordem !== null && ($ordem < 0 || $ordem > 999999)) {
    $erro = 'Ordem deve ser um número entre 0 e 999999.';
  } else {
    $stmt = $mysqli->prepare("UPDATE favoritos SET apelido = ?, ordem = ? WHERE id = ? AND usuario_id = ?");
    // bind null corretamente para ordem
    if ($ordem === null) {
      $null = null;
      $stmt->bind_param('siii', $apelido, $null, $id, $uid);
    } else {
      $stmt->bind_param('siii', $apelido, $ordem, $id, $uid);
    }
    $stmt->execute();
    if ($stmt->affected_rows >= 0) {
      $ok = 'Favorito atualizado com sucesso.';
      // recarrega dados
      $stmt = $mysqli->prepare("
        SELECT id, nome_local, latitude, longitude, apelido, ordem
        FROM favoritos WHERE id = ? AND usuario_id = ? LIMIT 1
      ");
      $stmt->bind_param('ii', $id, $uid);
      $stmt->execute();
      $fav = $stmt->get_result()->fetch_assoc();
    } else {
      $erro = 'Não foi possível atualizar. Tente novamente.';
    }
  }
}
?>

<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h4 mb-3">Editar favorito</h1>

        <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <?php if ($ok):   ?><div class="alert alert-success"><?= htmlspecialchars($ok)   ?></div><?php endif; ?>

        <dl class="row">
          <dt class="col-sm-4">Local</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($fav['nome_local']) ?></dd>

          <dt class="col-sm-4">Coordenadas</dt>
          <dd class="col-sm-8">(<?= $fav['latitude'] ?>, <?= $fav['longitude'] ?>)</dd>
        </dl>

        <form method="post" class="mt-3">
          <div class="mb-3">
            <label class="form-label" for="apelido">Apelido (opcional)</label>
            <input id="apelido" name="apelido" class="form-control" maxlength="60"
                   value="<?= htmlspecialchars($fav['apelido'] ?? '') ?>">
            <div class="form-text">Nome curto para aparecer na lista.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="ordem">Ordem (opcional)</label>
            <input id="ordem" name="ordem" type="number" class="form-control"
                   inputmode="numeric" min="0" max="999999"
                   value="<?= htmlspecialchars((string)($fav['ordem'] ?? '')) ?>">
            <div class="form-text">Menor número = aparece primeiro. Deixe vazio para ordem automática.</div>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-outline-secondary" href="favoritos.php">Voltar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
