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
if (!function_exists('build_sql_query_nacional')) {
    function build_sql_query_nacional($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem) {
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
        // Especificamente para os títulos que precisam de 'pontos_marcados'.
        if ($tituloNormalizado === 'brasileirão pontos corridos (2003 - ...)') {
            $campos .= ", SUM(c.pontos_marcados) AS pontos_marcados";
        } elseif ($tituloNormalizado === 'série b - pontos corridos (2006 - ...)') {
            $campos .= ", SUM(c.pontos_marcados) AS pontos_marcados";
        }

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

// Função para processar os cases nacionais
function processar_nacionais($tituloNormalizado, &$descricao, &$dados, &$id_competicao, &$ano_inicio, &$exibir_tabela, &$tipo_participacao, &$tabela_estatisticas, $pdo, $coluna_ordem, $tipo_ordem) {
    $retorno = false; // Indica se algum case nacional foi encontrado
    switch ($tituloNormalizado) {
        case 'era da taça brasil (1959 - 1968)':
            $descricao = "A Taça Brasil foi o primeiro campeonato nacional oficial do futebol brasileiro, realizado entre 1959 e 1968. 
            Foi organizado pela CBD (antiga CBF) e reunia os campeões estaduais das diversas federações do país.";
            $dados = [
                "A primeira edição foi disputada em 1959 e a última em 1968. O formato foi de eliminatória simples. Os clubes 
                participantes eram os campeões estaduais da temporada anterior. O maior campeão foi o Santos, com 5 conquistas 
                (1961, 1962, 1963, 1964, 1965).",
            ];
            $id_competicao = [17];
            $retorno = true;
            break;
        case 'era do torneio roberto gomes pedrosa (1967 - 1970)':
            $descricao = "O Torneio Roberto Gomes Pedrosa foi um torneio disputado entre 1967 e 1970. 
            Foi considerado uma espécie de campeonato prévio ao Campeonato Nacional. O torneio já era disputado com esse nome
            quando era o Torneio Rio-São Paulo até 1966. A partir de 1967 ele foi ampliado e contou com participantes do Rio
            Grande do Sul, de Minas Gerais, do Paraná, de Pernambuco e da Bahia.";
            $dados = [
                "Realizado entre 1967 e 1970, foi disputado no sistema de grupos e com classificação para um quadrangular 
                final. O maior campeão foi o Palmeiras (1967 e 1969).",
            ];
            $id_competicao = [18];
            $retorno = true;
            break;
        case 'brasileirão unificado (1959 - ...)':
            $descricao = "O Campeonato Brasileiro, também chamado de Brasileirão, é o principal torneio nacional do futebol brasileiro, 
            reunindo clubes de todo o país desde sua criação em 1959. O Campeonato Nacional mudou de nomenclatura e de formato por 
            diversas vezes. Ele foi unificado em 2010 pela CBF. Então ele iniciou com a Taça Brasil em 1959, passou pelo 'Robertão' 
            de 1967 a 1970 e culminou com o Campeonato Brasileiro desde 1971, além de ter tido dois momentos de dificuldades como em 
            1987 com a Copa União e em 2000 com a Copa João Havelange.";
            $dados = [
                "Principal campeonato nacional do Brasil, disputado anualmente desde 1959. Representa a unificação dos três torneios 
                nacionais: Taça Brasil, Torneio Roberto Gomes Pedrosa e Série A. Seu formato variou muito ao longo dos anos, mas a 
                partir de 2003 adotou o sistema de pontos corridos. O Palmeiras é o maior campeão, com 12 títulos (1960, 1967, 1967, 
                1969, 1972, 1973, 1993, 1994, 2016, 2018, 2022, 2023).",
            ];
            $id_competicao = [17, 18, 19];
            $retorno = true;
            break;
        case 'brasileirão (1971 - ...)':
            $descricao = "Campeonato Brasileiro disputado de 1971 até hoje, sendo uma evolução dos formatos anteriores até a 
            consolidação do formato de pontos corridos em 2003.";
            $dados = [
                "Sucessor dos torneios anteriores, disputado desde 1971. Seu formato variou muito até 2002. 
                A partir de 2003, adotou o sistema de pontos corridos. O maior campeão é o Palmeiras, com 8 conquistas 
                (1972, 1973, 1993, 1994, 2016, 2018, 2022, 2023).",
            ];
            $id_competicao = [19];
            $retorno = true;
            break;
        case 'brasileirão pontos corridos (2003 - ...)':
            $descricao = "A partir de 2003, o Campeonato Brasileiro adotou o formato de pontos corridos, similar ao modelo europeu, 
            aumentando a competitividade e o número de jogos. Teve seu início em 2003 com 24 equipes esse número foi mantido em 2004, 
            em 2005 diminuiu para 22 equipes e em 2006 adotou o sistema de 20 equipes.";
            $dados = [
                "Formato adotado desde 2003: sistema de pontos corridos com todos contra todos em jogos de ida e volta. Os maiores 
                campeões nesse formato são Palmeiras (2016, 2018, 2022, 2023) e Corinthians (2005, 2011, 2015, 2017), 
                com 4 conquistas cada.",
            ];
            $id_competicao = 19;
            $ano_inicio = 2003;
            $retorno = true;
            break;
        case 'copa do brasil (1989 - ...)':
            $descricao = "Disputada desde 1989, a Copa do Brasil é a segunda competição em importância no calendário do futebol 
            brasileiro. É disputada totalmente em formato eliminatório. No início era disputada pelos campeões estaduais.
            Com o passar do tempo foi sendo ampliada e contando com a participação de outras equipes de importância no futebol
            brasileiro.";
            $dados = ["O maior campeão é o Cruzeiro, com 6 conquistas (1993, 1996, 2000, 2003, 2017, 2018)."];
            $id_competicao = 23;
            $retorno = true;
            break;
        case 'brasileiro - série b (1971 - ...)':
            $descricao = "Segunda divisão do Campeonato Brasileiro disputada desde 1971 com interrupções. A sua consolidação e disputa
            contínua aconteceu a partir de 1994. A partir de 2006, passou a ser disputada em sistema de pontos corridos igual a 
            Série A do Campeonato Brasileiro.";
            $dados = ["O maior campeão, com 3 títulos, é o Coritiba (2007, 2010, 2025)."];
            $id_competicao = 20;
            $retorno = true;
            break;
        case 'série b - pontos corridos (2006 - ...)':
            $descricao = "A partir de 2006, o Campeonato Brasileiro - Série B adotou o formato de pontos corridos, 
            similando à primeira divisão, aumentando a competitividade e o número de jogos. Teve seu início em 2006 com 20 equipes.";
            $dados = [
                "Formato adotado desde 2006: sistema de pontos corridos com todos contra todos em jogos de ida e volta. Os maiores 
                campeões nesse formato são Coritiba e Botafogo, com 2 títulos cada.",
            ];
            $id_competicao = 20;
            $ano_inicio = 2006;
            $retorno = true;
            break;
        case 'brasileiro - série c (1981 - ...)':
            $descricao = "Terceira divisão do Campeonato Brasileiro disputada desde 1981 com interrupções. Sua consolidação no 
            calendário do futebol brasileiro se deu a partir de 1994. Modificou diversas vezes os eu formtato, chgando a ter mais 
            de 100 participantes durante os anos 1990. A partir de 2009, com a criação da Série D, a Série C ficou com menos times 
            e com um maior níve lde competitividade.";
            $dados = ["O maior campeão com 3 consquistas é o Vila Nova (1996, 2015, 2020)."];
            $id_competicao = 21;
            $retorno = true;
            break;
        case 'brasileiro - série d (2009 - ...)':
            $descricao = "Quarta divisão do Campeonato Brasileiro disputada desde 2009, sem interrupções. Apresentou diversos formatos
            de disputas, em regra tem uma primeira fase com grupos e depois uma fase eliminatória.";
            $dados = ["O maior campeão, com duas conquistas, é o Ferroviário/CE (2018, 2023)."];
            $id_competicao = 22;
            $retorno = true;
            break;
    }
    // Executa a consulta genérica *apenas* se for um case nacional, $exibir_tabela for true, e $tipo_participacao não estiver definido.
    if ($retorno && !empty($id_competicao) && $exibir_tabela && $tipo_participacao === null) {
        $tabela_estatisticas = build_sql_query_nacional($pdo, $id_competicao, $ano_inicio, $tituloNormalizado, $coluna_ordem, $tipo_ordem);
    }
    return $retorno; // Retorna true se o item foi um case nacional, false caso contrário
}
?>