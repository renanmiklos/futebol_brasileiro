<?php
/* =========================================
   ADMIN-DIVISOES.PHP
   Gerenciamento das Divisões Atuais
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

$feedback = getFlashAdmin('sucesso_divisao') ?? getFlashAdmin('sucesso');

$times = $pdo->query("
    SELECT id, nome, estado, escudo, extinto
    FROM times
    WHERE extinto = 0
    ORDER BY nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

$divisoes = $pdo->query("
    SELECT 
        da.id_time,
        da.divisao,
        t.nome,
        t.estado,
        t.escudo
    FROM divisao_atual da
    INNER JOIN times t ON t.id = da.id_time
    ORDER BY 
        FIELD(da.divisao, 'A', 'B', 'C', 'D'),
        t.nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

$timesSemDivisao = $pdo->query("
    SELECT 
        t.id,
        t.nome,
        t.estado,
        t.escudo
    FROM times t
    WHERE t.extinto = 0
      AND NOT EXISTS (
          SELECT 1
          FROM divisao_atual da
          WHERE da.id_time = t.id
      )
    ORDER BY t.nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

$totaisDivisao = [
    'A' => 0,
    'B' => 0,
    'C' => 0,
    'D' => 0,
];

foreach ($divisoes as $divisao) {
    $serie = strtoupper((string)($divisao['divisao'] ?? ''));

    if (isset($totaisDivisao[$serie])) {
        $totaisDivisao[$serie]++;
    }
}

$totalDivisoes = count($divisoes);
$totalTimes = count($times);
$totalSemDivisao = count($timesSemDivisao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gerenciar Divisões - Painel Administrativo</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="css-admin/admin.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include __DIR__ . '/../estrutura/header2.php'; ?>

<main class="admin-main">

    <?php
        renderAdminHero(
            'Clubes nas Divisões do Brasileirão',
            'Organize os clubes que atualmente integram as Séries A, B, C e D.',
            'Admin',
            [
                $totalDivisoes . ' clubes em divisões',
                $totalSemDivisao . ' clubes sem divisão definida'
            ]
        );
    ?>

    <?php renderAdminFeedback($feedback); ?>

    <section class="admin-resumo">
        <?php renderAdminResumoCard($totaisDivisao['A'], 'Série A'); ?>
        <?php renderAdminResumoCard($totaisDivisao['B'], 'Série B'); ?>
        <?php renderAdminResumoCard($totaisDivisao['C'], 'Série C'); ?>
        <?php renderAdminResumoCard($totaisDivisao['D'], 'Série D'); ?>
        <?php renderAdminResumoCard($totalSemDivisao, 'Sem divisão'); ?>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Adicionar / Atualizar Divisão', 'Cadastro'); ?>

            <form method="POST" action="admin-process.php" class="form-admin">
                <input type="hidden" name="acao" value="adicionar_divisao_individual">
                <?php renderAdminCsrf(); ?>

                <label for="id_time">Clube</label>
                <select id="id_time" name="id_time" required>
                    <option value="">Selecione</option>
                    <?php foreach ($times as $time): ?>
                        <option value="<?= (int)$time['id'] ?>">
                            <?= eAdmin($time['nome']) ?><?= !empty($time['estado']) ? ' — ' . eAdmin($time['estado']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="divisao">Divisão</label>
                <select id="divisao" name="divisao" required>
                    <option value="">Selecione</option>
                    <?php renderAdminOptionsAssoc($DIVISOES_BRASILEIRAO_ADMIN); ?>
                </select>

                <button type="submit">
                    Salvar Divisão
                </button>
            </form>

            <div class="aviso-sem-clube">
                Se o clube já estiver em uma divisão, o cadastro será atualizado automaticamente.
            </div>
        </div>

        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Clubes sem Divisão Definida', $totalSemDivisao . ' clubes'); ?>

            <?php
                renderAdminPesquisa(
                    'filtro-sem-divisao',
                    'Pesquisar clube sem divisão...'
                );
            ?>

            <div class="com-scroll">
                <table class="tabela-listagem" id="tabela-sem-divisao">
                    <thead>
                        <tr>
                            <th>Escudo</th>
                            <th>Clube</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($timesSemDivisao)): ?>
                            <?php foreach ($timesSemDivisao as $time): ?>
                                <tr>
                                    <td class="escudo-celula">
                                        <?php renderAdminImagemPreview($time['escudo'] ?? '', 'Escudo de ' . ($time['nome'] ?? ''), 'escudo-pequeno'); ?>
                                    </td>

                                    <td class="clube-nome"><?= eAdmin($time['nome'] ?? '') ?></td>
                                    <td><?= eAdmin($time['estado'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php renderAdminTabelaVazia(3, 'Todos os clubes ativos possuem divisão definida.'); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="painel-bloco">
        <div class="painel-coluna">
            <?php renderAdminTituloBloco('Divisões Cadastradas', $totalDivisoes . ' registros'); ?>

            <?php
                renderAdminPesquisa(
                    'filtro-divisoes',
                    'Pesquisar clube nas divisões...'
                );
            ?>

            <div class="com-scroll-1">
                <table class="tabela-listagem" id="tabela-divisoes">
                    <thead>
                        <tr>
                            <th>Divisão</th>
                            <th>Escudo</th>
                            <th>Clube</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($divisoes)): ?>
                            <?php foreach ($divisoes as $item): ?>
                                <?php
                                    $idTime = (int)$item['id_time'];
                                    $nomeTime = $item['nome'] ?? '';
                                    $divisao = strtoupper((string)($item['divisao'] ?? ''));
                                    $labelDivisao = $DIVISOES_BRASILEIRAO_ADMIN[$divisao] ?? $divisao;
                                ?>

                                <tr>
                                    <td>
                                        <span class="badge-admin">
                                            <?= eAdmin($labelDivisao) ?>
                                        </span>
                                    </td>

                                    <td class="escudo-celula">
                                        <?php renderAdminImagemPreview($item['escudo'] ?? '', 'Escudo de ' . $nomeTime, 'escudo-pequeno'); ?>
                                    </td>

                                    <td class="clube-nome"><?= eAdmin($nomeTime) ?></td>

                                    <td><?= eAdmin($item['estado'] ?? '') ?></td>

                                    <td class="acoes-celula">
                                        <form
                                            method="POST"
                                            action="admin-process.php"
                                            class="form-inline"
                                            onsubmit="return confirm('Deseja remover este clube da divisão atual?');"
                                        >
                                            <input type="hidden" name="acao" value="remover_divisao">
                                            <input type="hidden" name="id_time" value="<?= $idTime ?>">
                                            <?php renderAdminCsrf(); ?>

                                            <button type="submit" class="btn-excluir">
                                                Remover
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php renderAdminTabelaVazia(5, 'Nenhuma divisão cadastrada.'); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php renderAdminLinksRodape(true, true); ?>

</main>

<script src="js-admin/admin.js"></script>
<script src="js-admin/admin-divisoes.js"></script>

</body>
</html>