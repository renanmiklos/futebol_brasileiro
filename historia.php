<?php

function extract_youtube_id($url) {
    $url = parse_url($url, PHP_URL_PATH);
    $parts = explode('/', trim($url, '/'));
    return end($parts); // pega a última parte da URL (ex: abc123)
}


// Configurações do banco de dados
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar as 3 últimas fotos
    $stmt_fotos = $pdo->prepare("SELECT * FROM fotos ORDER BY data_publicacao DESC LIMIT 3");
    $stmt_fotos->execute();
    $galeriaFotos = $stmt_fotos->fetchAll(PDO::FETCH_ASSOC);

    // Buscar os 3 últimos vídeos
    $stmt_videos = $pdo->prepare("SELECT * FROM videos ORDER BY data_publicacao DESC LIMIT 3");
    $stmt_videos->execute();
    $galeriaVideos = $stmt_videos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>História do Futebol Brasileiro</title>
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/times.css">
    <link rel="stylesheet" href="assets/css/historia.css">
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

<!-- Footer -->
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