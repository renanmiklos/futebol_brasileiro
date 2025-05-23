<?php

require_once '../estrutura/conexaodb.php';

  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
  $stmt->execute([$id]);
  $noticia = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= $noticia ? htmlspecialchars($noticia['titulo']) : 'Notícia' ?> - Futebol Brasileiro</title>
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-noticias/detalhes_noticia.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

  <?php include '../estrutura/header2.php'; ?>

  <main class="secao-detalhe-noticia">
    <div class="container">
      <?php if ($noticia): ?>
        <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
        <h3><?= htmlspecialchars($noticia['subtitulo']) ?></h3>
        <span class="data"><?= date('d/m/Y', strtotime($noticia['data_publicacao'])) ?></span>
        <?php 
            $src_imagem = htmlspecialchars($noticia['imagem']);
            // Verifica se o caminho começa com "assets/images/"
            if (strpos($src_imagem, 'assets/images/') === 0) {
                // Adiciona "../" apenas para caminhos locais
                $src_imagem = '../' . $src_imagem;
            }
        ?>
        <img src="<?= $src_imagem ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>">
        <p><?= nl2br(htmlspecialchars($noticia['conteudo'])) ?></p>
        <p><a href="noticias.php" class="voltar-link">← Voltar para Notícias</a></p>
      <?php else: ?>
        <p>Notícia não encontrada.</p>
      <?php endif; ?>
    </div>
  </main>

  <?php include '../estrutura/footer2.php'; ?>

</body>
</html>
