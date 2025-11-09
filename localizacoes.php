<?php


require 'config.php';
require 'check_login.php';
require_login();

$usuario_id = get_user_id();
$message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $nome_local = $_POST['nome_local'] ?? '';
            $latitude = (float)($_POST['latitude'] ?? 0);
            $longitude = (float)($_POST['longitude'] ?? 0);
            
            if ($nome_local && $latitude && $longitude) {
                $stmt = $mysqli->prepare(
                    "INSERT INTO localizacoes (nome_local, latitude, longitude) VALUES (?, ?, ?)"
                );
                $stmt->bind_param('sdd', $nome_local, $latitude, $longitude);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Localização adicionada!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Erro ao adicionar localização.</div>';
                }
                $stmt->close();
            }
        } elseif ($_POST['action'] === 'update') {
            $local_id = (int)$_POST['local_id'];
            $nome_local = $_POST['nome_local'] ?? '';
            
            $stmt = $mysqli->prepare("UPDATE localizacoes SET nome_local = ? WHERE id = ?");
            $stmt->bind_param('si', $nome_local, $local_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Localização atualizada!</div>';
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'delete') {
            $local_id = (int)$_POST['local_id'];
            
            // Verificar se está vinculado a favoritos ou alertas
            $stmt = $mysqli->prepare(
                "SELECT COUNT(*) as count FROM favoritos WHERE latitude = (SELECT latitude FROM localizacoes WHERE id = ?) 
                 AND longitude = (SELECT longitude FROM localizacoes WHERE id = ?)"
            );
            $stmt->bind_param('ii', $local_id, $local_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $check = $result->fetch_assoc();
            $stmt->close();
            
            if ($check['count'] > 0) {
                $message = '<div class="alert alert-warning">Não é possível deletar uma localização vinculada a favoritos.</div>';
            } else {
                $stmt = $mysqli->prepare("DELETE FROM localizacoes WHERE id = ?");
                $stmt->bind_param('i', $local_id);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Localização removida!</div>';
                }
                $stmt->close();
            }
        }
    }
}


$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;


$result = $mysqli->query("SELECT COUNT(*) as total FROM localizacoes");
$count = $result->fetch_assoc();
$total = $count['total'];
$total_pages = ceil($total / $per_page);


$stmt = $mysqli->prepare("SELECT * FROM localizacoes ORDER BY nome_local ASC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$localizacoes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Localizações - CUMULUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Home.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4"><i class="fas fa-map-marker-alt"></i> Catálogo de Localizações</h1>
                
                <?php echo $message; ?>
                
                <!-- Formulário de nova localização -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus"></i> Adicionar Localização</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome da Localização</label>
                                    <input type="text" name="nome_local" class="form-control" required>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Latitude</label>
                                    <input type="number" name="latitude" class="form-control" step="0.000001" required>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Longitude</label>
                                    <input type="number" name="longitude" class="form-control" step="0.000001" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Adicionar
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de localizações -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Localizações Cadastradas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($localizacoes)): ?>
                            <p class="text-muted">Nenhuma localização cadastrada.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Latitude</th>
                                            <th>Longitude</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($localizacoes as $loc): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($loc['nome_local']); ?></strong>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo round($loc['latitude'], 6); ?></small>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo round($loc['longitude'], 6); ?></small>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $loc['id']; ?>">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                    <form method="POST" action="" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="local_id" value="<?php echo $loc['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">
                                                            <i class="fas fa-trash"></i> Deletar
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            
                                            <!-- Modal de edição -->
                                            <div class="modal fade" id="editModal<?php echo $loc['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Localização</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST" action="">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="action" value="update">
                                                                <input type="hidden" name="local_id" value="<?php echo $loc['id']; ?>">
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nome</label>
                                                                    <input type="text" name="nome_local" class="form-control" value="<?php echo htmlspecialchars($loc['nome_local']); ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-primary">Salvar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Paginação -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Paginação" class="mt-3">
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
