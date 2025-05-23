<?php
$categoriaSelecionada = isset($_GET['tipo']) ? $_GET['tipo'] : null;

$categorias = ['Internacionais', 'Nacionais'];

$estatisticas = [];

if ($categoriaSelecionada === 'Nacionais') {
    $estatisticas = [
        'Era da Taça Brasil (1959 - 1968)',
        'Era do Torneio Roberto Gomes Pedrosa (1967 - 1970)',
        'Brasileirão (1971 - ...)',
        'Brasileirão Pontos Corridos (2003 - ...)',
        'Brasileirão Unificado (1959 - ...)'
    ];
} elseif ($categoriaSelecionada === 'Internacionais') {
    $estatisticas = [
      'Copa do Mundo de Clubes (2000 - 2024)',
      'Copa Intercontinental (1960 - 1999)',
      'Libertadores da América (1960 - ...)',
      'Copa Sul-Americana (2002 - ...)'
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Estatísticas - Futebol Brasileiro</title>
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="../estatisticas//css-estisticas/estatisticas.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

  <?php include '../estrutura/header2.php'; ?>
  
  <main>
    <section class="secao-estatisticas">
      <div class="container">
        <aside class="menu-lateral">
          <h2>Tipos de Estatísticas</h2>
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

        <div class="conteudo-estatisticas">
          <h1>Estatísticas</h1>
          <p>Explore estatísticas históricas do futebol brasileiro, divididas por categorias.</p>

          <?php if ($categoriaSelecionada): ?>
            <h2><?= htmlspecialchars($categoriaSelecionada) ?></h2>
            <div class="lista">
              <div class="lista-estatisticas">
                <?php if (!empty($estatisticas)): ?>
                  <ul>
                    <?php foreach ($estatisticas as $estat): ?>
                      <li>
                        <a href="estatisticas-comp.php?item=<?= urlencode($estat) ?>">
                          <span><?= htmlspecialchars($estat) ?></span>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <p>Nenhuma estatística disponível nesta categoria no momento.</p>
                <?php endif; ?>
              </div>
            </div>  
          <?php else: ?>
            <p>Escolha uma categoria no menu ao lado para ver as estatísticas disponíveis.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <?php include '../estrutura/footer2.php'; ?>

</body>
</html>
