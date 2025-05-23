<?php

require_once '../estrutura/conexaodb.php';

// Buscar todos os clubes com pontuação
$sql = "
  SELECT 
    t.id,
    t.nome,
    t.estado,
    t.escudo,
    SUM(CASE WHEN c.tipo = 'Internacional' THEN cl.pontos ELSE 0 END) AS internacionais,
    SUM(CASE WHEN c.tipo = 'Nacional' THEN cl.pontos ELSE 0 END) AS nacionais,
    SUM(CASE WHEN c.tipo = 'Regional' THEN cl.pontos ELSE 0 END) AS regionais,
    SUM(CASE WHEN c.tipo = 'Estadual' THEN cl.pontos ELSE 0 END) AS estaduais,
    SUM(cl.pontos) AS total
  FROM classificacao cl
  INNER JOIN temporadas tp ON cl.id_temporada = tp.id
  INNER JOIN competicoes c ON tp.id_competicao = c.id
  INNER JOIN times t ON cl.id_time = t.id
  WHERE cl.nacional = 1 AND t.extinto = 0
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
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="../estatisticas/css-estisticas/ranking.css">
</head>
<body>

  <?php include '../estrutura/header2.php'; ?>
  
  <main class="container">
    <h1>Ranking dos Clubes Brasileiros</h1>
    <p>Este ranking é baseado na pontuação histórica das campanhas dos clubes em competições oficiais.</p>

    <div class="tabela-scroll">
      <table id="ranking-table">
        <thead>
          <tr>
            <th onclick="ordenar(0)">Pos</th>
            <th onclick="ordenar(1)">Clube</th>
            <th onclick="ordenar(2)">Estado</th>
            <th onclick="ordenar(3)">Internacionais</th>
            <th onclick="ordenar(4)">Nacionais</th>
            <th onclick="ordenar(5)">Regionais</th>
            <th onclick="ordenar(6)">Estaduais</th>
            <th onclick="ordenar(7)">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php $pos = 1; foreach ($ranking as $clube): ?>
            <tr>
              <td><?= $pos++ ?></td>
              <td style="text-align: left;">
                <img src="<?= '../' . htmlspecialchars($clube['escudo']) ?>" alt="Escudo de <?= htmlspecialchars($clube['nome']) ?>" style="height: 20px; vertical-align: middle; margin-right: 6px;">
                <?= htmlspecialchars($clube['nome']) ?></td>
              <td><?= htmlspecialchars($clube['estado']) ?></td>
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

  <?php include '../estrutura/footer2.php'; ?>

  <script src="../estatisticas/js-estatisticas/ranking.js"></script>

</body>
</html>
