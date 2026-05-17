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

function eGaleriaVideos($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
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

function thumbnailYoutubeGaleria($url)
{
    $videoId = extract_youtube_id((string)$url);

    if (!empty($videoId)) {
        return 'https://img.youtube.com/vi/' . eGaleriaVideos($videoId) . '/mqdefault.jpg';
    }

    return 'https://via.placeholder.com/320x180/000000/FFFFFF?text=Video+Indisponivel';
}

/* =========================================
   BUSCAR VÍDEOS
========================================= */

$stmt = $pdo->query("
    SELECT *
    FROM videos
    ORDER BY data_publicacao DESC, id DESC
");

$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Galeria de Vídeos - Futebol Brasileiro</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-historia/galeria-videos.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-galeria-videos">
        <div class="galeria-container">

            <a href="historia.php" class="voltar-link">
                ← Voltar para História
            </a>

            <section class="hero-galeria-videos">
                <span class="eyebrow">Galeria</span>

                <h1>Galeria de Vídeos</h1>

                <p>
                    Confira momentos históricos, entrevistas marcantes, jogos inesquecíveis,
                    registros audiovisuais e memórias importantes da trajetória do futebol brasileiro.
                </p>
            </section>

            <?php if (!empty($videos)): ?>
                <section class="galeria-lista">
                    <?php foreach ($videos as $video): ?>
                        <?php
                            $videoId = (int)($video['id'] ?? 0);
                            $tituloVideo = $video['titulo'] ?? 'Vídeo histórico do futebol brasileiro';
                            $descricaoVideo = $video['descricao'] ?? '';
                            $thumbnailUrl = thumbnailYoutubeGaleria($video['url'] ?? '');
                        ?>

                        <article class="galeria-card">
                            <a 
                                href="detalhes-galeria-videos.php?video_id=<?= $videoId ?>" 
                                class="galeria-card-link"
                            >
                                <div class="video-preview-wrapper">
                                    <img
                                        src="<?= $thumbnailUrl ?>"
                                        alt="<?= eGaleriaVideos($tituloVideo) ?>"
                                        class="video-preview"
                                        loading="lazy"
                                    >

                                    <span class="play-indicador" aria-hidden="true">
                                        ▶
                                    </span>
                                </div>

                                <div class="galeria-card-conteudo">
                                    <h2><?= eGaleriaVideos($tituloVideo) ?></h2>

                                    <?php if (!empty($descricaoVideo)): ?>
                                        <p><?= eGaleriaVideos($descricaoVideo) ?></p>
                                    <?php else: ?>
                                        <p>Registro audiovisual da história do futebol brasileiro.</p>
                                    <?php endif; ?>

                                    <span class="botao">
                                        Ver Vídeo
                                    </span>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <section class="card-mensagem-vazia">
                    <p class="mensagem-vazia">
                        Nenhum vídeo foi encontrado no momento.
                    </p>
                </section>
            <?php endif; ?>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

<div id="voltar-ao-topo">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e1e1e"
        stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 19V5M5 12l7-7 7 7" />
    </svg>

    <span class="tooltip-text">Voltar ao Topo</span>
</div>

<script src="js-historia/historia.js"></script>

</body>
</html>