<?php
/* =========================================
   RANKING-RENDER.PHP
   Componentes HTML reutilizáveis da área Ranking
   Futebol Brasileiro
========================================= */

/*
  Este arquivo não calcula pontuação.
  Ele apenas renderiza blocos HTML usados pelas páginas de ranking.
*/

/* =========================================
   MENU LATERAL DO RANKING
========================================= */

if (!function_exists('renderMenuRanking')) {
    function renderMenuRanking(array $menu, string $paginaAtual = ''): void
    {
        ?>
        <aside class="menu-lateral menu-ranking">
            <div class="menu-bloco">
                <h2>Ranking</h2>

                <ul>
                    <?php foreach ($menu as $item): ?>
                        <?php
                            $id = $item['id'] ?? '';
                            $label = $item['label'] ?? '';
                            $url = $item['url'] ?? '#';
                            $ativo = $id === $paginaAtual ? 'ativo' : '';
                        ?>

                        <li>
                            <a href="<?= eRanking($url) ?>" class="<?= eRanking($ativo) ?>">
                                <?= eRanking($label) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="menu-bloco">
                <h2>Artigos</h2>

                <ul>
                    <li>
                        <a href="../noticias/artigos.php?categoria=Ranking">
                            Sobre o Ranking
                        </a>
                    </li>

                    <li>
                        <a href="../noticias/artigos.php">
                            Todos os Artigos
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
        <?php
    }
}

/* =========================================
   HERO PADRÃO DO RANKING
========================================= */

if (!function_exists('renderHeroRanking')) {
    function renderHeroRanking(
        string $eyebrow,
        string $titulo,
        string $descricao,
        array $metas = []
    ): void {
        ?>
        <section class="hero-ranking">
            <?php if (!empty($eyebrow)): ?>
                <span class="eyebrow"><?= eRanking($eyebrow) ?></span>
            <?php endif; ?>

            <h1><?= eRanking($titulo) ?></h1>

            <?php if (!empty($descricao)): ?>
                <p><?= eRanking($descricao) ?></p>
            <?php endif; ?>

            <?php if (!empty($metas)): ?>
                <div class="ranking-meta">
                    <?php foreach ($metas as $meta): ?>
                        <?php if (!empty($meta)): ?>
                            <span><?= eRanking($meta) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php
    }
}

/* =========================================
   PESQUISA PADRÃO
========================================= */

if (!function_exists('renderPesquisaRanking')) {
    function renderPesquisaRanking(
        string $placeholder = 'Pesquisar...',
        string $inputId = 'filtro-time',
        string $label = 'Pesquisar'
    ): void {
        ?>
        <section class="card-pesquisa-ranking">
            <div class="pesquisa-time">
                <label for="<?= eRanking($inputId) ?>"><?= eRanking($label) ?></label>

                <input
                    type="text"
                    id="<?= eRanking($inputId) ?>"
                    placeholder="<?= eRanking($placeholder) ?>"
                    autocomplete="off"
                    data-ranking-filter
                >
            </div>
        </section>
        <?php
    }
}

/* =========================================
   TABELA DE RANKING DE CLUBES
========================================= */

