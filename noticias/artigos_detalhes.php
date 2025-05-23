<?php

require_once '../estrutura/conexaodb.php';

  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

  $stmt = $pdo->prepare("SELECT * FROM artigos WHERE id = ?");
  $stmt->execute([$id]);
  $artigo = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$artigo) {
    die("Artigo não encontrado.");
  }

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($artigo['titulo']) ?> - Futebol Brasileiro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-noticias/artigos_detalhes.css">
</head>
<body>
  
  <?php include '../estrutura/header2.php'; ?>
  
  <main>
    <section class="artigo-detalhe">
      <a href="artigos.php" class="voltar-link">← Voltar para artigos</a>
      <div class="container">
        <h1><?= htmlspecialchars($artigo['titulo']) ?></h1>
        <h4><?= htmlspecialchars($artigo['subtitulo']) ?></h4>
        
        <?php if (!empty($artigo['imagem'])): ?>
          <img src="<?= '../' . htmlspecialchars($artigo['imagem']) ?>" alt="Imagem do Artigo">
        <?php endif; ?>

        <div class="conteudo">
          <?= nl2br(htmlspecialchars($artigo['conteudo'])) ?>
        </div>
      </div>
      <span class="data-publicacao">Publicado em: <?= date('d/m/Y', strtotime($artigo['data_publicacao'])) ?></span>
    </section>
  </main>

  <?php include '../estrutura/footer2.php'; ?>

</body>
</html>
