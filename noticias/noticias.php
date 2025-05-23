<?php

require_once '../estrutura/conexaodb.php';

  $stmt = $pdo->query("SELECT * FROM noticias ORDER BY data_publicacao DESC");
  $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Notícias - Futebol Brasileiro</title>
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-noticias/noticias.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

  <?php include '../estrutura/header2.php'; ?>

  <main class="secao-noticias">
    <div class="container">
      <h1>Últimas Notícias</h1>
      <div class="grade-noticias">
        <?php foreach ($noticias as $noticia): ?>
          <a class="card-noticia" href="detalhes_noticia.php?id=<?= $noticia['id'] ?>">
            <?php 
                $src_imagem = htmlspecialchars($noticia['imagem']);
                // Verifica se o caminho começa com "assets/images/"
                if (strpos($src_imagem, 'assets/images/') === 0) {
                    // Adiciona "../" apenas para caminhos locais
                    $src_imagem = '../' . $src_imagem;
                }
            ?>
            <img src="<?= $src_imagem ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>">
            <div class="info">
              <h3 style="letter-spacing: 1.1px;"><?= htmlspecialchars($noticia['titulo']) ?></h3>
              <p><?= htmlspecialchars($noticia['subtitulo']) ?></p>
              <span><?= date('d/m/Y', strtotime($noticia['data_publicacao'])) ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </main>

  <?php include '../estrutura/footer2.php'; ?>

</body>
</html>
