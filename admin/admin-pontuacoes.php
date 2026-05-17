<?php
/* =========================================
   ADMIN-PONTUACOES.PHP
   Gerenciamento de Pontuações por Fase
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

$pontuacoes = $pdo->query("
    SELECT 
        pf.*,
        c.nome AS nome_competicao,
        c.tipo AS tipo_competicao
    FROM pontuacoes_fase pf
    INNER JOIN competicoes c ON c.id = pf.id_competicao
    ORDER BY 
        FIELD(c.tipo, 'Internacional', 'Nacional', 'Regional', 'Estadual'),
        c.nome ASC,
        pf.pontos DESC,
        pf.fase ASC
")->fetchAll(PDO::FETCH_ASSOC);

$totalPontuacoes = count($pontuacoes);
$totalCompeticoes = count($competicoes);

$competicoesComPontuacao = [];
$totalPontosBase = 0;

foreach ($pontuacoes as $pontuacao) {
    $competicoesComPontuacao[(int)$pontuacao['id_competicao']] = true;
    $totalPontosBase += (int)($pontuacao['pontos'] ?? 0);
}

$totalCompeticoesComPontuacao = count($competicoesComPontuacao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gerenciar Pontuações - Painel Administrativo</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="css-admin/admin.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include __DIR__ . '/../estrutura/header2.php'; ?>

<main class="admin-main">

    <?php
        renderAdminHero(
            'Gerenciamento de Pontuações',
            'Cadastre e edite as pontuações-base por fase utilizadas nos rankings e estatísticas.',
            'Admin',
            [
                $totalPontuacoes . ' pontuações cadastradas',
                $totalCompeticoesComPontuacao . ' competições pontuadas'
            ]
        );
    ?>

    <?php renderAdminFeedback($feedback); ?>

    <section class="admin-resumo">
        <?php renderAdminResumoCard($totalPontuacoes, 'Pontuações'); ?>
        <?php renderAdminResumoCard($totalCompeticoesComPontuacao, 'Competições pontuadas'); ?>
        <?php renderAdminResumoCard($totalCompeticoes, 'Competições'); ?>
        <?php renderAdminResumoCard(formatarNumeroAdmin($totalPontosBase), 'Soma base'); ?>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Adicionar Pontuação', 'Cadastro'); ?>

            <form method="POST" action="admin-process.php" class="form-admin">
                <input type="hidden" name="acao" value="inserir_pontuacao">
                <?php renderAdminCsrf(); ?>

                <label for="id_competicao">Competição</label>
                <select id="id_competicao" name="id_competicao" required>
                    <option value="">Selecione</option>
                    <?php foreach ($competicoes as $competicao): ?>
                        <option value="<?= (int)$competicao['id'] ?>">
                            <?= eAdmin($competicao['nome']) ?> — <?= eAdmin($competicao['tipo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="fase">Fase</label>
                <select id="fase" name="fase" required>
                    <option value="">Selecione</option>
                    <?php renderAdminOptionsFases($FASES_CLASSIFICACAO_ADMIN); ?>
                </select>

                <label for="pontos">Pontos</label>
                <input type="number" id="pontos" name="pontos" min="0" required>

                <button type="submit">Adicionar Pontuação</button>
            </form>
        </div>

        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Pontuações Cadastradas', $totalPontuacoes . ' registros'); ?>

            <?php
                renderAdminPesquisa(
                    'filtro-pontuacoes',
                    $PLACEHOLDERS_ADMIN['pesquisar_pontuacao'] ?? 'Pesquisar pela competição...'
                );
            ?>

            <div class="com-scroll-1">
                <table class="tabela-listagem" id="tabela-pontuacoes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Competição</th>
                            <th>Tipo</th>
                            <th>Fase</th>
                            <th>Pontos</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($pontuacoes)): ?>
                            <?php foreach ($pontuacoes as $pontuacao): ?>
                                <?php
                                    $idPontuacao = (int)$pontuacao['id'];
                                    $fase = (string)($pontuacao['fase'] ?? '');
                                ?>

                                <tr>
                                    <td><?= $idPontuacao ?></td>

                                    <td><?= eAdmin($pontuacao['nome_competicao'] ?? '') ?></td>

                                    <td><?= eAdmin($pontuacao['tipo_competicao'] ?? '') ?></td>

                                    <td><?= eAdmin(adminLabelFase($fase)) ?></td>

                                    <td><?= formatarNumeroAdmin($pontuacao['pontos'] ?? 0) ?></td>

                                    <td class="acoes-celula">
                                        <button
                                            type="button"
                                            class="btn-editar-pontuacao"
                                            data-id="<?= $idPontuacao ?>"
                                        >
                                            Editar
                                        </button>

                                        <form
                                            method="POST"
                                            action="admin-process.php"
                                            class="form-inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir esta pontuação?');"
                                        >
                                            <input type="hidden" name="acao" value="excluir_pontuacao">
                                            <input type="hidden" name="id" value="<?= $idPontuacao ?>">
                                            <?php renderAdminCsrf(); ?>

                                            <button type="submit" class="btn-excluir">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php renderAdminTabelaVazia(6, 'Nenhuma pontuação cadastrada.'); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php renderAdminLinksRodape(true, true); ?>

</main>

<!-- MODAL EDITAR PONTUAÇÃO -->
<div id="modal-editar-pontuacao" class="modal">
    <div class="modal-content">
        <span class="close" data-modal-close="modal-editar-pontuacao">&times;</span>

        <h2>Editar Pontuação</h2>

        <form method="POST" action="admin-process.php" id="form-editar-pontuacao">
            <input type="hidden" name="acao" value="editar_pontuacao">
            <input type="hidden" name="id" id="edit-pontuacao-id">
            <?php renderAdminCsrf(); ?>

            <label for="edit-pontuacao-id-competicao">Competição</label>
            <select id="edit-pontuacao-id-competicao" name="id_competicao" required>
                <option value="">Selecione</option>
                <?php foreach ($competicoes as $competicao): ?>
                    <option value="<?= (int)$competicao['id'] ?>">
                        <?= eAdmin($competicao['nome']) ?> — <?= eAdmin($competicao['tipo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="edit-pontuacao-fase">Fase</label>
            <select id="edit-pontuacao-fase" name="fase" required>
                <option value="">Selecione</option>
                <?php renderAdminOptionsFases($FASES_CLASSIFICACAO_ADMIN); ?>
            </select>

            <label for="edit-pontuacao-pontos">Pontos</label>
            <input type="number" id="edit-pontuacao-pontos" name="pontos" min="0" required>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>

<script src="js-admin/admin.js"></script>
<script src="js-admin/admin-pontuacoes.js"></script>

</body>
</html>