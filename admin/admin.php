<?php
/* =========================================
   ADMIN.PHP
   Painel Administrativo Principal
   Futebol Brasileiro
========================================= */

/* =========================================
   INCLUDES DO ADMIN
========================================= */

require_once __DIR__ . '/includes-admin/admin-auth.php';
require_once __DIR__ . '/includes-admin/admin-funcoes.php';
require_once __DIR__ . '/includes-admin/admin-opcoes.php';
require_once __DIR__ . '/includes-admin/admin-layout.php';

/* =========================================
   CONEXÃO COM BANCO
========================================= */

require_once __DIR__ . '/../estrutura/conexaodb.php';

if (!isset($pdo)) {
    die('Erro: Conexão com o banco de dados não estabelecida.');
}

/* =========================================
   FUNÇÕES ESPECÍFICAS DESTA PÁGINA
========================================= */

if (!function_exists('obterTotalAdminPainel')) {
    function obterTotalAdminPainel(PDO $pdo, string $tabela): int
    {
        /*
          Lista permitida para evitar uso indevido do nome da tabela.
        */
        $tabelasPermitidas = [
            'times',
            'competicoes',
            'temporadas',
            'pontuacoes_fase',
            'classificacao',
            'divisao_atual',
            'jogos',
            'noticias',
            'artigos',
            'fotos'
        ];

        if (!in_array($tabela, $tabelasPermitidas, true)) {
            return 0;
        }

        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$tabela}");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('obterUltimaAtualizacaoAdminPainel')) {
    function obterUltimaAtualizacaoAdminPainel(PDO $pdo): string
    {
        try {
            $stmt = $pdo->query("
                SELECT data_publicacao 
                FROM noticias 
                ORDER BY data_publicacao DESC 
                LIMIT 1
            ");

            $data = $stmt->fetchColumn();

            if (!empty($data)) {
                return formatarDataAdmin($data);
            }
        } catch (Exception $e) {
            return 'Não disponível';
        }

        return 'Não disponível';
    }
}

/* =========================================
   FEEDBACK FLASH
========================================= */

$feedback = getFlashAdmin('sucesso');

/* =========================================
   DADOS DO PAINEL
========================================= */

$totalTimes = obterTotalAdminPainel($pdo, 'times');
$totalCompeticoes = obterTotalAdminPainel($pdo, 'competicoes');
$totalTemporadas = obterTotalAdminPainel($pdo, 'temporadas');
$totalPontuacoes = obterTotalAdminPainel($pdo, 'pontuacoes_fase');
$totalClassificacoes = obterTotalAdminPainel($pdo, 'classificacao');
$totalDivisoes = obterTotalAdminPainel($pdo, 'divisao_atual');
$totalJogos = obterTotalAdminPainel($pdo, 'jogos');
$totalNoticias = obterTotalAdminPainel($pdo, 'noticias');
$totalArtigos = obterTotalAdminPainel($pdo, 'artigos');
$totalFotos = obterTotalAdminPainel($pdo, 'fotos');

$ultimaAtualizacao = obterUltimaAtualizacaoAdminPainel($pdo);

$usuarioAdmin = $adminUsuarioLogado ?? ($_SESSION['admin_usuario'] ?? 'admin');

/* =========================================
   CARDS DO PAINEL
========================================= */

$cardsAdmin = [
    [
        'titulo' => 'Gerenciamento de Times',
        'descricao' => 'Adicionar, editar e remover clubes cadastrados no sistema.',
        'url' => 'admin-times.php',
        'botao' => 'Gerenciar Times',
        'total' => $totalTimes,
        'label_total' => 'times'
    ],
    [
        'titulo' => 'Gerenciamento de Competições',
        'descricao' => 'Controlar competições internacionais, nacionais, regionais e estaduais.',
        'url' => 'admin-competicoes.php',
        'botao' => 'Gerenciar Competições',
        'total' => $totalCompeticoes,
        'label_total' => 'competições'
    ],
    [
        'titulo' => 'Gerenciamento de Temporadas',
        'descricao' => 'Cadastrar temporadas, anos e descrições das competições.',
        'url' => 'admin-temporadas.php',
        'botao' => 'Gerenciar Temporadas',
        'total' => $totalTemporadas,
        'label_total' => 'temporadas'
    ],
    [
        'titulo' => 'Gerenciamento de Pontuações',
        'descricao' => 'Definir fases e pontuações-base usadas nas estatísticas e rankings.',
        'url' => 'admin-pontuacoes.php',
        'botao' => 'Gerenciar Pontuações',
        'total' => $totalPontuacoes,
        'label_total' => 'pontuações'
    ],
    [
        'titulo' => 'Gerenciamento de Classificações',
        'descricao' => 'Cadastrar campanhas, fases, pontos, gols e desempenho dos clubes.',
        'url' => 'admin-classificacao.php',
        'botao' => 'Gerenciar Classificações',
        'total' => $totalClassificacoes,
        'label_total' => 'classificações'
    ],
    [
        'titulo' => 'Clubes nas Divisões do Brasileirão',
        'descricao' => 'Organizar os clubes nas Séries A, B, C e D atuais.',
        'url' => 'admin-divisoes.php',
        'botao' => 'Gerenciar Divisões',
        'total' => $totalDivisoes,
        'label_total' => 'clubes em divisões'
    ],
    [
        'titulo' => 'Gerenciamento de Jogos',
        'descricao' => 'Cadastrar partidas, placares, datas, rodadas, estádios e pênaltis.',
        'url' => 'admin-jogos.php',
        'botao' => 'Gerenciar Jogos',
        'total' => $totalJogos,
        'label_total' => 'jogos'
    ],
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Painel Administrativo - Futebol Brasileiro</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="css-admin/admin.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include __DIR__ . '/../estrutura/header2.php'; ?>

<main class="admin-main">

    <?php
        renderAdminHero(
            'Painel de Administração',
            'Área interna para gerenciamento dos dados centrais do portal Futebol Brasileiro.',
            'Admin',
            [
                'Usuário: ' . $usuarioAdmin,
                'Última atualização: ' . $ultimaAtualizacao
            ]
        );
    ?>

    <?php renderAdminFeedback($feedback); ?>

    <section class="admin-resumo">
        <?php renderAdminResumoCard($totalTimes, 'Times'); ?>
        <?php renderAdminResumoCard($totalCompeticoes, 'Competições'); ?>
        <?php renderAdminResumoCard($totalTemporadas, 'Temporadas'); ?>
        <?php renderAdminResumoCard($totalClassificacoes, 'Classificações'); ?>
        <?php renderAdminResumoCard($totalJogos, 'Jogos'); ?>
    </section>

    <section class="painel-grid">
        <?php foreach ($cardsAdmin as $card): ?>
            <?php
                renderAdminCard(
                    $card['titulo'],
                    $card['descricao'],
                    $card['url'],
                    $card['botao'],
                    (int)$card['total'],
                    $card['label_total']
                );
            ?>
        <?php endforeach; ?>
    </section>

    <section class="painel-bloco painel-secundario">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Conteúdo Editorial', 'Atalhos'); ?>

            <p>
                Atalhos para acompanhar conteúdos publicados no portal.
            </p>

            <div class="admin-atalhos">
                <?php renderAdminBadge($totalNoticias . ' notícias'); ?>
                <?php renderAdminBadge($totalArtigos . ' artigos'); ?>
                <?php renderAdminBadge($totalFotos . ' fotos'); ?>
            </div>

            <p>
                A gestão direta de notícias, artigos e galerias pode ser adicionada ao painel
                caso você queira centralizar toda a administração nesta área.
            </p>
        </div>
    </section>

    <?php renderAdminLinksRodape(false, true); ?>

</main>

<script src="js-admin/admin.js"></script>

</body>
</html>