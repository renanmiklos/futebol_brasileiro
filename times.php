<?php
$regioes = [
  'Sudeste' => ['SP', 'RJ', 'MG', 'ES'],
  'Sul' => ['RS', 'PR', 'SC'],
  'Nordeste' => ['BA', 'PE', 'CE', 'RN', 'MA', 'PB', 'PI', 'AL', 'SE'],
  'Centro-Oeste' => ['DF', 'GO', 'MT', 'MS'],
  'Norte' => ['AM', 'PA', 'AC', 'RO', 'RR', 'AP', 'TO']
];

// Captura a região selecionada pela URL
$regiaoSelecionada = isset($_GET['regiao']) ? urldecode($_GET['regiao']) : null;

// Selecione os estados da região selecionada
$estadosPorRegiao = $regiaoSelecionada && isset($regioes[$regiaoSelecionada]) ? $regioes[$regiaoSelecionada] : [];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clubes por Estado - Futebol Brasileiro</title>
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/times.css">
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
  <section class="secao-times">
    <div class="container">
      <!-- Menu Lateral -->
      <aside class="menu-lateral">
        <h2>Regiões</h2>
        <ul>
          <?php foreach ($regioes as $regiao => $estados): ?>
            <li>
              <a href="?regiao=<?= urlencode($regiao) ?>"
                class="<?= ($regiaoSelecionada === $regiao) ? 'ativo' : '' ?>">
                <?= htmlspecialchars($regiao) ?>
              </a>
            </li>
          <?php endforeach; ?>
          <li><a href="times_extintos.php">Times extintos</a></li>
        </ul>
      </aside>

      <!-- Conteúdo Principal -->
      <div class="conteudo-times">
        <h1>Clubes do Futebol Brasileiro</h1>
        <p>O futebol no Brasil é marcado por rivalidades regionais, paixões locais e histórias que atravessam gerações. 
          Aqui você encontra os clubes de todos os estados do país, organizados de forma simples e prática.</p>

        <?php if ($regiaoSelecionada): ?>
          <h2>Estados da Região <?= htmlspecialchars($regiaoSelecionada) ?></h2>
          <div class="lista-estados">
            <ul>
              <?php foreach ($estadosPorRegiao as $uf): ?>
                <li>
                  <a href="times_estado.php?uf=<?= htmlspecialchars($uf) ?>">
                    <?= htmlspecialchars($uf) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php else: ?>
          <p>Escolha uma região no menu ao lado para ver os estados disponíveis.</p>
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
