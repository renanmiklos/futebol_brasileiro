<?php

require_once '../estrutura/conexaodb.php';

    $uf = isset($_GET['uf']) ? strtoupper(trim($_GET['uf'])) : '';
    $extintos = isset($_GET['extintos']) ? true : false;

    $times = [];
    if (!empty($uf)) {
        if ($extintos) {
            $stmt = $pdo->prepare("SELECT * FROM times WHERE estado = ? AND extinto = 1 ORDER BY nome ASC");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM times WHERE estado = ? AND extinto = 0 ORDER BY nome ASC");
        }
        $stmt->execute([$uf]);
        $times = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $extintos ? 'Times Extintos' : 'Times Ativos' ?> do Estado <?= htmlspecialchars($uf) ?> - Futebol Brasileiro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-times/times_estado.css">
</head>
<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-times-estado">
        <div class="container">
            <a href="times.php" class="voltar-link">← Voltar para Times</a>
            <h1><?= $extintos ? 'Clubes Extintos' : 'Clubes em Atividade' ?> do Estado: <?= htmlspecialchars($uf) ?></h1>

            <?php if (!empty($times)): ?>
                <div class="grade-times">
                    <?php foreach ($times as $time): ?>
                        <a class="card-time" href="detalhes_time.php?id=<?= (int)$time['id'] ?>">
                            <img src="<?= '../' . htmlspecialchars($time['escudo'] ?: 'assets/images/escudo_padrao.png') ?>" alt="Escudo de <?= htmlspecialchars($time['nome']) ?>" onerror="this.onerror=null; this.src='assets/images/escudo_padrao.png';">
                            <div class="info">
                                <h3><?= htmlspecialchars($time['nome']) ?></h3>
                                <p>
                                    Fundado em
                                    <?= !empty($time['fundacao']) ? date('d/m/Y', strtotime($time['fundacao'])) : 'Data desconhecida' ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; margin-top: 30px;">Nenhum clube <?= $extintos ? 'extinto' : 'em atividade' ?> encontrado para este estado.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>
