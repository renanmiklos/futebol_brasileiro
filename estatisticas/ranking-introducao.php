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
   DADOS DA INTRODUÇÃO
========================================= */

$paginaAtual = 'introducao';

$rankingCompleto = gerarRankingGeral($pdo);
$top5 = array_slice($rankingCompleto, 0, 5);

$totalClubes = count($rankingCompleto);
$ultimaAtualizacao = obterUltimaAtualizacaoRanking($pdo);

$tituloPagina = 'Ranking dos Clubes - Futebol Brasileiro';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= eRanking($tituloPagina) ?></title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-estatisticas/ranking-introducao.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-ranking-introducao">
        <div class="ranking-introducao-container">

            <?php renderMenuRanking($MENU_RANKING, $paginaAtual); ?>

            <div class="conteudo-ranking-introducao">

                <?php
                    renderHeroRanking(
                        'Ranking',
                        'Ranking dos Clubes Brasileiros',
                        'Conheça o sistema de pontuação que organiza os clubes brasileiros por desempenho histórico em competições internacionais, nacionais, regionais e estaduais.',
                        [
                            $totalClubes . ' clubes avaliados',
                            'Última atualização: ' . $ultimaAtualizacao
                        ]
                    );
                ?>

                <section class="card-ranking-introducao">
                    <div class="titulo-bloco-ranking">
                        <h2>Como funciona o ranking?</h2>
                        <span>Critérios gerais</span>
                    </div>

                    <div class="texto-ranking">
                        <p>
                            Este ranking classifica os clubes do futebol brasileiro com base em um sistema
                            ponderado de pontuação histórica. Cada competição recebe um peso de acordo com
                            sua importância esportiva, nível de disputa e relevância dentro da pirâmide do
                            futebol brasileiro.
                        </p>

                        <p>
                            A pontuação é calculada a partir das campanhas registradas na tabela de classificação,
                            considerando a competição disputada, a temporada e a fase alcançada por cada clube.
                        </p>

                        <ul>
                            <li>
                                <strong>Competições internacionais:</strong>
                                maior peso, incluindo torneios continentais e mundiais.
                            </li>

                            <li>
                                <strong>Competições nacionais:</strong>
                                peso elevado, incluindo campeonatos brasileiros, copas nacionais e torneios nacionais históricos.
                            </li>

                            <li>
                                <strong>Competições regionais:</strong>
                                peso intermediário, respeitando a importância histórica de cada torneio regional.
                            </li>

                            <li>
                                <strong>Competições estaduais:</strong>
                                peso básico, mas relevante pela tradição e longevidade dos campeonatos estaduais.
                            </li>
                        </ul>
                    </div>
                </section>

                <section class="card-ranking-introducao">
                    <div class="titulo-bloco-ranking">
                        <h2>Top 5 do Ranking Geral</h2>
                        <span>Destaques</span>
                    </div>

                    <?php if (!empty($top5)): ?>
                        <?php renderTopRankingIntro($top5); ?>
                    <?php else: ?>
                        <?php renderMensagemVaziaRanking('Nenhum clube foi encontrado para composição do ranking.'); ?>
                    <?php endif; ?>
                </section>

                <section class="card-ranking-introducao">
                    <div class="titulo-bloco-ranking">
                        <h2>Consulta detalhada</h2>
                        <span>Rankings disponíveis</span>
                    </div>

                    <div class="texto-ranking">
                        <p>
                            Você pode consultar o ranking completo, rankings por tipo de competição,
                            rankings regionais e o ranking das federações estaduais.
                        </p>

                        <p>
                            Para entender os valores atribuídos a cada fase e competição, consulte a área de
                            critérios e pontuações.
                        </p>
                    </div>

                    <div class="acoes-ranking-introducao">
                        <a href="ranking.php" class="botao">Ver Ranking Geral</a>
                        <a href="estatisticas.php?tipo=criterios" class="botao botao-secundario">Ver Critérios</a>
                    </div>
                </section>

            </div>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>