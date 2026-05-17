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

function eHistoria($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function caminhoImagemHistoria($caminho, $fallback = '../assets/images/escudo_padrao.png')
{
    if (empty($caminho)) {
        return $fallback;
    }

    $caminho = trim((string)$caminho);

    if (
        str_starts_with($caminho, 'http://') ||
        str_starts_with($caminho, 'https://') ||
        str_starts_with($caminho, 'data:')
    ) {
        return eHistoria($caminho);
    }

    return '../' . eHistoria(ltrim($caminho, '/'));
}

/**
 * Extrai o ID do YouTube a partir de URL, iframe ou ID puro.
 */
function extract_youtube_id(string $input): string
{
    if (empty(trim($input))) {
        return '';
    }

    $url = trim($input);

    // Se for um iframe, extrai o src
    if (stripos($url, '<iframe') !== false) {
        if (preg_match('/src=["\']([^"\']+)["\']/i', $url, $matches)) {
            $url = $matches[1];
        } else {
            return '';
        }
    }

    // Já é um ID válido?
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

    // youtube.com com query v=ID
    if (strpos($host, 'youtube.com') !== false) {
        if ($query) {
            parse_str($query, $q);

            if (!empty($q['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $q['v'])) {
                return $q['v'];
            }
        }

        // youtube.com/embed/ID ou /v/ID
        if (preg_match('#/embed/([a-zA-Z0-9_-]{11})#i', $path, $m)) {
            return $m[1];
        }

        if (preg_match('#/v/([a-zA-Z0-9_-]{11})#i', $path, $m)) {
            return $m[1];
        }
    }

    // youtube.com/shorts/ID
    if (strpos($host, 'youtube.com') !== false) {
        if (preg_match('#/shorts/([a-zA-Z0-9_-]{11})#i', $path, $m)) {
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

/* =========================================
   GALERIA DE FOTOS
   Última foto de cada banco, limitando a 3 bancos
========================================= */

$stmt_fotos = $pdo->prepare("
    SELECT 
        f.*, 
        b.nome AS nome_banco
    FROM fotos f
    INNER JOIN (
        SELECT 
            banco_id, 
            MAX(id) AS ultimo_id
        FROM fotos
        GROUP BY banco_id
    ) ultimas ON f.id = ultimas.ultimo_id
    INNER JOIN bancos_de_fotos b ON b.id = f.banco_id
    ORDER BY f.data_publicacao DESC, f.id DESC
    LIMIT 3
");

$stmt_fotos->execute();
$galeriaFotos = $stmt_fotos->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   GALERIA DE VÍDEOS
========================================= */

$stmt_videos = $pdo->prepare("
    SELECT *
    FROM videos
    ORDER BY data_publicacao DESC, id DESC
    LIMIT 2
");

$stmt_videos->execute();
$galeriaVideos = $stmt_videos->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   CLUBES CAMPEÕES INTERNACIONAIS
   Ajuste os IDs abaixo conforme a regra do seu banco.
   Mantidos os IDs originais do seu código para preservar funcionamento.
========================================= */

$idsCompeticoesCampeoes = [5, 6];

$clubesCampeoes = [];
$temporadas = [];

if (!empty($idsCompeticoesCampeoes)) {
    $placeholdersCompeticoes = implode(',', array_fill(0, count($idsCompeticoesCampeoes), '?'));

    $stmt_temporadas = $pdo->prepare("
        SELECT 
            id, 
            ano
        FROM temporadas
        WHERE id_competicao IN ($placeholdersCompeticoes)
    ");

    $stmt_temporadas->execute($idsCompeticoesCampeoes);
    $temporadas = $stmt_temporadas->fetchAll(PDO::FETCH_KEY_PAIR);
}

if (!empty($temporadas)) {
    $idsTemporadas = array_keys($temporadas);
    $placeholdersTemporadas = implode(',', array_fill(0, count($idsTemporadas), '?'));

    $stmt_campeoes = $pdo->prepare("
        SELECT 
            c.id_time,
            t.nome,
            t.escudo,
            tempo.ano
        FROM classificacao c
        INNER JOIN times t ON t.id = c.id_time
        INNER JOIN temporadas tempo ON tempo.id = c.id_temporada
        WHERE c.id_temporada IN ($placeholdersTemporadas)
          AND c.fase IN ('Camp', '1º')
          AND t.estado IN (
              'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ',
              'RN','RS','RO','RR','SC','SP','SE','TO'
          )
        ORDER BY tempo.ano DESC
    ");

    $stmt_campeoes->execute($idsTemporadas);
    $campeoes = $stmt_campeoes->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($campeoes)) {
        $clubes = [];

        foreach ($campeoes as $row) {
            $id_time = (int)$row['id_time'];

            if (!isset($clubes[$id_time])) {
                $clubes[$id_time] = [
                    'id_time' => $id_time,
                    'nome' => $row['nome'],
                    'escudo' => $row['escudo'],
                    'titulos' => 0,
                    'ultimo_ano' => 0
                ];
            }

            $clubes[$id_time]['titulos']++;

            if ((int)$row['ano'] > (int)$clubes[$id_time]['ultimo_ano']) {
                $clubes[$id_time]['ultimo_ano'] = (int)$row['ano'];
            }
        }

        uasort($clubes, function ($a, $b) {
            if ((int)$b['titulos'] !== (int)$a['titulos']) {
                return (int)$b['titulos'] <=> (int)$a['titulos'];
            }

            return (int)$b['ultimo_ano'] <=> (int)$a['ultimo_ano'];
        });

        $clubesCampeoes = $clubes;
    }
}

/* =========================================
   TIMELINE FIXA
========================================= */

$timeline = [
    [
        'ano' => '1894',
        'titulo' => '⚽ Origens do Futebol',
        'texto' => 'Charles Miller traz o futebol da Inglaterra e organiza os primeiros jogos em São Paulo.'
    ],
    [
        'ano' => '1914',
        'titulo' => '🇧🇷 Estreia da Seleção',
        'texto' => 'A Seleção Brasileira faz sua primeira partida oficial contra o Exército Britânico.'
    ],
    [
        'ano' => '1958',
        'titulo' => '🥇 1º Título Mundial',
        'texto' => 'Brasil vence a Copa do Mundo na Suécia com Pelé, Garrincha e Didi.'
    ],
    [
        'ano' => '1970',
        'titulo' => '👑 O Tricampeonato',
        'texto' => 'A seleção de 1970 é eleita a mais completa da história, vencendo no México.'
    ],
    [
        'ano' => '2002',
        'titulo' => '🏆 Pentacampeonato',
        'texto' => 'Brasil conquista o quinto título em Yokohama, Japão, com Ronaldo Fenômeno.'
    ],
];

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>História do Futebol Brasileiro</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-historia/historia.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-historia">
        <div class="container">

            <!-- Menu Lateral -->
            <aside class="menu-lateral menu-historia">
                <div class="menu-bloco">
                    <h2>Galerias</h2>

                    <ul>
                        <li>
                            <a href="#fotos">
                                Galeria de Fotos
                            </a>
                        </li>

                        <li>
                            <a href="#videos">
                                Galeria de Vídeos
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="menu-bloco">
                    <h2>Artigos</h2>

                    <ul>
                        <li>
                            <a href="../noticias/artigos.php?categoria=Campeonatos">
                                Campeonatos
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Conteúdo Principal -->
            <div class="conteudo-historia">

                <section class="hero-historia">
                    <span class="eyebrow">História</span>

                    <h1>História do Futebol Brasileiro</h1>

                    <p>
                        O futebol não é apenas um esporte no Brasil: é parte da nossa identidade cultural,
                        uma paixão nacional que une milhões de brasileiros. Nesta seção, você encontrará
                        registros históricos, imagens marcantes e vídeos que contam a trajetória gloriosa
                        do nosso futebol — desde os primeiros jogos até os títulos mundiais.
                    </p>
                </section>

                <section class="card-historia">
                    <div class="titulo-bloco-historia">
                        <h2>Um Breve Histórico</h2>
                        <span>Linha do tempo</span>
                    </div>

                    <div class="timeline-container" id="timeline-container">
                        <?php foreach ($timeline as $item): ?>
                            <div class="timeline-item" data-aos>
                                <div class="timeline-date">
                                    <?= eHistoria($item['ano']) ?>
                                </div>

                                <div class="timeline-content">
                                    <h3><?= eHistoria($item['titulo']) ?></h3>
                                    <p><?= eHistoria($item['texto']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="card-historia">
                    <div class="titulo-bloco-historia">
                        <h2>Clubes Campeões da Copa Libertadores da América</h2>
                        <span><?= count($clubesCampeoes) ?> clubes</span>
                    </div>

                    <p class="texto-historia">
                        O futebol brasileiro coleciona títulos internacionais desde 1948. Abaixo, estão os clubes
                        brasileiros que conquistaram a Copa Libertadores da América.
                    </p>

                    <?php if (!empty($clubesCampeoes)): ?>
                        <div class="clubes-campeoes" id="clubes-campeoes">
                            <?php foreach ($clubesCampeoes as $clube): ?>
                                <a
                                    href="../times/detalhes_time.php?id=<?= (int)$clube['id_time'] ?>"
                                    class="clube-card"
                                    data-titulos="<?= (int)$clube['titulos'] ?>"
                                >
                                    <div class="clube-logo">
                                        <img
                                            src="<?= caminhoImagemHistoria($clube['escudo']) ?>"
                                            alt="Escudo de <?= eHistoria($clube['nome']) ?>"
                                            onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                        >

                                        <span><?= eHistoria($clube['nome']) ?></span>
                                    </div>

                                    <div class="clube-info">
                                        <span class="titulos-num"><?= (int)$clube['titulos'] ?></span>
                                        <span class="titulos-label">
                                            <?= ((int)$clube['titulos'] === 1) ? 'Título' : 'Títulos' ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="mensagem-vazia">
                            <?= !empty($temporadas) ? 'Nenhum clube brasileiro campeão nas competições selecionadas.' : 'Nenhum título internacional registrado.' ?>
                        </p>
                    <?php endif; ?>
                </section>

                <section class="card-historia" id="fotos">
                    <div class="titulo-bloco-historia">
                        <h2>Galeria de Fotos</h2>
                        <span>Últimas galerias</span>
                    </div>

                    <div class="galerias">
                        <div class="galeria-miniaturas">
                            <?php if (!empty($galeriaFotos)): ?>
                                <?php foreach ($galeriaFotos as $foto): ?>
                                    <article
                                        class="miniatura-item"
                                        data-banco-id="<?= (int)$foto['banco_id'] ?>"
                                        data-nome-banco="<?= eHistoria($foto['nome_banco']) ?>"
                                    >
                                        <a href="galeria-fotos.php">
                                            <img
                                                src="<?= eHistoria($foto['caminho_imagem']) ?>"
                                                alt="<?= eHistoria($foto['nome_banco']) ?>"
                                                onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                            >

                                            <p><?= eHistoria($foto['nome_banco']) ?></p>
                                        </a>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="mensagem-vazia">Nenhuma foto cadastrada.</p>
                            <?php endif; ?>
                        </div>

                        <a href="galeria-fotos.php" class="botao">
                            Ver todas as fotos
                        </a>
                    </div>
                </section>

                <section class="card-historia" id="videos">
                    <div class="titulo-bloco-historia">
                        <h2>Galeria de Vídeos</h2>
                        <span>Últimos vídeos</span>
                    </div>

                    <div class="galerias">
                        <div class="galeria-miniaturas-videos">
                            <?php if (!empty($galeriaVideos)): ?>
                                <?php foreach ($galeriaVideos as $video): ?>
                                    <?php
                                        $youtubeId = extract_youtube_id($video['url']);
                                        $thumbnail = $youtubeId
                                            ? 'https://img.youtube.com/vi/' . eHistoria($youtubeId) . '/mqdefault.jpg'
                                            : 'https://via.placeholder.com/320x180/000000/FFFFFF?text=Video+Indisponivel';
                                    ?>

                                    <article class="miniatura-video">
                                        <a href="galeria-videos.php">
                                            <img
                                                src="<?= eHistoria($thumbnail) ?>"
                                                alt="<?= eHistoria($video['titulo']) ?>"
                                            >

                                            <p><?= eHistoria($video['titulo']) ?></p>
                                        </a>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="mensagem-vazia">Nenhum vídeo cadastrado.</p>
                            <?php endif; ?>
                        </div>

                        <a href="galeria-videos.php" class="botao">
                            Ver todos os vídeos
                        </a>
                    </div>
                </section>

                <!-- Modais -->
                <div id="modal-foto" class="modal">
                    <div class="modal-content">
                        <span class="modal-close">&times;</span>

                        <div class="carousel-container">
                            <div class="carousel-slides" id="carousel-slides"></div>

                            <button class="carousel-prev" id="carousel-prev" type="button">&lt;</button>
                            <button class="carousel-next" id="carousel-next" type="button">&gt;</button>

                            <div class="carousel-indicators" id="carousel-indicators"></div>
                        </div>

                        <p id="modal-titulo">Galeria</p>
                    </div>
                </div>

                <div id="modal-video" class="modal">
                    <div class="modal-content">
                        <span class="modal-close">&times;</span>

                        <iframe
                            id="modal-iframe"
                            src=""
                            frameborder="0"
                            allowfullscreen
                            title="Vídeo histórico"
                        ></iframe>

                        <p id="modal-video-titulo"></p>
                    </div>
                </div>

            </div>
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