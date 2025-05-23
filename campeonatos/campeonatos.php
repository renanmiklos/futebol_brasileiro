<?php

require_once '../estrutura/conexaodb.php';

$categoriaSelecionada = isset($_GET['tipo']) ? $_GET['tipo'] : null;

$categorias = [
  'Internacional',   'Nacional',  'Regional',  'Estadual',
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
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-campeonatos/campeonatos.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

  <?php include '../estrutura/header2.php'; ?>
  
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
          <h2>Artigos</h2>
            <ul>
               <li><a href="../noticias/artigos.php?categoria=Campeonatos" class="<?= isset($_GET['categoria']) && $_GET['categoria'] === 'Campeonatos' ? 'ativo' : '' ?>">Campeonatos</a></li>
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

  <?php include '../estrutura/footer2.php'; ?>

</body>
</html>
