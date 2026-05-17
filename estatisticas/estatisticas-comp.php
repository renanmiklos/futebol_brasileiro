<?php
// estatisticas-comp.php

require_once 'estatistica-process.php';

/* =========================================
   GARANTIA DE VARIÁVEIS VINDAS DO PROCESSAMENTO
========================================= */

$tituloOriginal = $tituloOriginal ?? 'Estatística';
$descricao = $descricao ?? '';
$dados = $dados ?? [];
$tabela_estatisticas = $tabela_estatisticas ?? [];

$coluna_ordem = $coluna_ordem ?? 'pontos';
$tipo_ordem = $tipo_ordem ?? 'DESC';

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

if (!function_exists('eComp')) {
    function eComp($valor)
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('caminhoEscudoComp')) {
    function caminhoEscudoComp($escudo, $fallback = '../assets/images/escudo_padrao.png')
    {
        if (empty($escudo)) {
            return $fallback;
        }

        $escudo = trim((string)$escudo);

        if (
            str_starts_with($escudo, 'http://') ||
            str_starts_with($escudo, 'https://') ||
            str_starts_with($escudo, 'data:')
        ) {
            return eComp($escudo);
        }

        return '../' . eComp(ltrim($escudo, '/'));
    }
}

if (!function_exists('formatarNumeroComp')) {
    function formatarNumeroComp($valor)
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        if (is_numeric($valor)) {
            return number_format((float)$valor, 0, ',', '.');
        }

        return eComp($valor);
    }
}

if (!function_exists('link_coluna')) {
    function link_coluna($coluna, $tituloOriginal, $coluna_ordem, $tipo_ordem)
    {
        $tipo_ordem = strtoupper((string)$tipo_ordem);

        $novaOrdem = ($coluna_ordem === $coluna && $tipo_ordem === 'DESC') ? 'ASC' : 'DESC';

        return 'estatisticas-comp.php?item=' . urlencode($tituloOriginal)
            . '&coluna_ordem=' . urlencode($coluna)
            . '&tipo_ordem=' . urlencode($novaOrdem);
    }
}

if (!function_exists('classeOrdenacaoComp')) {
    function classeOrdenacaoComp($coluna, $coluna_ordem, $tipo_ordem)
    {
        if ($coluna !== $coluna_ordem) {
            return '';
        }

        return strtoupper((string)$tipo_ordem) === 'ASC' ? 'ordenado asc' : 'ordenado desc';
    }
}

