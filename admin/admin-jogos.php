<?php
/* =========================================
   ADMIN-JOGOS.PHP
   Gerenciamento de Jogos
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
   DADOS
========================================= */

$feedback = getFlashAdmin('sucesso');

$competicoes = $pdo->query("
    SELECT id, nome, tipo
    FROM competicoes
    ORDER BY 
        FIELD(tipo, 'Internacional', 'Nacional', 'Regional', 'Estadual'),
        nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

$times = $pdo->query("
    SELECT id, nome, estado, escudo, extinto
    FROM times
    ORDER BY nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

$jogos = $pdo->query("
    SELECT 
        j.*,
        t.ano,
        c.id AS id_competicao,
        c.nome AS nome_competicao,
        c.tipo AS tipo_competicao,
        mandante.nome AS nome_mandante,
        mandante.escudo AS escudo_mandante,
        visitante.nome AS nome_visitante,
        visitante.escudo AS escudo_visitante
    FROM jogos j
    INNER JOIN temporadas t ON t.id = j.id_temporada
    INNER JOIN competicoes c ON c.id = t.id_competicao
    LEFT JOIN times mandante ON mandante.id = j.id_time1
    LEFT JOIN times visitante ON visitante.id = j.id_time2
    ORDER BY 
        c.nome ASC,
        t.ano DESC,
        j.data DESC,
        j.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$totalJogos = count($jogos);
$totalCompeticoes = count($competicoes);
$totalTimes = count($times);

$jogosComPenaltis = 0;

foreach ($jogos as $jogo) {
    if (
        $jogo['penaltis_time1'] !== null &&
        $jogo['penaltis_time2'] !== null &&
        $jogo['penaltis_time1'] !== '' &&
        $jogo['penaltis_time2'] !== ''
    ) {
        $jogosComPenaltis++;
    }
}

$qtdLinhasMultiplas = (int)($LIMITES_ADMIN['jogos_por_envio'] ?? 5);
$qtdLinhasMultiplas = max(1, min($qtdLinhasMultiplas, 10));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gerenciar Jogos - Painel Administrativo</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="css-admin/admin.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include __DIR__ . '/../estrutura/header2.php'; ?>

<main class="admin-main">

    <?php
        renderAdminHero(
            'Gerenciamento de Jogos',
            'Cadastre partidas, placares, datas, rodadas, estádios e decisões por pênaltis.',
            'Admin',
            [
                $totalJogos . ' jogos cadastrados',
                $jogosComPenaltis . ' com pênaltis',
                $totalCompeticoes . ' competições'
            ]
        );
    ?>

    <?php renderAdminFeedback($feedback); ?>

    <section class="admin-resumo">
        <?php renderAdminResumoCard($totalJogos, 'Jogos'); ?>
        <?php renderAdminResumoCard($jogosComPenaltis, 'Com pênaltis'); ?>
        <?php renderAdminResumoCard($totalCompeticoes, 'Competições'); ?>
        <?php renderAdminResumoCard($totalTimes, 'Times'); ?>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Adicionar Jogo', 'Cadastro único'); ?>

            <form method="POST" action="admin-process.php" class="form-admin">
                <input type="hidden" name="acao" value="inserir_jogo">
                <?php renderAdminCsrf(); ?>

                <label for="id_competicao">Competição</label>
                <select
                    id="id_competicao"
                    name="id_competicao"
                    data-carregar-temporadas="#id_temporada"
                    required
                >
                    <option value="">Selecione</option>
                    <?php foreach ($competicoes as $competicao): ?>
                        <option value="<?= (int)$competicao['id'] ?>">
                            <?= eAdmin($competicao['nome']) ?> — <?= eAdmin($competicao['tipo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="id_temporada">Temporada</label>
                <select id="id_temporada" name="id_temporada" required>
                    <option value="">Selecione uma competição primeiro</option>
                </select>

                <div class="linha-form">
                    <div class="campo-medio">
                        <label for="id_time1">Time 1 cadastrado</label>
                        <select id="id_time1" name="id_time1">
                            <option value="">Time não cadastrado / manual</option>
                            <?php foreach ($times as $time): ?>
                                <option value="<?= (int)$time['id'] ?>">
                                    <?= eAdmin($time['nome']) ?><?= !empty($time['estado']) ? ' — ' . eAdmin($time['estado']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo-medio">
                        <label for="nome_time1">Nome manual do Time 1</label>
                        <input type="text" id="nome_time1" name="nome_time1">
                    </div>
                </div>

                <div class="linha-form">
                    <div class="campo-medio">
                        <label for="id_time2">Time 2 cadastrado</label>
                        <select id="id_time2" name="id_time2">
                            <option value="">Time não cadastrado / manual</option>
                            <?php foreach ($times as $time): ?>
                                <option value="<?= (int)$time['id'] ?>">
                                    <?= eAdmin($time['nome']) ?><?= !empty($time['estado']) ? ' — ' . eAdmin($time['estado']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo-medio">
                        <label for="nome_time2">Nome manual do Time 2</label>
                        <input type="text" id="nome_time2" name="nome_time2">
                    </div>
                </div>

                <div class="linha-form">
                    <div class="campo-medio">
                        <label for="data">Data e hora</label>
                        <input type="datetime-local" id="data" name="data">
                    </div>

                    <div class="campo-medio">
                        <label for="rodada">Rodada / fase do jogo</label>
                        <input type="text" id="rodada" name="rodada" placeholder="Ex.: Final, 1ª Rodada">
                    </div>
                </div>

                <label for="estadio">Estádio</label>
                <input type="text" id="estadio" name="estadio">

                <h2>Placar</h2>

                <div class="linha-form">
                    <div class="campo-curto">
                        <label for="gols_time1">Gols Time 1</label>
                        <input type="number" id="gols_time1" name="gols_time1" min="0">
                    </div>

                    <div class="campo-curto">
                        <label for="gols_time2">Gols Time 2</label>
                        <input type="number" id="gols_time2" name="gols_time2" min="0">
                    </div>

                    <div class="campo-curto">
                        <label for="penaltis_time1">Pênaltis Time 1</label>
                        <input type="number" id="penaltis_time1" name="penaltis_time1" min="0">
                    </div>

                    <div class="campo-curto">
                        <label for="penaltis_time2">Pênaltis Time 2</label>
                        <input type="number" id="penaltis_time2" name="penaltis_time2" min="0">
                    </div>
                </div>

                <button type="submit">Adicionar Jogo</button>
            </form>
        </div>

        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Adicionar Vários Jogos', $qtdLinhasMultiplas . ' linhas'); ?>

            <form method="POST" action="admin-process.php" class="form-admin form-varios-jogos">
                <input type="hidden" name="acao" value="inserir_varios_jogos">
                <?php renderAdminCsrf(); ?>

                <div class="com-scroll">
                    <table class="tabela-listagem tabela-form-jogos">
                        <thead>
                            <tr>
                                <th>Competição</th>
                                <th>Temporada</th>
                                <th>Time 1</th>
                                <th>Manual 1</th>
                                <th>Time 2</th>
                                <th>Manual 2</th>
                                <th>Data</th>
                                <th>Rodada</th>
                                <th>Estádio</th>
                                <th>G1</th>
                                <th>G2</th>
                                <th>P1</th>
                                <th>P2</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php for ($i = 0; $i < $qtdLinhasMultiplas; $i++): ?>
                                <tr>
                                    <td>
                                        <select
                                            name="jogos[<?= $i ?>][id_competicao]"
                                            class="jogo-competicao"
                                            data-carregar-temporadas="#jogo-temporada-<?= $i ?>"
                                        >
                                            <option value="">Selecione</option>
                                            <?php foreach ($competicoes as $competicao): ?>
                                                <option value="<?= (int)$competicao['id'] ?>">
                                                    <?= eAdmin($competicao['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>

                                    <td>
                                        <select
                                            id="jogo-temporada-<?= $i ?>"
                                            name="jogos[<?= $i ?>][id_temporada]"
                                        >
                                            <option value="">Selecione</option>
                                        </select>
                                    </td>

                                    <td>
                                        <select name="jogos[<?= $i ?>][id_time1]">
                                            <option value="">Manual</option>
                                            <?php foreach ($times as $time): ?>
                                                <option value="<?= (int)$time['id'] ?>">
                                                    <?= eAdmin($time['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" name="jogos[<?= $i ?>][nome_time1]">
                                    </td>

                                    <td>
                                        <select name="jogos[<?= $i ?>][id_time2]">
                                            <option value="">Manual</option>
                                            <?php foreach ($times as $time): ?>
                                                <option value="<?= (int)$time['id'] ?>">
                                                    <?= eAdmin($time['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" name="jogos[<?= $i ?>][nome_time2]">
                                    </td>

                                    <td>
                                        <input type="datetime-local" name="jogos[<?= $i ?>][data]">
                                    </td>

                                    <td>
                                        <input type="text" name="jogos[<?= $i ?>][rodada]">
                                    </td>

                                    <td>
                                        <input type="text" name="jogos[<?= $i ?>][estadio]">
                                    </td>

                                    <td>
                                        <input type="number" name="jogos[<?= $i ?>][gols_time1]" min="0">
                                    </td>

                                    <td>
                                        <input type="number" name="jogos[<?= $i ?>][gols_time2]" min="0">
                                    </td>

                                    <td>
                                        <input type="number" name="jogos[<?= $i ?>][penaltis_time1]" min="0">
                                    </td>

                                    <td>
                                        <input type="number" name="jogos[<?= $i ?>][penaltis_time2]" min="0">
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

                <button type="submit">Adicionar Jogos Preenchidos</button>
            </form>
        </div>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Jogos Cadastrados', $totalJogos . ' registros'); ?>

            <?php
                renderAdminPesquisa(
                    'filtro-jogos',
                    $PLACEHOLDERS_ADMIN['pesquisar_jogo'] ?? 'Pesquisar por competição...'
                );
            ?>

            <div class="com-scroll-1">
                <table class="tabela-listagem" id="tabela-jogos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Competição</th>
                            <th>Ano</th>
                            <th>Data</th>
                            <th>Rodada</th>
                            <th>Jogo</th>
                            <th>Placar</th>
                            <th>Pênaltis</th>
                            <th>Estádio</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($jogos)): ?>
                            <?php foreach ($jogos as $jogo): ?>
                                <?php
                                    $idJogo = (int)$jogo['id'];

                                    $nomeTime1 = $jogo['nome_mandante'] ?: ($jogo['nome_time1'] ?? 'Time 1');
                                    $nomeTime2 = $jogo['nome_visitante'] ?: ($jogo['nome_time2'] ?? 'Time 2');

                                    $placar = '-';

                                    if ($jogo['gols_time1'] !== null && $jogo['gols_time2'] !== null) {
                                        $placar = (int)$jogo['gols_time1'] . ' x ' . (int)$jogo['gols_time2'];
                                    }

                                    $penaltis = '-';

                                    if ($jogo['penaltis_time1'] !== null && $jogo['penaltis_time2'] !== null) {
                                        $penaltis = (int)$jogo['penaltis_time1'] . ' x ' . (int)$jogo['penaltis_time2'];
                                    }
                                ?>

                                <tr>
                                    <td><?= $idJogo ?></td>

                                    <td><?= eAdmin($jogo['nome_competicao'] ?? '') ?></td>

                                    <td><?= eAdmin($jogo['ano'] ?? '') ?></td>

                                    <td><?= formatarDataHoraAdmin($jogo['data'] ?? '') ?></td>

                                    <td><?= eAdmin($jogo['rodada'] ?? '') ?></td>

                                    <td class="clube-nome">
                                        <?= eAdmin($nomeTime1) ?> x <?= eAdmin($nomeTime2) ?>
                                    </td>

                                    <td><?= eAdmin($placar) ?></td>

                                    <td><?= eAdmin($penaltis) ?></td>

                                    <td><?= eAdmin($jogo['estadio'] ?? '') ?></td>

                                    <td class="acoes-celula">
                                        <button
                                            type="button"
                                            class="btn-editar-jogo"
                                            data-id="<?= $idJogo ?>"
                                        >
                                            Editar
                                        </button>

                                        <form
                                            method="POST"
                                            action="admin-process.php"
                                            class="form-inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este jogo?');"
                                        >
                                            <input type="hidden" name="acao" value="excluir_jogo">
                                            <input type="hidden" name="id" value="<?= $idJogo ?>">
                                            <?php renderAdminCsrf(); ?>

                                            <button type="submit" class="btn-excluir">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php renderAdminTabelaVazia(10, 'Nenhum jogo cadastrado.'); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php renderAdminLinksRodape(true, true); ?>

</main>

<!-- MODAL EDITAR JOGO -->
<div id="modal-editar-jogo" class="modal">
    <div class="modal-content">
        <span class="close" data-modal-close="modal-editar-jogo">&times;</span>

        <h2>Editar Jogo</h2>

        <form method="POST" action="admin-process.php" id="form-editar-jogo">
            <input type="hidden" name="acao" value="editar_jogo">
            <input type="hidden" name="id" id="edit-jogo-id">
            <?php renderAdminCsrf(); ?>

            <label for="edit-id-competicao">Competição</label>
            <select
                id="edit-id-competicao"
                name="id_competicao"
                data-carregar-temporadas="#edit-id-temporada"
                required
            >
                <option value="">Selecione</option>
                <?php foreach ($competicoes as $competicao): ?>
                    <option value="<?= (int)$competicao['id'] ?>">
                        <?= eAdmin($competicao['nome']) ?> — <?= eAdmin($competicao['tipo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="edit-id-temporada">Temporada</label>
            <select id="edit-id-temporada" name="id_temporada" required>
                <option value="">Selecione uma competição primeiro</option>
            </select>

            <div class="linha-form">
                <div class="campo-medio">
                    <label for="edit-id-time1">Time 1 cadastrado</label>
                    <select id="edit-id-time1" name="id_time1">
                        <option value="">Time não cadastrado / manual</option>
                        <?php foreach ($times as $time): ?>
                            <option value="<?= (int)$time['id'] ?>">
                                <?= eAdmin($time['nome']) ?><?= !empty($time['estado']) ? ' — ' . eAdmin($time['estado']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo-medio">
                    <label for="edit-nome-time1">Nome manual do Time 1</label>
                    <input type="text" id="edit-nome-time1" name="nome_time1">
                </div>
            </div>

            <div class="linha-form">
                <div class="campo-medio">
                    <label for="edit-id-time2">Time 2 cadastrado</label>
                    <select id="edit-id-time2" name="id_time2">
                        <option value="">Time não cadastrado / manual</option>
                        <?php foreach ($times as $time): ?>
                            <option value="<?= (int)$time['id'] ?>">
                                <?= eAdmin($time['nome']) ?><?= !empty($time['estado']) ? ' — ' . eAdmin($time['estado']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo-medio">
                    <label for="edit-nome-time2">Nome manual do Time 2</label>
                    <input type="text" id="edit-nome-time2" name="nome_time2">
                </div>
            </div>

            <div class="linha-form">
                <div class="campo-medio">
                    <label for="edit-data">Data e hora</label>
                    <input type="datetime-local" id="edit-data" name="data">
                </div>

                <div class="campo-medio">
                    <label for="edit-rodada">Rodada / fase do jogo</label>
                    <input type="text" id="edit-rodada" name="rodada">
                </div>
            </div>

            <label for="edit-estadio">Estádio</label>
            <input type="text" id="edit-estadio" name="estadio">

            <h2>Placar</h2>

            <div class="linha-form">
                <div class="campo-curto">
                    <label for="edit-gols-time1">Gols Time 1</label>
                    <input type="number" id="edit-gols-time1" name="gols_time1" min="0">
                </div>

                <div class="campo-curto">
                    <label for="edit-gols-time2">Gols Time 2</label>
                    <input type="number" id="edit-gols-time2" name="gols_time2" min="0">
                </div>

                <div class="campo-curto">
                    <label for="edit-penaltis-time1">Pênaltis Time 1</label>
                    <input type="number" id="edit-penaltis-time1" name="penaltis_time1" min="0">
                </div>

                <div class="campo-curto">
                    <label for="edit-penaltis-time2">Pênaltis Time 2</label>
                    <input type="number" id="edit-penaltis-time2" name="penaltis_time2" min="0">
                </div>
            </div>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>

<script src="js-admin/admin.js"></script>
<script src="js-admin/admin-jogos.js"></script>

</body>
</html>