<?php
/**
 * api/get-fotos.php
 * Endpoint para retornar fotos de um álbum específico em formato JSON.
 */

// Define cabeçalho de resposta JSON
header('Content-Type: application/json; charset=utf-8');

// Lista de caminhos possíveis para o arquivo de conexão
$possible_paths = [
    __DIR__ . '/../../estrutura/conexaodb.php',   // Padrão: api/ → projeto/estrutura/
    __DIR__ . '/../estrutura/conexaodb.php',
    __DIR__ . '/../../../estrutura/conexaodb.php'
];

$connection_loaded = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $connection_loaded = true;
        break;
    }
}

if (!$connection_loaded) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno: arquivo de conexão não encontrado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validação do parâmetro banco_id
if (!isset($_GET['banco_id']) || !is_numeric($_GET['banco_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetro "banco_id" inválido ou ausente.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$banco_id = (int) $_GET['banco_id'];

if ($banco_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do álbum deve ser um número positivo.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, titulo, caminho_imagem, descricao, data_publicacao
        FROM fotos
        WHERE banco_id = ?
        ORDER BY data_publicacao DESC
    ");
    $stmt->execute([$banco_id]);
    $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Opcional: ajustar caminho da imagem se necessário
    // Exemplo: $f['caminho_imagem'] = 'https://seusite.com/' . ltrim($f['caminho_imagem'], '/');

    echo json_encode(array_values($fotos), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro ao buscar fotos.',
        'details' => defined('DEBUG') ? $e->getMessage() : null
    ], JSON_UNESCAPED_UNICODE);
}