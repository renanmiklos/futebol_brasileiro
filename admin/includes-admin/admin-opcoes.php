<?php
/* =========================================
   ADMIN-OPCOES.PHP
   Opções fixas reutilizáveis do Painel Admin
   Futebol Brasileiro
========================================= */

/*
  Este arquivo centraliza listas fixas usadas em várias páginas do admin:
  - estados brasileiros
  - fases de classificação
  - tipos de competição
  - divisões nacionais
  - opções booleanas
*/

/* =========================================
   ESTADOS BRASILEIROS
========================================= */

$ESTADOS_BRASILEIROS_ADMIN = [
    'AC' => 'Acre',
    'AL' => 'Alagoas',
    'AP' => 'Amapá',
    'AM' => 'Amazonas',
    'BA' => 'Bahia',
    'CE' => 'Ceará',
    'DF' => 'Distrito Federal',
    'ES' => 'Espírito Santo',
    'GO' => 'Goiás',
    'MA' => 'Maranhão',
    'MT' => 'Mato Grosso',
    'MS' => 'Mato Grosso do Sul',
    'MG' => 'Minas Gerais',
    'PA' => 'Pará',
    'PB' => 'Paraíba',
    'PR' => 'Paraná',
    'PE' => 'Pernambuco',
    'PI' => 'Piauí',
    'RJ' => 'Rio de Janeiro',
    'RN' => 'Rio Grande do Norte',
    'RS' => 'Rio Grande do Sul',
    'RO' => 'Rondônia',
    'RR' => 'Roraima',
    'SC' => 'Santa Catarina',
    'SP' => 'São Paulo',
    'SE' => 'Sergipe',
    'TO' => 'Tocantins',
];

/* =========================================
   SIGLAS DOS ESTADOS
   Útil quando o select precisa mostrar apenas UF
========================================= */

$SIGLAS_ESTADOS_ADMIN = array_keys($ESTADOS_BRASILEIROS_ADMIN);

/* =========================================
   REGIÕES DO BRASIL
========================================= */

$REGIOES_BRASIL_ADMIN = [
    'Norte' => ['AC', 'AP', 'AM', 'PA', 'RO', 'RR', 'TO'],
    'Nordeste' => ['AL', 'BA', 'CE', 'MA', 'PB', 'PE', 'PI', 'RN', 'SE'],
    'Centro-Oeste' => ['DF', 'GO', 'MT', 'MS'],
    'Sudeste' => ['ES', 'MG', 'RJ', 'SP'],
    'Sul' => ['PR', 'RS', 'SC'],
];

/* =========================================
   TIPOS DE COMPETIÇÃO
========================================= */

$TIPOS_COMPETICAO_ADMIN = [
    'Internacional' => 'Internacional',
    'Nacional' => 'Nacional',
    'Regional' => 'Regional',
    'Estadual' => 'Estadual',
];

/* =========================================
   OPÇÕES SIM / NÃO
========================================= */

$OPCOES_SIM_NAO_ADMIN = [
    0 => 'Não',
    1 => 'Sim',
];

/* =========================================
   DIVISÕES DO CAMPEONATO BRASILEIRO
========================================= */

$DIVISOES_BRASILEIRAO_ADMIN = [
    'A' => 'Série A',
    'B' => 'Série B',
    'C' => 'Série C',
    'D' => 'Série D',
];

/* =========================================
   FASES DE CLASSIFICAÇÃO
========================================= */

$FASES_CLASSIFICACAO_ADMIN = [
    'Campeonato' => [
        'Camp' => 'Campeão',
        'Vice' => 'Vice',
        'Final' => 'Final',
        'Disputa3' => 'Disputa 3º lugar',
        'SF' => 'Semifinal',
        'QF' => 'Quartas de Final',
        'OF' => 'Oitavas de Final',
        '16avos' => '16 Avos de Final',
        '32avos' => '32 Avos de Final',
        '64avos' => '64 Avos de Final',
        'Eliminator' => 'Eliminatória',
    ],

    'Posições' => [
        '1º' => '1º Lugar',
        '2º' => '2º Lugar',
        '3º' => '3º Lugar',
        '4º' => '4º Lugar',
        '5º' => '5º Lugar',
        '6º' => '6º Lugar',
        '7º' => '7º Lugar',
        '8º' => '8º Lugar',
        '9º' => '9º Lugar',
        '10º' => '10º Lugar',
        '11º' => '11º Lugar',
        '12º' => '12º Lugar',
        '13º' => '13º Lugar',
        '14º' => '14º Lugar',
        '15º' => '15º Lugar',
        '16º' => '16º Lugar',
        '17º' => '17º Lugar',
        '18º' => '18º Lugar',
        '19º' => '19º Lugar',
        '20º' => '20º Lugar',
        '21º' => '21º Lugar',
        '22º' => '22º Lugar',
        '23º' => '23º Lugar',
        '24º' => '24º Lugar',
        '25º' => '25º Lugar',
    ],

    'Regional / Zonas' => [
        'Regional' => 'Regional',
        'ZonaClassificacao' => 'Zona de Classificação',
        'ZonaRebaixamento' => 'Zona de Rebaixamento',
        'Playoff' => 'Playoff',
    ],

    'Grupos' => [
        'Grupo' => 'Grupo',
        'FaseDeGrupos' => 'Fase de Grupos',
    ],

    'Pré-fases' => [
        'Pre' => 'Pré',
        'Pre1' => 'Pré 1ª Fase',
        'Pre2' => 'Pré 2ª Fase',
        'Pre3' => 'Pré 3ª Fase',
    ],

    'Jogos' => [
        'Ida' => 'Jogo de Ida',
        'Volta' => 'Jogo de Volta',
    ],
];

