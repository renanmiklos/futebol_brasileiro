<?php
// estatisticas-inicial.php

require_once '../estrutura/conexaodb.php';
require_once '../estrutura/calcula-pontuacoes.php';
require_once '../estrutura/gera-ranking.php';

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

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

/* =========================================
   CATEGORIA SELECIONADA
========================================= */

$categoriaSelecionada = isset($_GET['tipo'])
    ? trim((string)$_GET['tipo'])
    : null;

if ($categoriaSelecionada === '') {
    $categoriaSelecionada = null;
}

/* =========================================
   MENUS DA ÁREA DE ESTATÍSTICAS
========================================= */

$rankingItems = [
    'criterios',
    'tabela',
    'pontuacoes'
];

$rankingLabels = [
    'Critérios de Ranking',
    'Tabela de Campeonatos',
    'Pontuações por Campeonato'
];

$categorias = [
    'Internacionais',
    'Nacionais',
    'Regionais',
    'Participações',
    'Pontuações',
    'Nordeste',
    'Norte',
    'Centro-Oeste',
    'Sul',
    'Sudeste'
];

/* =========================================
   VALIDAÇÃO DO TIPO SELECIONADO
========================================= */

$tiposPermitidos = array_merge($rankingItems, $categorias);

if ($categoriaSelecionada !== null && !in_array($categoriaSelecionada, $tiposPermitidos, true)) {
    $categoriaSelecionada = null;
}

/* =========================================
   PONTUAÇÃO DINÂMICA DE REFERÊNCIA
   Mantida para compatibilidade com views-estatisticas.php
========================================= */

$pontos = 0;

try {
    /*
      Competição 34 e fase 1º mantidas conforme regra anterior do projeto.
      Essa variável é usada em views-estatisticas.php para alguns cálculos e exibições.
    */
    $pontos = getPontuacaoFinal($pdo, 34, '1º');

    if ($pontos === null || $pontos === false || $pontos === '') {
        $pontos = 0;
    }
} catch (Throwable $e) {
    $pontos = 0;
}

/* =========================================
   PONTUAÇÕES POR COMPETIÇÃO
   Usado em:
   - criterios
   - tabela
   - pontuacoes
========================================= */

$pontuacoesPorCompeticao = [];