if (!function_exists('renderEscudoNomeClubeComp')) {
    function renderEscudoNomeClubeComp($linha)
    {
        $nome = $linha['nome_time'] ?? $linha['clube'] ?? $linha['time'] ?? 'Clube não informado';
        $escudo = $linha['escudo'] ?? '';
        $idTime = $linha['id_time'] ?? null;

        $html = '<div class="celula-clube">';

        $html .= '<img 
            src="' . caminhoEscudoComp($escudo) . '" 
            alt="Escudo de ' . eComp($nome) . '" 
            class="escudo-clube"
            onerror="this.onerror=null; this.src=\'../assets/images/escudo_padrao.png\';"
        >';

        if (!empty($idTime)) {
            $html .= '<a href="../times/detalhes_time.php?id=' . (int)$idTime . '">' . eComp($nome) . '</a>';
        } else {
            $html .= '<span>' . eComp($nome) . '</span>';
        }

        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('tituloTabelaComp')) {
    function tituloTabelaComp($tipoTabela)
    {
        switch ($tipoTabela) {
            case 'rebaixados':
                return 'Rebaixados com Mais Pontos';

            case 'nao_rebaixados':
                return 'Não-Rebaixados com Menor Pontuação';

            case 'ultimos':
                return 'Últimos Colocados';

            case 'campeoes_pontos':
                return 'Campeões por Pontos Marcados';

            case 'participacoes':
                return 'Participações e Títulos por Clube';

            default:
                return 'Resumo Estatístico por Clube';
        }
    }
}

/* =========================================
   DETECÇÃO DO TIPO DE TABELA
========================================= */

$primeira_linha = !empty($tabela_estatisticas) ? reset($tabela_estatisticas) : [];

$eh_participacao = is_array($primeira_linha) && isset($primeira_linha['participacoes']);

$eh_campeoes_pontos_corridos = is_array($primeira_linha)
    && isset($primeira_linha['ano'])
    && isset($primeira_linha['pontos_marcados']);

$titulo_minusculo = mb_strtolower((string)$tituloOriginal, 'UTF-8');

$eh_rebaixados_mais_pontos = $eh_campeoes_pontos_corridos
    && (strpos($titulo_minusculo, 'rebaixad') !== false)
    && (strpos($titulo_minusculo, 'mais') !== false);

$eh_nao_rebaixados_menor = $eh_campeoes_pontos_corridos
    && (strpos($titulo_minusculo, 'rebaixad') !== false)
    && (
        strpos($titulo_minusculo, 'menor') !== false ||
        strpos($titulo_minusculo, 'não') !== false ||
        strpos($titulo_minusculo, 'nao') !== false
    );

$eh_ultimos_colocados = $eh_campeoes_pontos_corridos
    && (
        strpos($titulo_minusculo, 'ultim') !== false ||
        strpos($titulo_minusculo, 'últim') !== false ||
        strpos($titulo_minusculo, 'ultimo') !== false
    )
    && (
        strpos($titulo_minusculo, 'coloc') !== false ||
        strpos($titulo_minusculo, 'colocados') !== false
    );

$tipoTabela = 'resumo';

if ($eh_rebaixados_mais_pontos) {
    $tipoTabela = 'rebaixados';
} elseif ($eh_nao_rebaixados_menor) {
    $tipoTabela = 'nao_rebaixados';
} elseif ($eh_ultimos_colocados) {
    $tipoTabela = 'ultimos';
} elseif ($eh_campeoes_pontos_corridos) {
    $tipoTabela = 'campeoes_pontos';
} elseif ($eh_participacao) {
    $tipoTabela = 'participacoes';
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= eComp($tituloOriginal) ?> - Futebol Brasileiro</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="../estatisticas/css-estatisticas/estatisticas-comp.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-estatisticas-comp">
        <div class="container">

            <a href="estatisticas.php" class="voltar-link">
                ← Voltar para Estatísticas
            </a>

            <div class="layout-estatisticas-comp">

                <aside class="menu-lateral menu-estatisticas-comp">
                    <div class="menu-bloco">
                        <h2>Detalhes</h2>

                        <ul>
                            <li>
                                <a href="#" class="ativo">
                                    <?= eComp($tituloOriginal) ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </aside>

                <div class="conteudo-estatisticas-comp">

                    <section class="hero-estatisticas hero-categoria-estatisticas">
                        <span class="eyebrow">Estatísticas</span>

                        <h1><?= eComp($tituloOriginal) ?></h1>

                        <?php if (!empty($descricao)): ?>
                            <p class="descricao-intro">
                                <?= eComp($descricao) ?>
                            </p>
                        <?php endif; ?>
                    </section>

                    <?php if (!empty($dados)): ?>
                        <section class="card-estatisticas lista-dados-estatisticas">
                            <div class="titulo-bloco-estatisticas">
                                <h2>Resumo</h2>
                                <span><?= count($dados) ?> item<?= count($dados) === 1 ? '' : 's' ?></span>
                            </div>

                            <div class="lista-estatisticas-simples">
                                <ul>
                                    <?php foreach ($dados as $dado): ?>
                                        <li>
                                            <span><?= eComp($dado) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($tabela_estatisticas)): ?>

                        <section class="card-estatisticas tabela-wrapper-estatisticas-comp">

                            <div class="titulo-bloco-estatisticas">
                                <h2><?= eComp(tituloTabelaComp($tipoTabela)) ?></h2>
                                <span><?= count($tabela_estatisticas) ?> registro<?= count($tabela_estatisticas) === 1 ? '' : 's' ?></span>
                            </div>

                            <div class="pesquisa-time">
                                <input
                                    type="text"
                                    id="filtro-time"
                                    placeholder="Pesquisar por nome do time..."
                                    autocomplete="off"
                                >
                            </div>

                            <div class="tabela-scroll">

                                <?php if (in_array($tipoTabela, ['rebaixados', 'nao_rebaixados', 'ultimos', 'campeoes_pontos'], true)): ?>

                                    <table class="tabela-estatisticas-comp" id="tabela-estatisticas-comp">
                                        <thead>
                                            <tr>
                                                <th>Pos</th>
                                                <th>Clube</th>
                                                <th>Ano</th>
                                                <th>Pontos Marcados</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php $i = 1; foreach ($tabela_estatisticas as $linha): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>

                                                    <td>
                                                        <?= renderEscudoNomeClubeComp($linha) ?>
                                                    </td>

                                                    <td>
                                                        <?= eComp($linha['ano'] ?? '—') ?>
                                                    </td>

                                                    <td>
                                                        <span class="badge-pontos">
                                                            <?= formatarNumeroComp($linha['pontos_marcados'] ?? 0) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                <?php elseif ($tipoTabela === 'participacoes'): ?>

                                    <table class="tabela-estatisticas-comp" id="tabela-estatisticas-comp">
                                        <thead>
                                            <tr>
                                                <th>Pos</th>
                                                <th>Clube</th>
                                                <th>Participações</th>
                                                <th>Títulos</th>
                                                <th>Top 4</th>
                                                <th>Última</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php $i = 1; foreach ($tabela_estatisticas as $linha): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>

                                                    <td>
                                                        <?= renderEscudoNomeClubeComp($linha) ?>
                                                    </td>

                                                    <td>
                                                        <span class="badge-pontos">
                                                            <?= formatarNumeroComp($linha['participacoes'] ?? 0) ?>
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <?php if (!empty($linha['qtd_titulos']) && (int)$linha['qtd_titulos'] > 0): ?>
                                                            <span class="titulos-anos">
                                                                <?= formatarNumeroComp($linha['qtd_titulos']) ?>

                                                                <?php if (!empty($linha['anos_titulos'])): ?>
                                                                    <small>(<?= eComp($linha['anos_titulos']) ?>)</small>
                                                                <?php endif; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            0
                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                        <?= formatarNumeroComp($linha['top4'] ?? 0) ?>
                                                    </td>

                                                    <td>
                                                        <?= eComp($linha['ultima_participacao'] ?? '—') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                <?php else: ?>

                                    <table class="tabela-estatisticas-comp" id="tabela-estatisticas-comp">
                                        <thead>
                                            <tr>
                                                <th>Pos</th>

                                                <th>
                                                    <a 
                                                        href="<?= eComp(link_coluna('nome_time', $tituloOriginal, $coluna_ordem, $tipo_ordem)) ?>"
                                                        class="<?= eComp(classeOrdenacaoComp('nome_time', $coluna_ordem, $tipo_ordem)) ?>"
                                                    >
                                                        Clube
                                                    </a>
                                                </th>

                                                <?php if (isset($primeira_linha['pontos_marcados'])): ?>
                                                    <th>
                                                        <a 
                                                            href="<?= eComp(link_coluna('pontos_marcados', $tituloOriginal, $coluna_ordem, $tipo_ordem)) ?>"
                                                            class="<?= eComp(classeOrdenacaoComp('pontos_marcados', $coluna_ordem, $tipo_ordem)) ?>"
                                                        >
                                                            Pontos Marcados
                                                        </a>
                                                    </th>
                                                <?php endif; ?>

                                                <th>
                                                    <a 
                                                        href="<?= eComp(link_coluna('jogos', $tituloOriginal, $coluna_ordem, $tipo_ordem)) ?>"
                                                        class="<?= eComp(classeOrdenacaoComp('jogos', $coluna_ordem, $tipo_ordem)) ?>"
                                                    >
                                                        Jogos
                                                    </a>
                                                </th>

                                                <th>
                                                    <a 
                                                        href="<?= eComp(link_coluna('vitorias', $tituloOriginal, $coluna_ordem, $tipo_ordem)) ?>"
                                                        class="<?= eComp(classeOrdenacaoComp('vitorias', $coluna_ordem, $tipo_ordem)) ?>"
                                                    >
                                                        Vitórias
                                                    </a>
                                                </th>

                                                <th>
                                                    <a 
                                                        href="<?= eComp(link_coluna('empates', $tituloOriginal, $coluna_ordem, $tipo_ordem)) ?>"
                                                        class="<?= eComp(classeOrdenacaoComp('empates', $coluna_ordem, $tipo_ordem)) ?>"
                                                    >
                                                        Empates
                                                    </a>
                                                </th>

                                                <th>
                                                    <a 
                                                        href="<?= eComp(link_coluna('derrotas', $tituloOriginal, $coluna_ordem, $tipo_ordem)) ?>"
                                                        class="<?= eComp(classeOrdenacaoComp('derrotas', $coluna_ordem, $tipo_ordem)) ?>"
                                                    >
                                                        Derrotas
                                                    </a>
                                                </th>

                                                <th>
                                                    <a 
                                                        href="<?= eComp(link_coluna('gols_pro', $tituloOriginal, $coluna_ordem, $tipo_ordem)) ?>"
                                                        class="<?= eComp(classeOrdenacaoComp('gols_pro', $coluna_ordem, $tipo_ordem)) ?>"
                                                    >
                                                        Gols Pró
                                                    </a>
                                                </th>

                                                <th>
                                                    <a 
                                                        href="<?= eComp(link_coluna('gols_contra', $tituloOriginal, $coluna_ordem, $tipo_ordem)) ?>"
                                                        class="<?= eComp(classeOrdenacaoComp('gols_contra', $coluna_ordem, $tipo_ordem)) ?>"
                                                    >
                                                        Gols Contra
                                                    </a>
                                                </th>

                                                <th>
                                                    <a 
                                                        href="<?= eComp(link_coluna('saldo', $tituloOriginal, $coluna_ordem, $tipo_ordem)) ?>"
                                                        class="<?= eComp(classeOrdenacaoComp('saldo', $coluna_ordem, $tipo_ordem)) ?>"
                                                    >
                                                        Saldo
                                                    </a>
                                                </th>

                                                <th>
                                                    <a 
                                                        href="<?= eComp(link_coluna('pontos', $tituloOriginal, $coluna_ordem, $tipo_ordem)) ?>"
                                                        class="<?= eComp(classeOrdenacaoComp('pontos', $coluna_ordem, $tipo_ordem)) ?>"
                                                    >
                                                        Pontos
                                                    </a>
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php $i = 1; foreach ($tabela_estatisticas as $linha): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>

                                                    <td>
                                                        <?= renderEscudoNomeClubeComp($linha) ?>
                                                    </td>

                                                    <?php if (isset($primeira_linha['pontos_marcados'])): ?>
                                                        <td>
                                                            <?= formatarNumeroComp($linha['pontos_marcados'] ?? '-') ?>
                                                        </td>
                                                    <?php endif; ?>

                                                    <td><?= formatarNumeroComp($linha['jogos'] ?? '-') ?></td>
                                                    <td><?= formatarNumeroComp($linha['vitorias'] ?? '-') ?></td>
                                                    <td><?= formatarNumeroComp($linha['empates'] ?? '-') ?></td>
                                                    <td><?= formatarNumeroComp($linha['derrotas'] ?? '-') ?></td>
                                                    <td><?= formatarNumeroComp($linha['gols_pro'] ?? '-') ?></td>
                                                    <td><?= formatarNumeroComp($linha['gols_contra'] ?? '-') ?></td>
                                                    <td><?= formatarNumeroComp($linha['saldo'] ?? '-') ?></td>
                                                    <td>
                                                        <span class="badge-pontos">
                                                            <?= formatarNumeroComp($linha['pontos'] ?? '-') ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                <?php endif; ?>

                            </div>

                        </section>

                    <?php elseif (empty($dados)): ?>

                        <section class="card-estatisticas">
                            <p class="mensagem-vazia">
                                Nenhum dado encontrado para esta estatística.
                            </p>
                        </section>

                    <?php endif; ?>

                </div>

            </div>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

<script src="../estatisticas/js-estatisticas/estatisticas-comp.js"></script>

</body>
</html>