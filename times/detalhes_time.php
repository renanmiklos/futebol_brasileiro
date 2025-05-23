<?php

require_once '../estrutura/conexaodb.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT * FROM times WHERE id = ?");
$stmt->execute([$id]);
$time = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$time) 
  die("Time não encontrado.")
;

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($time['nome']) ?> - Detalhes | Futebol Brasileiro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-times/detalhes_time.css">
</head>
<body>
  
  <?php include '../estrutura/header2.php'; ?>
  
  <main>
    <section class="detalhes-time">
      <div class="container">
        <a class="voltar-link" href="times_estado.php?uf=<?= urlencode($time['estado']) ?>">← Voltar para o estado</a>
        
        <div class="perfil-time">
          <img class="escudo" src="<?= '../' . htmlspecialchars($time['escudo']) ?>" alt="Escudo de <?= htmlspecialchars($time['nome']) ?>">
          <div class="info">
            <h1><?= htmlspecialchars($time['nome_completo']) ?></h1>
            <p><strong>Fundação:</strong> <?= date('d/m/Y', strtotime($time['fundacao'])) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($time['estado']) ?></p>
            <p><strong>Cidade:</strong> <?= htmlspecialchars($time['cidade']) ?></p>
            <p><strong>Estádio:</strong> <?= htmlspecialchars($time['estadio']) ?> (<?= $time['capacidade'] ?>)</p>
            <p><strong>Extinto:</strong> <?= $time['extinto'] ? 'Sim' : 'Não' ?></p>
          </div>
        </div>

        <div class="descricao-time">
          <h2>História</h2>
          <p><?= nl2br(htmlspecialchars($time['historia'])) ?></p>

          <h2>Títulos</h2>
          <p><?= nl2br(htmlspecialchars($time['titulos'])) ?></p>
        </div>
      </div>
    </section>
  </main>

  <?php include '../estrutura/footer2.php'; ?>

</body>
</html>
