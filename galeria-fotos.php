<?php
// Conexão com o banco
$pdo = new PDO("mysql:host=localhost;dbname=futebol;charset=utf8", "root", "");

// Consulta para buscar os bancos de fotos junto com a primeira imagem
$sql = "
    SELECT b.id, b.nome, b.descricao, f.caminho_imagem
    FROM bancos_de_fotos b
    LEFT JOIN (
        SELECT banco_id, caminho_imagem
        FROM fotos
        WHERE id IN (
            SELECT MIN(id)
            FROM fotos
            GROUP BY banco_id
        )
    ) f ON b.id = f.banco_id
    ORDER BY b.data_criacao DESC
";

$stmt = $pdo->query($sql);
$bancos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Galeria de Fotos - Futebol Brasileiro</title>
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/galeria-fotos.css">
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
    <section class="galeria-fotos">
        <div class="galeria-container">
            <h1>Galeria de Fotos</h1>
            <p>Aqui estão registrados momentos marcantes da história do futebol brasileiro.</p>

            <div class="galeria-lista">
                <?php if (!empty($bancos)): ?>
                    <?php foreach ($bancos as $banco): ?>
                        <div class="galeria-card">
                            <h2><?= htmlspecialchars($banco['nome']) ?></h2>

                            <?php if (!empty($banco['caminho_imagem'])): ?>
                                <img src="<?= htmlspecialchars($banco['caminho_imagem']) ?>" alt="<?= htmlspecialchars($banco['nome']) ?>" class="imagem-preview">
                            <?php endif; ?>

                            <p><?= htmlspecialchars($banco['descricao']) ?></p>
                            <a href="detalhes-galeria-fotos.php?banco_id=<?= $banco['id'] ?>" class="botao">Ver Fotos</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhum álbum foi encontrado no momento.</p>
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