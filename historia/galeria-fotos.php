<?php

require_once '../estrutura/conexaodb.php';

// Consulta para buscar os bancos de fotos junto com a primeira imagem
$sql = "
    SELECT b.id AS banco_id, b.nome, b.descricao, f.caminho_imagem
    FROM bancos_de_fotos b
    LEFT JOIN (
        SELECT f1.banco_id, f1.caminho_imagem
        FROM fotos f1
        WHERE f1.id IN (
            SELECT MIN(f2.id)
            FROM fotos f2
            GROUP BY f2.banco_id
        )
    ) f ON b.id = f.banco_id
    ORDER BY b.data_criacao DESC
";

$stmt = $pdo->query($sql);
$bancos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Galeria de Fotos - Futebol Brasileiro</title>
    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-historia/galeria-fotos.css">
</head>
<body>

<?php include '../estrutura/header2.php'; ?>

<!-- Conteúdo Principal -->
<main>
    <a href="historia.php" class="voltar-link">← Voltar para História</a>
    <section class="galeria-fotos">
        <div class="galeria-container">
            <h1>Galeria de Fotos</h1>
            <p>Aqui estão registrados momentos marcantes da história do futebol brasileiro.</p>

            <div class="galeria-lista">
                <?php if (!empty($bancos)): ?>
                    <?php foreach ($bancos as $banco): ?>
                        <div class="galeria-card">
                            <h2><?= htmlspecialchars($banco['nome']) ?></h2>

                            <?php if (!empty($banco['caminho_imagem'])): ?>
                                <img src="<?= htmlspecialchars($banco['caminho_imagem']) ?>" alt="<?= htmlspecialchars($banco['nome']) ?>" class="imagem-preview">
                            <?php endif; ?>

                            <p><?= htmlspecialchars($banco['descricao']) ?></p>
                            <a href="detalhes-galeria-fotos.php?banco_id=<?= $banco['banco_id'] ?>" class="botao">Ver Fotos</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhum álbum foi encontrado no momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>