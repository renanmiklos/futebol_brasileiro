<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index_Noticias</title>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>
    
    <h1>Destaques</h1>
    <section class="principal">
        <div class="conteudo">
            <div class="noticias-container">
                <div class="noticia-principal">
                    <?php if ($noticia_principal): ?>
                        <a href="noticias/detalhes_noticia.php?id=<?= $noticia_principal['id'] ?>">
                            <img src="<?= htmlspecialchars($noticia_principal['imagem']) ?>" alt="Imagem da Notícia Principal">
                        </a>
                        <h2><a style="color:white; text-decoration: none;" href="detalhes_noticia.php?id=<?= $noticia_principal['id'] ?>">
                            <?= htmlspecialchars($noticia_principal['titulo']) ?></a></h2>
                        <h4><?= htmlspecialchars($noticia_principal['subtitulo']) ?></h4>
                    <?php endif; ?>
                </div>

                <div class="cards-noticias">
                    <?php foreach ($noticias_cards as $card): ?>
                        <div class="card">
                            <a href="noticias/detalhes_noticia.php?id=<?= $card['id'] ?>">
                                <img src="<?= htmlspecialchars($card['imagem']) ?>" alt="Imagem do Card">
                            </a>
                            <h4><a style="color:white; text-decoration: none;" href="detalhes_noticia.php?id=<?= $card['id'] ?>">
                                <?= htmlspecialchars($card['titulo']) ?></a></h4>
                            <h5><?= htmlspecialchars($card['subtitulo']) ?></h5>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="barra-lateral">
                <div class="carrossel-artigos">
                    <h3>Últimos Artigos</h3>
                    <div class="carrossel">
                        <?php foreach ($artigos as $index => $art): ?>
                            <div class="carrossel-item <?= $index === 0 ? 'active' : '' ?>">
                                <a href="noticias/artigos_detalhes.php?id=<?= $art['id'] ?>">
                                    <img src="<?= htmlspecialchars($art['imagem']) ?>" alt="Imagem do Artigo">
                                    <div class="carrossel-caption">
                                        <h5><?= htmlspecialchars($art['titulo']) ?></h5>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="links-importantes">
                    <h3>Links Úteis</h3>
                    <ul>
                        <li><a href="campeonatos/competicao.php?slug=campeonato-brasileiro">Campeonato Brasileiro</a></li>
                        <li><a href="times/times.php?regiao=Times+Extintos">Clubes Extintos</a></li>
                        <li><a href="noticias/artigos.php?categoria=Jogadores">Grandes Jogadores</a></li>
                        <li><a href="noticias/artigos.php">Ver Todos os Artigos</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>

</body>
</html>