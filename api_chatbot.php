<?php
/**
 * api_chatbot.php
 * API para integração com Ollama (principal) e fallback para OpenAI (teste)
 */

require 'config.php';
require 'check_login.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['message'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing message']);
        exit;
    }
    
    $message = trim($input['message']);
    $model = $input['model'] ?? 'llama2';
    
    if (empty($message)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Empty message']);
        exit;
    }
    
    // Preparar prompt com contexto climático
    $system_prompt = "Você é um assistente especializado em clima e meteorologia. Responda perguntas sobre clima, previsões do tempo, padrões climáticos e segurança climática. Seja conciso e útil. Use dados reais quando possível.";
    
    // --- INÍCIO: INTEGRAÇÃO OLLAMA (Padrão) ---
    // A URL abaixo deve ser ajustada para o seu servidor Ollama local
    $ollama_url = 'http://localhost:11434/api/generate';
    
    $payload = json_encode([
        'model' => $model,
        'prompt' => $message,
        'system' => $system_prompt,
        'stream' => false,
        'temperature' => 0.7
    ]);
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nUser-Agent: CumulusApp/1.0\r\n",
            'content' => $payload,
            'timeout' => 5 // Reduzido para falhar mais rápido no sandbox
        ]
    ];
    
    $context = stream_context_create($options);
    $start_time = microtime(true);
    
    // Tenta Ollama
    $response = @file_get_contents($ollama_url, false, $context);
    $duration_ms = (int)((microtime(true) - $start_time) * 1000);
    
    if ($response !== false) {
        $result = json_decode($response, true);
        if (isset($result['response'])) {
            echo json_encode([
                'ok' => true,
                'response' => trim($result['response']),
                'duration_ms' => $duration_ms,
                'source' => 'ollama'
            ]);
            exit;
        }
    }
    // --- FIM: INTEGRAÇÃO OLLAMA (Padrão) ---

    // --- INÍCIO: FALLBACK PARA OPENAI (Substituto Funcional para Teste) ---
    // Usando a API OpenAI como fallback funcional no ambiente de sandbox
    
    // Configuração da API OpenAI (ou compatível)
    $openai_url = 'https://api.openai.com/v1/chat/completions';
    $openai_model = 'gpt-4.1-mini'; // Modelo disponível no ambiente
    $api_key = getenv('OPENAI_API_KEY'); // Chave de API do ambiente
    
    if ($api_key) {
        $openai_payload = json_encode([
            'model' => $openai_model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $message]
            ],
            'temperature' => 0.7
        ]);
        
        $openai_options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAuthorization: Bearer $api_key\r\nUser-Agent: CumulusApp/1.0\r\n",
                'content' => $openai_payload,
                'timeout' => 15
            ]
        ];
        
        $openai_context = stream_context_create($openai_options);
        $start_time = microtime(true);
        
        $openai_response = @file_get_contents($openai_url, false, $openai_context);
        $duration_ms = (int)((microtime(true) - $start_time) * 1000);
        
        if ($openai_response !== false) {
            $openai_result = json_decode($openai_response, true);
            
            if (isset($openai_result['choices'][0]['message']['content'])) {
                echo json_encode([
                    'ok' => true,
                    'response' => trim($openai_result['choices'][0]['message']['content']),
                    'duration_ms' => $duration_ms,
                    'source' => 'openai_fallback'
                ]);
                exit;
            }
        }
    }
    // --- FIM: FALLBACK PARA OPENAI (Substituto Funcional para Teste) ---

    // Ollama e OpenAI falharam ou chave não disponível - retornar resposta genérica
    $generic_response = get_generic_response($message);
    
    echo json_encode([
        'ok' => true,
        'response' => $generic_response,
        'duration_ms' => $duration_ms,
        'source' => 'fallback'
    ]);
    exit;
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Server error',
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}

/**
 * Retornar resposta genérica quando Ollama não está disponível
 */
function get_generic_response($message) {
    $message_lower = strtolower($message);
    
    $responses = [
        'chuva' => 'A chuva é um fenômeno climático importante. Para informações precisas sobre chuva em sua região, consulte o mapa interativo ou adicione alertas personalizados.',
        'temperatura' => 'A temperatura varia conforme a localização e hora do dia. Use o mapa interativo para ver a temperatura atual em qualquer lugar.',
        'previsão' => 'Você pode ver previsões detalhadas de 7 dias no mapa interativo. Clique em qualquer local para ver a previsão completa.',
        'clima' => 'Para informações sobre o clima, use o mapa interativo ou configure alertas personalizados para sua região.',
        'alerta' => 'Você pode criar alertas personalizados na seção "Alertas". Configure regras para temperatura, chuva, vento e umidade.',
        'favorito' => 'Adicione locais aos favoritos clicando no mapa e salvando a localização. Acesse seus favoritos na seção "Favoritos".',
        'mapa' => 'O mapa interativo mostra condições climáticas em tempo real. Clique em qualquer local para ver o clima detalhado.',
    ];
    
    foreach ($responses as $keyword => $response) {
        if (strpos($message_lower, $keyword) !== false) {
            return $response;
        }
    }
    
    return 'Desculpe, não consegui processar sua pergunta completamente. Tente perguntar sobre clima, previsões, alertas ou como usar o aplicativo. O assistente funcionará melhor quando o Ollama estiver disponível.';
}
?>
