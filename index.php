<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt_principal = $pdo->prepare("SELECT * FROM noticias WHERE principal = 1 ORDER BY data_publicacao DESC LIMIT 1");
    $stmt_principal->execute();
    $noticia_principal = $stmt_principal->fetch(PDO::FETCH_ASSOC);

    $stmt_cards = $pdo->prepare("SELECT * FROM noticias WHERE principal = 0 ORDER BY data_publicacao DESC LIMIT 3");
    $stmt_cards->execute();
    $noticias_cards = $stmt_cards->fetchAll(PDO::FETCH_ASSOC);

    $stmt_artigos = $pdo->prepare("SELECT * FROM artigos ORDER BY data_publicacao DESC LIMIT 3");
    $stmt_artigos->execute();
    $artigos = $stmt_artigos->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("
        SELECT f.*
        FROM fotos f
        INNER JOIN (
            SELECT banco_id, MAX(data_publicacao) as ultima_data
            FROM fotos
            GROUP BY banco_id
        ) ultimas
        ON f.banco_id = ultimas.banco_id AND f.data_publicacao = ultimas.ultima_data
        ORDER BY f.data_publicacao DESC
    ");
    $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt_videos = $pdo->prepare("SELECT * FROM videos ORDER BY data_publicacao DESC LIMIT 1");
    $stmt_videos->execute();
    $videos = $stmt_videos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futebol Brasileiro</title>
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/hist_index.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <div class="logo-area">
                <img src="assets/images/logo.png" alt="Logo" class="logo">
                <span class="logo-text">Futebol Brasileiro</span>
            </div>
            <div class="menu-area">
                <form class="search-bar" action="busca.php" method="GET">
                    <input type="text" name="query" placeholder="Buscar...">
                    <button type="submit">🔍</button>
                </form>
                <nav class="menu-principal">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="noticias.php">Notícias</a></li>
                        <li><a href="historia.php">História</a></li>
                        <li><a href="times.php">Times</a></li>
                        <li><a href="campeonatos.php">Campeonatos</a></li>
                        <li><a href="ranking.php">Ranking</a></li>
                        <li><a href="artigos.php">Artigos</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <h1>Destaques</h1>
        <section class="principal">
            <div class="conteudo">
                <div class="noticias-container">
                    <div class="noticia-principal">
                        <?php if ($noticia_principal): ?>
                            <a href="detalhes_noticia.php?id=<?= $noticia_principal['id'] ?>">
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
                                <a href="detalhes_noticia.php?id=<?= $card['id'] ?>">
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
                                    <a href="artigos_detalhes.php?id=<?= $art['id'] ?>">
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
                            <li><a href="campeonatos.php">Campeonato Brasileiro</a></li>
                            <li><a href="clubes_extintos.php">Clubes Extintos</a></li>
                            <li><a href="jogadores.php">Grandes Jogadores</a></li>
                            <li><a href="artigos.php">Ver Todos os Artigos</a></li>
                        </ul>
                    </div>
                </aside>
            </div>
        </section>

        <!-- Seção: História - Vídeos e Fotos -->
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
                        <a href="historia.php" class="botao">Ver todas as fotos</a>
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
                            <a href="historia.php" class="botao">Ver todos os vídeos</a>
                        </p>
                    <?php else: ?>
                        <p>Nenhum vídeo encontrado.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="rodape">
        <div class="rodape-container">
            <p>&copy; <?= date('Y') ?> Futebol Brasileiro. Todos os direitos reservados.</p>

            <p style="font-size: 0.9em;">
            <button onclick="mostrarLinkAdmin()" class="btn-link-admin">Área Administrativa</button>
            </p>

            <p id="link-admin-revelado" style="display: none; font-size: 0.8em;">
            <a href="admin.php" class="admin-link" style="color: #FFD700;">Acessar Painel</a>
            </p>
        </div>

        <script>
            function mostrarLinkAdmin() {
            const link = document.getElementById('link-admin-revelado');
            link.style.display = 'block';
            }
        </script>
    </footer>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const carrosselItems = document.querySelectorAll(".carrossel-item");
            let currentIndex = 0;

            function showNextSlide() {
                // Remove a classe 'active' do slide atual
                carrosselItems[currentIndex].classList.remove("active");

                // Calcula o próximo índice
                currentIndex = (currentIndex + 1) % carrosselItems.length;

                // Adiciona a classe 'active' ao próximo slide
                carrosselItems[currentIndex].classList.add("active");
            }

            // Define o intervalo para trocar os slides automaticamente (ex.: a cada 5 segundos)
            setInterval(showNextSlide, 5000);

            // Inicializa o primeiro slide como ativo
            if (carrosselItems.length > 0) {
                carrosselItems[0].classList.add("active");
            }
        });
    </script>

    <!-- Carrossel da galeria de fotos -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const carrosselItems = document.querySelectorAll(".carrossel2-item");
            
            if (carrosselItems.length === 0) {
                console.warn("Nenhum item encontrado para o carrossel.");
                return;
            }

            let currentIndex = 0;

            function showNextSlide() {
                // Remove a classe 'active' do slide atual
                carrosselItems[currentIndex].classList.remove("active");

                // Calcula o próximo índice
                currentIndex = (currentIndex + 1) % carrosselItems.length;

                // Adiciona a classe 'active' ao próximo slide
                carrosselItems[currentIndex].classList.add("active");
            }

            // Troca de slide a cada 5 segundos
            setInterval(showNextSlide, 5000);

            // Garante que o primeiro slide esteja ativo
            carrosselItems[0].classList.add("active");
        });
    </script>

</body>
</html>