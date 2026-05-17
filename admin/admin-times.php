<?php
/* =========================================
   ADMIN-TIMES.PHP
   Gerenciamento de Times
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

$stmt = $pdo->query("
    SELECT *
    FROM times
    ORDER BY nome ASC
");

$times = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalTimes = count($times);
$totalAtivos = 0;
$totalExtintos = 0;

foreach ($times as $time) {
    if ((int)($time['extinto'] ?? 0) === 1) {
        $totalExtintos++;
    } else {
        $totalAtivos++;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gerenciar Times - Painel Administrativo</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="css-admin/admin.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include __DIR__ . '/../estrutura/header2.php'; ?>

<main class="admin-main">

    <?php
        renderAdminHero(
            'Gerenciamento de Times',
            'Cadastre, edite e organize os clubes exibidos no portal Futebol Brasileiro.',
            'Admin',
            [
                $totalTimes . ' times cadastrados',
                $totalAtivos . ' ativos',
                $totalExtintos . ' extintos'
            ]
        );
    ?>

    <?php renderAdminFeedback($feedback); ?>

    <section class="admin-resumo">
        <?php renderAdminResumoCard($totalTimes, 'Times'); ?>
        <?php renderAdminResumoCard($totalAtivos, 'Ativos'); ?>
        <?php renderAdminResumoCard($totalExtintos, 'Extintos'); ?>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Adicionar Time', 'Cadastro'); ?>

            <form method="POST" action="admin-process.php" class="form-admin">
                <input type="hidden" name="acao" value="inserir_time">
                <?php renderAdminCsrf(); ?>

                <div class="linha-form">
                    <div class="campo-medio">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>

                    <div class="campo-longo">
                        <label for="nome_completo">Nome completo</label>
                        <input type="text" id="nome_completo" name="nome_completo">
                    </div>
                </div>

                <div class="linha-form">
                    <div class="campo-curto">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="">Selecione</option>
                            <?php renderAdminOptionsAssoc($ESTADOS_BRASILEIROS_ADMIN); ?>
                        </select>
                    </div>

                    <div class="campo-medio">
                        <label for="cidade">Cidade</label>
                        <input type="text" id="cidade" name="cidade">
                    </div>

                    <div class="campo-curto">
                        <label for="fundacao">Fundação</label>
                        <input type="text" id="fundacao" name="fundacao" placeholder="Ex.: 1910">
                    </div>
                </div>

                <div class="linha-form">
                    <div class="campo-medio">
                        <label for="estadio">Estádio</label>
                        <input type="text" id="estadio" name="estadio">
                    </div>

                    <div class="campo-curto">
                        <label for="capacidade">Capacidade</label>
                        <input type="number" id="capacidade" name="capacidade" min="0">
                    </div>

                    <div class="campo-curto">
                        <label for="extinto">Extinto?</label>
                        <select id="extinto" name="extinto">
                            <?php renderAdminOptionsAssoc($OPCOES_SIM_NAO_ADMIN, 0); ?>
                        </select>
                    </div>
                </div>

                <label for="escudo">Caminho/URL do escudo</label>
                <input type="text" id="escudo" name="escudo" placeholder="assets/images/escudos/time.png">

                <label for="time">Imagem principal do time</label>
                <input type="text" id="time" name="time" placeholder="assets/images/times/time.jpg">

                <label for="legenda">Legenda da imagem principal</label>
                <input type="text" id="legenda" name="legenda">

                <label for="historia">História</label>
                <textarea id="historia" name="historia"></textarea>

                <label for="titulos">Títulos</label>
                <textarea id="titulos" name="titulos"></textarea>

                <h2>Imagens extras</h2>

                <?php foreach ($CAMPOS_EXTRAS_TIMES_ADMIN as $extra): ?>
                    <div class="linha-form">
                        <div class="campo-medio">
                            <label for="<?= eAdmin($extra['campo']) ?>"><?= eAdmin($extra['label']) ?></label>
                            <input
                                type="text"
                                id="<?= eAdmin($extra['campo']) ?>"
                                name="<?= eAdmin($extra['campo']) ?>"
                            >
                        </div>

                        <div class="campo-medio">
                            <label for="<?= eAdmin($extra['legenda']) ?>"><?= eAdmin($extra['label_legenda']) ?></label>
                            <input
                                type="text"
                                id="<?= eAdmin($extra['legenda']) ?>"
                                name="<?= eAdmin($extra['legenda']) ?>"
                            >
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit">Adicionar Time</button>
            </form>
        </div>

        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Times Cadastrados', $totalTimes . ' registros'); ?>

            <?php
                renderAdminPesquisa(
                    'filtro-times',
                    $PLACEHOLDERS_ADMIN['pesquisar_time'] ?? 'Pesquisar pelo nome do time...'
                );
            ?>

            <div class="com-scroll-1">
                <table class="tabela-listagem" id="tabela-times">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Escudo</th>
                            <th>Nome</th>
                            <th>Estado</th>
                            <th>Cidade</th>
                            <th>Extinto</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($times)): ?>
                            <?php foreach ($times as $time): ?>
                                <?php
                                    $idTime = (int)$time['id'];
                                    $nomeTime = $time['nome'] ?? '';
                                    $escudo = caminhoImagemAdmin($time['escudo'] ?? '');
                                ?>

                                <tr>
                                    <td><?= $idTime ?></td>

                                    <td class="escudo-celula">
                                        <?php renderAdminImagemPreview($time['escudo'] ?? '', 'Escudo de ' . $nomeTime, 'escudo-pequeno'); ?>
                                    </td>

                                    <td class="clube-nome"><?= eAdmin($nomeTime) ?></td>
                                    <td><?= eAdmin($time['estado'] ?? '') ?></td>
                                    <td><?= eAdmin($time['cidade'] ?? '') ?></td>
                                    <td><?= labelBooleanoAdmin($time['extinto'] ?? 0) ?></td>

                                    <td class="acoes-celula">
                                        <button
                                            type="button"
                                            class="btn-editar-time"
                                            data-id="<?= $idTime ?>"
                                        >
                                            Editar
                                        </button>

                                        <form
                                            method="POST"
                                            action="admin-process.php"
                                            class="form-inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este time?');"
                                        >
                                            <input type="hidden" name="acao" value="excluir_time">
                                            <input type="hidden" name="id" value="<?= $idTime ?>">
                                            <?php renderAdminCsrf(); ?>

                                            <button type="submit" class="btn-excluir">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php renderAdminTabelaVazia(7, 'Nenhum time cadastrado.'); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php renderAdminLinksRodape(true, true); ?>

</main>

<!-- MODAL EDITAR TIME -->
<div id="modal-editar-time" class="modal">
    <div class="modal-content">
        <span class="close" data-modal-close="modal-editar-time">&times;</span>

        <h2>Editar Time</h2>

        <form method="POST" action="admin-process.php" id="form-editar-time">
            <input type="hidden" name="acao" value="editar_time">
            <input type="hidden" name="id" id="edit-id">
            <?php renderAdminCsrf(); ?>

            <div class="linha-form">
                <div class="campo-medio">
                    <label for="edit-nome">Nome</label>
                    <input type="text" id="edit-nome" name="nome" required>
                </div>

                <div class="campo-longo">
                    <label for="edit-nome-completo">Nome completo</label>
                    <input type="text" id="edit-nome-completo" name="nome_completo">
                </div>
            </div>

            <div class="linha-form">
                <div class="campo-curto">
                    <label for="edit-estado">Estado</label>
                    <select id="edit-estado" name="estado" required>
                        <option value="">Selecione</option>
                        <?php renderAdminOptionsAssoc($ESTADOS_BRASILEIROS_ADMIN); ?>
                    </select>
                </div>

                <div class="campo-medio">
                    <label for="edit-cidade">Cidade</label>
                    <input type="text" id="edit-cidade" name="cidade">
                </div>

                <div class="campo-curto">
                    <label for="edit-fundacao">Fundação</label>
                    <input type="text" id="edit-fundacao" name="fundacao">
                </div>
            </div>

            <div class="linha-form">
                <div class="campo-medio">
                    <label for="edit-estadio">Estádio</label>
                    <input type="text" id="edit-estadio" name="estadio">
                </div>

                <div class="campo-curto">
                    <label for="edit-capacidade">Capacidade</label>
                    <input type="number" id="edit-capacidade" name="capacidade" min="0">
                </div>

                <div class="campo-curto">
                    <label for="edit-extinto">Extinto?</label>
                    <select id="edit-extinto" name="extinto">
                        <?php renderAdminOptionsAssoc($OPCOES_SIM_NAO_ADMIN); ?>
                    </select>
                </div>
            </div>

            <label for="edit-escudo">Caminho/URL do escudo</label>
            <input type="text" id="edit-escudo" name="escudo">

            <label for="edit-time">Imagem principal do time</label>
            <input type="text" id="edit-time" name="time">

            <label for="edit-legenda">Legenda da imagem principal</label>
            <input type="text" id="edit-legenda" name="legenda">

            <label for="edit-historia">História</label>
            <textarea id="edit-historia" name="historia"></textarea>

            <label for="edit-titulos">Títulos</label>
            <textarea id="edit-titulos" name="titulos"></textarea>

            <h2>Imagens extras</h2>

            <?php foreach ($CAMPOS_EXTRAS_TIMES_ADMIN as $extra): ?>
                <div class="linha-form">
                    <div class="campo-medio">
                        <label for="edit-<?= eAdmin($extra['campo']) ?>"><?= eAdmin($extra['label']) ?></label>
                        <input
                            type="text"
                            id="edit-<?= eAdmin($extra['campo']) ?>"
                            name="<?= eAdmin($extra['campo']) ?>"
                        >
                    </div>

                    <div class="campo-medio">
                        <label for="edit-<?= eAdmin($extra['legenda']) ?>"><?= eAdmin($extra['label_legenda']) ?></label>
                        <input
                            type="text"
                            id="edit-<?= eAdmin($extra['legenda']) ?>"
                            name="<?= eAdmin($extra['legenda']) ?>"
                        >
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>

<script src="js-admin/admin.js"></script>
<script src="js-admin/admin-times.js"></script>

</body>
</html>