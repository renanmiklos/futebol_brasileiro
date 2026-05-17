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

// Função reutilizável para montar e executar a query SQL genérica (necessária para tabelas de estatísticas gerais)
if (!function_exists('build_sql_query_regional')) {
    function build_sql_query_regional($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem) {
        $campos = "
            t.nome AS nome_time,
            t.escudo,
            SUM(c.jogos) AS jogos,
            SUM(c.vitorias) AS vitorias,
            SUM(c.empates) AS empates,
            SUM(c.derrotas) AS derrotas,
            SUM(c.gp) AS gols_pro,
            SUM(c.gc) AS gols_contra,
            SUM(c.saldo) AS saldo,
            SUM(c.pontos) AS pontos";

        // Adicione condições específicas para campos adicionais, se necessário, baseado no título.
        // Por enquanto, os regionais não parecem precisar de campos extras como 'pontos_marcados'.

        $where = "";
        $params = [];
        if (is_array($id_competicao)) {
            $ids_sql = implode(',', array_map('intval', $id_competicao));
            $where .= " temp.id_competicao IN ($ids_sql)";
        } else {
            $where .= " temp.id_competicao = :id_competicao";
            $params['id_competicao'] = $id_competicao;
        }
        if ($ano_inicio) {
            $where .= " AND temp.ano >= :ano_inicio";
            $params['ano_inicio'] = $ano_inicio;
        }

        $colunas_permitidas = ['nome_time', 'jogos', 'vitorias', 'empates', 'derrotas', 'gols_pro', 'gols_contra', 'saldo', 'pontos', 'pontos_marcados'];
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

// Função para processar os cases regionais
function processar_regionais($tituloNormalizado, &$descricao, &$dados, &$id_competicao, &$ano_inicio, &$exibir_tabela, &$tipo_participacao, &$tabela_estatisticas, $pdo, $coluna_ordem, $tipo_ordem) {
    $retorno = false; // Indica se algum case regional foi encontrado
    switch ($tituloNormalizado) {
        case 'torneio rio-são paulo (1933 – 2002)':
            $descricao = "O Torneio Rio-São Paulo foi uma das competições regionais mais tradicionais do futebol brasileiro, 
            reunindo os principais clubes dos estados do Rio de Janeiro e São Paulo. Disputado entre 1933 e 2002, com interrupções.";
            $dados = [
                "Disputado pela primeira vez em 1933, sofreu um hiato e voltou a ser disputado em 1950. Daí foi disputado até 1966.
                Retornou nos anos 90 e teve sua última edição em 2002. Sempre contou com os mais pretigiados clubes de Rio e 
                São Paulo.",
                "Os maiores vencedores são Corinthians (1950, 1953, 1954, 1966, 2002), Palmeiras (1933, 1951, 1965, 1993, 2000) e 
                Santos (1959, 1963, 1964, 1966, 1997), com 5 conquistas cada."
            ];
            $id_competicao = 26;
            $retorno = true;
            break;
        case 'copa do nordeste (1994 - ...)':
            $descricao = "A Copa do Nordeste é a principal competição regional do futebol brasileiro, reunindo clubes dos nove 
            estados da região Nordeste.";
            $dados = [
                "Criada em 1994 pela Liga do Nordeste. Passou por alguns formatos diferentes. No início, era disputada por
                clubes de 7 estados nordetinos, clubes do Piauí e do Maranhão disputavam a Copa Norte. A partir de 2015, a competição
                passou a contar com o clube dos 9 estados do nordeste.",
                "O Bahia é o maior campeão com 5 conquistas (2001, 2002, 2017, 2021, 2025)."
            ];
            $id_competicao = 28;
            $retorno = true;
            break;
        case 'copa verde (2014 - ...)':
            $descricao = "A Copa Verde é uma competição regional que reúne clubes dos estados da Região Norte, Centro-Oeste 
            e do Espírito Santo.";
            $dados = [
                "Criada em 2014 pela CBF. Busca valorizar clubes de regiões historicamente sub-representadas.",
                "O Paysandu é o maior campeão com 5 conquistas (2016, 2018, 2022, 2024, 2025)."
            ];
            $id_competicao = 33;
            $retorno = true;
            break;
        case 'copa sul (1999)':
            $descricao = "A Copa Sul foi uma competição regional realizada em 1999, reunindo clubes dos estados do Paraná, 
            Santa Catarina e Rio Grande do Sul.";
            $dados = [
                "Única edição realizada em 1999. O Grêmio foi o Campeão.",
            ];
            $id_competicao = 29;
            $retorno = true;
            break;
        case 'copa norte (1997 - 2002)':
            $descricao = "A Copa Norte foi uma competição regional que reuniu clubes da Região Norte e dos estados do Piauí e do
            Maranhão. ";
            $dados = [
                "Disputada de 1997 a 2002. O São Raimundo (AM) foi o maior campeão com 3 títulos (1999, 2000, 2001).",
            ];
            $id_competicao = 32;
            $retorno = true;
            break;
        case 'copa centro-oeste (1999 - 2002)':
            $descricao = "A Copa Centro-Oeste reuniu clubes dos estados de Goiás, Mato Grosso, Mato Grosso do Sul, Distrito Federal
            e do Espírito Santo. Além disso, em 1999, os clubes de Minas Gerais também disputaram a competição.";
            $dados = [
                "Realizada entre 1999 e 2002. O Goiás foi o maior campeão vencendo em 2000, 2001, 2002.",
            ];
            $id_competicao = 31;
            $retorno = true;
            break;
        case 'torneio norte-nordeste (1968 - 1970)':
            $descricao = "O Torneio Norte-Nordeste foi uma competição regional organizada pela CBD entre 1968 e 1970, 
            com clubes do Norte e Nordeste.";
            $dados = [
                "Disputado em três edições. Sport, Ceará e Fortaleza foram campeões.",
            ];
            $id_competicao = 27;
            $retorno = true;
            break;
        case 'copa sul-minas (2000 - 2002)':
            $descricao = "A Copa Sul-Minas foi uma competição regional disputada entre clubes dos estados de Minas Gerais, 
            Paraná, Santa Catarina e Rio Grande do Sul. Realizada entre 2000 e 2002, teve o objetivo de fomentar confrontos 
            interestaduais no sul e sudeste do Brasil.";
            $dados = [
                "Disputada entre 2000 e 2002. O Cruzeiro foi o maior campeão, vencendo as edições de 2001 e 2002."
            ];
            $id_competicao = 30;
            $retorno = true;
            break;
    }

    // Executa a consulta genérica *apenas* se for um case regional, $exibir_tabela for true, e $tipo_participacao não estiver definido.
    if ($retorno && !empty($id_competicao) && $exibir_tabela && $tipo_participacao === null) {
        $tabela_estatisticas = build_sql_query_regional($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem);
    }

    return $retorno; // Retorna true se o item foi um case regional, false caso contrário
}
?>