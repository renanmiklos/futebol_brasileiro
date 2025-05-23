<?php

require_once '../estrutura/conexaodb.php';

$regioes = [
  'Sudeste' => ['SP', 'RJ', 'MG', 'ES'],
  'Sul' => ['RS', 'PR', 'SC'],
  'Nordeste' => ['BA', 'PE', 'CE', 'RN', 'MA', 'PB', 'PI', 'AL', 'SE'],
  'Centro-Oeste' => ['DF', 'GO', 'MT', 'MS'],
  'Norte' => ['AM', 'PA', 'AC', 'RO', 'RR', 'AP', 'TO'],
  'Times Extintos' => ['AC','AL', 'AP','AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PR', 'PB', 'PE', 'PI', 
  'RN', 'RS', 'RJ', 'RO', 'RR', 'SC', 'SP', 'SE','TO'],
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
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-times/times.css">
</head>
<body>

<?php include '../estrutura/header2.php'; ?>

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
                  <a href="times_estado.php?uf=<?= htmlspecialchars($uf) ?><?= $regiaoSelecionada === 'Times Extintos' ? '&extintos=1' : '' ?>">
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

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>
