<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $uf = isset($_GET['uf']) ? strtoupper(trim($_GET['uf'])) : '';
    $extintos = isset($_GET['extintos']) ? true : false;

    $times = [];
    if (!empty($uf)) {
        if ($extintos) {
            $stmt = $pdo->prepare("SELECT * FROM times WHERE estado = ? AND extinto = 1 ORDER BY nome ASC");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM times WHERE estado = ? AND extinto = 0 ORDER BY nome ASC");
        }
        $stmt->execute([$uf]);
        $times = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $extintos ? 'Times Extintos' : 'Times Ativos' ?> do Estado <?= htmlspecialchars($uf) ?> - Futebol Brasileiro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/times_estado.css">
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
    <section class="secao-times-estado">
        <div class="container">
            <a href="times.php" class="voltar-link">← Voltar para Times</a>
            <h1><?= $extintos ? 'Clubes Extintos' : 'Clubes em Atividade' ?> do Estado: <?= htmlspecialchars($uf) ?></h1>

            <?php if (!empty($times)): ?>
                <div class="grade-times">
                    <?php foreach ($times as $time): ?>
                        <a class="card-time" href="detalhes_time.php?id=<?= (int)$time['id'] ?>">
                            <img src="<?= htmlspecialchars($time['escudo'] ?: 'assets/images/escudo_padrao.png') ?>" alt="Escudo de <?= htmlspecialchars($time['nome']) ?>" onerror="this.onerror=null; this.src='assets/images/escudo_padrao.png';">
                            <div class="info">
                                <h3><?= htmlspecialchars($time['nome']) ?></h3>
                                <p>
                                    Fundado em
                                    <?= !empty($time['fundacao']) ? date('d/m/Y', strtotime($time['fundacao'])) : 'Data desconhecida' ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; margin-top: 30px;">Nenhum clube <?= $extintos ? 'extinto' : 'em atividade' ?> encontrado para este estado.</p>
            <?php endif; ?>
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
            document.getElementById('link-admin-revelado').style.display = 'block';
        }
    </script>
</footer>

</body>
</html>
