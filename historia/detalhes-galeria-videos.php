<?php

require_once '../estrutura/conexaodb.php';

// Pegar o ID do vídeo da URL
$video_id = $_GET['video_id'] ?? 0;

if ($video_id <= 0) {
    header("Location: galeria-videos.php");
    exit;
}

// Buscar os dados do vídeo no banco
$stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->execute([$video_id]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    header("Location: galeria-videos.php");
    exit;
}

// Função para extrair o ID do YouTube
function extract_youtube_id($url) {
    $url = parse_url($url, PHP_URL_PATH);
    $parts = explode('/', trim($url, '/'));
    return end($parts);
}

// Extrair o ID do vídeo do campo 'url'
$youtube_id = extract_youtube_id($video['url']);

// Verifica se o ID foi extraído corretamente
if (empty($youtube_id)) {
    die("Erro ao carregar o vídeo. O ID do YouTube não foi encontrado.");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($video['titulo']) ?> - Galeria de Vídeos</title>
    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-historia/detalhes-galeria-videos.css">
</head>
<body>

<?php include '../estrutura/header2.php'; ?>

<main class="container-fotos">
    <h1 class="titulo-galeria"><?= htmlspecialchars($video['titulo']) ?></h1>
    <!-- Descrição do vídeo -->
    <p class="descricao-video">
        <?= nl2br(htmlspecialchars($video['descricao'])) ?>
    </p>

    <!-- Container do vídeo em destaque -->
    <div class="video-container">
        <iframe width="100%" height="315"
            src="https://www.youtube.com/embed/<?= $youtube_id ?>?rel=0&autoplay=1"
            title="<?= htmlspecialchars($video['titulo']) ?>"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
        </iframe>
    </div>

    <!-- Botão de voltar -->
    <a href="galeria-videos.php" class="botao voltar-galeria">Voltar à Galeria de Vídeos</a>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>