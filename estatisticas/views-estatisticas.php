<?php
// views-estatisticas.php

/* =========================================
   FUNÇÕES AUXILIARES DA VIEW
========================================= */

if (!function_exists('e')) {
    function e($valor)
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatarTipo')) {
    function formatarTipo($tipo)
    {
        $tipo = strtolower(trim((string)$tipo));

        switch ($tipo) {
            case 'internacional':
                return 'Internacional';

            case 'nacional':
                return 'Nacional';

            case 'regional':
                return 'Regional';

            case 'estadual':
                return 'Estadual';

            default:
                return ucfirst($tipo);
        }
    }
}

if (!function_exists('formatarNumeroViewEstatisticas')) {
    function formatarNumeroViewEstatisticas($valor)
    {
        if ($valor === null || $valor === '' || !is_numeric($valor)) {
            return '0';
        }

        return number_format((float)$valor, 0, ',', '.');
    }
}

if (!function_exists('normalizarTextoEstatisticas')) {
    function normalizarTextoEstatisticas($texto)
    {
        $texto = trim((string)$texto);
        $texto = mb_strtolower($texto, 'UTF-8');

        $mapa = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
            'é' => 'e', 'ê' => 'e',
            'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u',
            'ç' => 'c',
            '–' => '-',
            '—' => '-'
        ];

        return strtr($texto, $mapa);
    }
}

if (!function_exists('nomeFaseLegivelEstatisticas')) {
    function nomeFaseLegivelEstatisticas($fase)
    {
        $nomes = [
            'Camp'       => 'Campeão',
            '1º'         => '1º',
            'Vice'       => 'Vice',
            '2º'         => '2º',
            'SF'         => 'Semifinalista',
            'QF'         => 'Quartas',
            'OF'         => 'Oitavas',
            '1F'         => '1ª Fase',
            '2F'         => '2ª Fase',
            'Pre'        => 'Pré',
            'Pre1'       => 'Pré-1',
            'Pre2'       => 'Pré-2',
            'Pre3'       => 'Pré-3',
            'Grupo'      => 'Fase de Grupos',
            'Principal'  => 'Fase Principal',
            'Eliminator' => 'Fase Eliminatória',
            'Regional'   => 'Fase Regional',
            '3º'         => '3º',
            '4º'         => '4º',
            '5º'         => '5º',
            '6º'         => '6º',
            '7º'         => '7º',
            '8º'         => '8º',
            '9º'         => '9º',
            '10º'        => '10º',
            '11º'        => '11º',
            '12º'        => '12º',
            '13º'        => '13º',
            '14º'        => '14º',
            '15º'        => '15º',
            '16º'        => '16º',
            '17º'        => '17º',
            '18º'        => '18º',
            '19º'        => '19º',
            '20º'        => '20º',
            '21º'        => '21º',
            '22º'        => '22º',
            '23º'        => '23º',
            '24º'        => '24º',
            'Reb'        => 'Rebaixado',
            'Playoff'    => 'Playoff'
        ];

        return $nomes[$fase] ?? ucfirst(strtolower((string)$fase));
    }
}

if (!function_exists('ordenarFasesEstatisticas')) {
    function ordenarFasesEstatisticas($fases, $idCompeticao)
    {
        if (empty($fases)) {
            return [];
        }

        $mapa = [];

        foreach ($fases as $fase) {
            $mapa[$fase['fase']] = $fase;
        }

        /*
          Ordem especial para Série A e Série B, onde há fases históricas,
          posições finais e rebaixamento.
        */
        if (in_array((int)$idCompeticao, [19, 20], true)) {
            $ordem = [
                'Camp',
                '1º',
                'Vice',
                '2º',
                'SF',
                'QF',
                'OF',
                'Principal',
                'Grupo',
                'Eliminator',
                '3º', '4º', '5º', '6º', '7º', '8º', '9º', '10º',
                '11º', '12º', '13º', '14º', '15º', '16º', '17º', '18º',
                '19º', '20º', '21º', '22º', '23º', '24º',
                'Reb'
            ];

            $ordenadas = [];

            foreach ($ordem as $faseKey) {
                if (isset($mapa[$faseKey])) {
                    $ordenadas[] = $mapa[$faseKey];
                    unset($mapa[$faseKey]);
                }
            }

            foreach ($mapa as $faseRestante) {
                $ordenadas[] = $faseRestante;
            }

            return $ordenadas;
        }

        usort($fases, function ($a, $b) {
            $pontosA = (float)($a['pontos'] ?? 0);
            $pontosB = (float)($b['pontos'] ?? 0);

            if ($pontosA === $pontosB) {
                return strcmp((string)$a['fase'], (string)$b['fase']);
            }

            return $pontosB <=> $pontosA;
        });

        return $fases;
    }
}

