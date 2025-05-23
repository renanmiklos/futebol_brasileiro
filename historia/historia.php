<?php

require_once '../estrutura/conexaodb.php';

function extract_youtube_id($url) {
    $url = parse_url($url, PHP_URL_PATH);
    $parts = explode('/', trim($url, '/'));
    return end($parts); // pega a última parte da URL (ex: abc123)
}

    // Buscar a última foto de cada banco e pegar os 3 mais recentes
    $stmt_fotos = $pdo->prepare("
        SELECT f.*, b.nome AS nome_banco
        FROM fotos f
        INNER JOIN (
            SELECT banco_id, MAX(data_publicacao) AS ultima_data
            FROM fotos
            GROUP BY banco_id
        ) ultimas ON f.banco_id = ultimas.banco_id AND f.data_publicacao = ultimas.ultima_data
        INNER JOIN bancos_de_fotos b ON b.id = f.banco_id
        ORDER BY f.data_publicacao DESC
        LIMIT 3
    ");
    $stmt_fotos->execute();
    $galeriaFotos = $stmt_fotos->fetchAll(PDO::FETCH_ASSOC);

    // Buscar os 2 últimos vídeos
    $stmt_videos = $pdo->prepare("SELECT * FROM videos ORDER BY data_publicacao DESC LIMIT 2");
    $stmt_videos->execute();
    $galeriaVideos = $stmt_videos->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>História do Futebol Brasileiro</title>
    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="../times/css-times/times.css">
    <link rel="stylesheet" href="css-historia/historia.css">
</head>
<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-historia">
        <div class="container">
            <!-- Menu Lateral -->
            <aside class="menu-lateral">
                <h2>Galerias</h2>
                <ul>
                    <li><a href="#fotos" class="<?= isset($_GET['secao']) && $_GET['secao'] === 'fotos' ? 'ativo' : '' ?>">Galeria de Fotos</a></li>
                    <li><a href="#videos" class="<?= isset($_GET['secao']) && $_GET['secao'] === 'videos' ? 'ativo' : '' ?>">Galeria de Vídeos</a></li>
                </ul>
                <h2>Artigos</h2>
                <ul>
                     <li><a href="../noticias/artigos.php?categoria=Campeonatos" class="<?= isset($_GET['categoria']) && $_GET['categoria'] === 'Campeonatos' ? 'ativo' : '' ?>">Campeonatos</a></li>
                </ul>
            </aside>

            <!-- Conteúdo Principal -->
            <div class="conteudo-historia">
                <h1>História do Futebol Brasileiro</h1>
                <p>O futebol não é apenas um esporte no Brasil: é parte da nossa identidade cultural, uma paixão nacional que une milhões de brasileiros. Nesta seção, você encontrará registros históricos, imagens marcantes e vídeos que contam a trajetória gloriosa do nosso futebol — desde os primeiros jogos até os títulos mundiais.</p>

                <h2>Um Breve Histórico</h2>
                <p>O futebol chegou ao Brasil no final do século XIX, trazido por Charles Miller, considerado o pai do futebol brasileiro. Desde então, tornou-se rapidamente popular, conquistando corações em todos os cantos do país.</p>

                <p>A seleção brasileira estreou oficialmente em 1914 e já nos anos 1920 começava a mostrar sua força com vitórias regionais. A consagração mundial veio com o primeiro título da Copa do Mundo em 1958, na Suécia, com um jovem Pelé brilhando ao lado de Garrincha e Didi.</p>

                <p>Desde então, o Brasil coleciona cinco títulos mundiais (1958, 1962, 1970, 1994 e 2002), além de inúmeros campeões estaduais, nacionais e continentais. O futebol brasileiro é sinônimo de arte, técnica e emoção.</p>

                <h2 id="fotos">Galeria de Fotos</h2>
                <div class="galerias">
                    <div class="galeria-miniaturas">
                        <?php if (!empty($galeriaFotos)): ?>
                            <?php foreach ($galeriaFotos as $foto): ?>
                                <div class="miniatura-item">
                                    <a href="galeria-fotos.php">
                                        <img src="<?= htmlspecialchars($foto['caminho_imagem']) ?>" alt="<?= htmlspecialchars($foto['titulo']) ?>" width="95%">
                                        <p><?= htmlspecialchars($foto['titulo']) ?></p>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Nenhuma foto cadastrada.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <h2 id="videos">Galeria de Vídeos</h2>
                <div class="galerias">
                    <div class="galeria-miniaturas-videos">
                        <?php if (!empty($galeriaVideos)): ?>
                            <?php foreach ($galeriaVideos as $video): ?>
                                <div class="miniatura-video">
                                    <a href="galeria-videos.php">
                                        <!-- Miniatura automática do YouTube -->
                                        <img src="https://img.youtube.com/vi/<?=urlencode(extract_youtube_id($video['url']))?>/mqdefault.jpg" 
                                            alt="<?=htmlspecialchars($video['titulo'])?>" width="95%">
                                        <p><?=htmlspecialchars($video['titulo'])?></p>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Nenhum vídeo cadastrado.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>