/* =========================================
   FASES EM LISTA SIMPLES
   Útil para validações rápidas.
========================================= */

$FASES_CLASSIFICACAO_LISTA_ADMIN = [];

foreach ($FASES_CLASSIFICACAO_ADMIN as $grupoFases) {
    foreach ($grupoFases as $valor => $label) {
        $FASES_CLASSIFICACAO_LISTA_ADMIN[$valor] = $label;
    }
}

/* =========================================
   CAMPOS EXTRAS DE TIMES
========================================= */

$CAMPOS_EXTRAS_TIMES_ADMIN = [];

for ($i = 1; $i <= 10; $i++) {
    $CAMPOS_EXTRAS_TIMES_ADMIN[] = [
        'campo' => 'extra' . $i,
        'label' => 'Extra ' . $i,
        'legenda' => 'legenda' . $i,
        'label_legenda' => 'Legenda ' . $i,
    ];
}

/* =========================================
   MÓDULOS DO PAINEL ADMIN
========================================= */

$MODULOS_ADMIN = [
    [
        'id' => 'times',
        'titulo' => 'Times',
        'url' => 'admin-times.php',
        'descricao' => 'Gerenciar clubes, escudos, história, títulos e dados gerais.',
    ],
    [
        'id' => 'competicoes',
        'titulo' => 'Competições',
        'url' => 'admin-competicoes.php',
        'descricao' => 'Gerenciar competições internacionais, nacionais, regionais e estaduais.',
    ],
    [
        'id' => 'temporadas',
        'titulo' => 'Temporadas',
        'url' => 'admin-temporadas.php',
        'descricao' => 'Gerenciar temporadas e anos das competições.',
    ],
    [
        'id' => 'pontuacoes',
        'titulo' => 'Pontuações',
        'url' => 'admin-pontuacoes.php',
        'descricao' => 'Gerenciar pontuações por fase.',
    ],
    [
        'id' => 'classificacoes',
        'titulo' => 'Classificações',
        'url' => 'admin-classificacao.php',
        'descricao' => 'Gerenciar campanhas, fases e estatísticas dos clubes.',
    ],
    [
        'id' => 'divisoes',
        'titulo' => 'Divisões',
        'url' => 'admin-divisoes.php',
        'descricao' => 'Gerenciar clubes nas Séries A, B, C e D.',
    ],
    [
        'id' => 'jogos',
        'titulo' => 'Jogos',
        'url' => 'admin-jogos.php',
        'descricao' => 'Gerenciar partidas, placares, datas e estádios.',
    ],
];

/* =========================================
   PLACEHOLDERS PADRÃO
========================================= */

$PLACEHOLDERS_ADMIN = [
    'pesquisar_time' => 'Pesquisar pelo nome do time...',
    'pesquisar_competicao' => 'Pesquisar pelo nome da competição...',
    'pesquisar_temporada' => 'Pesquisar pelo ano...',
    'pesquisar_pontuacao' => 'Pesquisar pela competição...',
    'pesquisar_classificacao' => 'Pesquisar por competição ou time...',
    'pesquisar_jogo' => 'Pesquisar por competição...',
    'pesquisar_foto' => 'Pesquisar pelo título da foto...',
];

/* =========================================
   LIMITES DO ADMIN
========================================= */

$LIMITES_ADMIN = [
    'jogos_por_envio' => 5,
    'classificacoes_por_envio' => 10,
    'extras_time' => 10,
];

/* =========================================
   FUNÇÕES AUXILIARES DE OPÇÕES
========================================= */

if (!function_exists('adminListaFasesClassificacao')) {
    function adminListaFasesClassificacao(): array
    {
        global $FASES_CLASSIFICACAO_LISTA_ADMIN;

        return $FASES_CLASSIFICACAO_LISTA_ADMIN;
    }
}

if (!function_exists('adminFaseExiste')) {
    function adminFaseExiste(string $fase): bool
    {
        global $FASES_CLASSIFICACAO_LISTA_ADMIN;

        return array_key_exists($fase, $FASES_CLASSIFICACAO_LISTA_ADMIN);
    }
}

if (!function_exists('adminLabelFase')) {
    function adminLabelFase(string $fase): string
    {
        global $FASES_CLASSIFICACAO_LISTA_ADMIN;

        return $FASES_CLASSIFICACAO_LISTA_ADMIN[$fase] ?? $fase;
    }
}

if (!function_exists('adminTipoCompeticaoExiste')) {
    function adminTipoCompeticaoExiste(string $tipo): bool
    {
        global $TIPOS_COMPETICAO_ADMIN;

        return array_key_exists($tipo, $TIPOS_COMPETICAO_ADMIN);
    }
}

if (!function_exists('adminEstadoExiste')) {
    function adminEstadoExiste(string $uf): bool
    {
        global $ESTADOS_BRASILEIROS_ADMIN;

        return array_key_exists(strtoupper($uf), $ESTADOS_BRASILEIROS_ADMIN);
    }
}

if (!function_exists('adminDivisaoExiste')) {
    function adminDivisaoExiste(string $divisao): bool
    {
        global $DIVISOES_BRASILEIRAO_ADMIN;

        return array_key_exists(strtoupper($divisao), $DIVISOES_BRASILEIRAO_ADMIN);
    }
}