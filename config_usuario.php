<?php
/**
 * config_usuario.php
 * Configurações do usuário (BUC-10)
 */

require 'config.php';
require 'check_login.php';
require_login();

$usuario_id = get_user_id();
$message = '';

// Buscar ou criar configurações
$stmt = $mysqli->prepare("SELECT * FROM config_usuario WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$config = $result->fetch_assoc();
$stmt->close();

if (!$config) {
    $unidade_temp = 'C';
    $idioma = 'pt';
    $tema = 'light';
    
    $stmt = $mysqli->prepare("INSERT INTO config_usuario (usuario_id, unidade_temp, idioma, tema) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $usuario_id, $unidade_temp, $idioma, $tema);
    $stmt->execute();
    $stmt->close();
} else {
    $unidade_temp = $config['unidade_temp'] ?? 'C';
    $idioma = $config['idioma'] ?? 'pt';
    $tema = $config['tema'] ?? 'light';
}

// Processar atualização de configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unidade_temp = $_POST['unidade_temp'] ?? 'C';
    $idioma = $_POST['idioma'] ?? 'pt';
    $tema = $_POST['tema'] ?? 'light';
    
    $stmt = $mysqli->prepare(
        "UPDATE config_usuario SET unidade_temp = ?, idioma = ?, tema = ? WHERE usuario_id = ?"
    );
    $stmt->bind_param('sssi', $unidade_temp, $idioma, $tema, $usuario_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success"><i class="fas fa-check"></i> Configurações salvas com sucesso!</div>';
    } else {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation"></i> Erro ao salvar configurações.</div>';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - CUMULUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Home.css">
</head>
<body class="<?php echo $tema === 'dark' ? 'bg-dark text-white' : ''; ?>">
    <?php include 'partials/header.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="mb-4"><i class="fas fa-cog"></i> Configurações</h1>
                
                <?php echo $message; ?>
                
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-sliders-h"></i> Preferências Pessoais</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <!-- Unidade de Temperatura -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-thermometer-half"></i> Unidade de Temperatura
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="unidade_temp" id="temp_c" value="C" <?php echo $unidade_temp === 'C' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="temp_c">
                                        Celsius (°C)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="unidade_temp" id="temp_f" value="F" <?php echo $unidade_temp === 'F' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="temp_f">
                                        Fahrenheit (°F)
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    A mudança de temperatura refletirá imediatamente nas telas de clima.
                                </small>
                            </div>
                            
                            <hr>
                            
                            <!-- Idioma -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-globe"></i> Idioma
                                </label>
                                <select name="idioma" class="form-select">
                                    <option value="pt" <?php echo $idioma === 'pt' ? 'selected' : ''; ?>>Português</option>
                                    <option value="en" <?php echo $idioma === 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="es" <?php echo $idioma === 'es' ? 'selected' : ''; ?>>Español</option>
                                </select>
                                <small class="text-muted d-block mt-2">
                                    O idioma traduzirá os textos estáticos principais.
                                </small>
                            </div>
                            
                            <hr>
                            
                            <!-- Tema -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-palette"></i> Tema
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tema" id="tema_light" value="light" <?php echo $tema === 'light' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="tema_light">
                                        Claro (Light)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tema" id="tema_dark" value="dark" <?php echo $tema === 'dark' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="tema_dark">
                                        Escuro (Dark)
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    O tema alterará as cores em até 200ms.
                                </small>
                            </div>
                            
                            <hr>
                            
                            <!-- Botão de salvar -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Informações adicionais -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informações da Conta</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Usuário:</strong> <?php echo htmlspecialchars(get_user_name()); ?></p>
                        <p><strong>Tipo de Conta:</strong> 
                            <?php 
                            $role = get_user_role();
                            echo $role === 'admin' ? '<span class="badge bg-danger">Administrador</span>' : '<span class="badge bg-success">Usuário</span>';
                            ?>
                        </p>
                        <p><strong>Unidade de Temperatura:</strong> <?php echo $unidade_temp === 'C' ? 'Celsius (°C)' : 'Fahrenheit (°F)'; ?></p>
                        <p><strong>Idioma:</strong> 
                            <?php 
                            $idiomas = ['pt' => 'Português', 'en' => 'English', 'es' => 'Español'];
                            echo $idiomas[$idioma] ?? 'Português';
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
