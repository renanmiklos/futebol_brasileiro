<?php

require_once 'estatisticas-inicial.php';

/* =========================================
   GARANTIA DE VARIÁVEIS VINDAS DO ARQUIVO INICIAL
   Evita erros do Intelephense e falhas caso alguma variável não seja criada
========================================= */

$categoriaSelecionada = $categoriaSelecionada ?? null;

$rankingItems = $rankingItems ?? [];
$rankingLabels = $rankingLabels ?? [];
$categorias = $categorias ?? [];

$pontuacoesPorCompeticao = $pontuacoesPorCompeticao ?? [];
$estatisticas = $estatisticas ?? [];
$pontos = $pontos ?? 0;

if (!isset($pdo)) {
    die('Erro: conexão com o banco de dados não foi carregada.');
}

/* =========================================
   FUNÇÕES AUXILIARES DA PÁGINA
========================================= */

function caminhoEscudoEstatisticas($escudo, $fallback = '../assets/images/escudo_padrao.png')
{
    if (empty($escudo)) {
        return $fallback;
    }

    $escudo = trim($escudo);

    if (
        str_starts_with($escudo, 'http://') ||
        str_starts_with($escudo, 'https://') ||
        str_starts_with($escudo, 'data:')
    ) {
        return htmlspecialchars($escudo);
    }

    return '../' . htmlspecialchars(ltrim($escudo, '/'));
}

function formatarNumeroEstatisticas($valor)
{
    if ($valor === null || $valor === '' || !is_numeric($valor)) {
        return '0';
    }

    return number_format((float)$valor, 0, ',', '.');
}

function classeAtivaEstatisticas($categoriaSelecionada, $item)
{
    return ($categoriaSelecionada === $item) ? 'ativo' : '';
}

function pluralizarEstatisticas($quantidade, $singular, $plural)
{
    return ((int)$quantidade === 1) ? $singular : $plural;
}

/* =========================================
   DADOS DINÂMICOS DA PÁGINA INICIAL
========================================= */

$destaquesEstatisticas = null;

