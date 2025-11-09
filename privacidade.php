<?php
/**
 * privacidade.php
 * Página de política de privacidade
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
    <title>Privacidade - CUMULUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Home.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4"><i class="fas fa-shield-alt"></i> Política de Privacidade</h1>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5>1. Introdução</h5>
                        <p>
                            A CUMULUS ("nós", "nos", "nosso") opera o aplicativo web CUMULUS. Esta página informa você 
                            sobre nossas políticas de coleta, uso e divulgação de dados pessoais quando você usa nosso serviço.
                        </p>
                        
                        <h5 class="mt-4">2. Coleta de Dados</h5>
                        <p>
                            Coletamos vários tipos de informações para fins diversos:
                        </p>
                        <ul>
                            <li><strong>Dados de Conta:</strong> Nome, email e senha (criptografada)</li>
                            <li><strong>Dados de Localização:</strong> Coordenadas geográficas que você fornece</li>
                            <li><strong>Dados de Uso:</strong> Histórico de pesquisas e alertas configurados</li>
                            <li><strong>Dados Técnicos:</strong> Endereço IP, tipo de navegador, páginas visitadas</li>
                        </ul>
                        
                        <h5 class="mt-4">3. Uso dos Dados</h5>
                        <p>
                            Os dados coletados são usados para:
                        </p>
                        <ul>
                            <li>Fornecer e manter o serviço</li>
                            <li>Notificá-lo sobre mudanças no serviço</li>
                            <li>Permitir que você participe de recursos interativos</li>
                            <li>Melhorar e otimizar o serviço</li>
                            <li>Monitorar o uso do serviço</li>
                        </ul>
                        
                        <h5 class="mt-4">4. Segurança dos Dados</h5>
                        <p>
                            A segurança de seus dados é importante para nós. Usamos criptografia SSL/TLS para proteger 
                            dados em trânsito e senhas são armazenadas com hash seguro. No entanto, nenhum método de 
                            transmissão pela Internet ou armazenamento eletrônico é 100% seguro.
                        </p>
                        
                        <h5 class="mt-4">5. Compartilhamento de Dados</h5>
                        <p>
                            Não compartilhamos seus dados pessoais com terceiros, exceto:
                        </p>
                        <ul>
                            <li>Com provedores de serviço que nos auxiliam na operação do site</li>
                            <li>Quando obrigados por lei</li>
                            <li>Para proteger nossos direitos e segurança</li>
                        </ul>
                        
                        <h5 class="mt-4">6. Cookies</h5>
                        <p>
                            Usamos cookies para manter sua sessão de login ativa e melhorar sua experiência. 
                            Você pode controlar o uso de cookies através das configurações do seu navegador.
                        </p>
                        
                        <h5 class="mt-4">7. Seus Direitos</h5>
                        <p>
                            Você tem o direito de:
                        </p>
                        <ul>
                            <li>Acessar seus dados pessoais</li>
                            <li>Corrigir dados imprecisos</li>
                            <li>Solicitar a exclusão de seus dados</li>
                            <li>Exportar seus dados</li>
                        </ul>
                        
                        <h5 class="mt-4">8. Contato</h5>
                        <p>
                            Se você tiver dúvidas sobre esta Política de Privacidade, entre em contato conosco em:
                            <br><a href="mailto:privacidade@cumulus.app">privacidade@cumulus.app</a>
                        </p>
                        
                        <p class="text-muted mt-4">
                            <small>Última atualização: <?php echo date('d/m/Y'); ?></small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
