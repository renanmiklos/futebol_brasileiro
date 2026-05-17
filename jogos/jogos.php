<?php
require_once '../estrutura/conexaodb.php';

$categoriaSelecionada = isset($_GET['tipo']) ? $_GET['tipo'] : null;

$categorias = [
    'Internacional', 'Nacional', 'Regional', 'Amistoso'
];

$competicoes = [];
$estatisticas = null;

/* ============================================================
   BUSCAR COMPETIÇÕES POR CATEGORIA
   ============================================================ */
if ($categoriaSelecionada && in_array($categoriaSelecionada, $categorias)) {

    if ($categoriaSelecionada === 'Amistosos') {

        $stmt = $pdo->prepare("SELECT * FROM competicoes WHERE amistoso = 1 ORDER BY nome");
        $stmt->execute();
        $competicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $idsComp = [];

    } else {

        $stmt = $pdo->prepare("SELECT * FROM competicoes WHERE tipo = ? AND amistoso = 0 ORDER BY nome");
        $stmt->execute([$categoriaSelecionada]);
        $competicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $idsComp = array_column($competicoes, 'id');
    }

    /* ============================================================
       ESTATÍSTICAS (somente se NÃO for amistoso e houver competições)
       ============================================================ */
    if ($categoriaSelecionada !== 'Amistosos' && !empty($competicoes)) {

        $idsComp = array_column($competicoes, 'id');

        if (!empty($idsComp)) {

            $placeholders = implode(',', array_fill(0, count($idsComp), '?'));

            /* ====== CONTAR TEMPORADAS ====== */
            $stmt_temp = $pdo->prepare("
                SELECT COUNT(DISTINCT t.id)
                FROM temporadas t
                INNER JOIN jogos j ON j.id_temporada = t.id
                WHERE t.id_competicao IN ($placeholders)
            ");
            $stmt_temp->execute($idsComp);
            $totalTemporadas = (int) $stmt_temp->fetchColumn();

            if ($totalTemporadas > 0) {

                /* ====== TOTAL DE JOGOS ====== */
                $stmt_jogos_total = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM jogos j
                    INNER JOIN temporadas t ON t.id = j.id_temporada
                    WHERE t.id_competicao IN ($placeholders)
                ");
                $stmt_jogos_total->execute($idsComp);
                $totalJogos = (int) $stmt_jogos_total->fetchColumn();

                /* ============================================================
                   CLUBES DISTINTOS  
                   — IMPORTANTE: duplicar parâmetros do IN por causa do UNION
                   ============================================================ */

                $parametrosClubes = array_merge($idsComp, $idsComp);

                $stmt_clubes = $pdo->prepare("
                    SELECT COUNT(DISTINCT clube_identificador) FROM (
                        SELECT 
                            CASE 
                                WHEN j.id_time1 IS NOT NULL THEN CONCAT('id_', j.id_time1)
                                WHEN j.nome_time1 IS NOT NULL THEN CONCAT('nome_', j.nome_time1)
                            END AS clube_identificador
                        FROM jogos j
                        INNER JOIN temporadas t ON t.id = j.id_temporada
                        WHERE t.id_competicao IN ($placeholders)

                        UNION

                        SELECT 
                            CASE 
                                WHEN j.id_time2 IS NOT NULL THEN CONCAT('id_', j.id_time2)
                                WHEN j.nome_time2 IS NOT NULL THEN CONCAT('nome_', j.nome_time2)
                            END AS clube_identificador
                        FROM jogos j
                        INNER JOIN temporadas t ON t.id = j.id_temporada
                        WHERE t.id_competicao IN ($placeholders)
                    ) AS todos
                    WHERE clube_identificador IS NOT NULL
                ");

                $stmt_clubes->execute($parametrosClubes);
                $clubesDistintos = (int) $stmt_clubes->fetchColumn();

                $estatisticas = [
                    'competicoes' => count($idsComp),
                    'temporadas' => $totalTemporadas,
                    'jogos' => $totalJogos,
                    'clubes_distintos' => $clubesDistintos
                ];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resultados dos Jogos - Futebol Brasileiro</title>
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-jogos/jogos.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

  <?php include '../estrutura/header2.php'; ?>
  
  <main>
    <section class="secao-jogos">
      <div class="container">
        
        <aside class="menu-lateral">
          <h2>Tipos de Competições</h2>
          <ul>
            <?php foreach ($categorias as $categoria): ?>
              <li>
                <a href="?tipo=<?= urlencode($categoria) ?>" 
                   class="<?= ($categoriaSelecionada === $categoria) ? 'ativo' : '' ?>">
                  <?= htmlspecialchars($categoria) ?>
                </a>
              </li>
            <?php endforeach; ?>
            <li>
              <a href="por-time.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'por-time.php') ? 'ativo' : '' ?>">
                Por Time
              </a>
            </li>
          </ul>

          <h2>Artigos</h2>
          <ul>
            <li><a href="../noticias/artigos.php?categoria=Resultados">Resultados</a></li>
          </ul>
        </aside>

        <div class="conteudo-jogos">

          <h1>Resultados dos Jogos</h1>
          <p>Acompanhe os placares dos jogos das principais competições do futebol brasileiro, organizados por tipo de campeonato.</p>

          <?php if ($categoriaSelecionada): ?>

            <h2><?= htmlspecialchars($categoriaSelecionada) ?></h2>

            <?php if ($estatisticas): ?>
              <div class="card-estatisticas">
                <div class="stat-item">
                  <span class="stat-num"><?= $estatisticas['competicoes'] ?></span>
                  <span class="stat-label"><?= $estatisticas['competicoes'] == 1 ? 'Competição' : 'Competições' ?></span>
                </div>

                <div class="stat-item">
                  <span class="stat-num"><?= $estatisticas['jogos'] ?></span>
                  <span class="stat-label">Jogos Registrados</span>
                </div>
              </div>
            <?php endif; ?>

            <div class="lista">
              <div class="lista-competicoes">

                <?php if (!empty($competicoes)): ?>
                  <ul>
                    <?php foreach ($competicoes as $comp): ?>
                      <li>
                        <a href="resultados.php?slug=<?= htmlspecialchars($comp['slug']) ?>">
                          <?= htmlspecialchars($comp['nome']) ?>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>

                <?php else: ?>
                  <p>Nenhuma competição com jogos registrados nesta categoria.</p>
                <?php endif; ?>

              </div>
            </div>

          <?php else: ?>
            <p>Escolha uma categoria no menu ao lado para ver as competições com resultados disponíveis.</p>
          <?php endif; ?>

        </div>
      </div>
    </section>
  </main>

  <?php include '../estrutura/footer2.php'; ?>
</body>
</html>
