<?php
/* =========================================
   ADMIN-COMPETICOES.PHP
   Gerenciamento de Competições
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
    FROM competicoes
    ORDER BY 
        FIELD(tipo, 'Internacional', 'Nacional', 'Regional', 'Estadual'),
        nome ASC
");

$competicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtFotos = $pdo->query("
    SELECT 
        f.*,
        c.nome AS nome_competicao
    FROM fotos f
    LEFT JOIN competicoes c ON c.id = f.id_competicao
    WHERE f.id_competicao IS NOT NULL
    ORDER BY c.nome ASC, f.id DESC
");

$fotosCompeticoes = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

$totalCompeticoes = count($competicoes);
$totalFotos = count($fotosCompeticoes);

$totaisPorTipo = [
    'Internacional' => 0,
    'Nacional' => 0,
    'Regional' => 0,
    'Estadual' => 0,
];

foreach ($competicoes as $competicao) {
    $tipo = $competicao['tipo'] ?? '';

    if (isset($totaisPorTipo[$tipo])) {
        $totaisPorTipo[$tipo]++;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gerenciar Competições - Painel Administrativo</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="css-admin/admin.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include __DIR__ . '/../estrutura/header2.php'; ?>

<main class="admin-main">

    <?php
        renderAdminHero(
            'Gerenciamento de Competições',
            'Cadastre competições internacionais, nacionais, regionais e estaduais utilizadas no portal.',
            'Admin',
            [
                $totalCompeticoes . ' competições',
                $totalFotos . ' fotos vinculadas'
            ]
        );
    ?>

    <?php renderAdminFeedback($feedback); ?>

    <section class="admin-resumo">
        <?php renderAdminResumoCard($totalCompeticoes, 'Competições'); ?>
        <?php renderAdminResumoCard($totaisPorTipo['Internacional'], 'Internacionais'); ?>
        <?php renderAdminResumoCard($totaisPorTipo['Nacional'], 'Nacionais'); ?>
        <?php renderAdminResumoCard($totaisPorTipo['Regional'], 'Regionais'); ?>
        <?php renderAdminResumoCard($totaisPorTipo['Estadual'], 'Estaduais'); ?>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Adicionar Competição', 'Cadastro'); ?>

            <form method="POST" action="admin-process.php" class="form-admin">
                <input type="hidden" name="acao" value="inserir_competicao">
                <?php renderAdminCsrf(); ?>

                <label for="nome">Nome da competição</label>
                <input type="text" id="nome" name="nome" required>

                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" placeholder="ex.: campeonato-brasileiro-serie-a">

                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo" required>
                    <option value="">Selecione</option>
                    <?php renderAdminOptionsAssoc($TIPOS_COMPETICAO_ADMIN); ?>
                </select>

                <label for="amistoso">Competição amistosa?</label>
                <select id="amistoso" name="amistoso">
                    <?php renderAdminOptionsAssoc($OPCOES_SIM_NAO_ADMIN, 0); ?>
                </select>

                <button type="submit">Adicionar Competição</button>
            </form>

            <hr>

            <?php renderAdminTituloBloco('Adicionar Foto de Competição', 'Galeria'); ?>

            <form method="POST" action="admin-process.php" class="form-admin">
                <input type="hidden" name="acao" value="inserir_foto">
                <input type="hidden" name="voltar_para" value="competicoes">
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
                    placeholder="assets/images/competicoes/imagem.jpg"
                    required
                >

                <label for="foto-id-competicao">Competição</label>
                <select id="foto-id-competicao" name="id_competicao" required>
                    <option value="">Selecione</option>
                    <?php renderAdminOptionsRegistros($competicoes); ?>
                </select>

                <input type="hidden" name="id_temporada" value="">

                <button type="submit">Adicionar Foto</button>
            </form>
        </div>

        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Competições Cadastradas', $totalCompeticoes . ' registros'); ?>

            <?php
                renderAdminPesquisa(
                    'filtro-competicoes',
                    $PLACEHOLDERS_ADMIN['pesquisar_competicao'] ?? 'Pesquisar pelo nome da competição...'
                );
            ?>

            <div class="com-scroll-1">
                <table class="tabela-listagem" id="tabela-competicoes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Slug</th>
                            <th>Tipo</th>
                            <th>Amistoso</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($competicoes)): ?>
                            <?php foreach ($competicoes as $competicao): ?>
                                <?php $idCompeticao = (int)$competicao['id']; ?>

                                <tr>
                                    <td><?= $idCompeticao ?></td>
                                    <td><?= eAdmin($competicao['nome'] ?? '') ?></td>
                                    <td><?= eAdmin($competicao['slug'] ?? '') ?></td>
                                    <td><?= eAdmin($competicao['tipo'] ?? '') ?></td>
                                    <td><?= labelBooleanoAdmin($competicao['amistoso'] ?? 0) ?></td>

                                    <td class="acoes-celula">
                                        <button
                                            type="button"
                                            class="btn-editar-competicao"
                                            data-id="<?= $idCompeticao ?>"
                                        >
                                            Editar
                                        </button>

                                        <form
                                            method="POST"
                                            action="admin-process.php"
                                            class="form-inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir esta competição?');"
                                        >
                                            <input type="hidden" name="acao" value="excluir_competicao">
                                            <input type="hidden" name="id" value="<?= $idCompeticao ?>">
                                            <?php renderAdminCsrf(); ?>

                                            <button type="submit" class="btn-excluir">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php renderAdminTabelaVazia(6, 'Nenhuma competição cadastrada.'); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Fotos Vinculadas a Competições', $totalFotos . ' fotos'); ?>

            <?php
                renderAdminPesquisa(
                    'filtro-fotos-competicoes',
                    $PLACEHOLDERS_ADMIN['pesquisar_foto'] ?? 'Pesquisar pelo título da foto...'
                );
            ?>

            <div class="com-scroll">
                <table class="tabela-listagem" id="tabela-fotos-competicoes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Preview</th>
                            <th>Título</th>
                            <th>Competição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($fotosCompeticoes)): ?>
                            <?php foreach ($fotosCompeticoes as $foto): ?>
                                <?php $idFoto = (int)$foto['id']; ?>

                                <tr>
                                    <td><?= $idFoto ?></td>

                                    <td>
                                        <?php renderAdminImagemPreview($foto['caminho_imagem'] ?? '', $foto['titulo'] ?? 'Foto', 'escudo-pequeno'); ?>
                                    </td>

                                    <td><?= eAdmin($foto['titulo'] ?? '') ?></td>
                                    <td><?= eAdmin($foto['nome_competicao'] ?? 'Sem competição') ?></td>

                                    <td class="acoes-celula">
                                        <button
                                            type="button"
                                            class="btn-editar-foto-competicao"
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
                                            <input type="hidden" name="voltar_para" value="competicoes">
                                            <?php renderAdminCsrf(); ?>

                                            <button type="submit" class="btn-excluir">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php renderAdminTabelaVazia(5, 'Nenhuma foto vinculada a competição.'); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php renderAdminLinksRodape(true, true); ?>

</main>

<!-- MODAL EDITAR COMPETIÇÃO -->
<div id="modal-editar-competicao" class="modal">
    <div class="modal-content">
        <span class="close" data-modal-close="modal-editar-competicao">&times;</span>

        <h2>Editar Competição</h2>

        <form method="POST" action="admin-process.php" id="form-editar-competicao">
            <input type="hidden" name="acao" value="editar_competicao">
            <input type="hidden" name="id" id="edit-competicao-id">
            <?php renderAdminCsrf(); ?>

            <label for="edit-competicao-nome">Nome da competição</label>
            <input type="text" id="edit-competicao-nome" name="nome" required>

            <label for="edit-competicao-slug">Slug</label>
            <input type="text" id="edit-competicao-slug" name="slug">

            <label for="edit-competicao-tipo">Tipo</label>
            <select id="edit-competicao-tipo" name="tipo" required>
                <option value="">Selecione</option>
                <?php renderAdminOptionsAssoc($TIPOS_COMPETICAO_ADMIN); ?>
            </select>

            <label for="edit-competicao-amistoso">Competição amistosa?</label>
            <select id="edit-competicao-amistoso" name="amistoso">
                <?php renderAdminOptionsAssoc($OPCOES_SIM_NAO_ADMIN); ?>
            </select>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>

<!-- MODAL EDITAR FOTO -->
<div id="modal-editar-foto-competicao" class="modal">
    <div class="modal-content">
        <span class="close" data-modal-close="modal-editar-foto-competicao">&times;</span>

        <h2>Editar Foto de Competição</h2>

        <form method="POST" action="admin-process.php" id="form-editar-foto-competicao">
            <input type="hidden" name="acao" value="editar_foto">
            <input type="hidden" name="id" id="edit-foto-id">
            <input type="hidden" name="voltar_para" value="competicoes">
            <?php renderAdminCsrf(); ?>

            <label for="edit-foto-titulo">Título da foto</label>
            <input type="text" id="edit-foto-titulo" name="titulo" required>

            <label for="edit-foto-descricao">Descrição</label>
            <textarea id="edit-foto-descricao" name="descricao"></textarea>

            <label for="edit-foto-caminho">Caminho/URL da imagem</label>
            <input type="text" id="edit-foto-caminho" name="caminho_imagem" required>

            <label for="edit-foto-id-competicao">Competição</label>
            <select id="edit-foto-id-competicao" name="id_competicao" required>
                <option value="">Selecione</option>
                <?php renderAdminOptionsRegistros($competicoes); ?>
            </select>

            <input type="hidden" name="id_temporada" id="edit-foto-id-temporada">

            <button type="submit">Salvar Foto</button>
        </form>
    </div>
</div>

<script src="js-admin/admin.js"></script>
<script src="js-admin/admin-competicoes.js"></script>

</body>
</html>