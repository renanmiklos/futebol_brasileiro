<?php
require_once '../estrutura/conexaodb.php';

// Captura a categoria da URL, se houver
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;

// Prepara a consulta com ou sem filtro de categoria
if ($categoria && $categoria !== 'Ver Todos') {
    $stmt = $pdo->prepare("SELECT id, titulo, subtitulo, imagem, data_publicacao FROM artigos WHERE categoria = :categoria ORDER BY data_publicacao DESC");
    $stmt->bindParam(':categoria', $categoria);
    $stmt->execute();
} else {
    // Consulta para exibir todos os artigos, ordenados do mais recente para o mais antigo
    $stmt = $pdo->query("SELECT id, titulo, subtitulo, imagem, data_publicacao FROM artigos ORDER BY data_publicacao DESC");
}

$ultimosArtigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artigos - Futebol Brasileiro</title>
    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-noticias/artigos.css">
</head>
<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-artigos">
        <div class="container">
            <!-- Menu Lateral -->
            <aside class="menu-lateral">
                <h2>Categorias</h2>
                <ul>
                    <li><a href="artigos.php?categoria=Campeonatos">Campeonatos</a></li>
                    <li><a href="artigos.php?categoria=Times">Times</a></li>
                    <li><a href="artigos.php?categoria=Jogadores">Jogadores</a></li>
                    <li><a href="artigos.php?categoria=Estádios">Estádios</a></li>
                    <li><a href="artigos.php?categoria=Ver Todos">Ver Todos</a></li>
                </ul>
            </aside>

            <!-- Conteúdo Principal -->
            <div class="conteudo-artigos">
                <h1>Artigos Históricos do Futebol Brasileiro</h1>
                <?php if ($categoria && $categoria !== 'Ver Todos'): ?>
                    <p>Exibindo artigos da categoria: <strong><?= htmlspecialchars($categoria) ?></strong></p>
                <?php elseif ($categoria === 'Ver Todos'): ?>
                    <p>Exibindo todos os artigos disponíveis.</p>
                <?php else: ?>
                    <p>O futebol brasileiro é repleto de momentos marcantes, personagens lendários e histórias que merecem ser contadas.</p>
                <?php endif; ?>

                <h2><?= $categoria && $categoria !== 'Ver Todos' ? 'Artigos da Categoria' : 'Últimos Artigos' ?></h2>
                <div class="grade-artigos">
                    <?php if (!empty($ultimosArtigos)): ?>
                        <?php foreach ($ultimosArtigos as $artigo): ?>
                            <a class="card-artigo" href="artigos_detalhes.php?id=<?= htmlspecialchars($artigo['id']) ?>">
                                <?php
                                    $imagem = htmlspecialchars($artigo['imagem']);
                                    $caminhoImagem = (preg_match('/^https?:\/\//', $imagem)) ? $imagem : '../' . $imagem;
                                ?>
                                <img src="<?= $caminhoImagem ?>" alt="<?= htmlspecialchars($artigo['titulo']) ?>">

                                <div class="info">
                                    <h3><?= htmlspecialchars($artigo['titulo']) ?></h3>
                                    <p><?= htmlspecialchars($artigo['subtitulo']) ?></p>
                                    <span><?= date('d/m/Y', strtotime($artigo['data_publicacao'])) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Nenhum artigo encontrado<?= $categoria && $categoria !== 'Ver Todos' ? ' para esta categoria' : '' ?>.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>
