<?php

require_once '../estrutura/conexaodb.php';

$banco_id = $_GET['banco_id'] ?? 0;

// Buscar o nome do banco
$stmt = $pdo->prepare("SELECT nome FROM bancos_de_fotos WHERE id = ?");
$stmt->execute([$banco_id]);
$banco = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar até 6 fotos desse banco
$stmt = $pdo->prepare("SELECT * FROM fotos WHERE banco_id = ? ORDER BY id LIMIT 6");
$stmt->execute([$banco_id]);
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($banco['nome']) ?> - Galeria de Fotos</title>
    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-historia/detalhes-galeria-fotos.css">
</head>
<body>
    
    <?php include '../estrutura/header2.php'; ?>

    <main class="container-fotos">
    <h1 class="titulo-galeria"><?= htmlspecialchars($banco['nome']) ?></h1>

    <div class="grid-fotos">
        <?php foreach ($fotos as $foto): ?>
            <div class="foto-slot">
                <img src="<?= htmlspecialchars($foto['caminho_imagem']) ?>" 
                    alt="<?= htmlspecialchars($foto['titulo']) ?>">
                <p><?= htmlspecialchars($foto['titulo']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="galeria-fotos.php" class="botao voltar-galeria">Voltar à Galeria</a>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>