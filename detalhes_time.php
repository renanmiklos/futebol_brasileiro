<?php
$pdo = new PDO("mysql:host=localhost;dbname=futebol;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT * FROM times WHERE id = ?");
$stmt->execute([$id]);
$time = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$time) die("Time não encontrado.");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($time['nome']) ?> - Detalhes | Futebol Brasileiro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/detalhes_time.css">
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
    <section class="detalhes-time">
      <div class="container">
        <a class="voltar-link" href="times_estado.php?uf=<?= urlencode($time['estado']) ?>">← Voltar para o estado</a>
        
        <div class="perfil-time">
          <img class="escudo" src="<?= htmlspecialchars($time['escudo']) ?>" alt="Escudo de <?= htmlspecialchars($time['nome']) ?>">
          <div class="info">
            <h1><?= htmlspecialchars($time['nome_completo']) ?></h1>
            <p><strong>Fundação:</strong> <?= date('d/m/Y', strtotime($time['fundacao'])) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($time['estado']) ?></p>
            <p><strong>Cidade:</strong> <?= htmlspecialchars($time['cidade']) ?></p>
            <p><strong>Estádio:</strong> <?= htmlspecialchars($time['estadio']) ?> (<?= $time['capacidade'] ?>)</p>
            <p><strong>Extinto:</strong> <?= $time['extinto'] ? 'Sim' : 'Não' ?></p>
          </div>
        </div>

        <div class="descricao-time">
          <h2>História</h2>
          <p><?= nl2br(htmlspecialchars($time['historia'])) ?></p>

          <h2>Títulos</h2>
          <p><?= nl2br(htmlspecialchars($time['titulos'])) ?></p>
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
