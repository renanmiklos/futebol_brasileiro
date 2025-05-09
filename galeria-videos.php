<?php
// Função para extrair o ID do vídeo do YouTube (a partir de URLs embed)
function extract_youtube_id($url) {
    $url = parse_url($url, PHP_URL_PATH);
    $parts = explode('/', trim($url, '/'));
    return end($parts); // pega a última parte da URL
}

// Conexão com o banco
try {
    $pdo = new PDO("mysql:host=localhost;dbname=futebol;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar todos os vídeos
    $stmt = $pdo->query("SELECT * FROM videos ORDER BY data_publicacao DESC");
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Galeria de Vídeos - Futebol Brasileiro</title>
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/galeria-videos.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto :wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Header -->
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

<!-- Conteúdo Principal -->
<main>
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
                            <h2><?= htmlspecialchars($video['titulo']) ?></h2>
                            <p><?= htmlspecialchars($video['descricao']) ?></p>

                            <!-- Vídeo embutido -->
                            <div class="video-frame">
                                <iframe width="100%" height="315"
                                        src="https://www.youtube.com/embed/<?= $videoId ?>?rel=0"
                                        title="<?= htmlspecialchars($video['titulo']) ?>"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                </iframe>
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

</body>
</html>