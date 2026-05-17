<?php

require_once '../estrutura/conexaodb.php';

/* =========================================================
   FUNÇÕES AUXILIARES
========================================================= */

function traduzFase($fase) {
    $fases = [
        'Camp' => 'Campeão',
        'Vice' => 'Vice-campeão',
        'SF' => 'Semifinal',
        'QF' => 'Quartas de Final',
        'OF' => 'Oitavas de Final',
        '4F' => 'Quarta Fase',
        '16avos' => '16 avos',
        '3F' => 'Terceira Fase',
        '32avos' => '32 avos',
        '2F' => 'Segunda Fase',
        '64avos' => '64 avos',
        '1F' => 'Primeira Fase',
        'Principal' => 'Principal',
        'Regional' => 'Regional',
        'Eliminator' => 'Eliminatória',
        'Grupo' => 'Fase de Grupos',
        'FaseDeGrupos' => 'Fase de Grupos',
        'Playoff' => 'Playoff',
        'Pre3' => 'Pré 3',
        'Pre2' => 'Pré 2',
        'Pre1' => 'Pré 1',
        'Pre' => 'Pré',
        'Reb' => 'Rebaixado',
        '1' => '1º',
        '2' => '2º',
        '1º' => '1º',
        '2º' => '2º',
        '3º' => '3º',
        '4º' => '4º',
        '5º' => '5º',
        '6º' => '6º',
        '7º' => '7º',
        '8º' => '8º',
        '9º' => '9º',
        '10º' => '10º',
        '11º' => '11º',
        '12º' => '12º',
        '13º' => '13º',
        '14º' => '14º',
        '15º' => '15º',
        '16º' => '16º',
        '17º' => '17º',
        '18º' => '18º',
        '19º' => '19º',
        '20º' => '20º',
        '21º' => '21º',
        '22º' => '22º',
        '23º' => '23º',
        '24º' => '24º',
        '25º' => '25º'
    ];

    return $fases[$fase] ?? $fase;
}

function resolverCaminhoImagem($caminho) {
    if (empty($caminho)) {
        return '';
    }

    if (preg_match('/^https?:\/\//', $caminho)) {
        return htmlspecialchars($caminho, ENT_QUOTES, 'UTF-8');
    }

    return '../' . htmlspecialchars(ltrim($caminho, '/'), ENT_QUOTES, 'UTF-8');
}

/* =========================================================
   PARÂMETROS
========================================================= */

$id_competicao = isset($_GET['id_competicao']) ? intval($_GET['id_competicao']) : 0;
$ano = isset($_GET['ano']) ? intval($_GET['ano']) : 0;

if ($id_competicao <= 0 || $ano <= 0) {
    die("Temporada não encontrada.");
}

/* =========================================================
   BUSCAR COMPETIÇÃO
========================================================= */

$stmtComp = $pdo->prepare("SELECT * FROM competicoes WHERE id = ?");
$stmtComp->execute([$id_competicao]);
$competicao = $stmtComp->fetch(PDO::FETCH_ASSOC);

if (!$competicao) {
    die("Competição não encontrada.");
}

/* =========================================================
   BUSCAR TEMPORADA
========================================================= */

