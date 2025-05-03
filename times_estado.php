<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $uf = isset($_GET['uf']) ? strtoupper($_GET['uf']) : '';

  $stmt = $pdo->prepare("SELECT * FROM times WHERE estado = ? ORDER BY nome ASC");
  $stmt->execute([$uf]);
  $times = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Times do Estado <?= $uf ?> - Futebol Brasileiro</title>
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
      <h1>Clubes do Estado: <?= htmlspecialchars($uf) ?></h1>

      <div class="grade-times">
        <?php foreach ($times as $time): ?>
          <a class="card-time" href="detalhes_time.php?id=<?= $time['id'] ?>">
            <img src="<?= htmlspecialchars($time['escudo']) ?>" alt="Escudo de <?= htmlspecialchars($time['nome']) ?>">
            <div class="info">
              <h3><?= htmlspecialchars($time['nome']) ?></h3>
              <p>Fundado em <?= date('d/m/Y', strtotime($time['fundacao'])) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
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
