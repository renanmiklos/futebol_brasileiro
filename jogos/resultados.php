<?php
require_once '../estrutura/conexaodb.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header('Location: jogos.php');
    exit;
}

$stmt_comp = $pdo->prepare("SELECT * FROM competicoes WHERE slug = ?");
$stmt_comp->execute([$slug]);
$competicao = $stmt_comp->fetch(PDO::FETCH_ASSOC);

if (!$competicao) {
    die("Competição não encontrada.");
}

$isInternacional = ($competicao['tipo'] === 'Internacional');

// Buscar anos com jogos
$stmt_temps = $pdo->prepare("
    SELECT DISTINCT t.ano
    FROM temporadas t
    INNER JOIN jogos j ON j.id_temporada = t.id
    WHERE t.id_competicao = ?
    ORDER BY t.ano DESC
");
$stmt_temps->execute([$competicao['id']]);
$temporadasComJogos = array_column($stmt_temps->fetchAll(), 'ano');

$anoSelecionado = $_GET['ano'] ?? null;
$clubeFiltro = $_GET['clube'] ?? null;

// === Carregar lista de clubes brasileiros com jogos na competição (para filtro) ===
$clubesBrasileiros = [];
if ($isInternacional) {
    $stmt_clubes_brasileiros = $pdo->prepare("
        SELECT DISTINCT t.id, t.nome
        FROM jogos j
        INNER JOIN temporadas temp ON temp.id = j.id_temporada
        INNER JOIN times t ON (t.id = j.id_time1 OR t.id = j.id_time2)
        WHERE temp.id_competicao = ? AND t.brasileiro = 1
        ORDER BY t.nome
    ");
    $stmt_clubes_brasileiros->execute([$competicao['id']]);
    $clubesBrasileiros = $stmt_clubes_brasileiros->fetchAll(PDO::FETCH_ASSOC);
}

// === Carregar jogos (todos ou de um ano específico) ===
$jogos = [];
$sql_jogos = "
    SELECT 
        j.*,
        t1.nome AS time1_nome_db, t1.escudo AS time1_escudo,
        t2.nome AS time2_nome_db, t2.escudo AS time2_escudo
    FROM jogos j
    LEFT JOIN times t1 ON t1.id = j.id_time1
    LEFT JOIN times t2 ON t2.id = j.id_time2
    INNER JOIN temporadas temp ON temp.id = j.id_temporada
    WHERE temp.id_competicao = ?
";

$params = [$competicao['id']];

// Filtro por ano, se aplicável
if ($anoSelecionado) {
    $sql_jogos .= " AND temp.ano = ?";
    $params[] = $anoSelecionado;
}

// Filtro por clube (só em internacionais — agora SEM exigir ano)
if ($clubeFiltro && $isInternacional) {
    $sql_jogos .= " AND (j.id_time1 = ? OR j.id_time2 = ?)";
    $params[] = $clubeFiltro;
    $params[] = $clubeFiltro;
}

$sql_jogos .= " ORDER BY j.data DESC, j.rodada ASC";

$stmt_jogos = $pdo->prepare($sql_jogos);
$stmt_jogos->execute($params);
$jogos = $stmt_jogos->fetchAll(PDO::FETCH_ASSOC);

// Normalizar nomes
foreach ($jogos as &$j) {
    $j['time1_nome'] = $j['id_time1'] ? $j['time1_nome_db'] : $j['nome_time1'];
    $j['time2_nome'] = $j['id_time2'] ? $j['time2_nome_db'] : $j['nome_time2'];
}

// === ESTATÍSTICAS PARA COMPETIÇÕES INTERNACIONAIS ===
$statsGlobais = null;
$topJogos = $topVitorias = $topGols = [];

if ($isInternacional) {
    // Totais gerais — incluindo empates, derrotas, gols pró e contra
    $stmt_totais = $pdo->prepare("
        SELECT
            COUNT(*) AS total_jogos,
            SUM(
                CASE 
                    WHEN (t.id = j.id_time1 AND j.gols_time1 > j.gols_time2) 
                      OR (t.id = j.id_time2 AND j.gols_time2 > j.gols_time1)
                    THEN 1 ELSE 0 END
            ) AS total_vitorias,
            SUM(
                CASE 
                    WHEN j.gols_time1 = j.gols_time2
                    THEN 1 ELSE 0 END
            ) AS total_empates,
            SUM(
                CASE 
                    WHEN (t.id = j.id_time1 AND j.gols_time1 < j.gols_time2) 
                      OR (t.id = j.id_time2 AND j.gols_time2 < j.gols_time1)
                    THEN 1 ELSE 0 END
            ) AS total_derrotas,
            SUM(
                CASE 
                    WHEN t.id = j.id_time1 THEN COALESCE(j.gols_time1, 0)
                    WHEN t.id = j.id_time2 THEN COALESCE(j.gols_time2, 0)
                    ELSE 0 END
            ) AS gols_pro,
            SUM(
                CASE 
                    WHEN t.id = j.id_time1 THEN COALESCE(j.gols_time2, 0)
                    WHEN t.id = j.id_time2 THEN COALESCE(j.gols_time1, 0)
                    ELSE 0 END
            ) AS gols_contra
        FROM jogos j
        INNER JOIN temporadas temp ON temp.id = j.id_temporada
        INNER JOIN times t ON (t.id = j.id_time1 OR t.id = j.id_time2)
        WHERE temp.id_competicao = ? AND t.brasileiro = 1
    ");
    $stmt_totais->execute([$competicao['id']]);
    $statsGlobais = $stmt_totais->fetch(PDO::FETCH_ASSOC);

    // Top por critério
    $stmt_top = $pdo->prepare("
        SELECT
            t.id, t.nome, t.escudo,
            COUNT(*) AS jogos,
            SUM(
                CASE 
                    WHEN (t.id = j.id_time1 AND j.gols_time1 > j.gols_time2) 
                      OR (t.id = j.id_time2 AND j.gols_time2 > j.gols_time1)
                    THEN 1 ELSE 0 END
            ) AS vitorias,
            SUM(
                CASE WHEN t.id = j.id_time1 THEN COALESCE(j.gols_time1, 0)
                     WHEN t.id = j.id_time2 THEN COALESCE(j.gols_time2, 0)
                     ELSE 0 END
            ) AS gols
        FROM jogos j
        INNER JOIN temporadas temp ON temp.id = j.id_temporada
        INNER JOIN times t ON (t.id = j.id_time1 OR t.id = j.id_time2)
        WHERE temp.id_competicao = ? AND t.brasileiro = 1
        GROUP BY t.id, t.nome, t.escudo
        ORDER BY jogos DESC, vitorias DESC, gols DESC
    ");
    $stmt_top->execute([$competicao['id']]);
    $todosClubes = $stmt_top->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($todosClubes)) {
        $maxJogos = max(array_column($todosClubes, 'jogos'));
        $maxVitorias = max(array_column($todosClubes, 'vitorias'));
        $maxGols = max(array_column($todosClubes, 'gols'));

        $topJogos = array_filter($todosClubes, fn($c) => $c['jogos'] == $maxJogos);
        $topVitorias = array_filter($todosClubes, fn($c) => $c['vitorias'] == $maxVitorias);
        $topGols = array_filter($todosClubes, fn($c) => $c['gols'] == $maxGols);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resultados – <?= htmlspecialchars($competicao['nome']) ?> - Futebol Brasileiro</title>
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-jogos/resultados.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

  <?php include '../estrutura/header2.php'; ?>

  <main>
    <section class="secao-resultados">
      <div class="container">
        <a href="jogos.php" class="voltar-link">← Voltar para Resultados</a>
        <h1>Resultados – <?= htmlspecialchars($competicao['nome']) ?></h1>

        <div class="conteudo-com-coluna">
          <div class="coluna-esquerda">

            <?php if ($isInternacional): ?>
              <h2>Estatísticas dos Clubes Brasileiros</h2>

              <?php if ($statsGlobais): ?>
                <div class="card-estatisticas-geral">
                  <div class="stat-item">
                    <span class="stat-num"><?= $statsGlobais['total_jogos'] ?></span>
                    <span class="stat-label">Jogos</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-num"><?= $statsGlobais['total_vitorias'] ?></span>
                    <span class="stat-label">Vitórias</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-num"><?= $statsGlobais['total_empates'] ?></span>
                    <span class="stat-label">Empates</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-num"><?= $statsGlobais['total_derrotas'] ?></span>
                    <span class="stat-label">Derrotas</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-num"><?= $statsGlobais['gols_pro'] ?></span>
                    <span class="stat-label">Gols Pró</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-num"><?= $statsGlobais['gols_contra'] ?></span>
                    <span class="stat-label">Gols Contra</span>
                  </div>
                </div>
              <?php endif; ?>

              <?php if (!empty($topJogos)): ?>
                <div class="destaque-clube">
                  <h3>Mais Jogos</h3>
                  <div class="clube-info-list">
                    <?php foreach ($topJogos as $clube): ?>
                      <div class="clube-item">
                        <?php if (!empty($clube['escudo'])): ?>
                          <img src="<?= htmlspecialchars('../' . ltrim($clube['escudo'], '/')) ?>"
                               alt="<?= htmlspecialchars($clube['nome']) ?>"
                               class="escudo-medio"
                               onerror="this.style.display='none'">
                        <?php endif; ?>
                        <span><?= htmlspecialchars($clube['nome']) ?></span>
                        <span class="valor"><?= $clube['jogos'] ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <?php if (!empty($topVitorias)): ?>
                <div class="destaque-clube">
                  <h3>Mais Vitórias</h3>
                  <div class="clube-info-list">
                    <?php foreach ($topVitorias as $clube): ?>
                      <div class="clube-item">
                        <?php if (!empty($clube['escudo'])): ?>
                          <img src="<?= htmlspecialchars('../' . ltrim($clube['escudo'], '/')) ?>"
                               alt="<?= htmlspecialchars($clube['nome']) ?>"
                               class="escudo-medio"
                               onerror="this.style.display='none'">
                        <?php endif; ?>
                        <span><?= htmlspecialchars($clube['nome']) ?></span>
                        <span class="valor"><?= $clube['vitorias'] ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <?php if (!empty($topGols)): ?>
                <div class="destaque-clube">
                  <h3>Mais Gols Marcados</h3>
                  <div class="clube-info-list">
                    <?php foreach ($topGols as $clube): ?>
                      <div class="clube-item">
                        <?php if (!empty($clube['escudo'])): ?>
                          <img src="<?= htmlspecialchars('../' . ltrim($clube['escudo'], '/')) ?>"
                               alt="<?= htmlspecialchars($clube['nome']) ?>"
                               class="escudo-medio"
                               onerror="this.style.display='none'">
                        <?php endif; ?>
                        <span><?= htmlspecialchars($clube['nome']) ?></span>
                        <span class="valor"><?= $clube['gols'] ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

            <?php endif; ?>

            <!-- Filtro por ano -->
            <?php if (!empty($temporadasComJogos)): ?>
              <div class="seletor-ano" id="ano">
                <label for="ano-select">Ano:</label>
                <select id="ano-select" onchange="location = this.value + '#ano';">
                  <option value="?slug=<?= urlencode($slug) ?>">Todas as temporadas</option>
                  <?php foreach ($temporadasComJogos as $ano): ?>
                    <option value="?slug=<?= urlencode($slug) ?>&ano=<?= $ano ?>" <?= $ano == $anoSelecionado ? 'selected' : '' ?>>
                      <?= $ano ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Filtro por clube (só em internacionais — agora SEM exigir ano) -->
              <?php if ($isInternacional && !empty($clubesBrasileiros)): ?>
                <div class="seletor-ano">
                  <label for="clube-select">Clube brasileiro:</label>
                  <select id="clube-select" onchange="location = this.value;">
                    <option value="?slug=<?= urlencode($slug) ?><?= $anoSelecionado ? '&ano=' . $anoSelecionado : '' ?>">Todos os clubes</option>
                    <?php foreach ($clubesBrasileiros as $clube): ?>
                      <option value="?slug=<?= urlencode($slug) ?><?= $anoSelecionado ? '&ano=' . $anoSelecionado : '' ?>&clube=<?= $clube['id'] ?>" <?= ($clubeFiltro == $clube['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($clube['nome']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>

              <!-- Resultados -->
              <?php if (!empty($jogos)): ?>
                <div class="lista-jogos">
                  <?php
                  $jogosAgrupados = [];
                  foreach ($jogos as $jogo) {
                      $data = date('d/m/Y', strtotime($jogo['data']));
                      $jogosAgrupados[$data][] = $jogo;
                  }
                  foreach ($jogosAgrupados as $data => $partidas):
                  ?>
                    <div class="dia-jogos">
                      <h3 class="data-jogos"><?= $data ?></h3>
                      <?php foreach ($partidas as $jogo): ?>
                        <div class="jogo-card">
                          <div class="time-esquerdo">
                            <?php if (!empty($jogo['time1_escudo'])): ?>
                              <img src="<?= htmlspecialchars('../' . ltrim($jogo['time1_escudo'], '/')) ?>"
                                   alt="<?= htmlspecialchars($jogo['time1_nome']) ?>"
                                   onerror="this.style.display='none'">
                            <?php endif; ?>
                            <span><?= htmlspecialchars($jogo['time1_nome']) ?></span>
                          </div>
                          <div class="placar">
                            <?php if ($jogo['gols_time1'] !== null && $jogo['gols_time2'] !== null): ?>
                              <div class="placar-principal">
                                <span class="gols"><?= $jogo['gols_time1'] ?></span>
                                <span class="separador">–</span>
                                <span class="gols"><?= $jogo['gols_time2'] ?></span>
                              </div>
                              <?php if ($jogo['penaltis_time1'] !== null && $jogo['penaltis_time2'] !== null): ?>
                                <div class="placar-penaltis">
                                  <span class="penaltis">(<?= $jogo['penaltis_time1'] ?>–<?= $jogo['penaltis_time2'] ?> pen.)</span>
                                </div>
                              <?php endif; ?>
                            <?php else: ?>
                              <span class="em-andamento">–</span>
                            <?php endif; ?>
                          </div>
                          <div class="time-direito">
                            <span><?= htmlspecialchars($jogo['time2_nome']) ?></span>
                            <?php if (!empty($jogo['time2_escudo'])): ?>
                              <img src="<?= htmlspecialchars('../' . ltrim($jogo['time2_escudo'], '/')) ?>"
                                   alt="<?= htmlspecialchars($jogo['time2_nome']) ?>"
                                   onerror="this.style.display='none'">
                            <?php endif; ?>
                          </div>
                        </div>
                        <div class="info-local">
                          <?php if (!empty($jogo['estadio'])): ?>
                            <div class="info-estadio"><?= htmlspecialchars($jogo['estadio']) ?></div>
                          <?php endif; ?>
                          <?php if (!empty($jogo['rodada'])): ?>
                            <div class="info-rodada"><?= htmlspecialchars($jogo['rodada']) ?></div>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p>Nenhum jogo encontrado.</p>
              <?php endif; ?>

            <?php else: ?>
              <p>Nenhuma temporada com jogos registrada.</p>
            <?php endif; ?>

          </div>

          <!-- COLUNA DIREITA: SÓ ANOS -->
          <div class="coluna-direita">
            <h2>Temporadas</h2>
            <?php if (!empty($temporadasComJogos)): ?>
              <ul class="lista-temporadas">
                <li>
                  <a href="?slug=<?= urlencode($slug) ?>#ano" class="<?= $anoSelecionado === null ? 'ativo' : '' ?>">
                    Todas as temporadas
                  </a>
                </li>
                <?php foreach ($temporadasComJogos as $ano): ?>
                  <li>
                    <a href="?slug=<?= urlencode($slug) ?>&ano=<?= $ano ?>#ano" class="<?= $ano == $anoSelecionado ? 'ativo' : '' ?>">
                      <?= $ano ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p>Nenhuma temporada registrada.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include '../estrutura/footer2.php'; ?>

  <div id="voltar-ao-topo">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e1e1e"
        stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 19V5M5 12l7-7 7 7" />
    </svg>
    <span class="tooltip-text">Voltar ao Topo</span>
  </div>

  <script src="js/resultado.js"></script>

</body>
</html>