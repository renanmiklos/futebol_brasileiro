<?php
// estatistica-process.php

require_once '../estrutura/conexaodb.php';

require_once 'paginas/internacionais.php';
require_once 'paginas/nacionais.php';
require_once 'paginas/regionais.php';
require_once 'paginas/participacoes.php';
require_once 'paginas/nordeste.php';
require_once 'paginas/norte.php';
require_once 'paginas/centro-oeste.php';
require_once 'paginas/sul.php';
require_once 'paginas/sudeste.php';

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

if (!function_exists('normalizarTextoProcess')) {
    function normalizarTextoProcess($texto)
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
            '—' => '-',
            '-' => '-'
        ];

        return strtr($texto, $mapa);
    }
}

if (!function_exists('validarColunaOrdemProcess')) {
    function validarColunaOrdemProcess($coluna)
    {
        $permitidas = [
            'nome_time',
            'jogos',
            'vitorias',
            'empates',
            'derrotas',
            'gols_pro',
            'gols_contra',
            'saldo',
            'pontos',
            'pontos_marcados',
            'participacoes',
            'qtd_titulos',
            'top4',
            'ultima_participacao',
            'ano'
        ];

        return in_array($coluna, $permitidas, true) ? $coluna : 'pontos';
    }
}

if (!function_exists('validarTipoOrdemProcess')) {
    function validarTipoOrdemProcess($ordem)
    {
        $ordem = strtoupper(trim((string)$ordem));

        return in_array($ordem, ['ASC', 'DESC'], true) ? $ordem : 'DESC';
    }
}

/* =========================================
   CAPTURA DE PARÂMETROS
========================================= */

$item = isset($_GET['item']) ? urldecode((string)$_GET['item']) : '';

$tituloOriginal = trim($item);
$tituloOriginal = $tituloOriginal !== '' ? $tituloOriginal : 'Estatística não especificada';

$tituloNormalizado = mb_strtolower(trim($tituloOriginal), 'UTF-8');

/*
  Versão sem acentos/hífens especiais para facilitar comparações
  sem perder compatibilidade com as funções antigas.
*/
$tituloNormalizadoSemAcento = normalizarTextoProcess($tituloOriginal);

$descricao = '';
$dados = [];
$tabela_estatisticas = [];

$id_competicao = null;
$ano_inicio = null;
$exibir_tabela = true;
$tipo_participacao = null;
$nivel_rebaixamento = null;

/* =========================================
   ORDENAÇÃO
========================================= */

$coluna_ordem = isset($_GET['coluna_ordem'])
    ? trim((string)$_GET['coluna_ordem'])
    : 'pontos';

$tipo_ordem = isset($_GET['tipo_ordem'])
    ? trim((string)$_GET['tipo_ordem'])
    : 'DESC';

$coluna_ordem = validarColunaOrdemProcess($coluna_ordem);
$tipo_ordem = validarTipoOrdemProcess($tipo_ordem);

/* =========================================
   FLAGS DE PROCESSAMENTO
========================================= */

$processado_internacional = false;
$processado_nacional = false;
$processado_regional = false;
$processado_participacao = false;
$processado_nordeste = false;
$processado_norte = false;
$processado_centro_oeste = false;
$processado_sul = false;
$processado_sudeste = false;

/* =========================================
   PROCESSAMENTO POR ARQUIVOS AUXILIARES
========================================= */

$processado_internacional = processar_internacionais(
    $tituloNormalizado,
    $descricao,
    $dados,
    $id_competicao,
    $ano_inicio,
    $exibir_tabela,
    $tipo_participacao,
    $tabela_estatisticas,
    $pdo,
    $coluna_ordem,
    $tipo_ordem
);

if (!$processado_internacional) {
    $processado_nacional = processar_nacionais(
        $tituloNormalizado,
        $descricao,
        $dados,
        $id_competicao,
        $ano_inicio,
        $exibir_tabela,
        $tipo_participacao,
        $tabela_estatisticas,
        $pdo,
        $coluna_ordem,
        $tipo_ordem
    );
}

