<?php
require_once '../estrutura/conexaodb.php';

/* =========================================
   VERIFICAÇÃO DE CONEXÃO
========================================= */

if (!isset($pdo)) {
    die('Erro: Conexão com o banco de dados não estabelecida.');
}

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

function eDetalhesVideos($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function nl2brSeguroDetalhesVideos($valor)
{
    return nl2br(eDetalhesVideos($valor));
}

/**
 * Extrai o ID do YouTube a partir de:
 * - iframe completo
 * - youtube.com/watch?v=ID
 * - youtube.com/embed/ID
 * - youtube.com/shorts/ID
 * - youtu.be/ID
 * - ID puro
 */
function extract_youtube_id(string $input): string
{
    if (empty(trim($input))) {
        return '';
    }

    $url = trim($input);

    // Se for iframe, extrai o src
    if (stripos($url, '<iframe') !== false) {
        if (preg_match('/src=["\']([^"\']+)["\']/i', $url, $matches)) {
            $url = $matches[1];
        } else {
            return '';
        }
    }

    // Se já for ID puro
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
        return $url;
    }

    $parsed = parse_url($url);

    if (!$parsed || empty($parsed['host'])) {
        return '';
    }

    $host = strtolower($parsed['host']);
    $path = $parsed['path'] ?? '';
    $query = $parsed['query'] ?? '';

    // youtube.com/watch?v=ID
    if (strpos($host, 'youtube.com') !== false && !empty($query)) {
        parse_str($query, $q);

        if (!empty($q['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $q['v'])) {
            return $q['v'];
        }
    }

    // youtube.com/embed/ID, /shorts/ID ou /v/ID
    if (strpos($host, 'youtube.com') !== false) {
        if (preg_match('#/embed/([a-zA-Z0-9_-]{11})#i', $path, $m)) {
            return $m[1];
        }

        if (preg_match('#/shorts/([a-zA-Z0-9_-]{11})#i', $path, $m)) {
            return $m[1];
        }

        if (preg_match('#/v/([a-zA-Z0-9_-]{11})#i', $path, $m)) {
            return $m[1];
        }
    }

    // youtu.be/ID
    if (strpos($host, 'youtu.be') !== false) {
        $id = trim($path, '/');

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $id)) {
            return $id;
        }
    }

    return '';
}

function formatarDataDetalhesVideos($data)
{
    if (empty($data)) {
        return '';
    }

    $timestamp = strtotime((string)$data);

    if (!$timestamp) {
        return '';
    }

    return date('d/m/Y', $timestamp);
}

/* =========================================
   CAPTURA E VALIDAÇÃO DO ID DO VÍDEO
========================================= */

$video_id = filter_input(INPUT_GET, 'video_id', FILTER_VALIDATE_INT) ?: 0;

$video = null;
$youtube_id = '';

if ($video_id > 0) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM videos
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$video_id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($video && !empty($video['url'])) {
        $youtube_id = extract_youtube_id($video['url']);
    }
}

$tituloPagina = $video
    ? ($video['titulo'] ?? 'Vídeo histórico') . ' - Galeria de Vídeos'
    : 'Vídeo não encontrado - Galeria de Vídeos';

$videoValido = $video && !empty($youtube_id);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= eDetalhesVideos($tituloPagina) ?></title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-historia/detalhes-galeria-videos.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-detalhes-galeria-videos">
        <div class="detalhes-video-container">

            <a href="galeria-videos.php" class="voltar-link">
                ← Voltar à Galeria de Vídeos
            </a>

            <?php if ($video): ?>

                <section class="hero-detalhes-video">
                    <span class="eyebrow">Vídeo histórico</span>

                    <h1><?= eDetalhesVideos($video['titulo'] ?? 'Vídeo histórico do futebol brasileiro') ?></h1>

                    <?php if (!empty($video['descricao'])): ?>
                        <p><?= nl2brSeguroDetalhesVideos($video['descricao']) ?></p>
                    <?php else: ?>
                        <p>
                            Registro audiovisual relacionado à história do futebol brasileiro.
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($video['data_publicacao'])): ?>
                        <div class="video-meta">
                            <span>Publicado em <?= eDetalhesVideos(formatarDataDetalhesVideos($video['data_publicacao'])) ?></span>
                        </div>
                    <?php endif; ?>
                </section>

                <?php if ($videoValido): ?>

                    <section class="card-video-detalhe">
                        <div class="video-container">
                            <iframe
                                src="https://www.youtube.com/embed/<?= eDetalhesVideos($youtube_id) ?>?rel=0"
                                title="<?= eDetalhesVideos($video['titulo'] ?? 'Vídeo histórico do futebol brasileiro') ?>"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </section>

                <?php else: ?>

                    <section class="card-mensagem-vazia">
                        <p class="mensagem-vazia">
                            O vídeo foi encontrado, mas a URL cadastrada não contém um ID válido do YouTube.
                        </p>
                    </section>

                <?php endif; ?>

            <?php else: ?>

                <section class="hero-detalhes-video">
                    <span class="eyebrow">Galeria</span>

                    <h1>Vídeo não encontrado</h1>

                    <p>
                        O vídeo solicitado não existe, foi removido ou o endereço acessado está incorreto.
                    </p>
                </section>

                <section class="card-mensagem-vazia">
                    <p class="mensagem-vazia">
                        Volte para a galeria principal e escolha um vídeo disponível.
                    </p>
                </section>

            <?php endif; ?>

            <div class="acoes-video">
                <a href="galeria-videos.php" class="botao">
                    Voltar à Galeria de Vídeos
                </a>
            </div>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>