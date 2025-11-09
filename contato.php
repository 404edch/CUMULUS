<?php
/**
 * contato.php
 * Página de contato
 */

require 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $assunto = $_POST['assunto'] ?? '';
    $mensagem = $_POST['mensagem'] ?? '';
    
    if ($nome && $email && $assunto && $mensagem) {
        // Aqui você poderia enviar um email
        // Por enquanto, apenas salvamos uma mensagem de sucesso
        $message = '<div class="alert alert-success"><i class="fas fa-check"></i> Mensagem enviada com sucesso! Entraremos em contato em breve.</div>';
    } else {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation"></i> Por favor, preencha todos os campos.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - CUMULUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Home.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4 text-center"><i class="fas fa-envelope"></i> Entre em Contato</h1>
                
                <?php echo $message; ?>
                
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Tem dúvidas ou sugestões? Preencha o formulário abaixo e entraremos em contato em breve.
                        </p>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="assunto" class="form-label">Assunto</label>
                                <select class="form-select" id="assunto" name="assunto" required>
                                    <option value="">Selecione um assunto...</option>
                                    <option value="sugestao">Sugestão</option>
                                    <option value="problema">Problema</option>
                                    <option value="duvida">Dúvida</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mensagem" class="form-label">Mensagem</label>
                                <textarea class="form-control" id="mensagem" name="mensagem" rows="5" required></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane"></i> Enviar Mensagem
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Informações de Contato -->
                <div class="row mt-5">
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-envelope text-primary"></i></h5>
                                <h6>Email</h6>
                                <p class="text-muted"><a href="mailto:contato@cumulus.app">contato@cumulus.app</a></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-phone text-success"></i></h5>
                                <h6>Telefone</h6>
                                <p class="text-muted">(11) 9999-9999</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-map-marker-alt text-danger"></i></h5>
                                <h6>Localização</h6>
                                <p class="text-muted">São Paulo, Brasil</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
