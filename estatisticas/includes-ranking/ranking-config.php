<?php
/* =========================================
   RANKING-CONFIG.PHP
   Configurações centrais da área Ranking
   Futebol Brasileiro
========================================= */

/*
  Este arquivo não executa cálculo.
  Ele apenas concentra configurações fixas usadas pelos rankings.
*/

/* =========================================
   MENU DA ÁREA RANKING
========================================= */

$MENU_RANKING = [
    [
        'id' => 'introducao',
        'label' => 'Introdução',
        'url' => 'ranking-introducao.php'
    ],
    [
        'id' => 'geral',
        'label' => 'Ranking Geral',
        'url' => 'ranking.php'
    ],
    [
        'id' => 'nacional',
        'label' => 'Só Nacionais',
        'url' => 'ranking-nac.php'
    ],
    [
        'id' => 'internacional',
        'label' => 'Só Internacionais',
        'url' => 'ranking-int.php'
    ],
    [
        'id' => 'nordeste',
        'label' => 'Ranking Nordeste',
        'url' => 'ranking-ne.php'
    ],
    [
        'id' => 'norte',
        'label' => 'Ranking Norte',
        'url' => 'ranking-norte.php'
    ],
    [
        'id' => 'centro-oeste',
        'label' => 'Ranking Centro-Oeste',
        'url' => 'ranking-co.php'
    ],
    [
        'id' => 'sudeste',
        'label' => 'Ranking Sudeste',
        'url' => 'ranking-se.php'
    ],
    [
        'id' => 'sul',
        'label' => 'Ranking Sul',
        'url' => 'ranking-sul.php'
    ],
    [
        'id' => 'federacoes',
        'label' => 'Ranking das Federações',
        'url' => 'ranking-fed.php'
    ],
    [
        'id' => 'criterios',
        'label' => 'Critérios',
        'url' => 'estatisticas.php?tipo=criterios'
    ],
];

/* =========================================
   ESTADOS DO BRASIL
========================================= */

$ESTADOS_BRASIL = [
    'AC', 'AL', 'AP', 'AM',
    'BA', 'CE', 'DF', 'ES',
    'GO', 'MA', 'MT', 'MS',
    'MG', 'PA', 'PB', 'PR',
    'PE', 'PI', 'RJ', 'RN',
    'RS', 'RO', 'RR', 'SC',
    'SP', 'SE', 'TO'
];

/* =========================================
   REGIÕES DO BRASIL
========================================= */

$REGIOES_RANKING = [
    'norte' => [
        'titulo' => 'Ranking dos Clubes do Norte',
        'slug' => 'norte',
        'estados' => ['AC', 'AP', 'AM', 'PA', 'RO', 'RR', 'TO']
    ],

    'nordeste' => [
        'titulo' => 'Ranking dos Clubes do Nordeste',
        'slug' => 'nordeste',
        'estados' => ['AL', 'BA', 'CE', 'MA', 'PB', 'PE', 'PI', 'RN', 'SE']
    ],

    'centro-oeste' => [
        'titulo' => 'Ranking dos Clubes do Centro-Oeste',
        'slug' => 'centro-oeste',
        'estados' => ['DF', 'GO', 'MT', 'MS']
    ],

    'sudeste' => [
        'titulo' => 'Ranking dos Clubes do Sudeste',
        'slug' => 'sudeste',
        'estados' => ['ES', 'MG', 'RJ', 'SP']
    ],

    'sul' => [
        'titulo' => 'Ranking dos Clubes do Sul',
        'slug' => 'sul',
        'estados' => ['PR', 'RS', 'SC']
    ],
];

/* =========================================
   COMPETIÇÕES NACIONAIS
   IDs conforme banco de dados atual
========================================= */

$COMPETICOES_NACIONAIS = [
    'taca_brasil' => [
        'id' => 17,
        'label' => 'Taça Brasil',
        'coluna' => 'taca_brasil'
    ],

    'roberto_pedrosa' => [
        'id' => 18,
        'label' => 'Robertão',
        'coluna' => 'roberto_pedrosa'
    ],

    'serie_a' => [
        'id' => 19,
        'label' => 'Série A',
        'coluna' => 'serie_a'
    ],

    'serie_b' => [
        'id' => 20,
        'label' => 'Série B',
        'coluna' => 'serie_b'
    ],

    'serie_c' => [
        'id' => 21,
        'label' => 'Série C',
        'coluna' => 'serie_c'
    ],

    'serie_d' => [
        'id' => 22,
        'label' => 'Série D',
        'coluna' => 'serie_d'
    ],

    'copa_brasil' => [
        'id' => 23,
        'label' => 'Copa do Brasil',
        'coluna' => 'copa_brasil'
    ],

    'supercopa_brasil' => [
        'id' => 24,
        'label' => 'Supercopa',
        'coluna' => 'supercopa_brasil'
    ],

    'torneio_campeoes' => [
        'id' => 16,
        'label' => 'T. Campeões',
        'coluna' => 'torneio_campeoes'
    ],

    'copa_campeoes' => [
        'id' => 25,
        'label' => 'C. Campeões',
        'coluna' => 'copa_campeoes'
    ],
];

/* =========================================
   COMPETIÇÕES INTERNACIONAIS
   IDs conforme banco de dados atual
========================================= */

