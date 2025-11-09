<?php
/**
 * favoritos.php
 * Gerenciamento de localizações favoritas (BUC-05)
 */

require 'config.php';
require 'check_login.php';
require_login();

$usuario_id = get_user_id();

// Buscar favoritos com paginação
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Contar total de favoritos
$stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM favoritos WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$count_result = $result->fetch_assoc();
$total = $count_result['total'] ?? 0;
$total_pages = ceil($total / $per_page);
$stmt->close();

// Buscar favoritos da página atual
$stmt = $mysqli->prepare("SELECT * FROM favoritos WHERE usuario_id = ? ORDER BY COALESCE(ordem, 999999), criado_em DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $usuario_id, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$favoritos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoritos - CUMULUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Home.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4"><i class="fas fa-heart"></i> Meus Favoritos</h1>
                
                <?php if (empty($favoritos)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Você não tem favoritos salvos. 
                        <a href="home.php">Clique aqui para adicionar</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Coordenadas</th>
                                    <th>Apelido</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($favoritos as $fav): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($fav['nome_local']); ?></strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo round($fav['latitude'], 6); ?>, <?php echo round($fav['longitude'], 6); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($fav['apelido'] ?? '-'); ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i', strtotime($fav['criado_em'])); ?></small>
                                        </td>
                                        <td>
                                            <a href="editar_favorito.php?id=<?php echo $fav['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a href="remover_favorito.php?id=<?php echo $fav['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">
                                                <i class="fas fa-trash"></i> Remover
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Paginação">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1">Primeira</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Anterior</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Próxima</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $total_pages; ?>">Última</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                    <p class="text-muted text-center mt-3">
                        Total: <?php echo $total; ?> favorito(s)
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
