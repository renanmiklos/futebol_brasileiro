<?php
require_once 'estrutura/conexaodb.php';

$stmt_principal = $pdo->prepare("SELECT * FROM noticias WHERE principal = 1 ORDER BY data_publicacao DESC LIMIT 1");
$stmt_principal->execute();
$noticia_principal = $stmt_principal->fetch(PDO::FETCH_ASSOC);

$stmt_cards = $pdo->prepare("SELECT * FROM noticias WHERE principal = 0 ORDER BY data_publicacao DESC LIMIT 3");
$stmt_cards->execute();
$noticias_cards = $stmt_cards->fetchAll(PDO::FETCH_ASSOC);

$stmt_artigos = $pdo->prepare("SELECT * FROM artigos ORDER BY data_publicacao DESC LIMIT 3");
$stmt_artigos->execute();
$artigos = $stmt_artigos->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT f.*
    FROM fotos f
    INNER JOIN (
        SELECT banco_id, MAX(data_publicacao) AS ultima_data
        FROM fotos
        GROUP BY banco_id
    ) ultimas
    ON f.banco_id = ultimas.banco_id 
    AND f.data_publicacao = ultimas.ultima_data
    ORDER BY f.data_publicacao DESC
");
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futebol Brasileiro</title>

    <link rel="stylesheet" href="estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/index_noticias.css">
    <link rel="stylesheet" href="assets/css/index_historia.css">
    <link rel="stylesheet" href="assets/css/index_times.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'estrutura/header.php'; ?>

    <main>
        <?php include 'noticias/index_noticias.php'; ?>
        <?php include 'historia/index_historia.php'; ?>
        <?php include 'times/index_times.php'; ?>

        <div style="display: flex; justify-content: center; gap: 50px; margin: 30px 0;">
            <a href="times/times.php" class="botao">Ver todos os Times</a>
            <a href="estatisticas/ranking-introducao.php" class="botao">Ver Ranking Completo</a>
            <a href="estatisticas/estatisticas.php" class="botao">Ver mais Estatísticas</a>
        </div>
    </main>

    <?php include 'estrutura/footer.php'; ?>

    <div id="voltar-ao-topo">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e1e1e"
            stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 19V5M5 12l7-7 7 7" />
        </svg>
        <span class="tooltip-text">Voltar ao Topo</span>
    </div>

    <script src="assets/js/index.js"></script>
</body>
</html>