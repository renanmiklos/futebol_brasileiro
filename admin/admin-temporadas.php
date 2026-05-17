<?php
/* =========================================
   ADMIN-TEMPORADAS.PHP
   Gerenciamento de Temporadas
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

$temporadas = $pdo->query("
    SELECT 
        t.*,
        c.nome AS nome_competicao,
        c.tipo AS tipo_competicao
    FROM temporadas t
    INNER JOIN competicoes c ON c.id = t.id_competicao
    ORDER BY 
        c.nome ASC,
        t.ano DESC
")->fetchAll(PDO::FETCH_ASSOC);

$fotosTemporadas = $pdo->query("
    SELECT 
        f.*,
        t.ano,
        c.nome AS nome_competicao
    FROM fotos f
    LEFT JOIN temporadas t ON t.id = f.id_temporada
    LEFT JOIN competicoes c ON c.id = t.id_competicao
    WHERE f.id_temporada IS NOT NULL
    ORDER BY 
        c.nome ASC,
        t.ano DESC,
        f.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$totalTemporadas = count($temporadas);
$totalCompeticoes = count($competicoes);
$totalFotos = count($fotosTemporadas);

$anos = [];

foreach ($temporadas as $temporada) {
    if (!empty($temporada['ano'])) {
        $anos[] = (int)$temporada['ano'];
    }
}

$anoInicio = !empty($anos) ? min($anos) : null;
$anoFim = !empty($anos) ? max($anos) : null;
$periodoTexto = $anoInicio && $anoFim ? $anoInicio . '–' . $anoFim : 'Sem período definido';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gerenciar Temporadas - Painel Administrativo</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="css-admin/admin.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include __DIR__ . '/../estrutura/header2.php'; ?>

<main class="admin-main">

    <?php
        renderAdminHero(
            'Gerenciamento de Temporadas',
            'Cadastre temporadas, anos e descrições históricas das competições do portal.',
            'Admin',
            [
                $totalTemporadas . ' temporadas',
                $totalCompeticoes . ' competições',
                'Período: ' . $periodoTexto
            ]
        );
    ?>

    <?php renderAdminFeedback($feedback); ?>

    <section class="admin-resumo">
        <?php renderAdminResumoCard($totalTemporadas, 'Temporadas'); ?>
        <?php renderAdminResumoCard($totalCompeticoes, 'Competições'); ?>
        <?php renderAdminResumoCard($totalFotos, 'Fotos vinculadas'); ?>
        <?php renderAdminResumoCard($periodoTexto, 'Período'); ?>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Adicionar Temporada', 'Cadastro'); ?>

            <form method="POST" action="admin-process.php" class="form-admin">
                <input type="hidden" name="acao" value="inserir_temporada">
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

                <label for="ano">Ano</label>
                <input
                    type="number"
                    id="ano"
                    name="ano"
                    min="1800"
                    max="2100"
                    required
                >

                <label for="descricao">Descrição</label>
                <textarea
                    id="descricao"
                    name="descricao"
                    placeholder="Descrição histórica, observações ou contexto da temporada."
                ></textarea>

                <button type="submit">Adicionar Temporada</button>
            </form>

            <hr>

            <?php renderAdminTituloBloco('Adicionar Foto de Temporada', 'Galeria'); ?>

            <form method="POST" action="admin-process.php" class="form-admin">
                <input type="hidden" name="acao" value="inserir_foto">
                <input type="hidden" name="voltar_para" value="temporadas">
                <?php renderAdminCsrf(); ?>

                <label for="foto-titulo">Título da foto</label>
                <input type="text" id="foto-titulo" name="titulo" required>

                <label for="foto-descricao">Descrição</label>
                <textarea id="foto-descricao" name="descricao"></textarea>

                <label for="foto-caminho">Caminho/URL da imagem</label>
                <input
                    type="text"
                    id="foto-caminho"
                    name="caminho_imagem"
                    placeholder="assets/images/temporadas/imagem.jpg"
                    required
                >

                <label for="foto-id-temporada">Temporada</label>
                <select id="foto-id-temporada" name="id_temporada" required>
                    <option value="">Selecione</option>
                    <?php foreach ($temporadas as $temporada): ?>
                        <option value="<?= (int)$temporada['id'] ?>">
                            <?= eAdmin($temporada['nome_competicao']) ?> — <?= eAdmin($temporada['ano']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="hidden" name="id_competicao" value="">

                <button type="submit">Adicionar Foto</button>
            </form>
        </div>

        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Temporadas Cadastradas', $totalTemporadas . ' registros'); ?>

            <?php
                renderAdminPesquisa(
                    'filtro-temporadas',
                    $PLACEHOLDERS_ADMIN['pesquisar_temporada'] ?? 'Pesquisar pelo ano...'
                );
            ?>

            <div class="com-scroll-1">
                <table class="tabela-listagem" id="tabela-temporadas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Competição</th>
                            <th>Tipo</th>
                            <th>Ano</th>
                            <th>Descrição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($temporadas)): ?>
                            <?php foreach ($temporadas as $temporada): ?>
                                <?php $idTemporada = (int)$temporada['id']; ?>

                                <tr>
                                    <td><?= $idTemporada ?></td>

                                    <td><?= eAdmin($temporada['nome_competicao'] ?? '') ?></td>

                                    <td><?= eAdmin($temporada['tipo_competicao'] ?? '') ?></td>

                                    <td><?= eAdmin($temporada['ano'] ?? '') ?></td>

                                    <td><?= eAdmin(resumirTextoAdmin($temporada['descricao'] ?? '', 90)) ?></td>

                                    <td class="acoes-celula">
                                        <button
                                            type="button"
                                            class="btn-editar-temporada"
                                            data-id="<?= $idTemporada ?>"
                                        >
                                            Editar
                                        </button>

                                        <form
                                            method="POST"
                                            action="admin-process.php"
                                            class="form-inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir esta temporada?');"
                                        >
                                            <input type="hidden" name="acao" value="excluir_temporada">
                                            <input type="hidden" name="id" value="<?= $idTemporada ?>">
                                            <?php renderAdminCsrf(); ?>

                                            <button type="submit" class="btn-excluir">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php renderAdminTabelaVazia(6, 'Nenhuma temporada cadastrada.'); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Fotos Vinculadas a Temporadas', $totalFotos . ' fotos'); ?>

            <?php
                renderAdminPesquisa(
                    'filtro-fotos-temporadas',
                    $PLACEHOLDERS_ADMIN['pesquisar_foto'] ?? 'Pesquisar pelo título da foto...'
                );
            ?>

            <div class="com-scroll">
                <table class="tabela-listagem" id="tabela-fotos-temporadas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Preview</th>
                            <th>Título</th>
                            <th>Competição</th>
                            <th>Ano</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($fotosTemporadas)): ?>
                            <?php foreach ($fotosTemporadas as $foto): ?>
                                <?php $idFoto = (int)$foto['id']; ?>

                                <tr>
                                    <td><?= $idFoto ?></td>

                                    <td>
                                        <?php renderAdminImagemPreview($foto['caminho_imagem'] ?? '', $foto['titulo'] ?? 'Foto', 'escudo-pequeno'); ?>
                                    </td>

                                    <td><?= eAdmin($foto['titulo'] ?? '') ?></td>

                                    <td><?= eAdmin($foto['nome_competicao'] ?? 'Sem competição') ?></td>

                                    <td><?= eAdmin($foto['ano'] ?? '') ?></td>

                                    <td class="acoes-celula">
                                        <button
                                            type="button"
                                            class="btn-editar-foto-temporada"
                                            data-id="<?= $idFoto ?>"
                                        >
                                            Editar
                                        </button>

                                        <form
                                            method="POST"
                                            action="admin-process.php"
                                            class="form-inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir esta foto?');"
                                        >
                                            <input type="hidden" name="acao" value="excluir_foto">
                                            <input type="hidden" name="id" value="<?= $idFoto ?>">
                                            <input type="hidden" name="voltar_para" value="temporadas">
                                            <?php renderAdminCsrf(); ?>

                                            <button type="submit" class="btn-excluir">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php renderAdminTabelaVazia(6, 'Nenhuma foto vinculada a temporada.'); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php renderAdminLinksRodape(true, true); ?>

</main>

<!-- MODAL EDITAR TEMPORADA -->
<div id="modal-editar-temporada" class="modal">
    <div class="modal-content">
        <span class="close" data-modal-close="modal-editar-temporada">&times;</span>

        <h2>Editar Temporada</h2>

        <form method="POST" action="admin-process.php" id="form-editar-temporada">
            <input type="hidden" name="acao" value="editar_temporada">
            <input type="hidden" name="id" id="edit-temporada-id">
            <?php renderAdminCsrf(); ?>

            <label for="edit-temporada-id-competicao">Competição</label>
            <select id="edit-temporada-id-competicao" name="id_competicao" required>
                <option value="">Selecione</option>
                <?php foreach ($competicoes as $competicao): ?>
                    <option value="<?= (int)$competicao['id'] ?>">
                        <?= eAdmin($competicao['nome']) ?> — <?= eAdmin($competicao['tipo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="edit-temporada-ano">Ano</label>
            <input
                type="number"
                id="edit-temporada-ano"
                name="ano"
                min="1800"
                max="2100"
                required
            >

            <label for="edit-temporada-descricao">Descrição</label>
            <textarea id="edit-temporada-descricao" name="descricao"></textarea>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>

<!-- MODAL EDITAR FOTO -->
<div id="modal-editar-foto-temporada" class="modal">
    <div class="modal-content">
        <span class="close" data-modal-close="modal-editar-foto-temporada">&times;</span>

        <h2>Editar Foto de Temporada</h2>

        <form method="POST" action="admin-process.php" id="form-editar-foto-temporada">
            <input type="hidden" name="acao" value="editar_foto">
            <input type="hidden" name="id" id="edit-foto-id">
            <input type="hidden" name="voltar_para" value="temporadas">
            <?php renderAdminCsrf(); ?>

            <label for="edit-foto-titulo">Título da foto</label>
            <input type="text" id="edit-foto-titulo" name="titulo" required>

            <label for="edit-foto-descricao">Descrição</label>
            <textarea id="edit-foto-descricao" name="descricao"></textarea>

            <label for="edit-foto-caminho">Caminho/URL da imagem</label>
            <input type="text" id="edit-foto-caminho" name="caminho_imagem" required>

            <label for="edit-foto-id-temporada">Temporada</label>
            <select id="edit-foto-id-temporada" name="id_temporada" required>
                <option value="">Selecione</option>
                <?php foreach ($temporadas as $temporada): ?>
                    <option value="<?= (int)$temporada['id'] ?>">
                        <?= eAdmin($temporada['nome_competicao']) ?> — <?= eAdmin($temporada['ano']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="hidden" name="id_competicao" id="edit-foto-id-competicao">

            <button type="submit">Salvar Foto</button>
        </form>
    </div>
</div>

<script src="js-admin/admin.js"></script>
<script src="js-admin/admin-temporadas.js"></script>

</body>
</html>