<?php
/* =========================================
   ADMIN-CLASSIFICACAO.PHP
   Gerenciamento de Classificações
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

$temporadas = $pdo->query("
    SELECT 
        t.id,
        t.ano,
        t.id_competicao,
        c.nome AS nome_competicao,
        c.tipo AS tipo_competicao
    FROM temporadas t
    INNER JOIN competicoes c ON c.id = t.id_competicao
    ORDER BY 
        c.nome ASC,
        t.ano DESC
")->fetchAll(PDO::FETCH_ASSOC);

$classificacoes = $pdo->query("
    SELECT 
        cl.*,
        tm.nome AS nome_time,
        tm.estado AS estado_time,
        tm.escudo AS escudo_time,
        temp.ano,
        comp.nome AS nome_competicao,
        comp.tipo AS tipo_competicao
    FROM classificacao cl
    INNER JOIN times tm ON tm.id = cl.id_time
    INNER JOIN temporadas temp ON temp.id = cl.id_temporada
    INNER JOIN competicoes comp ON comp.id = temp.id_competicao
    ORDER BY 
        comp.nome ASC,
        temp.ano DESC,
        cl.pontos DESC,
        tm.nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

$totalClassificacoes = count($classificacoes);
$totalCompeticoes = count($competicoes);
$totalTimes = count($times);
$totalTemporadas = count($temporadas);

$totalNacionais = 0;
$totalNaoNacionais = 0;

foreach ($classificacoes as $classificacao) {
    if ((int)($classificacao['nacional'] ?? 0) === 1) {
        $totalNacionais++;
    } else {
        $totalNaoNacionais++;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gerenciar Classificações - Painel Administrativo</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="css-admin/admin.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include __DIR__ . '/../estrutura/header2.php'; ?>

<main class="admin-main">

    <?php
        renderAdminHero(
            'Gerenciamento de Classificações',
            'Cadastre campanhas, fases, pontuações e estatísticas dos clubes por temporada.',
            'Admin',
            [
                $totalClassificacoes . ' classificações',
                $totalCompeticoes . ' competições',
                $totalTemporadas . ' temporadas'
            ]
        );
    ?>

    <?php renderAdminFeedback($feedback); ?>

    <section class="admin-resumo">
        <?php renderAdminResumoCard($totalClassificacoes, 'Classificações'); ?>
        <?php renderAdminResumoCard($totalNacionais, 'Nacionais'); ?>
        <?php renderAdminResumoCard($totalNaoNacionais, 'Não nacionais'); ?>
        <?php renderAdminResumoCard($totalTimes, 'Times'); ?>
        <?php renderAdminResumoCard($totalTemporadas, 'Temporadas'); ?>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Adicionar Classificação', 'Cadastro'); ?>

            <form method="POST" action="admin-process.php" class="form-admin">
                <input type="hidden" name="acao" value="inserir_classificacao">
                <?php renderAdminCsrf(); ?>

                <label for="id_competicao">Competição</label>
                <select id="id_competicao" name="id_competicao" data-carregar-temporadas="#id_temporada" required>
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

                <label for="id_time">Time</label>
                <select id="id_time" name="id_time" required>
                    <option value="">Selecione</option>
                    <?php foreach ($times as $time): ?>
                        <option value="<?= (int)$time['id'] ?>">
                            <?= eAdmin($time['nome']) ?><?= !empty($time['estado']) ? ' — ' . eAdmin($time['estado']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="linha-form">
                    <div class="campo-medio">
                        <label for="fase">Fase / Colocação</label>
                        <select id="fase" name="fase" required>
                            <option value="">Selecione</option>
                            <?php renderAdminOptionsFases($FASES_CLASSIFICACAO_ADMIN); ?>
                        </select>
                    </div>

                    <div class="campo-curto">
                        <label for="nacional">Conta no ranking?</label>
                        <select id="nacional" name="nacional">
                            <?php renderAdminOptionsAssoc($OPCOES_SIM_NAO_ADMIN, 1); ?>
                        </select>
                    </div>
                </div>

                <h2>Estatísticas da campanha</h2>

                <div class="linha-form">
                    <div class="campo-curto">
                        <label for="vitorias">Vitórias</label>
                        <input type="number" id="vitorias" name="vitorias" min="0" value="0">
                    </div>

                    <div class="campo-curto">
                        <label for="empates">Empates</label>
                        <input type="number" id="empates" name="empates" min="0" value="0">
                    </div>

                    <div class="campo-curto">
                        <label for="derrotas">Derrotas</label>
                        <input type="number" id="derrotas" name="derrotas" min="0" value="0">
                    </div>
                </div>

                <div class="linha-form">
                    <div class="campo-curto">
                        <label for="gp">Gols Pró</label>
                        <input type="number" id="gp" name="gp" min="0" value="0">
                    </div>

                    <div class="campo-curto">
                        <label for="gc">Gols Contra</label>
                        <input type="number" id="gc" name="gc" min="0" value="0">
                    </div>

                    <div class="campo-curto">
                        <label for="pontos_marcados">Pontos Marcados</label>
                        <input type="number" id="pontos_marcados" name="pontos_marcados" min="0" value="0">
                    </div>
                </div>

                <button type="submit">Adicionar Classificação</button>
            </form>

            <form
                method="POST"
                action="admin-process.php"
                class="form-admin form-correcao"
                onsubmit="return confirm('Deseja recalcular os pontos de todas as classificações?');"
            >
                <input type="hidden" name="acao" value="corrigir_classificacao">
                <?php renderAdminCsrf(); ?>

                <button type="submit">
                    Recalcular Pontuações das Classificações
                </button>
            </form>
        </div>

        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Classificações Cadastradas', $totalClassificacoes . ' registros'); ?>

            <?php
                renderAdminPesquisa(
                    'filtro-classificacoes',
                    $PLACEHOLDERS_ADMIN['pesquisar_classificacao'] ?? 'Pesquisar por competição ou time...'
                );
            ?>

            <div class="com-scroll-1">
                <table class="tabela-listagem" id="tabela-classificacoes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Competição</th>
                            <th>Ano</th>
                            <th>Time</th>
                            <th>Fase</th>
                            <th>Ranking</th>
                            <th>Pontos</th>
                            <th>V</th>
                            <th>E</th>
                            <th>D</th>
                            <th>GP</th>
                            <th>GC</th>
                            <th>Pts Camp.</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($classificacoes)): ?>
                            <?php foreach ($classificacoes as $classificacao): ?>
                                <?php
                                    $idClassificacao = (int)$classificacao['id'];
                                    $nomeTime = $classificacao['nome_time'] ?? '';
                                    $escudoTime = $classificacao['escudo_time'] ?? '';
                                ?>

                                <tr>
                                    <td><?= $idClassificacao ?></td>

                                    <td><?= eAdmin($classificacao['nome_competicao'] ?? '') ?></td>

                                    <td><?= eAdmin($classificacao['ano'] ?? '') ?></td>

                                    <td class="clube-nome">
                                        <?php renderAdminImagemPreview($escudoTime, 'Escudo de ' . $nomeTime, 'escudo-pequeno'); ?>
                                        <?= eAdmin($nomeTime) ?>
                                    </td>

                                    <td><?= eAdmin(adminLabelFase((string)($classificacao['fase'] ?? ''))) ?></td>

                                    <td><?= labelBooleanoAdmin($classificacao['nacional'] ?? 0) ?></td>

                                    <td><?= formatarNumeroAdmin($classificacao['pontos'] ?? 0) ?></td>

                                    <td><?= (int)($classificacao['vitorias'] ?? 0) ?></td>
                                    <td><?= (int)($classificacao['empates'] ?? 0) ?></td>
                                    <td><?= (int)($classificacao['derrotas'] ?? 0) ?></td>
                                    <td><?= (int)($classificacao['gp'] ?? 0) ?></td>
                                    <td><?= (int)($classificacao['gc'] ?? 0) ?></td>
                                    <td><?= (int)($classificacao['pontos_marcados'] ?? 0) ?></td>

                                    <td class="acoes-celula">
                                        <button
                                            type="button"
                                            class="btn-editar-classificacao"
                                            data-id="<?= $idClassificacao ?>"
                                            data-id-competicao="<?= (int)$classificacao['id_competicao'] ?>"
                                            data-id-temporada="<?= (int)$classificacao['id_temporada'] ?>"
                                        >
                                            Editar
                                        </button>

                                        <form
                                            method="POST"
                                            action="admin-process.php"
                                            class="form-inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir esta classificação?');"
                                        >
                                            <input type="hidden" name="acao" value="excluir_classificacao">
                                            <input type="hidden" name="id" value="<?= $idClassificacao ?>">
                                            <?php renderAdminCsrf(); ?>

                                            <button type="submit" class="btn-excluir">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php renderAdminTabelaVazia(14, 'Nenhuma classificação cadastrada.'); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php renderAdminLinksRodape(true, true); ?>

</main>

<!-- MODAL EDITAR CLASSIFICAÇÃO -->
<div id="modal-editar-classificacao" class="modal">
    <div class="modal-content">
        <span class="close" data-modal-close="modal-editar-classificacao">&times;</span>

        <h2>Editar Classificação</h2>

        <form method="POST" action="admin-process.php" id="form-editar-classificacao">
            <input type="hidden" name="acao" value="editar_classificacao">
            <input type="hidden" name="id" id="edit-classificacao-id">
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

            <label for="edit-id-time">Time</label>
            <select id="edit-id-time" name="id_time" required>
                <option value="">Selecione</option>
                <?php foreach ($times as $time): ?>
                    <option value="<?= (int)$time['id'] ?>">
                        <?= eAdmin($time['nome']) ?><?= !empty($time['estado']) ? ' — ' . eAdmin($time['estado']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="linha-form">
                <div class="campo-medio">
                    <label for="edit-fase">Fase / Colocação</label>
                    <select id="edit-fase" name="fase" required>
                        <option value="">Selecione</option>
                        <?php renderAdminOptionsFases($FASES_CLASSIFICACAO_ADMIN); ?>
                    </select>
                </div>

                <div class="campo-curto">
                    <label for="edit-nacional">Conta no ranking?</label>
                    <select id="edit-nacional" name="nacional">
                        <?php renderAdminOptionsAssoc($OPCOES_SIM_NAO_ADMIN); ?>
                    </select>
                </div>
            </div>

            <h2>Estatísticas da campanha</h2>

            <div class="linha-form">
                <div class="campo-curto">
                    <label for="edit-vitorias">Vitórias</label>
                    <input type="number" id="edit-vitorias" name="vitorias" min="0">
                </div>

                <div class="campo-curto">
                    <label for="edit-empates">Empates</label>
                    <input type="number" id="edit-empates" name="empates" min="0">
                </div>

                <div class="campo-curto">
                    <label for="edit-derrotas">Derrotas</label>
                    <input type="number" id="edit-derrotas" name="derrotas" min="0">
                </div>
            </div>

            <div class="linha-form">
                <div class="campo-curto">
                    <label for="edit-gp">Gols Pró</label>
                    <input type="number" id="edit-gp" name="gp" min="0">
                </div>

                <div class="campo-curto">
                    <label for="edit-gc">Gols Contra</label>
                    <input type="number" id="edit-gc" name="gc" min="0">
                </div>

                <div class="campo-curto">
                    <label for="edit-pontos-marcados">Pontos Marcados</label>
                    <input type="number" id="edit-pontos-marcados" name="pontos_marcados" min="0">
                </div>
            </div>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>

<script src="js-admin/admin.js"></script>
<script src="js-admin/admin-classificacao.js"></script>

</body>
</html>