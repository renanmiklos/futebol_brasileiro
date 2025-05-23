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
            SELECT banco_id, MAX(data_publicacao) as ultima_data
            FROM fotos
            GROUP BY banco_id
        ) ultimas
        ON f.banco_id = ultimas.banco_id AND f.data_publicacao = ultimas.ultima_data
        ORDER BY f.data_publicacao DESC
    ");
    $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt_videos = $pdo->prepare("SELECT * FROM videos ORDER BY data_publicacao DESC LIMIT 1");
    $stmt_videos->execute();
    $videos = $stmt_videos->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="assets/css/hist_index.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <?php include 'estrutura/header.php'; ?>
    
    <main>

        <?php include 'noticias/index_noticias.php'; ?>

        <?php include 'historia/index_historia.php'; ?>

    </main>

    <?php include 'estrutura/footer.php'; ?>

    <script src = "assets/js/index.js"></script>

</body>
</html>