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
if (!function_exists('build_sql_query_internacional')) {
    function build_sql_query_internacional($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem) {
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

        if ($tituloNormalizado === 'campeonato mundial de clubes (2025)') {
            $campos .= ", SUM(c.pontos_marcados) AS pontos_marcados";
        }
        // Adicione outras condições específicas para campos adicionais, se necessário.

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

        $colunas_permitidas = ['nome_time', 'jogos', 'vitorias', 'empates', 'derrotas', 'gols_pro', 'gols_contra', 
        'saldo', 'pontos', 'pontos_marcados'];
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

// Função para processar os cases internacionais
function processar_internacionais($tituloNormalizado, &$descricao, &$dados, &$id_competicao, &$ano_inicio, &$exibir_tabela, &$tipo_participacao, &$tabela_estatisticas, $pdo, $coluna_ordem, $tipo_ordem) {
    $retorno = false; // Indica se algum case internacional foi encontrado

    switch ($tituloNormalizado) {
        case 'campeonato mundial de clubes (2025)':
            $descricao = "O Campeonato Mundial de Clubes tornou-se a competição mais prestigiada do futebol mundial, reunindo todos 
            os campeões continentais e mais clubes classificados pelo ranking da FIFA. Ao todo, reúne as 32 principais equipes do 
            mundo, em um formato semelhante à Copa do Mundo de seleções.";
            $dados = [
                "Sua primeira edição foi em 2025, disputada nos EUA. Participaram 32 equipes de todos os continentes. Quatro clubes 
                brasileiros participaram: Palmeiras, Flamengo, Fluminense e Botafogo — classificados por serem os últimos quatro 
                campeões da Taça Libertadores da América.",
            ];
            $id_competicao = [62];
            $retorno = true;
            break;
        case 'copa do mundo de clubes (2000 - 2024)':
            $descricao = "A Copa do Mundo de Clubes foi a competição mais prestigiada do futebol mundial até 2025, 
            reunindo os campeões continentais e o campeão local do país-sede.";
            $dados = [
                "Organizada pela FIFA teve uma primeira edição em 2000 e substituiu a Copa Intercontinental, definitivamente, a partir
                de 2005. Participam da competição 7 clubes, os campeões Continentais e o representante do país sede. É disputado 
                anualmente.",
                "O maior campeão é o Real Madri (ESP) com 6 títulos. Os brasileiros campeões são: Corinthians (2000, 2012), São Paulo (2005) e 
                Internacional (2006)."
            ];
            $id_competicao = [1];
            $retorno = true;
            break;
        case 'copa intercontinental (1960 - 2004)':
            $descricao = "A Copa Intercontinental era disputada entre o campeão da Europa e o campeão da América do Sul. Hoje, 
            substituída pela Copa do Mundo de Clubes.";
            $dados = [
                "Foi disputada entre 1960 e 2004. Enfrentavam-se o campeão da Europa e da América do Sul, inicialmente o formato era
                de ida e volta, e depois, passou a ser disputado em uma partida no Japão.",
                "Os maiores campeões com 3 títulos cada são: Milan (ITA), Peñarol (URU), Real Madri (ESP), Boca Juniors (ARG) e 
                Nacional (URU). Os clubes brasileiro que venceram foram: Santos (1962, 1963), São Paulo (1992, 1993), Grêmio (1983) e
                Flamengo (1981)."
            ];
            $id_competicao = [2];
            $retorno = true;
            break;
        case 'libertadores da américa (1960 - ...)':
            $descricao = "A Copa Libertadores da América é a principal competição sul-americana de clubes, promovida pela CONMEBOL 
            desde 1960.";
            $dados = [
                "Apresentou vários formatos, antes apenas os campeões nacionais participavam, posteriormente, os vice-campeões também
                passaram a participar. Clubes do México participaram como convidados entre os anos de 1998 e 2017.",
                "Temos hoje uma primeira fase eliminatória chamada de Pré-Libertadores, os clubes dela classificadas vão a fase de 
                grupos, que é a primeira fase da competição. Após isso, inicia-se a fase final com classificação em formato de 
                ida e volta.",
                "O maior campeão da competição é o Independiente (ARG) com 7 triunfos. Os clubes brasileiros e argentinos detém
                25 troféus cada."
            ];
            $id_competicao = [5];
            $retorno = true;
            break;
        case 'copa sul-americana (2002 - ...)':
            $descricao = "A Copa Sul-Americana é a segunda competição mais importante da CONMEBOL, criada em 2002 como sucessora 
            da outras competições que existiram durante os anos 1980 e 1990.";
            $dados = [
                "É disputada pelos clubes sul-americanos que não disputam a Copa Libertadores, por esse motivo é considerada como
                uma competição de segundo escalão continental. Apresentou diversos formatos, hoje apresenta uma fase de grupos,
                depois recebe alguns clubes eliminados da Libertadores e prossegue com uma fase final com jogos eliminatórios
                de ida e volta.",
                "Os maiores campeões com 2 títulos cada são: LDU (EQU), Boca Juniors (ARG), Independiente (ARG), Athlético Paranaense e 
                Independiente del Valle (EQU).",
                "Os outros brasileiros que venceram a competição foram: São Paulo (2012), Internacional (2008) e Capecoense (2016).",
                "A Argentina é o país com maior número de títulos, 10 ao total. O Brasil venceu a competição 5 vezes."
            ];
            $id_competicao = [7];
            $retorno = true;
            break;
        case 'todas as competições internacionais':
            $descricao = "Soma agregada de todas as competições internacionais registradas no banco de dados 
            (Libertadores, Sul-Americana, Copa do Mundo de Clubes, Copa Intercontinental, Mundial de Clubes, etc.). 
            Os dados apresentam a soma das estatísticas por clube em todas essas competições.";
            $id_competicao = [62, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 61];
            $exibir_tabela = true;
            $retorno = true;
            break;
    }

    if ($retorno && !empty($id_competicao) && $exibir_tabela) {
        // Executa a consulta genérica para os casos internacionais
        $tabela_estatisticas = build_sql_query_internacional($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem);
    }

    return $retorno; // Retorna true se o item foi um case internacional, false caso contrário
}
?>