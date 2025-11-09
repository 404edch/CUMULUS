<?php
/**
 * chatbot.php
 * Assistente climático com Ollama (BUC-07)
 */

require 'config.php';
require 'check_login.php';
require_login();

$usuario_id = get_user_id();
$usuario_nome = get_user_name();

// Variáveis para o chatbot
$ollama_url = 'http://localhost:11434/api/generate';
$model = 'llama2'; // Pode ser alterado para outro modelo disponível
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistente Climático - CUMULUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Home.css">
    
    <style>
        #chatContainer {
            height: 500px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #dee2e6;
        }
        
        .message {
            margin-bottom: 15px;
            animation: fadeIn 0.3s ease-in;
        }
        
        .message.user {
            text-align: right;
        }
        
        .message.bot {
            text-align: left;
        }
        
        .message-content {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 8px;
            max-width: 70%;
            word-wrap: break-word;
        }
        
        .message.user .message-content {
            background-color: #007bff;
            color: white;
        }
        
        .message.bot .message-content {
            background-color: #e9ecef;
            color: #333;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .typing-indicator span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #999;
            animation: typing 1.4s infinite;
        }
        
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% {
                opacity: 0.5;
            }
            30% {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4"><i class="fas fa-comments"></i> Assistente Climático</h1>
                
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-robot"></i> Chat com Assistente IA
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <!-- Chat Container -->
                        <div id="chatContainer">
                            <div class="message bot">
                                <div class="message-content">
                                    <strong>Assistente:</strong> Olá! Sou seu assistente climático. Posso responder perguntas sobre clima, previsões, e muito mais. Como posso ajudá-lo?
                                </div>
                            </div>
                        </div>
                        
                        <!-- Input Area -->
                        <div class="mt-3">
                            <div class="input-group">
                                <input type="text" id="userInput" class="form-control" placeholder="Digite sua pergunta sobre o clima..." autocomplete="off">
                                <button class="btn btn-primary" id="sendBtn" type="button">
                                    <i class="fas fa-paper-plane"></i> Enviar
                                </button>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle"></i> O assistente responderá em até 5 segundos com base em dados climáticos.
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Exemplos de perguntas -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Exemplos de Perguntas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="sendMessage('Qual é a melhor hora para correr amanhã em São Paulo?')">
                                    Melhor hora para correr
                                </button>
                                <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="sendMessage('Como o clima de hoje se compara ao mês passado?')">
                                    Comparação climática
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="sendMessage('Qual é a previsão de chuva para os próximos 7 dias?')">
                                    Previsão de chuva
                                </button>
                                <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="sendMessage('Quais são os riscos climáticos atuais?')">
                                    Riscos climáticos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
    
    <script>
        const chatContainer = document.getElementById('chatContainer');
        const userInput = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');
        const ollamaUrl = '<?php echo $ollama_url; ?>';
        const model = '<?php echo $model; ?>';
        
        // Enviar mensagem ao pressionar Enter
        userInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        
        // Enviar ao clicar no botão
        sendBtn.addEventListener('click', sendMessage);
        
        function sendMessage(messageText = null) {
            const message = messageText || userInput.value.trim();
            
            if (!message) return;
            
            // Adicionar mensagem do usuário
            addMessage('user', message);
            userInput.value = '';
            sendBtn.disabled = true;
            userInput.disabled = true;
            
            // Mostrar indicador de digitação
            showTypingIndicator();
            
            // Enviar para o servidor
            fetch('api_chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    model: model
                })
            })
            .then(response => response.json())
            .then(data => {
                removeTypingIndicator();
                
                if (data.ok) {
                    addMessage('bot', data.response);
                } else {
                    addMessage('bot', 'Desculpe, houve um erro ao processar sua pergunta. Tente novamente.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                removeTypingIndicator();
                addMessage('bot', 'Erro ao conectar com o assistente. Verifique se o Ollama está rodando.');
            })
            .finally(() => {
                sendBtn.disabled = false;
                userInput.disabled = false;
                userInput.focus();
            });
        }
        
        function addMessage(sender, text) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = (sender === 'bot' ? '<strong>Assistente:</strong> ' : '<strong>Você:</strong> ') + escapeHtml(text);
            
            messageDiv.appendChild(contentDiv);
            chatContainer.appendChild(messageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        
        function showTypingIndicator() {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message bot';
            messageDiv.id = 'typingIndicator';
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
            
            messageDiv.appendChild(contentDiv);
            chatContainer.appendChild(messageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        
        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) {
                indicator.remove();
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
