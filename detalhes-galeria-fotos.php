<?php
$banco_id = $_GET['banco_id'] ?? 0;

$pdo = new PDO("mysql:host=localhost;dbname=futebol;charset=utf8", "root", "");

// Buscar o nome do banco
$stmt = $pdo->prepare("SELECT nome FROM bancos_de_fotos WHERE id = ?");
$stmt->execute([$banco_id]);
$banco = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar até 6 fotos desse banco
$stmt = $pdo->prepare("SELECT * FROM fotos WHERE banco_id = ? ORDER BY id LIMIT 6");
$stmt->execute([$banco_id]);
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($banco['nome']) ?> - Galeria de Fotos</title>
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/detalhes-galeria-fotos.css">
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

    <main class="container-fotos">
    <h1 class="titulo-galeria"><?= htmlspecialchars($banco['nome']) ?></h1>

    <div class="grid-fotos">
        <?php foreach ($fotos as $foto): ?>
            <div class="foto-slot">
                <img src="<?= htmlspecialchars($foto['caminho_imagem']) ?>" 
                    alt="<?= htmlspecialchars($foto['titulo']) ?>">
                <p><?= htmlspecialchars($foto['titulo']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="galeria-fotos.php" class="botao voltar-galeria">Voltar à Galeria</a>
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