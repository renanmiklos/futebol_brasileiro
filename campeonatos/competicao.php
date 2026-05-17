<?php

require_once '../estrutura/conexaodb.php';

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function resolverImagem(?string $caminho, string $prefixoLocal = '../'): string {
    $caminho = trim((string) $caminho);

    if ($caminho === '') {
        return $prefixoLocal . 'assets/images/escudo_padrao.png';
    }

    if (preg_match('/^https?:\/\//i', $caminho)) {
        return $caminho;
    }

    if (strpos($caminho, '../') === 0) {
        return $caminho;
    }

    if (strpos($caminho, '/') === 0) {
        return $caminho;
    }

    return $prefixoLocal . ltrim($caminho, '/');
}

/* =========================================
   BUSCAR COMPETIÇÃO
========================================= */

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug === '') {
    http_response_code(404);
    die("Competição não encontrada.");
}

$stmt = $pdo->prepare("SELECT * FROM competicoes WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$competicao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$competicao) {
    http_response_code(404);
    die("Competição não encontrada.");
}

$idCompeticao = (int) $competicao['id'];

/* =========================================
   FOTOS DA COMPETIÇÃO
========================================= */

$stmt_fotos = $pdo->prepare("
    SELECT *
    FROM fotos
    WHERE id_competicao = ?
    ORDER BY data_publicacao DESC, id DESC
");
$stmt_fotos->execute([$idCompeticao]);
$fotos = $stmt_fotos->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   TEMPORADAS
========================================= */

$stmt_temp = $pdo->prepare("
    SELECT *
    FROM temporadas
    WHERE id_competicao = ?
    ORDER BY ano DESC
");
$stmt_temp->execute([$idCompeticao]);
$temporadas = $stmt_temp->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   ESTATÍSTICAS GERAIS
========================================= */

$stmt_stats = $pdo->prepare("
    SELECT
        COUNT(DISTINCT tp.id) AS total_temporadas,
        COUNT(CASE WHEN cl.fase IN ('Camp', '1º') THEN 1 END) AS total_titulos,
        COUNT(DISTINCT CASE WHEN cl.fase IN ('Camp', '1º') THEN cl.id_time END) AS clubes_campeoes
    FROM temporadas tp
    LEFT JOIN classificacao cl ON cl.id_temporada = tp.id
    WHERE tp.id_competicao = ?
");
$stmt_stats->execute([$idCompeticao]);
$estatisticas = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$totalTemporadas = (int) ($estatisticas['total_temporadas'] ?? 0);
$totalTitulos = (int) ($estatisticas['total_titulos'] ?? 0);
$totalClubesCampeoes = (int) ($estatisticas['clubes_campeoes'] ?? 0);

/* =========================================
   ÚLTIMO CAMPEÃO
========================================= */

$stmt_ultimo = $pdo->prepare("
    SELECT
        tm.id,
        tm.nome,
        tm.escudo,
        tp.ano
    FROM classificacao cl
    INNER JOIN temporadas tp ON tp.id = cl.id_temporada
    INNER JOIN times tm ON tm.id = cl.id_time
    WHERE tp.id_competicao = ?
      AND cl.fase IN ('Camp', '1º')
    ORDER BY tp.ano DESC, cl.id DESC
    LIMIT 1
");
$stmt_ultimo->execute([$idCompeticao]);
$ultimoCampeao = $stmt_ultimo->fetch(PDO::FETCH_ASSOC);

/* =========================================
   LISTA DE CAMPEÕES
   Critério:
   1) quantidade de títulos
   2) título mais recente
   3) nome do clube
========================================= */

$stmt_campeoes = $pdo->prepare("
    SELECT
        tm.id,
        tm.nome,
        tm.escudo,
        COUNT(*) AS total_titulos,
        MAX(tp.ano) AS ultimo_titulo,
        GROUP_CONCAT(tp.ano ORDER BY tp.ano DESC SEPARATOR ', ') AS anos
    FROM classificacao cl
    INNER JOIN temporadas tp ON tp.id = cl.id_temporada
    INNER JOIN times tm ON tm.id = cl.id_time
    WHERE tp.id_competicao = ?
      AND cl.fase IN ('Camp', '1º')
    GROUP BY tm.id, tm.nome, tm.escudo
    ORDER BY total_titulos DESC, ultimo_titulo DESC, tm.nome ASC
");
$stmt_campeoes->execute([$idCompeticao]);
$listaCampeoes = $stmt_campeoes->fetchAll(PDO::FETCH_ASSOC);

$totalClubesDiferentesCampeoes = count($listaCampeoes);
$temMaisDeCincoCampeoes = $totalClubesDiferentesCampeoes > 5;
$campeoesExibidos = array_slice($listaCampeoes, 0, 5);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= e($competicao['nome']) ?> - Futebol Brasileiro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-campeonatos/competicao.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
  <section class="secao-competicao">
    <div class="container">

      <a href="campeonatos.php" class="voltar-link">← Voltar para Campeonatos</a>

      <div class="competicao-layout-principal">

        <!-- COLUNA 1: APRESENTAÇÃO -->
        <section class="coluna-apresentacao">
          <div class="hero-competicao">
            <?php if (!empty($competicao['tipo'])): ?>
              <span class="competicao-tipo">
                <?= e($competicao['amistoso'] ? 'Amistoso' : $competicao['tipo']) ?>
              </span>
            <?php endif; ?>

            <h1><?= e($competicao['nome']) ?></h1>

            <?php if (!empty($competicao['descricao'])): ?>
              <div class="descricao-competicao">
                <?= nl2br(e($competicao['descricao'])) ?>
              </div>
            <?php else: ?>
              <p class="sem-dado">
                Esta competição ainda não possui descrição cadastrada.
              </p>
            <?php endif; ?>
          </div>

          <?php if (!empty($fotos)): ?>
            <div class="box-lateral galeria-fotos galeria-principal">
              <h2>Galeria</h2>

              <div class="grid-fotos-competicao">
                <?php foreach ($fotos as $foto): ?>
                  <?php
                    $imagemSrc = resolverImagem($foto['caminho_imagem'] ?? null, '../');
                    $tituloFoto = $foto['titulo'] ?? $competicao['nome'];
                  ?>

                  <div class="foto-item">
                    <img
                      class="imagem-item"
                      src="<?= e($imagemSrc) ?>"
                      alt="<?= e($tituloFoto) ?>"
                      loading="lazy"
                      onerror="this.style.display='none'"
                    >

                    <?php if (!empty($foto['titulo'])): ?>
                      <p class="legenda"><?= e($foto['titulo']) ?></p>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </section>

        <!-- COLUNA 2: DADOS E CAMPEÕES -->
        <section class="coluna-dados">

          <div class="resumo-competicao compacto">
            <div class="resumo-card">
              <span class="resumo-numero"><?= $totalTemporadas ?></span>
              <span class="resumo-label">Temporadas</span>
            </div>

            <div class="resumo-card">
              <span class="resumo-numero"><?= $totalClubesCampeoes ?></span>
              <span class="resumo-label">Clubes campeões</span>
            </div>
          </div>

          <div class="cards-campeoes-competicao">

            <?php if ($ultimoCampeao): ?>
              <article class="card-campeao-info">
                <span class="card-campeao-subtitulo">Último campeão</span>

                <a class="campeao-link" href="../times/detalhes_time.php?id=<?= (int) $ultimoCampeao['id'] ?>">
                  <img
                    src="<?= e(resolverImagem($ultimoCampeao['escudo'] ?? null)) ?>"
                    alt="Escudo de <?= e($ultimoCampeao['nome']) ?>"
                    loading="lazy"
                    onerror="this.src='../assets/images/escudo_padrao.png'"
                  >

                  <div>
                    <strong><?= e($ultimoCampeao['nome']) ?></strong>
                    <small>Campeão em <?= (int) $ultimoCampeao['ano'] ?></small>
                  </div>
                </a>
              </article>
            <?php endif; ?>

            <article class="card-campeao-info lista-campeoes-info">
              <span class="card-campeao-subtitulo">
                <?= $temMaisDeCincoCampeoes ? 'Maiores campeões' : 'Campeões' ?>
              </span>

              <?php if ($temMaisDeCincoCampeoes): ?>
                <p class="aviso-campeoes">
                  Exibindo os 5 maiores campeões desta competição.
                </p>
              <?php endif; ?>

              <?php if (!empty($campeoesExibidos)): ?>
                <div class="lista-campeoes-competicao">
                  <?php foreach ($campeoesExibidos as $index => $campeao): ?>
                    <a class="campeao-lista-link" href="../times/detalhes_time.php?id=<?= (int) $campeao['id'] ?>">
                      <span class="posicao-campeao"><?= $index + 1 ?>º</span>

                      <img
                        src="<?= e(resolverImagem($campeao['escudo'] ?? null)) ?>"
                        alt="Escudo de <?= e($campeao['nome']) ?>"
                        loading="lazy"
                        onerror="this.src='../assets/images/escudo_padrao.png'"
                      >

                      <span class="dados-campeao-lista">
                        <strong><?= e($campeao['nome']) ?></strong>
                        <small>
                          <?= (int) $campeao['total_titulos'] ?>
                          <?= ((int) $campeao['total_titulos'] === 1) ? 'título' : 'títulos' ?>
                          · último em <?= (int) $campeao['ultimo_titulo'] ?>
                        </small>
                      </span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="sem-dado">Ainda não há campeões cadastrados para esta competição.</p>
              <?php endif; ?>
            </article>

          </div>
        </section>

        <!-- COLUNA 3: TEMPORADAS -->
        <aside class="coluna-direita">

          <div class="box-lateral">
            <h2>Temporadas</h2>

            <?php if (!empty($temporadas)): ?>
              <ul class="lista-temporadas">
                <?php foreach ($temporadas as $temp): ?>
                  <li>
                    <a href="temporada.php?id_competicao=<?= $idCompeticao ?>&ano=<?= (int) $temp['ano'] ?>">
                      <?= (int) $temp['ano'] ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="sem-dado">Temporadas ainda não cadastradas.</p>
            <?php endif; ?>
          </div>

        </aside>

      </div>

    </div>
  </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>