if (
    !$processado_internacional &&
    !$processado_nacional
) {
    $processado_regional = processar_regionais(
        $tituloNormalizado,
        $descricao,
        $dados,
        $id_competicao,
        $ano_inicio,
        $exibir_tabela,
        $tipo_participacao,
        $tabela_estatisticas,
        $pdo,
        $coluna_ordem,
        $tipo_ordem
    );
}

if (
    !$processado_internacional &&
    !$processado_nacional &&
    !$processado_regional
) {
    $processado_participacao = processar_participacoes(
        $tituloNormalizado,
        $descricao,
        $dados,
        $id_competicao,
        $ano_inicio,
        $exibir_tabela,
        $tipo_participacao,
        $tabela_estatisticas,
        $pdo,
        $coluna_ordem,
        $tipo_ordem
    );
}

if (
    !$processado_internacional &&
    !$processado_nacional &&
    !$processado_regional &&
    !$processado_participacao
) {
    $processado_nordeste = processar_nordeste(
        $tituloNormalizado,
        $descricao,
        $dados,
        $id_competicao,
        $ano_inicio,
        $exibir_tabela,
        $tipo_participacao,
        $tabela_estatisticas,
        $pdo,
        $coluna_ordem,
        $tipo_ordem
    );
}

if (
    !$processado_internacional &&
    !$processado_nacional &&
    !$processado_regional &&
    !$processado_participacao &&
    !$processado_nordeste
) {
    $processado_norte = processar_norte(
        $tituloNormalizado,
        $descricao,
        $dados,
        $id_competicao,
        $ano_inicio,
        $exibir_tabela,
        $tipo_participacao,
        $tabela_estatisticas,
        $pdo,
        $coluna_ordem,
        $tipo_ordem
    );
}

if (
    !$processado_internacional &&
    !$processado_nacional &&
    !$processado_regional &&
    !$processado_participacao &&
    !$processado_nordeste &&
    !$processado_norte
) {
    $processado_centro_oeste = processar_centro_oeste(
        $tituloNormalizado,
        $descricao,
        $dados,
        $id_competicao,
        $ano_inicio,
        $exibir_tabela,
        $tipo_participacao,
        $tabela_estatisticas,
        $pdo,
        $coluna_ordem,
        $tipo_ordem
    );
}

if (
    !$processado_internacional &&
    !$processado_nacional &&
    !$processado_regional &&
    !$processado_participacao &&
    !$processado_nordeste &&
    !$processado_norte &&
    !$processado_centro_oeste
) {
    $processado_sul = processar_sul(
        $tituloNormalizado,
        $descricao,
        $dados,
        $id_competicao,
        $ano_inicio,
        $exibir_tabela,
        $tipo_participacao,
        $tabela_estatisticas,
        $pdo,
        $coluna_ordem,
        $tipo_ordem
    );
}

if (
    !$processado_internacional &&
    !$processado_nacional &&
    !$processado_regional &&
    !$processado_participacao &&
    !$processado_nordeste &&
    !$processado_norte &&
    !$processado_centro_oeste &&
    !$processado_sul
) {
    $processado_sudeste = processar_sudeste(
        $tituloNormalizado,
        $descricao,
        $dados,
        $id_competicao,
        $ano_inicio,
        $exibir_tabela,
        $tipo_participacao,
        $tabela_estatisticas,
        $pdo,
        $coluna_ordem,
        $tipo_ordem
    );
}

/* =========================================
   PROCESSAMENTO DAS ESTATÍSTICAS DE PONTUAÇÕES
========================================= */

$processado_por_auxiliares = (
    $processado_internacional ||
    $processado_nacional ||
    $processado_regional ||
    $processado_participacao ||
    $processado_nordeste ||
    $processado_norte ||
    $processado_centro_oeste ||
    $processado_sul ||
    $processado_sudeste
);

