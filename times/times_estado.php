<?php

require_once '../estrutura/conexaodb.php';

$uf = isset($_GET['uf']) ? strtoupper(trim($_GET['uf'])) : '';
$extintos = isset($_GET['extintos']);

$times = [];

if (!empty($uf)) {

    // Subconsulta para pegar a pontuação dos times no Ranking Nacional
    // Inclui todos os clubes que possuem pontuação nacional registrada
    $subQuery = "
        SELECT 
            cl.id_time, 
            SUM(cl.pontos) AS total
        FROM classificacao cl
        INNER JOIN temporadas tp ON cl.id_temporada = tp.id
        INNER JOIN competicoes c ON tp.id_competicao = c.id
        INNER JOIN times t ON cl.id_time = t.id
        WHERE cl.nacional = 1
        GROUP BY cl.id_time
    ";

    if ($extintos) {
        // Times extintos - ordenados pela pontuação no Ranking Nacional
        // Clubes extintos não exibem selo de divisão atual
        $sql = "
            SELECT 
                t.*, 
                COALESCE(r.total, 0) AS pontos_ranking,
                NULL AS divisao_atual
            FROM times t
            LEFT JOIN ($subQuery) r ON t.id = r.id_time
            WHERE t.estado = ? 
              AND t.extinto = 1
            ORDER BY COALESCE(r.total, 0) DESC, t.nome ASC
        ";
    } else {
        // Times em atividade - ordenados pela pontuação no Ranking Nacional
        // A divisão atual é exibida apenas quando o clube estiver na tabela divisao_atual
        $sql = "
            SELECT 
                t.*, 
                COALESCE(r.total, 0) AS pontos_ranking,
                da.divisao AS divisao_atual
            FROM times t
            LEFT JOIN ($subQuery) r ON t.id = r.id_time
            LEFT JOIN divisao_atual da ON da.id_time = t.id
            WHERE t.estado = ? 
              AND t.extinto = 0
            ORDER BY COALESCE(r.total, 0) DESC, t.nome ASC
        ";
    }

    $stmt = $pdo->prepare($sql);
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

            <h1>
                <?= $extintos ? 'Clubes Extintos' : 'Clubes em Atividade' ?> do Estado: <?= htmlspecialchars($uf) ?>
            </h1>

            <p class="aviso">
                * Ordenado pela pontuação no Ranking Nacional
            </p>

            <?php if (!empty($times)): ?>
                <div class="grade-times">
                    <?php foreach ($times as $time): ?>

                        <?php
                            $escudo = !empty($time['escudo'])
                                ? '../' . htmlspecialchars($time['escudo'])
                                : '../assets/images/escudo_padrao.png';

                            $fundacao = !empty($time['fundacao'])
                                ? date('d/m/Y', strtotime($time['fundacao']))
                                : 'Data desconhecida';

                            $divisaoAtual = !$extintos && !empty($time['divisao_atual'])
                                ? strtoupper(trim($time['divisao_atual']))
                                : '';
                        ?>

                        <a class="card-time" href="detalhes_time.php?id=<?= (int)$time['id'] ?>">

                            <?php if (in_array($divisaoAtual, ['A', 'B', 'C', 'D'], true)): ?>
                                <span 
                                    class="badge-serie serie-<?= strtolower($divisaoAtual) ?>"
                                    title="Série <?= htmlspecialchars($divisaoAtual) ?> do Campeonato Brasileiro"
                                >
                                    <?= htmlspecialchars($divisaoAtual) ?>
                                </span>
                            <?php endif; ?>

                            <img 
                                src="<?= $escudo ?>" 
                                alt="Escudo de <?= htmlspecialchars($time['nome']) ?>" 
                                onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                            >

                            <div class="info">
                                <h3><?= htmlspecialchars($time['nome']) ?></h3>

                                <p>
                                    Fundado em <?= $fundacao ?>
                                </p>

                                <p class="ranking-time">
                                    Ranking Nacional: <?= number_format((float)$time['pontos_ranking'], 0, ',', '.') ?> pts
                                </p>
                            </div>
                        </a>

                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="mensagem-vazia">
                    Nenhum clube <?= $extintos ? 'extinto' : 'em atividade' ?> encontrado para este estado.
                </p>
            <?php endif; ?>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>