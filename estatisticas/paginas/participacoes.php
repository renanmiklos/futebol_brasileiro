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

// Função reutilizável para montar e executar a query SQL genérica para participações
if (!function_exists('build_sql_query_participacoes')) {
    function build_sql_query_participacoes($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem) {
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
                     WHERE cl2.id_time = t.id
                       AND temp2.id_competicao = :id_competicao
                       " . ($ano_inicio ? " AND temp2.ano >= :ano_inicio " : "") . "
                       AND (cl2.fase = 'Camp' OR cl2.fase = '1º')
                    ), ''
                ) AS anos_titulos,
                COALESCE(
                    (SELECT COUNT(*)
                     FROM classificacao cl3
                     INNER JOIN temporadas temp3 ON temp3.id = cl3.id_temporada
                     WHERE cl3.id_time = t.id
                       AND temp3.id_competicao = :id_competicao
                       " . ($ano_inicio ? " AND temp3.ano >= :ano_inicio " : "") . "
                       AND cl3.fase IN ('Camp', 'Vice', 'SF', '3º', '4º')
                    ), 0
                ) AS top4,
                (SELECT MAX(temp4.ano)
                 FROM classificacao cl4
                 INNER JOIN temporadas temp4 ON temp4.id = cl4.id_temporada
                 WHERE cl4.id_time = t.id
                   AND temp4.id_competicao = :id_competicao
                   " . ($ano_inicio ? " AND temp4.ano >= :ano_inicio " : "") . "
                ) AS ultima_participacao
            FROM classificacao c
            INNER JOIN temporadas temp ON temp.id = c.id_temporada
            INNER JOIN times t ON t.id = c.id_time
            WHERE temp.id_competicao = :id_competicao
            " . $where_ano_main . "
            GROUP BY t.id, t.nome, t.escudo
            ORDER BY 
                participacoes DESC,
                (LENGTH(anos_titulos) - LENGTH(REPLACE(anos_titulos, ',', '')) + 1) DESC,
                top4 DESC,
                ultima_participacao DESC,
                t.nome ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_competicao', $id_competicao, PDO::PARAM_INT);
        // Vincula o parâmetro :ano_inicio apenas se $ano_inicio estiver definido
        if ($ano_inicio) {
            $stmt->bindValue(':ano_inicio', $ano_inicio, PDO::PARAM_INT);
        }
        $stmt->execute();
        $tabela_estatisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pós-processamento: calcular quantidade de títulos (qtd_titulos) a partir de anos_titulos
        foreach ($tabela_estatisticas as &$linha) {
            $anos = $linha['anos_titulos'];
            if (!empty($anos)) {
                $lista_anos = explode(', ', $anos);
                $linha['qtd_titulos'] = count($lista_anos);
            } else {
                $linha['qtd_titulos'] = 0;
            }
        }
        unset($linha); // Limpa a referência
        return $tabela_estatisticas;
    }
}

// Função para processar os cases de participações
function processar_participacoes($tituloNormalizado, &$descricao, &$dados, &$id_competicao, &$ano_inicio, &$exibir_tabela, &$tipo_participacao, &$tabela_estatisticas, $pdo, $coluna_ordem, $tipo_ordem) {
    $retorno = false; // Indica se algum case de participação foi encontrado

    switch ($tituloNormalizado) {
        case 'participações por clube na copa libertadores':
            $descricao = "Número de edições distintas em que cada clube brasileiro participou da Copa Libertadores da América.";
            $id_competicao = 5;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes';
            $retorno = true;
            break;
        case 'participações por clube na copa sul americana':
            $descricao = "Número de edições distintas em que cada clube brasileiro participou da Copa Sul-Americana.";
            $id_competicao = 7;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes';
            $retorno = true;
            break;
        case 'participações por clube no brasileirão - série a':
            $descricao = "Número de edições distintas em que cada clube participou do Campeonato Brasileiro Série A.";
            $id_competicao = 19;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes';
            $retorno = true;
            break;
        case 'participações por clube no brasileirão - série b':
            $descricao = "Número de edições distintas em que cada clube participou do Campeonato Brasileiro Série B.";
            $id_competicao = 20;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes';
            $retorno = true;
            break;
        case 'participações por clube no brasileirão - série c':
            $descricao = "Número de edições distintas em que cada clube participou do Campeonato Brasileiro Série C.";
            $id_competicao = 21;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes';
            $retorno = true;
            break;
        case 'participações por clube no brasileirão - série d':
            $descricao = "Número de edições distintas em que cada clube participou do Campeonato Brasileiro Série D.";
            $id_competicao = 22;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes';
            $retorno = true;
            break;

        case 'participações por clube na copa do brasil':
            $descricao = "Número de edições distintas em que cada clube brasileiro participou da Copa do Brasil.";
            $id_competicao = 23; // ID da Copa do Brasil no seu banco
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes';
            $retorno = true;
            break;

        // Casos "Pontos corridos" que você usa (devem aplicar ano_inicio nas colunas participações, títulos e top4)
        case 'clubes no brasileirão - série a - pontos corridos':
            $descricao = "Participações no Campeonato Brasileiro Série A no formato de pontos corridos (2003 em diante).";
            $id_competicao = 19; // Série A
            $ano_inicio = 2003;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes';
            $retorno = true;
            break;

        case 'clubes no brasileirão - série b - pontos corridos':
            $descricao = "Participações no Campeonato Brasileiro Série B no formato de pontos corridos (2006 em diante).";
            $id_competicao = 20; // Série B
            $ano_inicio = 2006;
            $exibir_tabela = true;
            $tipo_participacao = 'participacoes';
            $retorno = true;
            break;

        // Adicione outros cases de participações aqui, se necessário
    }

    if ($retorno && !empty($id_competicao) && $exibir_tabela && $tipo_participacao === 'participacoes') {
        // Executa a consulta específica para participações
        // IMPORTANTE: Passar $ano_inicio aqui para que a função saiba filtrar os anos
        $tabela_estatisticas = build_sql_query_participacoes($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem);
    }

    return $retorno; // Retorna true se o item foi um case de participação, false caso contrário
}