if (in_array($categoriaSelecionada, ['criterios', 'tabela', 'pontuacoes'], true)) {
    try {
        $stmt = $pdo->query("
            SELECT 
                c.id,
                c.nome AS competicao,
                c.tipo,
                GROUP_CONCAT(
                    fases.fase_ponto 
                    ORDER BY fases.id_fase 
                    SEPARATOR '<br>'
                ) AS fases_pontuacoes
            FROM competicoes c
            INNER JOIN (
                SELECT 
                    pf.id_competicao,
                    pf.id AS id_fase,
                    CONCAT(pf.fase, ': ', pf.pontos) AS fase_ponto
                FROM pontuacoes_fase pf
            ) AS fases ON fases.id_competicao = c.id
            GROUP BY 
                c.id, 
                c.nome, 
                c.tipo
            ORDER BY 
                FIELD(c.tipo, 'internacional', 'nacional', 'regional', 'estadual'),
                FIELD(c.id, 
                    62, 1, 2, 3, 61, 4, 5, 6, 7, 8, 9, 10, 11, 12,
                    13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24,
                    25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36,
                    37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48,
                    49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60
                ),
                c.nome ASC
        ");

        $pontuacoesPorCompeticao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $pontuacoesPorCompeticao = [];
    }
}

/* =========================================
   ESTATÍSTICAS POR CATEGORIA
========================================= */

$estatisticas = [];

switch ($categoriaSelecionada) {

    case 'Nacionais':
        $estatisticas = [
            'Era da Taça Brasil (1959 - 1968)',
            'Era do Torneio Roberto Gomes Pedrosa (1967 - 1970)',
            'Brasileirão (1971 - ...)',
            'Brasileirão Pontos Corridos (2003 - ...)',
            'Brasileirão Unificado (1959 - ...)',
            'Copa do Brasil (1989 - ...)',
            'Brasileiro - Série B (1971 - ...)',
            'Série B - Pontos Corridos (2006 - ...)',
            'Brasileiro - Série C (1981 - ...)',
            'Brasileiro - Série D (2009 - ...)'
        ];
        break;

    case 'Internacionais':
        $estatisticas = [
            'Campeonato Mundial de Clubes (2025)',
            'Copa do Mundo de Clubes (2000 - 2024)',
            'Copa Intercontinental (1960 - 2004)',
            'Libertadores da América (1960 - ...)',
            'Copa Sul-Americana (2002 - ...)',
            'Todas as competições internacionais'
        ];
        break;

    case 'Regionais':
        $estatisticas = [
            'Torneio Rio-São Paulo (1933 – 2002)',
            'Copa Sul/Sudeste (2026 - ...)',
            'Copa do Nordeste (1994 - ...)',
            'Copa Verde (2014 - ...)',
            'Copa Sul (1999)',
            'Copa Sul-Minas (2000 - 2002)',
            'Copa Norte (1997 - 2002)',
            'Copa Centro-Oeste (1999 - 2002)',
            'Torneio Norte-Nordeste (1968 - 1970)'
        ];
        break;

    case 'Participações':
        $estatisticas = [
            'Participações por Clube na Copa Libertadores',
            'Participações por Clube na Copa Sul Americana',
            'Participações por Clube no Brasileirão - Série A',
            'Clubes no Brasileirão - Série A - Pontos Corridos',
            'Participações por Clube no Brasileirão - Série B',
            'Clubes no Brasileirão - Série B - Pontos Corridos',
            'Participações por Clube no Brasileirão - Série C',
            'Participações por Clube no Brasileirão - Série D',
            'Participações por Clube na Copa do Brasil'
        ];
        break;

    case 'Pontuações':
        $estatisticas = [
            'Campeões Brasileiro dos Pontos Corridos',
            'Campeões Série B dos Pontos Corridos',
            'Rebaixado com mais pontos – Série A',
            'Rebaixado com mais pontos – Série B',
            'Não-Rebaixados com menor pontuação - Série A',
            'Não-Rebaixados com menor pontuação - Série B',
            'Últimos Colocados - Série A',
            'Últimos Colocados - Série B'
        ];
        break;

    case 'Nordeste':
        $estatisticas = [
            'Nordestinos na Libertadores',
            'Participações na Libertadores',
            'Nordestinos na Sul Americana',
            'Participações na Sul Americana',
            'Nordestinos na Série A',
            'Nordestinos na Série A - Pontos Corridos',
            'Participações - Série A',
            'Participações - Série A - Pontos Corridos',
            'Nordestinos na Série B',
            'Nordestinos na Série B - Pontos Corridos',
            'Participações - Série B',
            'Participações - Série B - Pontos Corridos',
            'Nordestinos na Série C',
            'Participações - Série C',
            'Nordestinos na Série D',
            'Participações - Série D'
        ];
        break;

    case 'Norte':
        $estatisticas = [
            'Norte na Libertadores',
            'Participações do Norte na Libertadores',
            'Norte na Sul Americana',
            'Participações do Norte na Sul Americana',
            'Norte na Série A',
            'Norte na Série A - Pontos Corridos',
            'Participações do Norte - Série A',
            'Participações do Norte - Série A - Pontos Corridos',
            'Norte na Série B',
            'Norte na Série B - Pontos Corridos',
            'Participações do Norte - Série B',
            'Participações do Norte - Série B - Pontos Corridos',
            'Norte na Série C',
            'Participações do Norte - Série C',
            'Norte na Série D',
            'Participações do Norte - Série D'
        ];
        break;

    case 'Centro-Oeste':
        $estatisticas = [
            'Centro-Oeste na Libertadores',
            'Participações do Centro-Oeste na Libertadores',
            'Centro-Oeste na Sul Americana',
            'Participações do Centro-Oeste na Sul Americana',
            'Centro-Oeste na Série A',
            'Centro-Oeste na Série A - Pontos Corridos',
            'Participações do Centro-Oeste - Série A',
            'Participações do Centro-Oeste - Série A - Pontos Corridos',
            'Centro-Oeste na Série B',
            'Centro-Oeste na Série B - Pontos Corridos',
            'Participações do Centro-Oeste - Série B',
            'Participações do Centro-Oeste - Série B - Pontos Corridos',
            'Centro-Oeste na Série C',
            'Participações do Centro-Oeste - Série C',
            'Centro-Oeste na Série D',
            'Participações do Centro-Oeste - Série D'
        ];
        break;

    case 'Sul':
        $estatisticas = [
            'Sul na Libertadores',
            'Participações do Sul na Libertadores',
            'Sul na Sul Americana',
            'Participações do Sul na Sul Americana',
            'Sul na Série A',
            'Sul na Série A - Pontos Corridos',
            'Participações do Sul - Série A',
            'Participações do Sul - Série A - Pontos Corridos',
            'Sul na Série B',
            'Sul na Série B - Pontos Corridos',
            'Participações do Sul - Série B',
            'Participações do Sul - Série B - Pontos Corridos',
            'Sul na Série C',
            'Participações do Sul - Série C',
            'Sul na Série D',
            'Participações do Sul - Série D'
        ];
        break;

    case 'Sudeste':
        $estatisticas = [
            'Sudeste na Libertadores',
            'Participações do Sudeste na Libertadores',
            'Sudeste na Sul Americana',
            'Participações do Sudeste na Sul Americana',
            'Sudeste na Série A',
            'Sudeste na Série A - Pontos Corridos',
            'Participações do Sudeste - Série A',
            'Participações do Sudeste - Série A - Pontos Corridos',
            'Sudeste na Série B',
            'Sudeste na Série B - Pontos Corridos',
            'Participações do Sudeste - Série B',
            'Participações do Sudeste - Série B - Pontos Corridos',
            'Sudeste na Série C',
            'Participações do Sudeste - Série C',
            'Sudeste na Série D',
            'Participações do Sudeste - Série D'
        ];
        break;

    default:
        $estatisticas = [];
        break;
}

/* =========================================
   VIEW / RENDERIZAÇÃO DO CONTEÚDO
========================================= */

require_once 'views-estatisticas.php';
?>