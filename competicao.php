<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $slug = isset($_GET['slug']) ? $_GET['slug'] : '';

    $stmt = $pdo->prepare("SELECT * FROM competicoes WHERE slug = ?");
    $stmt->execute([$slug]);
    $competicao = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt_fotos = $pdo->prepare("SELECT * FROM fotos WHERE id_competicao = ?");
    $stmt_fotos->execute([$competicao['id']]);
    $fotos = $stmt_fotos->fetchAll(PDO::FETCH_ASSOC);

    if (!$competicao) {
        die("Competição não encontrada.");
    }

    $stmt_temp = $pdo->prepare("SELECT * FROM temporadas WHERE id_competicao = ? ORDER BY ano DESC");
    $stmt_temp->execute([$competicao['id']]);
    $temporadas = $stmt_temp->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($competicao['nome']) ?> - Futebol Brasileiro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/competicao.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto :wght@400;700&display=swap" rel="stylesheet">
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
  <section class="secao-competicao">
    <div class="container">
      <a href="campeonatos.php" class="voltar-link">← Voltar para Campeonatos</a>

      <h1><?= htmlspecialchars($competicao['nome']) ?></h1>

      <div class="conteudo-com-coluna">
        <!-- Conteúdo Principal -->
        <div class="coluna-esquerda">
          <?php if (!empty($competicao['descricao'])): ?>
            <div class="descricao">
              <p><?= nl2br(htmlspecialchars($competicao['descricao'])) ?></p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Temporadas à Direita -->
        <div class="coluna-direita">
          <?php if (!empty($fotos)): ?>
            <div class="galeria-fotos">
              <?php foreach ($fotos as $foto): ?>
                <div class="foto-item">
                  <?php
                    $src = $foto['caminho_imagem'];
                    // Se for uma URL externa (começa com http ou https), usa direto
                    if (preg_match('/^https?:\/\//', $src)) {
                        $imagem_src = $src;
                    } else {
                        // Caminho interno
                        $imagem_src = htmlspecialchars($src);
                    }
                  ?>
                  <img class="imagem-item" src="<?= $imagem_src ?>" alt="<?= htmlspecialchars($foto['titulo']) ?>">
                  <p class="legenda"><?= htmlspecialchars($foto['titulo']) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <h2>Temporadas disputadas</h2>
          <?php if (!empty($temporadas)): ?>
            <ul class="lista-temporadas">
              <?php foreach ($temporadas as $temp): ?>
                <li>
                  <a href="temporada.php?id_competicao=<?= $competicao['id'] ?>&ano=<?= $temp['ano'] ?>">
                    <?= $temp['ano'] ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>Temporadas ainda não cadastradas.</p>
          <?php endif; ?>
        </div>
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