<?php

require_once '../estrutura/conexaodb.php';

// Função para extrair o ID do vídeo do YouTube (a partir de URLs embed)
function extract_youtube_id($url) {
    $url = parse_url($url, PHP_URL_PATH);
    $parts = explode('/', trim($url, '/'));
    return end($parts); // pega a última parte da URL
}


    // Buscar todos os vídeos
    $stmt = $pdo->query("SELECT * FROM videos ORDER BY data_publicacao DESC");
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Galeria de Vídeos - Futebol Brasileiro</title>
    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-historia/galeria-videos.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto :wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../estrutura/header2.php'; ?>

<!-- Conteúdo Principal -->
<main>
    <a href="historia.php" class="voltar-link">← Voltar para História</a>
    <section class="galeria-videos">
        <div class="galeria-container">
            <h1>Galeria de Vídeos</h1>
            <p>Confira momentos históricos, entrevistas marcantes e jogos inesquecíveis do futebol brasileiro.</p>

            <div class="galeria-lista">
                <?php if (!empty($videos)): ?>
                    <?php foreach ($videos as $video): ?>
                        <?php
                            // Extrair ID do vídeo do campo 'url' (espera-se que seja algo como https://www.youtube.com/embed/abc123 )
                            $videoId = extract_youtube_id($video['url']);
                            $thumbnailUrl = "https://img.youtube.com/vi/" . urlencode($videoId) . "/mqdefault.jpg";
                        ?>
                        <div class="galeria-card">
                            <a style="text-decoration: none;" href="detalhes-galeria-videos.php?video_id=<?= $video['id'] ?>">
                                <h2><?= htmlspecialchars($video['titulo']) ?></h2>
                            </a>
                            <p><?= htmlspecialchars($video['descricao']) ?></p>

                            <!-- Vídeo embutido -->
                            <div class="video-frame">
                                <a href="detalhes-galeria-videos.php?video_id=<?= $video['id'] ?>">
                                <img src="https://img.youtube.com/vi/<?=urlencode(extract_youtube_id($video['url']))?>/mqdefault.jpg" 
                                    alt="<?=htmlspecialchars($video['titulo'])?>" width="95%">
                                <p><?=htmlspecialchars($video['titulo'])?></p></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhum vídeo foi encontrado no momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>