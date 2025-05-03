<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->query("SELECT * FROM noticias ORDER BY data_publicacao DESC");
  $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Erro na conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Notícias - Futebol Brasileiro</title>
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/noticias.css">
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


  <main class="secao-noticias">
    <div class="container">
      <h1>Últimas Notícias</h1>
      <div class="grade-noticias">
        <?php foreach ($noticias as $noticia): ?>
          <a class="card-noticia" href="detalhes_noticia.php?id=<?= $noticia['id'] ?>">
            <img src="<?= htmlspecialchars($noticia['imagem']) ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>">
            <div class="info">
              <h3><?= htmlspecialchars($noticia['titulo']) ?></h3>
              <p><?= htmlspecialchars($noticia['subtitulo']) ?></p>
              <span><?= date('d/m/Y', strtotime($noticia['data_publicacao'])) ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
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
