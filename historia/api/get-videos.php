<?php
/**
 * api/get-videos.php
 * Endpoint para retornar vídeos do banco de dados, com extração automática do ID do YouTube,
 * thumbnail e URL amigável.
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

/**
 * Registra mensagens de log em arquivo.
 */
function api_log(string $msg): void {
    $logfile = __DIR__ . '/error_log.txt';
    $entry = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);
}

// === Carregamento da conexão com o banco ===
$possible_paths = [
    __DIR__ . '/../../estrutura/conexaodb.php',
    __DIR__ . '/../estrutura/conexaodb.php',
    __DIR__ . '/../../../estrutura/conexaodb.php'
];

$connection_loaded = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        try {
            require_once $path;
            $connection_loaded = true;
            break;
        } catch (Throwable $e) {
            api_log("Erro ao incluir $path: " . $e->getMessage());
        }
    } else {
        api_log("Caminho não existe: $path");
    }
}

if (!$connection_loaded) {
    http_response_code(500);
    api_log("Arquivo conexaodb.php não encontrado.");
    echo json_encode(['error' => 'Erro interno: conexão com banco indisponível.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    api_log("\$pdo não está definido ou não é uma instância válida de PDO.");
    echo json_encode(['error' => 'Erro interno: conexão com banco inválida.'], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Extrai o ID do YouTube de URLs ou strings variadas.
 */
function extractYouTubeID(string $input): string {
    $value = trim($input);
    if ($value === '') {
        return '';
    }

    // Já é um ID válido?
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $value)) {
        return $value;
    }

    $patterns = [
        '/youtu\.be\/([a-zA-Z0-9_-]{11})/i',
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/i',
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/i',
        '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/i',
        '/[?&]v=([a-zA-Z0-9_-]{11})/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $value, $matches)) {
            return $matches[1];
        }
    }

    // Fallback: último segmento que pareça um ID
    $parts = preg_split('/[\/\?&=#]+/', $value);
    foreach (array_reverse($parts) as $part) {
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $part)) {
            return $part;
        }
    }

    return '';
}

/**
 * Detecta campo mais provável de conter URL ou ID do vídeo.
 */
function detect_video_candidate(array $row): string {
    // Prioriza colunas com nomes relacionados a vídeo
    $priority_keys = [];
    foreach ($row as $key => $value) {
        $lowerKey = strtolower($key);
        if (
            strpos($lowerKey, 'youtube') !== false ||
            strpos($lowerKey, 'video') !== false ||
            strpos($lowerKey, 'url') !== false ||
            strpos($lowerKey, 'link') !== false ||
            strpos($lowerKey, 'caminho') !== false ||
            strpos($lowerKey, 'path') !== false ||
            strpos($lowerKey, 'src') !== false
        ) {
            $priority_keys[] = $key;
        }
    }

    foreach ($priority_keys as $key) {
        $val = trim((string) ($row[$key] ?? ''));
        if ($val !== '') {
            return $val;
        }
    }

    // Fallback: qualquer valor não vazio
    foreach ($row as $value) {
        $val = trim((string) $value);
        if ($val !== '') {
            return $val;
        }
    }

    return '';
}

// === Leitura de parâmetros ===
$id = $_GET['id'] ?? null;
$titulo = isset($_GET['titulo']) ? trim($_GET['titulo']) : null;

if ($id !== null && !is_numeric($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetro "id" inválido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($id !== null) {
        $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ? LIMIT 1");
        $stmt->execute([(int) $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($titulo !== '') {
        $stmt = $pdo->prepare("SELECT * FROM videos WHERE titulo = ? ORDER BY id DESC");
        $stmt->execute([$titulo]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->query("SELECT * FROM videos ORDER BY id DESC LIMIT 30");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $result = [];
    foreach ($rows as $row) {
        $candidate = detect_video_candidate($row);
        $youtube_id = extractYouTubeID($candidate);

        if ($youtube_id !== '') {
            //  Correção crítica: removido espaço extra nas URLs
            $thumbnail = 'https://img.youtube.com/vi/' . $youtube_id . '/mqdefault.jpg';
            $video_url = 'https://www.youtube.com/watch?v=' . $youtube_id;
        } else {
            // Fallback para URLs não-YouTube
            $video_url = filter_var($candidate, FILTER_VALIDATE_URL) ? $candidate : '';
            $thumbnail = '/historia/assets/video-placeholder.png';
        }

        $row['youtube_id'] = $youtube_id;
        $row['thumbnail'] = $thumbnail;
        $row['video_url'] = $video_url;

        $result[] = $row;
    }

    echo json_encode(array_values($result), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    api_log("Erro ao buscar vídeos: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno ao processar requisição.'], JSON_UNESCAPED_UNICODE);
}