if (!function_exists('renderTabelaRankingClubes')) {
    function renderTabelaRankingClubes(
        array $ranking,
        array $colunas,
        array $opcoes = []
    ): void {
        $mostrarDivisao = $opcoes['mostrar_divisao'] ?? false;
        $mostrarEstado = $opcoes['mostrar_estado'] ?? true;
        $linkClube = $opcoes['link_clube'] ?? true;
        $tabelaId = $opcoes['tabela_id'] ?? 'ranking-table';

        /*
          Divisão atual dos clubes.
          Carregamos aqui para manter a renderização autônoma.
        */
        global $pdo;
        $mapeamentoDivisao = [];

        if (isset($pdo) && $mostrarDivisao) {
            $mapeamentoDivisao = carregarDivisoesAtuaisRanking($pdo);
        }
        ?>

        <div class="tabela-scroll">
            <table id="<?= eRanking($tabelaId) ?>" class="tabela-ranking">
                <thead>
                    <tr>
                        <th data-coluna="0">Pos</th>
                        <th data-coluna="1">Clube</th>

                        <?php if ($mostrarEstado): ?>
                            <th>Estado</th>
                        <?php endif; ?>

                        <?php if ($mostrarDivisao): ?>
                            <th>Série</th>
                        <?php endif; ?>

                        <?php foreach ($colunas as $coluna): ?>
                            <th><?= eRanking($coluna['label'] ?? '') ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php $pos = 1; ?>

                    <?php foreach ($ranking as $clube): ?>
                        <?php
                            $idClube = (int)($clube['id'] ?? 0);
                            $nomeClube = $clube['nome'] ?? 'Clube';
                            $estadoClube = $clube['estado'] ?? '';
                            $escudoClube = caminhoEscudoRanking($clube['escudo'] ?? '');
                            $divisao = $mapeamentoDivisao[$idClube] ?? '';
                        ?>

                        <tr>
                            <td><?= $pos++ ?></td>

                            <td class="coluna-clube">
                                <?php if ($linkClube && $idClube > 0): ?>
                                    <a href="../times/detalhes_time.php?id=<?= $idClube ?>" class="clube-ranking-link">
                                        <img
                                            src="<?= $escudoClube ?>"
                                            alt="Escudo de <?= eRanking($nomeClube) ?>"
                                            loading="lazy"
                                            onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                        >

                                        <span><?= eRanking($nomeClube) ?></span>
                                    </a>
                                <?php else: ?>
                                    <div class="clube-ranking-link sem-link">
                                        <img
                                            src="<?= $escudoClube ?>"
                                            alt="Escudo de <?= eRanking($nomeClube) ?>"
                                            loading="lazy"
                                            onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                        >

                                        <span><?= eRanking($nomeClube) ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <?php if ($mostrarEstado): ?>
                                <td><?= eRanking($estadoClube) ?></td>
                            <?php endif; ?>

                            <?php if ($mostrarDivisao): ?>
                                <td>
                                    <?php if (!empty($divisao)): ?>
                                        <span class="badge-serie">
                                            <?= eRanking($divisao) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-serie badge-serie-vazia">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>

                            <?php foreach ($colunas as $coluna): ?>
                                <?php
                                    $key = $coluna['key'] ?? '';
                                    $tipo = $coluna['tipo'] ?? 'numero';
                                    $valor = $clube[$key] ?? 0;
                                ?>

                                <td class="<?= $tipo === 'total' ? 'coluna-total' : '' ?>">
                                    <?php if ($tipo === 'total'): ?>
                                        <strong><?= formatarNumeroRanking($valor) ?></strong>
                                    <?php else: ?>
                                        <?= formatarNumeroRanking($valor) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

/* =========================================
   TABELA DE RANKING DAS FEDERAÇÕES
========================================= */

if (!function_exists('renderTabelaRankingFederacoes')) {
    function renderTabelaRankingFederacoes(
        array $ranking,
        array $colunas,
        array $opcoes = []
    ): void {
        $tabelaId = $opcoes['tabela_id'] ?? 'ranking-table';
        ?>

        <div class="tabela-scroll">
            <table id="<?= eRanking($tabelaId) ?>" class="tabela-ranking tabela-ranking-federacoes">
                <thead>
                    <tr>
                        <th data-coluna="0">Pos</th>
                        <th data-coluna="1">Estado</th>

                        <?php foreach ($colunas as $coluna): ?>
                            <th><?= eRanking($coluna['label'] ?? '') ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php $pos = 1; ?>

                    <?php foreach ($ranking as $fed): ?>
                        <?php
                            $estado = $fed['estado'] ?? '';
                        ?>

                        <tr>
                            <td><?= $pos++ ?></td>

                            <td class="coluna-estado">
                                <span class="badge-estado">
                                    <?= eRanking($estado) ?>
                                </span>
                            </td>

                            <?php foreach ($colunas as $coluna): ?>
                                <?php
                                    $key = $coluna['key'] ?? '';
                                    $tipo = $coluna['tipo'] ?? 'numero';
                                    $valor = $fed[$key] ?? 0;
                                ?>

                                <td class="<?= $tipo === 'total' ? 'coluna-total' : '' ?>">
                                    <?php if ($tipo === 'total'): ?>
                                        <strong><?= formatarNumeroRanking($valor) ?></strong>
                                    <?php else: ?>
                                        <?= formatarNumeroRanking($valor) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

/* =========================================
   TOP RANKING DA INTRODUÇÃO
========================================= */

if (!function_exists('renderTopRankingIntro')) {
    function renderTopRankingIntro(array $ranking): void
    {
        ?>
        <div class="ranking-lista top-ranking-intro">
            <?php foreach ($ranking as $i => $clube): ?>
                <?php
                    $idClube = (int)($clube['id'] ?? 0);
                    $nomeClube = $clube['nome'] ?? 'Clube';
                    $escudoClube = caminhoEscudoRanking($clube['escudo'] ?? '');
                    $total = $clube['total'] ?? 0;
                ?>

                <a
                    href="../times/detalhes_time.php?id=<?= $idClube ?>"
                    class="ranking-item"
                >
                    <span class="ranking-pos"><?= $i + 1 ?>º</span>

                    <div class="ranking-nome">
                        <img
                            src="<?= $escudoClube ?>"
                            alt="Escudo de <?= eRanking($nomeClube) ?>"
                            loading="lazy"
                            onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                        >

                        <span><?= eRanking($nomeClube) ?></span>
                    </div>

                    <span class="ranking-pontos">
                        <?= formatarNumeroRanking($total) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

/* =========================================
   MENSAGEM VAZIA
========================================= */

if (!function_exists('renderMensagemVaziaRanking')) {
    function renderMensagemVaziaRanking(string $mensagem): void
    {
        ?>
        <div class="card-mensagem-vazia">
            <p class="mensagem-vazia">
                <?= eRanking($mensagem) ?>
            </p>
        </div>
        <?php
    }
}

/* =========================================
   CARD DE TEXTO SIMPLES
   Útil para introdução/critério se necessário
========================================= */

if (!function_exists('renderCardTextoRanking')) {
    function renderCardTextoRanking(
        string $titulo,
        string $tag,
        string $conteudoHtml
    ): void {
        ?>
        <section class="card-ranking-introducao">
            <div class="titulo-bloco-ranking">
                <h2><?= eRanking($titulo) ?></h2>

                <?php if (!empty($tag)): ?>
                    <span><?= eRanking($tag) ?></span>
                <?php endif; ?>
            </div>

            <div class="texto-ranking">
                <?= $conteudoHtml ?>
            </div>
        </section>
        <?php
    }
}