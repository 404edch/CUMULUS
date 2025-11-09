<?php
/**
 * sobre.php
 * Página de informações sobre o projeto
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre - CUMULUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Home.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4 text-center"><i class="fas fa-cloud"></i> CUMULUS</h1>
                <p class="text-center text-muted h5 mb-5">Seu guia para o clima</p>
                
                <!-- Sobre o Projeto -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Sobre o Projeto</h5>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>CUMULUS</strong> é um aplicativo web inovador de previsão do tempo que oferece 
                            informações climáticas precisas e em tempo real, com alertas personalizados e um assistente 
                            inteligente baseado em IA.
                        </p>
                        <p>
                            Com CUMULUS, você nunca será pego de surpresa pelo clima. Tenha previsões confiáveis, 
                            alertas instantâneos sobre mudanças bruscas no clima e um mapa interativo que coloca o 
                            controle do tempo nas suas mãos.
                        </p>
                    </div>
                </div>
                
                <!-- Recursos Principais -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-star"></i> Recursos Principais</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <i class="fas fa-map text-primary"></i> <strong>Mapa Interativo</strong> - Visualize condições climáticas em tempo real
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-search text-info"></i> <strong>Busca Avançada</strong> - Pesquise qualquer localização em até 3 segundos
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-bell text-warning"></i> <strong>Alertas Personalizados</strong> - Configure regras para receber notificações
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-calendar text-danger"></i> <strong>Previsão de 7 Dias</strong> - Planeje com confiança
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-heart text-danger"></i> <strong>Favoritos</strong> - Salve seus locais preferidos
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-robot text-secondary"></i> <strong>Assistente IA</strong> - Faça perguntas sobre o clima
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Equipe de Desenvolvimento -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Equipe de Desenvolvimento</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-user-circle"></i> Eduardo Henrique Chechin Teixeira</li>
                            <li><i class="fas fa-user-circle"></i> Carlos Eduardo Aguiar Sacerdote</li>
                            <li><i class="fas fa-user-circle"></i> Gabriel Mendes da Silva Cardin</li>
                            <li><i class="fas fa-user-circle"></i> Gabriel Ruffato Prestes</li>
                            <li><i class="fas fa-user-circle"></i> Gustavo Fagundes de Amorim</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Tecnologias -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-code"></i> Tecnologias Utilizadas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Backend</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check"></i> PHP 8.1+</li>
                                    <li><i class="fas fa-check"></i> MySQL/MariaDB</li>
                                    <li><i class="fas fa-check"></i> OpenWeatherMap API</li>
                                    <li><i class="fas fa-check"></i> Ollama (IA)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Frontend</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check"></i> HTML5</li>
                                    <li><i class="fas fa-check"></i> CSS3 / Bootstrap 5</li>
                                    <li><i class="fas fa-check"></i> JavaScript</li>
                                    <li><i class="fas fa-check"></i> Leaflet.js (Mapas)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contato -->
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-envelope"></i> Contato</h5>
                    </div>
                    <div class="card-body">
                        <p>
                            Para dúvidas, sugestões ou reportar problemas, entre em contato através de:
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-envelope"></i> <a href="mailto:contato@cumulus.app">contato@cumulus.app</a></li>
                            <li><i class="fas fa-globe"></i> <a href="https://cumulus.app" target="_blank">www.cumulus.app</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
