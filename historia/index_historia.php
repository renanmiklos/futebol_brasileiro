<?php
/* =========================================
   INDEX_HISTORIA.PHP
   Bloco História da Página Principal
========================================= */

if (!function_exists('eIndexHistoria')) {
    function eIndexHistoria($valor)
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('extractYoutubeIdIndexHistoria')) {
    function extractYoutubeIdIndexHistoria(string $input): string
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

        // Se já for ID puro do YouTube
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

        // youtube.com/embed/ID
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
}

/* =========================================
   GARANTIA DE VARIÁVEIS
========================================= */

$fotos = $fotos ?? [];
$video = null;
$embed_url = '';

if (isset($pdo)) {
    $stmt_video = $pdo->prepare("
        SELECT *
        FROM videos
        ORDER BY data_publicacao DESC, id DESC
        LIMIT 1
    ");

    $stmt_video->execute();
    $video = $stmt_video->fetch(PDO::FETCH_ASSOC);

    if (!empty($video['url'])) {
        $video_id = extractYoutubeIdIndexHistoria($video['url']);

        if (!empty($video_id)) {
            $embed_url = 'https://www.youtube.com/embed/' . $video_id;
        }
    }
}
?>

<section class="index-historia">
    <div class="index-historia-header">
        <span class="eyebrow">História</span>

        <h1>História</h1>

        <p>
            Conheça momentos marcantes, ídolos, clubes, imagens históricas e vídeos que fizeram parte
            da trajetória do futebol brasileiro.
        </p>
    </div>

    <div class="index-historia-grid">

        <!-- Galeria de Fotos -->
        <article class="index-historia-card galeria-fotos">
            <div class="titulo-bloco-index-historia">
                <h3>Galeria de Fotos</h3>
                <span>Memória visual</span>
            </div>

            <?php if (!empty($fotos)): ?>
                <div class="carrossel2-fotos">
                    <?php foreach ($fotos as $index => $foto): ?>
                        <?php
                            $caminhoImagem = $foto['caminho_imagem'] ?? '';
                            $tituloFoto = $foto['titulo'] ?? 'Imagem histórica do futebol brasileiro';
                        ?>

                        <div class="carrossel2-item<?= $index === 0 ? ' active' : '' ?>">
                            <img
                                src="<?= eIndexHistoria($caminhoImagem) ?>"
                                alt="<?= eIndexHistoria($tituloFoto) ?>"
                                onerror="this.onerror=null; this.src='assets/images/escudo_padrao.png';"
                            >

                            <div class="carrossel2-caption">
                                <h5><?= eIndexHistoria($tituloFoto) ?></h5>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="mensagem-vazia">
                    Nenhuma foto histórica cadastrada no momento.
                </p>
            <?php endif; ?>

            <div class="index-historia-acoes">
                <a href="historia/galeria-fotos.php" class="botao">
                    Ver todas as fotos
                </a>
            </div>
        </article>

        <!-- Galeria de Vídeos -->
        <article class="index-historia-card galeria-video">
            <div class="titulo-bloco-index-historia">
                <h3>Último Vídeo</h3>
                <span>Arquivo audiovisual</span>
            </div>

            <?php if (!empty($video)): ?>

                <?php if (!empty($embed_url)): ?>
                    <div class="video-destaque-index">
                        <iframe
                            src="<?= eIndexHistoria($embed_url) ?>"
                            title="<?= eIndexHistoria($video['titulo'] ?? 'Vídeo histórico do futebol brasileiro') ?>"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>
                <?php else: ?>
                    <p class="mensagem-vazia">
                        O vídeo mais recente está cadastrado, mas a URL não pôde ser reconhecida.
                    </p>
                <?php endif; ?>

                <p class="descricao-video-index">
                    <?= eIndexHistoria($video['descricao'] ?? 'Clique abaixo para ver mais vídeos históricos do futebol brasileiro.') ?>
                </p>

            <?php else: ?>

                <p class="mensagem-vazia">
                    Nenhum vídeo histórico cadastrado no momento.
                </p>

            <?php endif; ?>

            <div class="index-historia-acoes">
                <a href="historia/galeria-videos.php" class="botao">
                    Ver todos os vídeos
                </a>
            </div>
        </article>

    </div>
</section>