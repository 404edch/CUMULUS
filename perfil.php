<?php
/**
 * perfil.php
 * Perfil do usuário
 */

require 'config.php';
require 'check_login.php';
require_login();

$usuario_id = get_user_id();

// Buscar informações do usuário
$stmt = $mysqli->prepare("SELECT id, nome, email, role, criado_em FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Buscar estatísticas
$stmt = $mysqli->prepare(
    "SELECT COUNT(*) as total_pesquisas, SUM(CASE WHEN sucesso = 1 THEN 1 ELSE 0 END) as pesquisas_sucesso 
     FROM historico_pesquisas WHERE usuario_id = ?"
);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$stmt->close();

// Contar favoritos
$stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM favoritos WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$fav_count = $result->fetch_assoc();
$stmt->close();

// Contar alertas
$stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM alertas WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$alert_count = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - CUMULUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Home.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4"><i class="fas fa-user"></i> Meu Perfil</h1>
                
                <!-- Informações Pessoais -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-id-card"></i> Informações Pessoais</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Nome</label>
                                <p class="h6"><?php echo htmlspecialchars($usuario['nome']); ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Email</label>
                                <p class="h6"><?php echo htmlspecialchars($usuario['email']); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tipo de Conta</label>
                                <p>
                                    <?php 
                                    if ($usuario['role'] === 'admin') {
                                        echo '<span class="badge bg-danger">Administrador</span>';
                                    } else {
                                        echo '<span class="badge bg-success">Usuário</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Membro desde</label>
                                <p class="h6"><?php echo date('d/m/Y', strtotime($usuario['criado_em'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h3 class="text-primary">
                                    <i class="fas fa-heart"></i> <?php echo $fav_count['total']; ?>
                                </h3>
                                <p class="text-muted">Favoritos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h3 class="text-warning">
                                    <i class="fas fa-bell"></i> <?php echo $alert_count['total']; ?>
                                </h3>
                                <p class="text-muted">Alertas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h3 class="text-info">
                                    <i class="fas fa-search"></i> <?php echo $stats['total_pesquisas'] ?? 0; ?>
                                </h3>
                                <p class="text-muted">Pesquisas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h3 class="text-success">
                                    <i class="fas fa-check"></i> <?php echo $stats['pesquisas_sucesso'] ?? 0; ?>
                                </h3>
                                <p class="text-muted">Sucesso</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ações Rápidas -->
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt"></i> Ações Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="config_usuario.php" class="btn btn-outline-primary">
                                <i class="fas fa-cog"></i> Configurações
                            </a>
                            <a href="favoritos.php" class="btn btn-outline-primary">
                                <i class="fas fa-heart"></i> Meus Favoritos
                            </a>
                            <a href="alertas.php" class="btn btn-outline-primary">
                                <i class="fas fa-bell"></i> Meus Alertas
                            </a>
                            <a href="logout.php" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