if (!$processado_por_auxiliares) {

    switch ($tituloNormalizadoSemAcento) {

        case 'campeoes brasileiro dos pontos corridos':
            $descricao = 'Lista dos campeões do Campeonato Brasileiro Série A no formato de pontos corridos, a partir de 2003, ordenados pela quantidade de pontos marcados.';
            $id_competicao = 19;
            $ano_inicio = 2003;
            $exibir_tabela = true;
            $tipo_participacao = 'campeoes_pontos_corridos';
            break;

        case 'campeoes serie b dos pontos corridos':
            $descricao = 'Lista dos campeões do Campeonato Brasileiro Série B no formato de pontos corridos, a partir de 2006, ordenados pela quantidade de pontos marcados.';
            $id_competicao = 20;
            $ano_inicio = 2006;
            $exibir_tabela = true;
            $tipo_participacao = 'campeoes_pontos_corridos';
            break;

        case 'rebaixado com mais pontos - serie a':
            $descricao = 'Clubes rebaixados da Série A que obtiveram a maior pontuação em suas respectivas edições, a partir de 2003.';
            $id_competicao = 19;
            $ano_inicio = 2003;
            $exibir_tabela = true;
            $tipo_participacao = 'rebaixados_mais_pontos';
            $nivel_rebaixamento = 'A';
            break;

        case 'rebaixado com mais pontos - serie b':
            $descricao = 'Clubes rebaixados da Série B que obtiveram a maior pontuação em suas respectivas edições, a partir de 2006.';
            $id_competicao = 20;
            $ano_inicio = 2006;
            $exibir_tabela = true;
            $tipo_participacao = 'rebaixados_mais_pontos';
            $nivel_rebaixamento = 'B';
            break;

        case 'nao-rebaixados com menor pontuacao - serie a':
            $descricao = 'Clubes não rebaixados para a Série B que tiveram as menores pontuações em suas respectivas edições, a partir de 2003.';
            $id_competicao = 19;
            $ano_inicio = 2003;
            $exibir_tabela = true;
            $tipo_participacao = 'menor_pontuacao_nao_rebaixado';
            $nivel_rebaixamento = 'A';
            break;

        case 'nao-rebaixados com menor pontuacao - serie b':
            $descricao = 'Clubes não rebaixados para a Série C que tiveram as menores pontuações em suas respectivas edições, a partir de 2006.';
            $id_competicao = 20;
            $ano_inicio = 2006;
            $exibir_tabela = true;
            $tipo_participacao = 'menor_pontuacao_nao_rebaixado';
            $nivel_rebaixamento = 'B';
            break;

        case 'ultimos colocados - serie a':
            $descricao = 'Últimos colocados da Série A por edição, considerando a posição final registrada na classificação.';
            $id_competicao = 19;
            $ano_inicio = 2003;
            $exibir_tabela = true;
            $tipo_participacao = 'ultimos_colocados';
            $nivel_rebaixamento = 'A';
            break;

        case 'ultimos colocados - serie b':
            $descricao = 'Últimos colocados da Série B por edição, considerando a posição final registrada na classificação.';
            $id_competicao = 20;
            $ano_inicio = 2006;
            $exibir_tabela = true;
            $tipo_participacao = 'ultimos_colocados';
            $nivel_rebaixamento = 'B';
            break;

        default:
            $descricao = 'Estatística não encontrada.';
            $dados = [];
            $id_competicao = null;
            $ano_inicio = null;
            $exibir_tabela = false;
            $tipo_participacao = null;
            break;
    }
}

/* =========================================
   FUNÇÃO PARA QUERY GENÉRICA
========================================= */

