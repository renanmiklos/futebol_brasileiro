<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buscar todos os clubes com pontuação
$sql = "
  SELECT 
    t.id,
    t.nome,
    SUM(CASE WHEN c.tipo = 'Internacional' THEN cl.pontos ELSE 0 END) AS internacionais,
    SUM(CASE WHEN c.tipo = 'Nacional' THEN cl.pontos ELSE 0 END) AS nacionais,
    SUM(CASE WHEN c.tipo = 'Regional' THEN cl.pontos ELSE 0 END) AS regionais,
    SUM(CASE WHEN c.tipo = 'Estadual' THEN cl.pontos ELSE 0 END) AS estaduais,
    SUM(cl.pontos) AS total
  FROM classificacao cl
  INNER JOIN temporadas tp ON cl.id_temporada = tp.id
  INNER JOIN competicoes c ON tp.id_competicao = c.id
  INNER JOIN times t ON cl.id_time = t.id
  WHERE cl.nacional = 1
  GROUP BY t.id
  ORDER BY total DESC
";
$ranking = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Ranking dos Clubes - Futebol Brasileiro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/ranking.css">
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

  <main class="container">
    <h1>Ranking dos Clubes Brasileiros</h1>
    <p>Este ranking é baseado na pontuação histórica das campanhas dos clubes em competições oficiais.</p>

    <div class="tabela-scroll">
      <table id="ranking-table">
        <thead>
          <tr>
            <th onclick="ordenar(0)">Pos</th>
            <th onclick="ordenar(1)">Clube</th>
            <th onclick="ordenar(2)">Internacionais</th>
            <th onclick="ordenar(3)">Nacionais</th>
            <th onclick="ordenar(4)">Regionais</th>
            <th onclick="ordenar(5)">Estaduais</th>
            <th onclick="ordenar(6)">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php $pos = 1; foreach ($ranking as $clube): ?>
            <tr>
              <td><?= $pos++ ?></td>
              <td><?= htmlspecialchars($clube['nome']) ?></td>
              <td><?= $clube['internacionais'] ?></td>
              <td><?= $clube['nacionais'] ?></td>
              <td><?= $clube['regionais'] ?></td>
              <td><?= $clube['estaduais'] ?></td>
              <td><strong><?= $clube['total'] ?></strong></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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

  <script>
    function ordenar(coluna) {
      const tabela = document.getElementById("ranking-table");
      const linhas = Array.from(tabela.rows).slice(1);
      const corpo = tabela.tBodies[0];
      const tipoNumero = coluna !== 1;

      linhas.sort((a, b) => {
        let valA = a.cells[coluna].innerText;
        let valB = b.cells[coluna].innerText;

        if (tipoNumero) {
          valA = parseInt(valA) || 0;
          valB = parseInt(valB) || 0;
          return valB - valA;
        } else {
          return valA.localeCompare(valB);
        }
      });

      linhas.forEach(l => corpo.appendChild(l));
    }
  </script>
</body>
</html>
