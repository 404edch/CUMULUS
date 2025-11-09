<?php
/**
 * alertas.php
 * Gerenciamento de alertas personalizados (BUC-03, BUC-08)
 */

require 'config.php';
require 'check_login.php';
require_login();

$usuario_id = get_user_id();
$message = '';

// Processar formulário de novo alerta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $tipo = $_POST['tipo'] ?? '';
        $mensagem = $_POST['mensagem'] ?? '';
        $localidade = $_POST['localidade'] ?? '';
        $regra = json_encode([
            'tipo' => $_POST['regra_tipo'] ?? '',
            'valor' => $_POST['regra_valor'] ?? '',
            'operador' => $_POST['regra_operador'] ?? ''
        ]);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        $stmt = $mysqli->prepare(
            "INSERT INTO alertas (usuario_id, tipo, mensagem, localidade, regra, ativo) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('issssi', $usuario_id, $tipo, $mensagem, $localidade, $regra, $ativo);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Alerta criado com sucesso!</div>';
        } else {
            $message = '<div class="alert alert-danger">Erro ao criar alerta.</div>';
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'delete') {
        $alerta_id = (int)$_POST['alerta_id'];
        $stmt = $mysqli->prepare("DELETE FROM alertas WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param('ii', $alerta_id, $usuario_id);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Alerta removido!</div>';
        }
        $stmt->close();
    }
}

// Buscar alertas do usuário
$stmt = $mysqli->prepare("SELECT * FROM alertas WHERE usuario_id = ? ORDER BY criado_em DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$alertas = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas - CUMULUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Home.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4"><i class="fas fa-bell"></i> Meus Alertas</h1>
                
                <?php echo $message; ?>
                
                <!-- Formulário de novo alerta -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus"></i> Novo Alerta</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de Alerta</label>
                                    <select name="tipo" class="form-control" required>
                                        <option value="">Selecione...</option>
                                        <option value="temperatura">Temperatura</option>
                                        <option value="chuva">Chuva</option>
                                        <option value="vento">Vento</option>
                                        <option value="umidade">Umidade</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Localidade</label>
                                    <input type="text" name="localidade" class="form-control" placeholder="Ex: São Paulo">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Condição</label>
                                    <select name="regra_operador" class="form-control" required>
                                        <option value=">=">Maior ou igual (>=)</option>
                                        <option value="<=">Menor ou igual (<=)</option>
                                        <option value=">">Maior que (>)</option>
                                        <option value="<">Menor que (<)</option>
                                        <option value="==">Igual (==)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Valor</label>
                                    <input type="number" name="regra_valor" class="form-control" step="0.1" placeholder="Ex: 30">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Unidade</label>
                                    <select name="regra_tipo" class="form-control" required>
                                        <option value="°C">°C (Temperatura)</option>
                                        <option value="mm">mm (Chuva)</option>
                                        <option value="km/h">km/h (Vento)</option>
                                        <option value="%">% (Umidade)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mensagem do Alerta</label>
                                <textarea name="mensagem" class="form-control" rows="2" placeholder="Ex: Temperatura muito alta!"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ativo" id="ativo" checked>
                                    <label class="form-check-label" for="ativo">
                                        Ativar alerta
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Criar Alerta
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de alertas -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Alertas Ativos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($alertas)): ?>
                            <p class="text-muted">Nenhum alerta configurado.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Localidade</th>
                                            <th>Regra</th>
                                            <th>Mensagem</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($alertas as $alerta): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-warning">
                                                        <?php echo ucfirst(htmlspecialchars($alerta['tipo'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($alerta['localidade'] ?? '-'); ?>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php 
                                                        $regra = json_decode($alerta['regra'], true);
                                                        echo htmlspecialchars($regra['operador'] ?? '') . ' ' . htmlspecialchars($regra['valor'] ?? '') . ' ' . htmlspecialchars($regra['tipo'] ?? '');
                                                        ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars(substr($alerta['mensagem'] ?? '', 0, 30)); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($alerta['ativo']): ?>
                                                        <span class="badge bg-success">Ativo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inativo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="POST" action="" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="alerta_id" value="<?php echo $alerta['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
