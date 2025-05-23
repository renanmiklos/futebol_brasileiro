<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index_Historia</title>
</head>
<body>

    <h1>História</h1>
    <p style="margin-left: 150px;">Conheça momentos marcantes, ídolos e estádios que fizeram parte da história do nosso futebol.</p>
    <section class="historia">
        <div class="galerias">
            <!-- Galeria de Fotos -->
            <div class="galeria-fotos">
                <h3>Galeria de Fotos</h3>
                <div class="carrossel2-fotos">
                    <?php foreach ($fotos as $index => $foto): ?>
                        <div class="carrossel2-item<?= $index === 0 ? ' active' : '' ?>">
                            <img src="<?= htmlspecialchars($foto['caminho_imagem']) ?>" alt="<?= htmlspecialchars($foto['titulo']) ?>">
                            <div class="carrossel2-caption">
                                <h5><?= htmlspecialchars($foto['titulo']) ?></h5>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p>
                    <?= htmlspecialchars($fotos[0]['descricao'] ?? 'Clique aqui para ver mais fotos históricas do futebol brasileiro.') ?>
                    <br><br>
                    <a href="historia/historia.php" class="botao">Ver todas as fotos</a>
                </p>
            </div>
        
            <!-- Galeria de Vídeos -->
            <div class="galeria-video">
                <?php
                // Buscar o último vídeo do banco
                $stmt_video = $pdo->prepare("SELECT * FROM videos ORDER BY data_publicacao DESC LIMIT 1");
                $stmt_video->execute();
                $video = $stmt_video->fetch(PDO::FETCH_ASSOC);

                if ($video):
                    // Extrair o ID do vídeo do YouTube da URL
                    $video_id = '';
                    parse_str(parse_url($video['url'], PHP_URL_QUERY), $params);
                    if (isset($params['v'])) {
                        $video_id = $params['v'];
                    } else {
                        $video_id = basename($video['url']); // caso seja um link encurtado ou formato diferente
                    }
                ?>
                    <h3>Último Vídeo</h3>
                    <iframe width="100%" height="350" 
                            src="https://www.youtube.com/embed/<?= $video_id ?>" 
                            title="<?= htmlspecialchars($video['titulo']) ?>"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                    <p>
                        <?= htmlspecialchars($video['descricao'] ?? 'Clique aqui para ver mais vídeos históricos do futebol brasileiro.') ?>
                        <br><br>
                        <a href="historia/historia.php" class="botao">Ver todos os vídeos</a>
                    </p>
                <?php else: ?>
                    <p>Nenhum vídeo encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
</body>
</html>