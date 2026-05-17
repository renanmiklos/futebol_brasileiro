<?php

// Função auxiliar para gerar links de ordenação (necessária para as tabelas)
if (!function_exists('link_coluna')) {
    function link_coluna($nome_coluna, $tituloOriginal, $coluna_atual, $tipo_ordem_atual) {
        $nova_ordem = ($nome_coluna === $coluna_atual && $tipo_ordem_atual === 'ASC') ? 'DESC' : 'ASC';
        return "?item=" . urlencode($tituloOriginal) .
               "&coluna_ordem=" . urlencode($nome_coluna) .
               "&tipo_ordem=" . urlencode($nova_ordem);
    }
}

// Função reutilizável para montar e executar a query SQL genérica para clubes da Região Centro-Oeste
if (!function_exists('build_sql_query_centro_oeste')) {
    function build_sql_query_centro_oeste($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem) {
        $campos = "
            t.nome AS nome_time,
            t.escudo,
            t.estado,
            SUM(c.jogos) AS jogos,
            SUM(c.vitorias) AS vitorias,
            SUM(c.empates) AS empates,
            SUM(c.derrotas) AS derrotas,
            SUM(c.gp) AS gols_pro,
            SUM(c.gc) AS gols_contra,
            SUM(c.saldo) AS saldo,
            SUM(c.pontos) AS pontos";

        // Adiciona campos específicos baseados no título original
        if ($tituloNormalizado === 'centro-oeste na série a - pontos corridos') {
            $campos .= ", SUM(c.pontos_marcados) AS pontos_marcados";
        } elseif ($tituloNormalizado === 'centro-oeste na série b - pontos corridos') {
            $campos .= ", SUM(c.pontos_marcados) AS pontos_marcados";
        }
        // Outros títulos podem ser adicionados aqui se necessário

        // Filtro pelos estados da Região Centro-Oeste
        $where = " t.estado IN ('DF', 'GO', 'MT', 'MS')";

        $params = [];

        if (is_array($id_competicao)) {
            $ids_sql = implode(',', array_map('intval', $id_competicao));
            $where .= " AND temp.id_competicao IN ($ids_sql)";
        } else {
            $where .= " AND temp.id_competicao = :id_competicao";
            $params['id_competicao'] = $id_competicao;
        }

        if ($ano_inicio) {
            $where .= " AND temp.ano >= :ano_inicio";
            $params['ano_inicio'] = $ano_inicio;
        }

        $colunas_permitidas = [
            'nome_time', 'estado', 'jogos', 'vitorias', 'empates', 'derrotas',
            'gols_pro', 'gols_contra', 'saldo', 'pontos', 'pontos_marcados'
        ];

        if (!in_array($coluna_ordem, $colunas_permitidas)) {
            $coluna_ordem = 'pontos';
        }

        $sql = "
            SELECT $campos
            FROM classificacao c
            INNER JOIN temporadas temp ON temp.id = c.id_temporada
            INNER JOIN times t ON t.id = c.id_time
            WHERE $where
            GROUP BY c.id_time
            ORDER BY $coluna_ordem $tipo_ordem
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Função reutilizável para montar e executar a query SQL genérica para participações da Região Centro-Oeste
if (!function_exists('build_sql_query_participacoes_centro_oeste')) {
    function build_sql_query_participacoes_centro_oeste($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem) {
        // Siglas dos estados da Região Centro-Oeste
        $siglas_centro_oeste = ['DF', 'GO', 'MT', 'MS'];

        // Preparar named placeholders para IN (...)
        $estado_placeholders = [];
        foreach ($siglas_centro_oeste as $i => $sigla) {
            $estado_placeholders[] = ":estado{$i}";
        }
        $inEstados = implode(', ', $estado_placeholders);

        // Preparar condição de ano para WHERE principal (usa alias temp)
        $where_ano_main = "";
        if ($ano_inicio) {
            $where_ano_main = " AND temp.ano >= :ano_inicio ";
        }

        $sql = "
            SELECT
                t.nome AS nome_time,
                t.escudo,
                COUNT(DISTINCT temp.ano) AS participacoes,
                COALESCE(
                    (SELECT GROUP_CONCAT(temp2.ano ORDER BY temp2.ano SEPARATOR ', ')
                     FROM classificacao cl2
                     INNER JOIN temporadas temp2 ON temp2.id = cl2.id_temporada
                     INNER JOIN times t2 ON t2.id = cl2.id_time
                     WHERE cl2.id_time = t.id
                       AND temp2.id_competicao = :id_competicao
                       " . ($ano_inicio ? " AND temp2.ano >= :ano_inicio " : "") . "
                       AND (cl2.fase = 'Camp' OR cl2.fase = '1º')
                       AND t2.estado IN ($inEstados)
                    ), ''
                ) AS anos_titulos,
                COALESCE(
                    (SELECT COUNT(*)
                     FROM classificacao clq
                     INNER JOIN temporadas tempq ON tempq.id = clq.id_temporada
                     INNER JOIN times t3 ON t3.id = clq.id_time
                     WHERE clq.id_time = t.id
                       AND tempq.id_competicao = :id_competicao
                       " . ($ano_inicio ? " AND tempq.ano >= :ano_inicio " : "") . "
                       AND (clq.fase = 'Camp' OR clq.fase = '1º')
                       AND t3.estado IN ($inEstados)
                    ), 0
                ) AS qtd_titulos,
                COALESCE(
                    (SELECT COUNT(*)
                     FROM classificacao cl3
                     INNER JOIN temporadas temp3 ON temp3.id = cl3.id_temporada
                     INNER JOIN times t4 ON t4.id = cl3.id_time
                     WHERE cl3.id_time = t.id
                       AND temp3.id_competicao = :id_competicao
                       " . ($ano_inicio ? " AND temp3.ano >= :ano_inicio " : "") . "
                       AND cl3.fase IN ('Camp', 'Vice', 'SF', '3º', '4º')
                       AND t4.estado IN ($inEstados)
                    ), 0
                ) AS top4,
                (SELECT MAX(temp4.ano)
                 FROM classificacao cl4
                 INNER JOIN temporadas temp4 ON temp4.id = cl4.id_temporada
                 INNER JOIN times t5 ON t5.id = cl4.id_time
                 WHERE cl4.id_time = t.id
                   AND temp4.id_competicao = :id_competicao
                   " . ($ano_inicio ? " AND temp4.ano >= :ano_inicio " : "") . "
                   AND t5.estado IN ($inEstados)
                ) AS ultima_participacao
            FROM classificacao c
            INNER JOIN temporadas temp ON temp.id = c.id_temporada
            INNER JOIN times t ON t.id = c.id_time
            WHERE temp.id_competicao = :id_competicao
            " . $where_ano_main . "
              AND t.estado IN ($inEstados)
            GROUP BY t.id, t.nome, t.escudo
            ORDER BY
                participacoes DESC,
                qtd_titulos DESC,
                top4 DESC,
                ultima_participacao DESC,
                t.nome ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_competicao', $id_competicao, PDO::PARAM_INT);
        if ($ano_inicio) {
            $stmt->bindValue(':ano_inicio', $ano_inicio, PDO::PARAM_INT);
        }
        foreach ($siglas_centro_oeste as $i => $sigla) {
            $stmt->bindValue(":estado{$i}", $sigla, PDO::PARAM_STR);
        }

        $stmt->execute();
        $tabela_estatisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pós-processamento: calcular quantidade de títulos (qtd_titulos) a partir de anos_titulos (fallback).
        foreach ($tabela_estatisticas as &$linha) {
            if (!isset($linha['qtd_titulos']) || $linha['qtd_titulos'] === null || $linha['qtd_titulos'] === '') {
                $anos = $linha['anos_titulos'];
                if (!empty($anos)) {
                    $lista_anos = explode(', ', $anos);
                    $linha['qtd_titulos'] = count($lista_anos);
                } else {
                    $linha['qtd_titulos'] = 0;
                }
            } else {
                $linha['qtd_titulos'] = (int)$linha['qtd_titulos'];
            }
        }
        unset($linha);

        return $tabela_estatisticas;
    }
}

// Função para processar os cases da Região Centro-Oeste
function processar_centro_oeste($tituloNormalizado, &$descricao, &$dados, &$id_competicao, &$ano_inicio, &$exibir_tabela, &$tipo_participacao, &$tabela_estatisticas, $pdo, $coluna_ordem, $tipo_ordem) {
    $retorno = false; // Indica se algum case centro-oeste foi encontrado

    switch ($tituloNormalizado) {
        // Casos para Participações da Região Centro-Oeste
        case 'participações do centro-oeste na libertadores':
            $descricao = "Participações dos clubes da Região Centro-Oeste na Copa Libertadores da América.";
            $id_competicao = 5;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes_centro_oeste';
            $retorno = true;
            break;
        case 'participações do centro-oeste na sul americana':
            $descricao = "Participações dos clubes da Região Centro-Oeste na Copa Sul Americana.";
            $id_competicao = 7;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes_centro_oeste';
            $retorno = true;
            break;
        case 'participações do centro-oeste - série a':
            $descricao = "Participações dos clubes da Região Centro-Oeste no Campeonato Brasileiro Série A.";
            $id_competicao = 19; // Série A
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes_centro_oeste';
            $retorno = true;
            break;
        case 'participações do centro-oeste - série a - pontos corridos':
            $descricao = "Participações dos clubes da Região Centro-Oeste no Campeonato Brasileiro Série A
            no período dos Pontos Corridos.";
            $id_competicao = 19;
            $ano_inicio = 2003;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes_centro_oeste';
            $retorno = true;
            break;
        case 'participações do centro-oeste - série b':
            $descricao = "Participações dos clubes da Região Centro-Oeste no Campeonato Brasileiro Série B.";
            $id_competicao = 20; // Série B
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes_centro_oeste';
            $retorno = true;
            break;
        case 'participações do centro-oeste - série b - pontos corridos':
            $descricao = "Participações dos clubes da Região Centro-Oeste no Campeonato Brasileiro Série B 
            no período dos Pontos Corridos.";
            $id_competicao = 20;
            $ano_inicio = 2006;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes_centro_oeste';
            $retorno = true;
            break;
        case 'participações do centro-oeste - série c':
            $descricao = "Participações dos clubes da Região Centro-Oeste no Campeonato Brasileiro Série C.";
            $id_competicao = 21; // Série C
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes_centro_oeste';
            $retorno = true;
            break;
        case 'participações do centro-oeste - série d':
            $descricao = "Participações dos clubes da Região Centro-Oeste no Campeonato Brasileiro Série D.";
            $id_competicao = 22; // Série D
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes_centro_oeste';
            $retorno = true;
            break;

        // Casos para Resultados da Região Centro-Oeste (resultados gerais)
        case 'centro-oeste na libertadores':
            $descricao = "Estatísticas gerais dos clubes da Região Centro-Oeste na Copa Libertadores da América.";
            $id_competicao = 5;
            $exibir_tabela = true;
            $retorno = true;
            break;
        case 'centro-oeste na sul americana':
            $descricao = "Estatísticas gerais dos clubes da Região Centro-Oeste na Copa Sul Americana.";
            $id_competicao = 7;
            $exibir_tabela = true;
            $retorno = true;
            break;
        case 'centro-oeste na série a':
            $descricao = "Estatísticas gerais dos clubes da Região Centro-Oeste na Série A do Campeonato Brasileiro.";
            $id_competicao = 19; // Série A
            $exibir_tabela = true;
            $retorno = true;
            break;
        case 'centro-oeste na série a - pontos corridos':
            $descricao = "Estatísticas gerais dos clubes da Região Centro-Oeste na Série A do Campeonato Brasileiro 
            no período dos pontos corridos.";
            $id_competicao = 19;
            $ano_inicio = 2003;
            $exibir_tabela = true;
            $retorno = true;
            break;
        case 'centro-oeste na série b':
            $descricao = "Estatísticas gerais dos clubes da Região Centro-Oeste na Série B do Campeonato Brasileiro.";
            $id_competicao = 20; // Série B
            $exibir_tabela = true;
            $retorno = true;
            break;
        case 'centro-oeste na série b - pontos corridos':
            $descricao = "Estatísticas gerais dos clubes da Região Centro-Oeste na Série B do Campeonato Brasileiro 
            no período dos pontos corridos.";
            $id_competicao = 20;
            $ano_inicio = 2006;
            $exibir_tabela = true;
            $retorno = true;
            break;
        case 'centro-oeste na série c':
            $descricao = "Estatísticas gerais dos clubes da Região Centro-Oeste na Série C do Campeonato Brasileiro.";
            $id_competicao = 21; // Série C
            $exibir_tabela = true;
            $retorno = true;
            break;
        case 'centro-oeste na série d':
            $descricao = "Estatísticas gerais dos clubes da Região Centro-Oeste na Série D do Campeonato Brasileiro.";
            $id_competicao = 22; // Série D
            $exibir_tabela = true;
            $retorno = true;
            break;
    }

    // Executa a consulta específica para participações da Região Centro-Oeste antes da consulta genérica de resultados
    if ($retorno && !empty($id_competicao) && $exibir_tabela && $tipo_participacao === 'participacoes_centro_oeste') {
        $tabela_estatisticas = build_sql_query_participacoes_centro_oeste($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem);
    }
    // Executa a consulta genérica apenas se for um case de resultados da Região Centro-Oeste e não for do tipo participacoes
    elseif ($retorno && !empty($id_competicao) && $exibir_tabela && $tipo_participacao === null) {
        $tabela_estatisticas = build_sql_query_centro_oeste($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem);
    }

    return $retorno; // Retorna true se o item foi um case da Região Centro-Oeste, false caso contrário
}
?>