$COMPETICOES_INTERNACIONAIS = [
    'mundial' => [
        'id' => 62,
        'label' => 'Mundial',
        'coluna' => 'mundial'
    ],

    'copa_mundo' => [
        'id' => 1,
        'label' => 'Copa Mundo',
        'coluna' => 'copa_mundo'
    ],

    'intercontinental' => [
        'id' => 2,
        'label' => 'Intercont.',
        'coluna' => 'intercontinental'
    ],

    'libertadores' => [
        'id' => 5,
        'label' => 'Libertadores',
        'coluna' => 'libertadores'
    ],

    'sul_americana' => [
        'id' => 7,
        'label' => 'Sul-Americana',
        'coluna' => 'sul_americana'
    ],

    'supercopa' => [
        'id' => 8,
        'label' => 'Supercopa',
        'coluna' => 'supercopa'
    ],

    'conmebol' => [
        'id' => 10,
        'label' => 'Conmebol',
        'coluna' => 'conmebol'
    ],

    'mercosul' => [
        'id' => 9,
        'label' => 'Mercosul',
        'coluna' => 'mercosul'
    ],

    'recopa' => [
        'id' => 11,
        'label' => 'Recopa',
        'coluna' => 'recopa'
    ],

    /*
      Grupo "Outros" reúne competições internacionais/históricas
      que aparecem em uma única coluna no ranking internacional.
    */
    'outros' => [
        'ids' => [3, 4, 6, 12, 13, 14, 15, 61],
        'label' => 'Outros',
        'coluna' => 'outros'
    ],
];

/* =========================================
   COLUNAS DO RANKING GERAL / REGIONAL
========================================= */

$COLUNAS_RANKING_GERAL = [
    [
        'key' => 'internacionais',
        'label' => 'Internacionais',
        'tipo' => 'numero'
    ],
    [
        'key' => 'nacionais',
        'label' => 'Nacionais',
        'tipo' => 'numero'
    ],
    [
        'key' => 'regionais',
        'label' => 'Regionais',
        'tipo' => 'numero'
    ],
    [
        'key' => 'estaduais',
        'label' => 'Estaduais',
        'tipo' => 'numero'
    ],
    [
        'key' => 'total',
        'label' => 'Total',
        'tipo' => 'total'
    ],
];

/* =========================================
   COLUNAS DO RANKING NACIONAL
========================================= */

$COLUNAS_RANKING_NACIONAL = [
    [
        'key' => 'taca_brasil',
        'label' => 'Taça Brasil',
        'tipo' => 'numero'
    ],
    [
        'key' => 'roberto_pedrosa',
        'label' => 'RGP',
        'tipo' => 'numero'
    ],
    [
        'key' => 'serie_a',
        'label' => 'Série A',
        'tipo' => 'numero'
    ],
    [
        'key' => 'serie_b',
        'label' => 'Série B',
        'tipo' => 'numero'
    ],
    [
        'key' => 'serie_c',
        'label' => 'Série C',
        'tipo' => 'numero'
    ],
    [
        'key' => 'serie_d',
        'label' => 'Série D',
        'tipo' => 'numero'
    ],
    [
        'key' => 'copa_brasil',
        'label' => 'Copa do Brasil',
        'tipo' => 'numero'
    ],
    [
        'key' => 'supercopa_brasil',
        'label' => 'Supercopa',
        'tipo' => 'numero'
    ],
    [
        'key' => 'torneio_campeoes',
        'label' => 'T. Campeões',
        'tipo' => 'numero'
    ],
    [
        'key' => 'copa_campeoes',
        'label' => 'C. Campeões',
        'tipo' => 'numero'
    ],
    [
        'key' => 'total',
        'label' => 'Total',
        'tipo' => 'total'
    ],
];

/* =========================================
   COLUNAS DO RANKING INTERNACIONAL
========================================= */

$COLUNAS_RANKING_INTERNACIONAL = [
    [
        'key' => 'mundial',
        'label' => 'Mundial',
        'tipo' => 'numero'
    ],
    [
        'key' => 'copa_mundo',
        'label' => 'Copa Mundo',
        'tipo' => 'numero'
    ],
    [
        'key' => 'intercontinental',
        'label' => 'Intercont.',
        'tipo' => 'numero'
    ],
    [
        'key' => 'libertadores',
        'label' => 'Libertadores',
        'tipo' => 'numero'
    ],
    [
        'key' => 'sul_americana',
        'label' => 'Sul-Americana',
        'tipo' => 'numero'
    ],
    [
        'key' => 'supercopa',
        'label' => 'Supercopa',
        'tipo' => 'numero'
    ],
    [
        'key' => 'conmebol',
        'label' => 'Conmebol',
        'tipo' => 'numero'
    ],
    [
        'key' => 'mercosul',
        'label' => 'Mercosul',
        'tipo' => 'numero'
    ],
    [
        'key' => 'recopa',
        'label' => 'Recopa',
        'tipo' => 'numero'
    ],
    [
        'key' => 'outros',
        'label' => 'Outros',
        'tipo' => 'numero'
    ],
    [
        'key' => 'total',
        'label' => 'Total',
        'tipo' => 'total'
    ],
];

/* =========================================
   COLUNAS DO RANKING DAS FEDERAÇÕES
========================================= */

$COLUNAS_RANKING_FEDERACOES = [
    [
        'key' => 'internacionais',
        'label' => 'Internacionais',
        'tipo' => 'numero'
    ],
    [
        'key' => 'nacionais',
        'label' => 'Nacionais',
        'tipo' => 'numero'
    ],
    [
        'key' => 'regionais',
        'label' => 'Regionais',
        'tipo' => 'numero'
    ],
    [
        'key' => 'estaduais',
        'label' => 'Estaduais',
        'tipo' => 'numero'
    ],
    [
        'key' => 'total',
        'label' => 'Total',
        'tipo' => 'total'
    ],
];