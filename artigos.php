<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT * FROM artigos ORDER BY data_publicacao DESC");
    $artigos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Artigos - Futebol Brasileiro</title>
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/artigos.css">
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
    <section class="secao-artigos">
      <div class="container">
        <h1>Artigos Históricos do Futebol Brasileiro</h1>

        <div class="grade-artigos">
          <?php foreach ($artigos as $artigo): ?>
            <a class="card-artigo" href="artigos_detalhes.php?id=<?= $artigo['id'] ?>">
              <img src="<?= htmlspecialchars($artigo['imagem']) ?>" alt="Imagem do artigo">
              <div class="info">
                <h3><?= htmlspecialchars($artigo['titulo']) ?></h3>
                <p><?= htmlspecialchars($artigo['subtitulo']) ?></p>
                <span><?= date('d/m/Y', strtotime($artigo['data_publicacao'])) ?></span>
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