if (!function_exists('obterIdCompeticaoPorNomeEstatisticas')) {
    function obterIdCompeticaoPorNomeEstatisticas(PDO $pdo, $nomeCompeticao)
    {
        $stmt = $pdo->prepare("
            SELECT id
            FROM competicoes
            WHERE nome = ?
            LIMIT 1
        ");

        $stmt->execute([$nomeCompeticao]);
        $id = $stmt->fetchColumn();

        return $id ? (int)$id : null;
    }
}

if (!function_exists('pontuacaoDinamicaViewEstatisticas')) {
    function pontuacaoDinamicaViewEstatisticas(PDO $pdo, $idCompeticao, $fase, $nomeCompeticao = '')
    {
        $idCompeticao = (int)$idCompeticao;
        $fase = (string)$fase;
        $nomeNormalizado = normalizarTextoEstatisticas($nomeCompeticao);

        /*
          Regra especial:
          Copa Sul/Sudeste = 50% do campeão/1º colocado do Torneio Rio-São Paulo.
          A ideia é manter a view coerente mesmo se a função central ainda não tratar o caso.
        */
        if (
            strpos($nomeNormalizado, 'copa sul/sudeste') !== false
            && in_array($fase, ['Camp', '1º'], true)
        ) {
            $idRioSaoPaulo = obterIdCompeticaoPorNomeEstatisticas($pdo, 'Torneio Rio-São Paulo');

            if ($idRioSaoPaulo && function_exists('getPontuacaoFinal')) {
                $base = getPontuacaoFinal($pdo, $idRioSaoPaulo, 'Camp');

                if ($base === null || $base === false || $base === '' || (float)$base <= 0) {
                    $base = getPontuacaoFinal($pdo, $idRioSaoPaulo, '1º');
                }

                if ($base !== null && $base !== false && $base !== '') {
                    return round(((float)$base) * 0.5);
                }
            }
        }

        if (function_exists('getPontuacaoFinal')) {
            $valor = getPontuacaoFinal($pdo, $idCompeticao, $fase);

            if ($valor !== null && $valor !== false && $valor !== '') {
                return $valor;
            }
        }

        return 0;
    }
}

if (!function_exists('buscarFasesCompeticaoEstatisticas')) {
    function buscarFasesCompeticaoEstatisticas(PDO $pdo, $idCompeticao)
    {
        $stmt = $pdo->prepare("
            SELECT fase, pontos
            FROM pontuacoes_fase
            WHERE id_competicao = ?
        ");

        $stmt->execute([(int)$idCompeticao]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('renderFasesPontuacaoEstatisticas')) {
    function renderFasesPontuacaoEstatisticas(PDO $pdo, $idCompeticao, $nomeCompeticao = '')
    {
        $fases = buscarFasesCompeticaoEstatisticas($pdo, $idCompeticao);
        $fases = ordenarFasesEstatisticas($fases, $idCompeticao);

        if (empty($fases)) {
            return '<span class="texto-vazio">Sem dados de fases.</span>';
        }

        $html = '<div class="fases-pontuacao-lista">';

        foreach ($fases as $fase) {
            $faseCodigo = $fase['fase'];
            $faseNome = nomeFaseLegivelEstatisticas($faseCodigo);
            $pontos = pontuacaoDinamicaViewEstatisticas($pdo, $idCompeticao, $faseCodigo, $nomeCompeticao);

            $html .= '
                <div class="fase-pontuacao-item">
                    <span>' . e($faseNome) . '</span>
                    <strong>' . e(formatarNumeroViewEstatisticas($pontos)) . ' pts</strong>
                </div>
            ';
        }

        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('renderCardListaEstatisticas')) {
    function renderCardListaEstatisticas($titulo, $descricao = '')
    {
        $html = '<section class="card-estatisticas bloco-texto-estatisticas">';
        $html .= '<div class="titulo-bloco-estatisticas">';
        $html .= '<h2>' . e($titulo) . '</h2>';

        if (!empty($descricao)) {
            $html .= '<span>' . e($descricao) . '</span>';
        }

        $html .= '</div>';

        return $html;
    }
}

/* =========================================
   FUNÇÃO PRINCIPAL DE RENDERIZAÇÃO
========================================= */

function renderConteudoEstatisticas(
    $categoriaSelecionada,
    $pontuacoesPorCompeticao,
    $estatisticas,
    $pontos,
    $pdo
) {
    ob_start();

    $categoriaSelecionada = trim((string)$categoriaSelecionada);

    /* =========================================
       CRITÉRIOS
    ========================================= */

    if ($categoriaSelecionada === 'criterios') {
        ?>
        <section class="hero-estatisticas hero-categoria-estatisticas">
            <span class="eyebrow">Ranking</span>
            <h1>Critérios de Ranking</h1>
            <p class="descricao-intro">
                Os critérios para pontuação do ranking foram organizados para reduzir subjetividades
                e valorizar competições oficiais, competitividade, fases alcançadas e peso histórico
                de cada torneio.
            </p>
        </section>

        <section class="card-estatisticas bloco-texto-estatisticas">
            <div class="titulo-bloco-estatisticas">
                <h2>Princípio geral</h2>
                <span>Modelo de cálculo</span>
            </div>

            <p>
                As competições que apresentam sistema de disputa em fases seguem uma lógica proporcional:
                o campeão recebe a pontuação-base da competição e as fases seguintes recebem percentuais
                menores. Como regra geral, há queda de 20% do campeão para o vice e, depois disso,
                reduções progressivas entre as fases.
            </p>

            <p>
                A organização do ranking considera competições internacionais, nacionais, regionais e estaduais.
                O peso de cada competição pode variar conforme sua importância histórica, abrangência,
                nível competitivo e relação com competições de referência.
            </p>
        </section>

        <section class="card-estatisticas bloco-texto-estatisticas">
            <div class="titulo-bloco-estatisticas">
                <h2>Hierarquia esportiva</h2>
                <span>Exemplo nacional</span>
            </div>

            <p>
                Nas competições nacionais, o modelo segue uma lógica semelhante à hierarquia usada em rankings
                esportivos: a Série A possui peso superior à Série B; a Série B possui peso superior à Série C;
                e a Série C possui peso superior à Série D.
            </p>

            <p>
                Essa estrutura ajuda a preservar a coerência entre divisões, evitando que conquistas em níveis
                competitivos distintos recebam a mesma valoração.
            </p>
        </section>

        <section class="card-estatisticas bloco-texto-estatisticas">
            <div class="titulo-bloco-estatisticas">
                <h2>Regionais e estaduais</h2>
                <span>Pontuação dinâmica</span>
            </div>

            <p>
                As competições regionais e estaduais podem ter pontuação dinâmica. Isso significa que sua
                pontuação pode variar de acordo com o desempenho dos clubes em competições nacionais e
                internacionais, mantendo o ranking atualizado conforme a força esportiva de cada contexto.
            </p>

            <p>
                A <strong>Copa Sul/Sudeste</strong> recebe tratamento especial: sua pontuação deve corresponder
                dinamicamente a <strong>50% da pontuação do Torneio Rio-São Paulo</strong>, evitando valores
                fixos e mantendo coerência com a lógica do sistema.
            </p>
        </section>

        <section class="card-estatisticas bloco-texto-estatisticas">
            <div class="titulo-bloco-estatisticas">
                <h2>Exemplo de pontuação-base</h2>
                <span>Referência atual</span>
            </div>

            <p>
                Pontuação de referência carregada pelo sistema:
                <strong><?= e(formatarNumeroViewEstatisticas($pontos)) ?> pts</strong>.
            </p>

            <p>
                Para consultar os valores calculados para cada fase e competição, acesse a opção
                <strong>Pontuações por Campeonato</strong> no menu lateral.
            </p>
        </section>
        <?php

    /* =========================================
       TABELA DE CAMPEONATOS
    ========================================= */

    } elseif ($categoriaSelecionada === 'tabela') {
        ?>
        <section class="hero-estatisticas hero-categoria-estatisticas">
            <span class="eyebrow">Ranking</span>
            <h1>Tabela de Campeonatos</h1>
            <p class="descricao-intro">
                Lista das competições rankiadas, organizadas por tipo e exibindo a pontuação dinâmica
                do campeão ou 1º colocado.
            </p>
        </section>

        <section class="card-estatisticas tabela-wrapper-estatisticas">
            <div class="titulo-bloco-estatisticas">
                <h2>Campeonatos Rankiados</h2>
                <span><?= count($pontuacoesPorCompeticao) ?> competições</span>
            </div>

            <?php if (!empty($pontuacoesPorCompeticao)): ?>
                <div class="tabela-scroll">
                    <table class="tabela-estatisticas">
                        <thead>
                            <tr>
                                <th>Competição</th>
                                <th>Tipo</th>
                                <th>Pontuação do Campeão</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pontuacoesPorCompeticao as $competicao): ?>
                                <?php
                                    $idCompeticao = (int)($competicao['id'] ?? 0);
                                    $nomeCompeticao = $competicao['competicao'] ?? '';
                                    $tipoCompeticao = $competicao['tipo'] ?? '';

                                    $pontosCampeao = 0;

                                    if ($idCompeticao > 0) {
                                        $pontosCampeao = pontuacaoDinamicaViewEstatisticas(
                                            $pdo,
                                            $idCompeticao,
                                            'Camp',
                                            $nomeCompeticao
                                        );

                                        if ((float)$pontosCampeao <= 0) {
                                            $pontosCampeao = pontuacaoDinamicaViewEstatisticas(
                                                $pdo,
                                                $idCompeticao,
                                                '1º',
                                                $nomeCompeticao
                                            );
                                        }
                                    }
                                ?>

                                <tr>
                                    <td>
                                        <strong><?= e($nomeCompeticao) ?></strong>
                                    </td>

                                    <td>
                                        <?= e(formatarTipo($tipoCompeticao)) ?>
                                    </td>

                                    <td>
                                        <span class="badge-pontos">
                                            <?= e(formatarNumeroViewEstatisticas($pontosCampeao)) ?> pts
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="mensagem-vazia">
                    Nenhuma competição encontrada no momento.
                </p>
            <?php endif; ?>
        </section>
        <?php

    /* =========================================
       PONTUAÇÕES POR CAMPEONATO
    ========================================= */

    } elseif ($categoriaSelecionada === 'pontuacoes') {
        ?>
        <section class="hero-estatisticas hero-categoria-estatisticas">
            <span class="eyebrow">Ranking</span>
            <h1>Pontuações por Campeonato</h1>
            <p class="descricao-intro">
                Consulte as pontuações dinâmicas de cada fase nas competições rankiadas.
            </p>
        </section>

        <section class="card-estatisticas tabela-wrapper-estatisticas">
            <div class="titulo-bloco-estatisticas">
                <h2>Fases e Pontuações</h2>
                <span><?= count($pontuacoesPorCompeticao) ?> competições</span>
            </div>

            <?php if (!empty($pontuacoesPorCompeticao)): ?>
                <div class="tabela-scroll">
                    <table class="tabela-estatisticas tabela-pontuacoes-detalhada">
                        <thead>
                            <tr>
                                <th>Competição</th>
                                <th>Tipo</th>
                                <th>Fases</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pontuacoesPorCompeticao as $pontuacao): ?>
                                <?php
                                    $idCompeticao = (int)($pontuacao['id'] ?? 0);
                                    $nomeCompeticao = $pontuacao['competicao'] ?? '';
                                    $tipoCompeticao = $pontuacao['tipo'] ?? '';
                                ?>

                                <tr>
                                    <td>
                                        <strong><?= e($nomeCompeticao) ?></strong>
                                    </td>

                                    <td>
                                        <?= e(formatarTipo($tipoCompeticao)) ?>
                                    </td>

                                    <td>
                                        <?php if ($idCompeticao > 0): ?>
                                            <?= renderFasesPontuacaoEstatisticas($pdo, $idCompeticao, $nomeCompeticao) ?>
                                        <?php else: ?>
                                            <span class="texto-vazio">ID da competição não encontrado.</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="mensagem-vazia">
                    Nenhuma pontuação encontrada no momento.
                </p>
            <?php endif; ?>
        </section>
        <?php

    /* =========================================
       CATEGORIAS PADRÃO
    ========================================= */

    } else {
        ?>
        <section class="hero-estatisticas hero-categoria-estatisticas">
            <span class="eyebrow">Estatísticas</span>
            <h1><?= e($categoriaSelecionada) ?></h1>
            <p class="descricao-intro">
                Escolha uma das estatísticas disponíveis abaixo para visualizar os dados detalhados.
            </p>
        </section>

        <section class="card-estatisticas lista-categoria-estatisticas">
            <div class="titulo-bloco-estatisticas">
                <h2><?= e($categoriaSelecionada) ?></h2>
                <span><?= count($estatisticas) ?> itens</span>
            </div>

            <?php if (!empty($estatisticas)): ?>
                <div class="grid-links-estatisticas">
                    <?php foreach ($estatisticas as $estatistica): ?>
                        <a
                            href="estatisticas-comp.php?item=<?= urlencode($estatistica) ?>"
                            class="link-estatistica-item"
                        >
                            <span><?= e($estatistica) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="mensagem-vazia">
                    Nenhuma estatística disponível nesta categoria no momento.
                </p>
            <?php endif; ?>
        </section>
        <?php
    }

    return ob_get_clean();
}
?>