if (!function_exists('build_sql_query')) {
    function build_sql_query(
        PDO $pdo,
        $id_competicao,
        $ano_inicio,
        $tituloNormalizado,
        $coluna_ordem,
        $tipo_ordem
    ) {
        $coluna_ordem = validarColunaOrdemProcess($coluna_ordem);
        $tipo_ordem = validarTipoOrdemProcess($tipo_ordem);

        $campos = "
            t.id AS id_time,
            t.nome AS nome_time,
            t.escudo,
            SUM(c.jogos) AS jogos,
            SUM(c.vitorias) AS vitorias,
            SUM(c.empates) AS empates,
            SUM(c.derrotas) AS derrotas,
            SUM(c.gp) AS gols_pro,
            SUM(c.gc) AS gols_contra,
            SUM(c.saldo) AS saldo,
            SUM(c.pontos) AS pontos
        ";

        $tituloNormalizadoSemAcento = normalizarTextoProcess($tituloNormalizado);

        if (
            $tituloNormalizadoSemAcento === 'brasileirao pontos corridos (2003 - ...)' ||
            $tituloNormalizadoSemAcento === 'serie b - pontos corridos (2006 - ...)'
        ) {
            $campos .= ", SUM(c.pontos_marcados) AS pontos_marcados";
        }

        $where = '';
        $params = [];

        if (is_array($id_competicao)) {
            $ids_sql = implode(',', array_map('intval', $id_competicao));
            $where .= " temp.id_competicao IN ($ids_sql)";
        } else {
            $where .= " temp.id_competicao = :id_competicao";
            $params['id_competicao'] = (int)$id_competicao;
        }

        if (!empty($ano_inicio)) {
            $where .= " AND temp.ano >= :ano_inicio";
            $params['ano_inicio'] = (int)$ano_inicio;
        }

        $sql = "
            SELECT $campos
            FROM classificacao c
            INNER JOIN temporadas temp ON temp.id = c.id_temporada
            INNER JOIN times t ON t.id = c.id_time
            WHERE $where
            GROUP BY c.id_time, t.id, t.nome, t.escudo
            ORDER BY $coluna_ordem $tipo_ordem, t.nome ASC
        ";

        $stmt = $pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(
                ':' . $key,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/* =========================================
   CONSULTAS ESPECIAIS DE PONTUAÇÕES
========================================= */

if (
    !empty($id_competicao) &&
    $exibir_tabela &&
    !$processado_por_auxiliares
) {

    if ($tipo_participacao === 'campeoes_pontos_corridos') {

        $sql = "
            SELECT 
                t.id AS id_time,
                t.nome AS nome_time,
                t.escudo,
                temp.ano,
                c.pontos_marcados
            FROM classificacao c
            INNER JOIN temporadas temp ON temp.id = c.id_temporada
            INNER JOIN times t ON t.id = c.id_time
            WHERE temp.id_competicao = :id_competicao
              AND temp.ano >= :ano_inicio
              AND (c.fase = 'Camp' OR c.fase = '1º')
              AND c.pontos_marcados IS NOT NULL
            ORDER BY c.pontos_marcados DESC, temp.ano DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_competicao', (int)$id_competicao, PDO::PARAM_INT);
        $stmt->bindValue(':ano_inicio', (int)$ano_inicio, PDO::PARAM_INT);
        $stmt->execute();

        $tabela_estatisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($tipo_participacao === 'rebaixados_mais_pontos') {

        $nivel = $nivel_rebaixamento ?: 'A';
        $ano_inicio = ($nivel === 'A') ? 2003 : 2006;

        $sql = "
            SELECT
                t.id AS id_time,
                t.nome AS nome_time,
                t.escudo,
                temp.ano,
                c.pontos_marcados,
                c.fase,
                CAST(SUBSTRING_INDEX(c.fase, 'º', 1) AS UNSIGNED) AS posicao_real
            FROM classificacao c
            INNER JOIN temporadas temp ON temp.id = c.id_temporada
            INNER JOIN times t ON t.id = c.id_time
            WHERE temp.id_competicao = :id_competicao
              AND temp.ano >= :ano_inicio
              AND c.pontos_marcados IS NOT NULL
              AND c.fase REGEXP '^[0-9]+º$'
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_competicao', (int)$id_competicao, PDO::PARAM_INT);
        $stmt->bindValue(':ano_inicio', (int)$ano_inicio, PDO::PARAM_INT);
        $stmt->execute();

        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $por_ano = [];

        foreach ($todos as $linha) {
            if ((int)$linha['posicao_real'] > 0) {
                $por_ano[(int)$linha['ano']][] = $linha;
            }
        }

        $resultados = [];

        foreach ($por_ano as $ano => $linhas) {
            $pos_alvo = null;

            if ($nivel === 'A') {
                if ($ano === 2003) {
                    $pos_alvo = 21;
                } elseif ($ano === 2004 || $ano === 2005) {
                    $pos_alvo = 19;
                } elseif ($ano >= 2006) {
                    $pos_alvo = 17;
                }
            } else {
                if ($ano >= 2006) {
                    $pos_alvo = 17;
                }
            }

            if ($pos_alvo === null) {
                continue;
            }

            foreach ($linhas as $linha) {
                if ((int)$linha['posicao_real'] === (int)$pos_alvo) {
                    $resultados[] = [
                        'id_time' => $linha['id_time'],
                        'nome_time' => $linha['nome_time'],
                        'escudo' => $linha['escudo'],
                        'ano' => $linha['ano'],
                        'pontos_marcados' => $linha['pontos_marcados'],
                        'posicao' => $linha['posicao_real']
                    ];

                    break;
                }
            }
        }

        usort($resultados, function ($a, $b) {
            if ((float)$a['pontos_marcados'] === (float)$b['pontos_marcados']) {
                return (int)$b['ano'] <=> (int)$a['ano'];
            }

            return (float)$b['pontos_marcados'] <=> (float)$a['pontos_marcados'];
        });

        $tabela_estatisticas = $resultados;

    } elseif ($tipo_participacao === 'menor_pontuacao_nao_rebaixado') {

        $nivel = $nivel_rebaixamento ?: 'A';
        $ano_inicio = ($nivel === 'A') ? 2003 : 2006;

        $sql = "
            SELECT
                t.id AS id_time,
                t.nome AS nome_time,
                t.escudo,
                temp.ano,
                c.pontos_marcados,
                c.fase,
                CAST(SUBSTRING_INDEX(c.fase, 'º', 1) AS UNSIGNED) AS posicao_real
            FROM classificacao c
            INNER JOIN temporadas temp ON temp.id = c.id_temporada
            INNER JOIN times t ON t.id = c.id_time
            WHERE temp.id_competicao = :id_competicao
              AND temp.ano >= :ano_inicio
              AND c.pontos_marcados IS NOT NULL
              AND c.fase REGEXP '^[0-9]+º$'
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_competicao', (int)$id_competicao, PDO::PARAM_INT);
        $stmt->bindValue(':ano_inicio', (int)$ano_inicio, PDO::PARAM_INT);
        $stmt->execute();

        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $por_ano = [];

        foreach ($todos as $linha) {
            if ((int)$linha['posicao_real'] > 0) {
                $por_ano[(int)$linha['ano']][] = $linha;
            }
        }

        $resultados = [];

        foreach ($por_ano as $ano => $linhas) {
            $pos_alvo = null;

            if ($nivel === 'A') {
                if ($ano === 2003) {
                    $pos_alvo = 22;
                } elseif ($ano === 2004) {
                    $pos_alvo = 20;
                } elseif ($ano === 2005) {
                    $pos_alvo = 18;
                } elseif ($ano >= 2006) {
                    $pos_alvo = 16;
                }
            } else {
                if ($ano >= 2006) {
                    $pos_alvo = 16;
                }
            }

            if ($pos_alvo === null) {
                continue;
            }

            foreach ($linhas as $linha) {
                if ((int)$linha['posicao_real'] === (int)$pos_alvo) {
                    $resultados[] = [
                        'id_time' => $linha['id_time'],
                        'nome_time' => $linha['nome_time'],
                        'escudo' => $linha['escudo'],
                        'ano' => $linha['ano'],
                        'pontos_marcados' => $linha['pontos_marcados'],
                        'posicao' => $linha['posicao_real']
                    ];

                    break;
                }
            }
        }

        usort($resultados, function ($a, $b) {
            if ((float)$a['pontos_marcados'] === (float)$b['pontos_marcados']) {
                return (int)$b['ano'] <=> (int)$a['ano'];
            }

            return (float)$a['pontos_marcados'] <=> (float)$b['pontos_marcados'];
        });

        $tabela_estatisticas = $resultados;

    } elseif ($tipo_participacao === 'ultimos_colocados') {

        $nivel = $nivel_rebaixamento ?: 'A';
        $ano_inicio = ($nivel === 'A') ? 2003 : 2006;

        $sql = "
            SELECT
                t.id AS id_time,
                t.nome AS nome_time,
                t.escudo,
                temp.ano,
                c.pontos_marcados,
                c.fase,
                CAST(SUBSTRING_INDEX(c.fase, 'º', 1) AS UNSIGNED) AS posicao_real
            FROM classificacao c
            INNER JOIN temporadas temp ON temp.id = c.id_temporada
            INNER JOIN times t ON t.id = c.id_time
            WHERE temp.id_competicao = :id_competicao
              AND temp.ano >= :ano_inicio
              AND c.pontos_marcados IS NOT NULL
              AND c.fase REGEXP '^[0-9]+º$'
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_competicao', (int)$id_competicao, PDO::PARAM_INT);
        $stmt->bindValue(':ano_inicio', (int)$ano_inicio, PDO::PARAM_INT);
        $stmt->execute();

        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $por_ano = [];

        foreach ($todos as $linha) {
            if ((int)$linha['posicao_real'] > 0) {
                $por_ano[(int)$linha['ano']][] = $linha;
            }
        }

        $resultados = [];

        foreach ($por_ano as $ano => $linhas) {
            $pos_alvo = null;

            if ($nivel === 'A') {
                if ($ano === 2003 || $ano === 2004) {
                    $pos_alvo = 24;
                } elseif ($ano === 2005) {
                    $pos_alvo = 22;
                } elseif ($ano >= 2006) {
                    $pos_alvo = 20;
                }
            } else {
                if ($ano >= 2006) {
                    $pos_alvo = 20;
                }
            }

            if ($pos_alvo === null) {
                continue;
            }

            foreach ($linhas as $linha) {
                if ((int)$linha['posicao_real'] === (int)$pos_alvo) {
                    $resultados[] = [
                        'id_time' => $linha['id_time'],
                        'nome_time' => $linha['nome_time'],
                        'escudo' => $linha['escudo'],
                        'ano' => $linha['ano'],
                        'pontos_marcados' => $linha['pontos_marcados'],
                        'posicao' => $linha['posicao_real']
                    ];

                    break;
                }
            }
        }

        usort($resultados, function ($a, $b) {
            if ((float)$a['pontos_marcados'] === (float)$b['pontos_marcados']) {
                return (int)$b['ano'] <=> (int)$a['ano'];
            }

            return (float)$a['pontos_marcados'] <=> (float)$b['pontos_marcados'];
        });

        $tabela_estatisticas = $resultados;

    } else {

        if ($id_competicao !== null) {
            $tabela_estatisticas = build_sql_query(
                $pdo,
                $id_competicao,
                $ano_inicio,
                $tituloNormalizado,
                $coluna_ordem,
                $tipo_ordem
            );
        }
    }
}

?>