$stmtTemp = $pdo->prepare("
    SELECT *
    FROM temporadas
    WHERE id_competicao = ?
      AND ano = ?
    LIMIT 1
");
$stmtTemp->execute([$id_competicao, $ano]);
$temporada = $stmtTemp->fetch(PDO::FETCH_ASSOC);

if (!$temporada) {
    die("Temporada não encontrada.");
}

/* =========================================================
   FOTO DA TEMPORADA
========================================================= */

$stmtFoto = $pdo->prepare("
    SELECT caminho_imagem, titulo
    FROM fotos
    WHERE id_temporada = ?
    ORDER BY data_publicacao DESC, id DESC
    LIMIT 1
");
$stmtFoto->execute([$temporada['id']]);
$foto = $stmtFoto->fetch(PDO::FETCH_ASSOC);

/* =========================================================
   CLASSIFICAÇÃO
========================================================= */

$stmtClass = $pdo->prepare("
    SELECT 
        c.fase,
        t.id AS id_time,
        t.nome,
        t.escudo
    FROM classificacao c
    JOIN times t ON c.id_time = t.id
    WHERE c.id_temporada = ?
      AND c.nacional = 1
    ORDER BY  
      CASE c.fase
        WHEN 'Camp' THEN 1
        WHEN '1º' THEN 1
        WHEN 'Vice' THEN 2
        WHEN '2º' THEN 2
        WHEN '3º' THEN 3
        WHEN '4º' THEN 4
        WHEN '5º' THEN 5
        WHEN '6º' THEN 6
        WHEN '7º' THEN 7
        WHEN '8º' THEN 8
        WHEN '9º' THEN 9
        WHEN '10º' THEN 10
        WHEN '11º' THEN 11
        WHEN '12º' THEN 12
        WHEN '13º' THEN 13
        WHEN '14º' THEN 14
        WHEN '15º' THEN 15
        WHEN '16º' THEN 16
        WHEN '17º' THEN 17
        WHEN '18º' THEN 18
        WHEN '19º' THEN 19
        WHEN '20º' THEN 20
        WHEN '21º' THEN 21
        WHEN '22º' THEN 22
        WHEN '23º' THEN 23
        WHEN '24º' THEN 24
        WHEN '25º' THEN 25
        WHEN 'SF' THEN 26
        WHEN 'QF' THEN 27
        WHEN 'OF' THEN 28
        WHEN '4F' THEN 29
        WHEN '16avos' THEN 30
        WHEN '3F' THEN 31
        WHEN '32avos' THEN 32
        WHEN '64avos' THEN 33
        WHEN '2F' THEN 34
        WHEN 'Principal' THEN 35
        WHEN 'Playoff' THEN 36
        WHEN 'Grupo' THEN 37
        WHEN 'FaseDeGrupos' THEN 37
        WHEN 'Regional' THEN 38
        WHEN 'Eliminator' THEN 39
        WHEN '1F' THEN 40
        WHEN 'Pre3' THEN 41
        WHEN 'Pre2' THEN 42
        WHEN 'Pre1' THEN 43
        WHEN 'Pre' THEN 44
        WHEN 'Reb' THEN 45
        ELSE 99
      END,
      t.nome ASC
");
$stmtClass->execute([$temporada['id']]);
$classificacao = $stmtClass->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   DESTAQUES: CAMPEÃO E VICE
========================================================= */

$campeao = null;
$vice = null;

foreach ($classificacao as $linha) {
    if (!$campeao && in_array($linha['fase'], ['Camp', '1º'], true)) {
        $campeao = $linha;
    }

    if (!$vice && in_array($linha['fase'], ['Vice', '2º'], true)) {
        $vice = $linha;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($competicao['nome'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($ano, ENT_QUOTES, 'UTF-8') ?> | Futebol Brasileiro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-campeonatos/temporada.css">
</head>
<body>

<?php include '../estrutura/header2.php'; ?>

<main>
  <section class="secao-temporada">
    <div class="container">

      <a href="competicao.php?slug=<?= urlencode($competicao['slug']) ?>" class="voltar-link">
        ← Voltar para <?= htmlspecialchars($competicao['nome'], ENT_QUOTES, 'UTF-8') ?>
      </a>

      <header class="cabecalho-temporada">
        <div>
          <span class="etiqueta-temporada">Temporada</span>
          <h1><?= htmlspecialchars($competicao['nome'], ENT_QUOTES, 'UTF-8') ?> – <?= htmlspecialchars($ano, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>

        <?php if (!empty($competicao['tipo'])): ?>
          <span class="tipo-competicao">
            <?= htmlspecialchars($competicao['tipo'], ENT_QUOTES, 'UTF-8') ?>
          </span>
        <?php endif; ?>
      </header>

      <div class="layout-temporada">

        <!-- COLUNA ESQUERDA -->
        <aside class="coluna-resumo">

          <?php if (!empty($foto)): ?>
            <div class="foto-temporada">
              <img
                src="<?= resolverCaminhoImagem($foto['caminho_imagem']) ?>"
                alt="<?= htmlspecialchars($foto['titulo'] ?: $competicao['nome'], ENT_QUOTES, 'UTF-8') ?>"
                class="imagem-temporada"
              >
              <?php if (!empty($foto['titulo'])): ?>
                <p class="legenda"><?= htmlspecialchars($foto['titulo'], ENT_QUOTES, 'UTF-8') ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div class="cards-destaque-temporada">
            <?php if ($campeao): ?>
              <a href="../times/detalhes_time.php?id=<?= (int) $campeao['id_time'] ?>" class="card-destaque clube-campeao">
                <span class="card-label">Campeão</span>

                <?php if (!empty($campeao['escudo'])): ?>
                  <img
                    src="<?= resolverCaminhoImagem($campeao['escudo']) ?>"
                    alt="Escudo de <?= htmlspecialchars($campeao['nome'], ENT_QUOTES, 'UTF-8') ?>"
                  >
                <?php endif; ?>

                <strong><?= htmlspecialchars($campeao['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
              </a>
            <?php endif; ?>

            <?php if ($vice): ?>
              <a href="../times/detalhes_time.php?id=<?= (int) $vice['id_time'] ?>" class="card-destaque clube-vice">
                <span class="card-label">Vice-campeão</span>

                <?php if (!empty($vice['escudo'])): ?>
                  <img
                    src="<?= resolverCaminhoImagem($vice['escudo']) ?>"
                    alt="Escudo de <?= htmlspecialchars($vice['nome'], ENT_QUOTES, 'UTF-8') ?>"
                  >
                <?php endif; ?>

                <strong><?= htmlspecialchars($vice['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
              </a>
            <?php endif; ?>
          </div>

        </aside>

        <!-- COLUNA DIREITA -->
        <div class="coluna-conteudo">

          <?php if (!empty($temporada['descricao'])): ?>
            <section class="descricao">
              <h2>Resumo da edição</h2>
              <p><?= nl2br(htmlspecialchars($temporada['descricao'], ENT_QUOTES, 'UTF-8')) ?></p>
            </section>
          <?php endif; ?>

          <section class="bloco-classificacao">
            <div class="titulo-bloco">
              <h2>Classificação</h2>
              <span><?= count($classificacao) ?> <?= count($classificacao) === 1 ? 'clube registrado' : 'clubes registrados' ?></span>
            </div>

            <?php if (!empty($classificacao)): ?>
              <div class="tabela-wrapper">
                <table class="tabela-classificacao">
                  <thead>
                    <tr>
                      <th>Fase</th>
                      <th>Clube</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($classificacao as $linha): ?>
                      <tr>
                        <td class="fase">
                          <?= htmlspecialchars(traduzFase($linha['fase']), ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="clube">
                          <a href="../times/detalhes_time.php?id=<?= (int) $linha['id_time'] ?>">
                            <?php if (!empty($linha['escudo'])): ?>
                              <img
                                src="<?= resolverCaminhoImagem($linha['escudo']) ?>"
                                alt="Escudo de <?= htmlspecialchars($linha['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                class="escudo-clube"
                              >
                            <?php endif; ?>

                            <span><?= htmlspecialchars($linha['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="mensagem-vazia">Nenhum clube brasileiro classificado nessa edição.</p>
            <?php endif; ?>
          </section>

        </div>
      </div>

    </div>
  </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>