if (empty($categoriaSelecionada)) {

    // Total de competições oficiais
    $stmtTotalCompeticoes = $pdo->query("
        SELECT COUNT(*) 
        FROM competicoes 
        WHERE amistoso = 0
    ");

    $totalCompeticoes = (int)$stmtTotalCompeticoes->fetchColumn();

    // Total de clubes ranquiados e ativos
    $stmtTotalTimes = $pdo->query("
        SELECT COUNT(*) 
        FROM times 
        WHERE id BETWEEN 1 AND 2065 
          AND (extinto IS NULL OR extinto = 0)
    ");

    $totalTimes = (int)$stmtTotalTimes->fetchColumn();

    // Ranking completo e Top 5
    $rankingCompleto = gerarRankingCompleto($pdo);
    $top5 = array_slice($rankingCompleto, 0, 5);

    // Maior campeão por títulos oficiais
    $stmtMaiorCampeao = $pdo->prepare("
        SELECT 
            t.id,
            t.nome, 
            t.escudo, 
            COUNT(*) AS total_titulos
        FROM classificacao c
        INNER JOIN times t ON t.id = c.id_time
        INNER JOIN temporadas tmp ON tmp.id = c.id_temporada
        INNER JOIN competicoes comp ON comp.id = tmp.id_competicao
        WHERE c.fase IN ('Camp', '1º')
          AND t.id BETWEEN 1 AND 65
          AND (t.extinto IS NULL OR t.extinto = 0)
          AND comp.amistoso = 0
        GROUP BY t.id, t.nome, t.escudo
        ORDER BY total_titulos DESC, t.nome ASC
        LIMIT 1
    ");

    $stmtMaiorCampeao->execute();
    $maiorCampeao = $stmtMaiorCampeao->fetch(PDO::FETCH_ASSOC);

    $destaquesEstatisticas = [
        'total_competicoes' => $totalCompeticoes,
        'total_times'       => $totalTimes,
        'maior_campeao'     => $maiorCampeao,
        'top5'              => $top5
    ];
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Estatísticas - Futebol Brasileiro</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="../estatisticas/css-estatisticas/estatisticas.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-estatisticas">
        <div class="container">

            <!-- Menu Lateral -->
            <aside class="menu-lateral menu-estatisticas">

                <div class="menu-bloco">
                    <h2>Ranking</h2>

                    <ul>
                        <?php foreach ($rankingItems as $index => $item): ?>
                            <li>
                                <a
                                    href="?tipo=<?= urlencode($item) ?>"
                                    class="<?= classeAtivaEstatisticas($categoriaSelecionada, $item) ?>"
                                >
                                    <?= htmlspecialchars($rankingLabels[$index] ?? $item) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="menu-bloco">
                    <h2>Tipos de Estatísticas</h2>

                    <ul>
                        <?php foreach ($categorias as $categoria): ?>
                            <li>
                                <a
                                    href="?tipo=<?= urlencode($categoria) ?>"
                                    class="<?= classeAtivaEstatisticas($categoriaSelecionada, $categoria) ?>"
                                >
                                    <?= htmlspecialchars($categoria) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </aside>

            <!-- Conteúdo Principal -->
            <div class="conteudo-estatisticas">

                <?php if (empty($categoriaSelecionada)): ?>

                    <section class="hero-estatisticas">
                        <span class="eyebrow">Estatísticas</span>

                        <h1>Estatísticas do Futebol Brasileiro</h1>

                        <p class="descricao-intro">
                            Bem-vindo à área mais completa de estatísticas históricas do futebol brasileiro.
                            Aqui, reunimos dados oficiais de campeonatos nacionais, internacionais, regionais
                            e estaduais, desde a <strong>Taça Brasil de 1959</strong> até as competições mais recentes.
                        </p>
                    </section>

                    <!-- Estatísticas Gerais -->
                    <section class="stats-resumo">
                        <article class="stat-resumo-item">
                            <span class="stat-resumo-num">
                                <?= formatarNumeroEstatisticas($destaquesEstatisticas['total_competicoes'] ?? 0) ?>
                            </span>

                            <span class="stat-resumo-label">
                                Competições oficiais
                            </span>
                        </article>

                        <article class="stat-resumo-item">
                            <span class="stat-resumo-num">
                                <?= formatarNumeroEstatisticas($destaquesEstatisticas['total_times'] ?? 0) ?>
                            </span>

                            <span class="stat-resumo-label">
                                Clubes ranquiados
                            </span>
                        </article>
                    </section>

                    <!-- Top 5 Ranking Oficial -->
                    <?php if (!empty($destaquesEstatisticas['top5'])): ?>
                        <section class="top5-ranking card-estatisticas">
                            <div class="titulo-bloco-estatisticas">
                                <h2>Ranking Oficial</h2>
                                <span>Top 5 clubes</span>
                            </div>

                            <div class="ranking-lista">
                                <?php foreach ($destaquesEstatisticas['top5'] as $i => $clube): ?>
                                    <?php
                                        $escudoClube = caminhoEscudoEstatisticas($clube['escudo'] ?? '');
                                        $idClube = isset($clube['id']) ? (int)$clube['id'] : null;
                                    ?>

                                    <div class="ranking-item">
                                        <span class="ranking-pos">
                                            <?= $i + 1 ?>º
                                        </span>

                                        <?php if ($idClube): ?>
                                            <a href="../times/detalhes_time.php?id=<?= $idClube ?>" class="ranking-nome">
                                        <?php else: ?>
                                            <div class="ranking-nome">
                                        <?php endif; ?>

                                            <img
                                                src="<?= $escudoClube ?>"
                                                alt="Escudo de <?= htmlspecialchars($clube['nome'] ?? 'clube') ?>"
                                                onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                            >

                                            <span><?= htmlspecialchars($clube['nome'] ?? 'Clube não informado') ?></span>

                                        <?php if ($idClube): ?>
                                            </a>
                                        <?php else: ?>
                                            </div>
                                        <?php endif; ?>

                                        <span class="ranking-pontos">
                                            <?= formatarNumeroEstatisticas($clube['total'] ?? 0) ?> pts
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Maior Campeão -->
                    <?php if (!empty($destaquesEstatisticas['maior_campeao'])): ?>
                        <?php
                            $maiorCampeao = $destaquesEstatisticas['maior_campeao'];
                            $escudoMaiorCampeao = caminhoEscudoEstatisticas($maiorCampeao['escudo'] ?? '');
                            $totalTitulos = (int)($maiorCampeao['total_titulos'] ?? 0);
                        ?>

                        <section class="clube-destaque-wrapper card-estatisticas">
                            <div class="titulo-bloco-estatisticas">
                                <h2>Maior campeão por títulos</h2>
                                <span>Conquistas oficiais</span>
                            </div>

                            <a
                                href="../times/detalhes_time.php?id=<?= (int)$maiorCampeao['id'] ?>"
                                class="clube-destaque-item"
                            >
                                <div class="clube-detalhe">
                                    <img
                                        src="<?= $escudoMaiorCampeao ?>"
                                        alt="Escudo de <?= htmlspecialchars($maiorCampeao['nome'] ?? 'clube') ?>"
                                        onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                    >

                                    <div>
                                        <span class="clube-nome">
                                            <?= htmlspecialchars($maiorCampeao['nome'] ?? 'Clube não informado') ?>
                                        </span>

                                        <span class="clube-dado">
                                            <?= formatarNumeroEstatisticas($totalTitulos) ?>
                                            <?= pluralizarEstatisticas($totalTitulos, 'título', 'títulos') ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </section>
                    <?php endif; ?>

                    <!-- Acesso Rápido -->
                    <section class="botoes-acesso">
                        <a href="?tipo=criterios" class="botao-acesso">
                            Critérios de Ranking
                        </a>

                        <a href="ranking.php" class="botao-acesso">
                            Ranking Completo
                        </a>

                        <a href="?tipo=Nacionais" class="botao-acesso">
                            Competições Nacionais
                        </a>
                    </section>

                <?php else: ?>

                    <section class="conteudo-categoria-estatisticas">
                        <?= renderConteudoEstatisticas(
                            $categoriaSelecionada,
                            $pontuacoesPorCompeticao,
                            $estatisticas,
                            $pontos,
                            $pdo
                        ); ?>
                    </section>

                <?php endif; ?>

            </div>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>