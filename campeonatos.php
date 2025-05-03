<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

$categoriaSelecionada = isset($_GET['tipo']) ? $_GET['tipo'] : null;

$categorias = [
  'Internacional',
  'Nacional',
  'Regional',
  'Estadual',
  'Amistosos' // essa opção usaremos para buscar onde `amistoso = 1`
];

// Verifica se é uma categoria válida
$competicoes = [];
if ($categoriaSelecionada && in_array($categoriaSelecionada, $categorias)) {
    if ($categoriaSelecionada === 'Amistosos') {
        $stmt = $pdo->prepare("SELECT * FROM competicoes WHERE amistoso = 1 ORDER BY nome");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM competicoes WHERE tipo = ? AND amistoso = 0 ORDER BY nome");
        $stmt->execute([$categoriaSelecionada]);
    }
    $competicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Campeonatos - Futebol Brasileiro</title>
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/campeonatos.css">
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

  <main>
    <section class="secao-campeonatos">
      <div class="container">
        <aside class="menu-lateral">
          <h2>Tipos de Competições</h2>
          <ul>
            <?php foreach ($categorias as $categoria): ?>
              <li>
                <a href="?tipo=<?= urlencode($categoria) ?>"
                  class="<?= ($categoriaSelecionada === $categoria) ? 'ativo' : '' ?>">
                  <?= $categoria ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </aside>

        <div class="conteudo-campeonatos">
          <h1>Campeonatos</h1>
          <p>Esta página reúne os principais campeonatos disputados pelos clubes brasileiros, organizados por tipo: internacionais, nacionais, regionais, estaduais e amistosos.</p>

          <?php if ($categoriaSelecionada): ?>
            <h2>Competições <?= htmlspecialchars($categoriaSelecionada) ?></h2>
            <div class="lista">
              <div class="lista-competicoes">
                <?php if (!empty($competicoes)): ?>
                  <ul>
                    <?php foreach ($competicoes as $comp): ?>
                      <li>
                        <a href="competicao.php?slug=<?= htmlspecialchars($comp['slug']) ?>">
                          <?= htmlspecialchars($comp['nome']) ?>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <p>Nenhuma competição cadastrada para esta categoria.</p>
                <?php endif; ?>
              </div>
            </div>  
          <?php else: ?>
            <p>Escolha uma categoria no menu ao lado para ver as competições disponíveis.</p>
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
