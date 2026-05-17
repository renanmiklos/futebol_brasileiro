<?php
require_once __DIR__ . '/../estrutura/conexaodb.php';
require_once __DIR__ . '/../estrutura/calcula-pontuacoes.php';
require_once __DIR__ . '/includes-ranking/ranking-config.php';
require_once __DIR__ . '/includes-ranking/ranking-funcoes.php';
require_once __DIR__ . '/includes-ranking/ranking-calculo.php';
require_once __DIR__ . '/includes-ranking/ranking-render.php';

/* =========================================
   VERIFICAÇÃO DE CONEXÃO
========================================= */

if (!isset($pdo)) {
    die('Erro: Conexão com o banco de dados não estabelecida.');
}

/* =========================================
   DADOS DO RANKING DAS FEDERAÇÕES
========================================= */

$paginaAtual = 'federacoes';

$ranking = gerarRankingFederacoes($pdo, $ESTADOS_BRASIL);

$totalFederacoesRanking = count($ranking);
$ultimaAtualizacao = obterUltimaAtualizacaoRanking($pdo);

$tituloPagina = 'Ranking das Federações Estaduais - Futebol Brasileiro';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <title><?= eRanking($tituloPagina) ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-estatisticas/ranking.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-ranking">
        <div class="ranking-container">

            <a href="ranking-introducao.php" class="voltar-link">
                ← Voltar à Introdução
            </a>

            <?php
                renderHeroRanking(
                    'Ranking das Federações',
                    'Ranking das Federações Estaduais',
                    'Classificação das federações estaduais considerando a pontuação acumulada pelos clubes ativos de cada estado em competições internacionais, nacionais, regionais e estaduais.',
                    [
                        $totalFederacoesRanking . ' federações',
                        'Última atualização: ' . $ultimaAtualizacao
                    ]
                );
            ?>

            <?php renderPesquisaRanking('Digite a sigla do estado...', 'filtro-time'); ?>

            <section class="card-tabela-ranking">
                <div class="titulo-bloco-ranking">
                    <h2>Classificação por Estado</h2>
                    <span>Ranking das federações</span>
                </div>

                <?php if (!empty($ranking)): ?>
                    <?php
                        renderTabelaRankingFederacoes(
                            $ranking,
                            $COLUNAS_RANKING_FEDERACOES,
                            [
                                'tabela_id' => 'ranking-table'
                            ]
                        );
                    ?>
                <?php else: ?>
                    <?php renderMensagemVaziaRanking('Nenhuma federação foi encontrada para composição do ranking.'); ?>
                <?php endif; ?>
            </section>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

<script src="js-estatisticas/ranking.js"></script>

</body>
</html>