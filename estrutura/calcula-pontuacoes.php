<?php
/* =========================================
   CALCULA-PONTUACOES.PHP
   Regras centrais de pontuação do site
   Futebol Brasileiro
========================================= */

/*
  Este arquivo é essencial para:
  - ranking geral
  - rankings regionais
  - rankings nacional/internacional
  - ranking das federações
  - estatísticas por competição

  A função principal é:
  getPontuacaoFinal($pdo, $id_competicao, $fase)
*/

require_once __DIR__ . '/conexaodb.php';

/* =========================================
   CARREGAR PONTUAÇÕES DE FASE
========================================= */

if (!function_exists('carregarPontuacoesFase')) {
    /**
     * Carrega a tabela pontuacoes_fase inteira em memória uma vez por request.
     *
     * Retorna array:
     * "id_competicao:fase" => pontos
     */
    function carregarPontuacoesFase(PDO $pdo): array
    {
        static $cachePF = null;

        if ($cachePF !== null) {
            return $cachePF;
        }

        $cachePF = [];

        $stmt = $pdo->query("
            SELECT 
                id_competicao, 
                fase, 
                pontos 
            FROM pontuacoes_fase
        ");

        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $idCompeticao = (int)($r['id_competicao'] ?? 0);
            $fase = (string)($r['fase'] ?? '');
            $pontos = (int)($r['pontos'] ?? 0);

            if ($idCompeticao > 0 && $fase !== '') {
                $cachePF[$idCompeticao . ':' . $fase] = $pontos;
            }
        }

        return $cachePF;
    }
}

/* =========================================
   COLETAR TEMPORADAS POR COMPETIÇÃO
========================================= */

if (!function_exists('coletarTemporadas')) {
    /**
     * Coleta número de temporadas por competição.
     *
     * Retorna:
     * id_competicao => total_temporadas
     */
    function coletarTemporadas(array $competicoes_ids, PDO $pdo): array
    {
        $competicoes_ids = array_values(array_unique(array_filter(array_map('intval', $competicoes_ids))));

        if (empty($competicoes_ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($competicoes_ids), '?'));

        $sql = "
            SELECT 
                id_competicao, 
                COUNT(*) AS cnt 
            FROM temporadas 
            WHERE id_competicao IN ($placeholders) 
            GROUP BY id_competicao
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($competicoes_ids);

        $res = [];

        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $res[(int)$r['id_competicao']] = (int)$r['cnt'];
        }

        return $res;
    }
}

/* =========================================
   COLETAR PARTICIPAÇÕES POR COMPETIÇÃO
========================================= */

if (!function_exists('coletarParticipacoesPorCompeticao')) {
    /**
     * Coleta participações/títulos agrupados por competição e fase,
     * para um conjunto de estados.
     *
     * Retorna:
     * participacoes[id_competicao][fase] = count
     */
    function coletarParticipacoesPorCompeticao(array $competicoes_ids, array $estados, PDO $pdo): array
    {
        $competicoes_ids = array_values(array_unique(array_filter(array_map('intval', $competicoes_ids))));

        $estados = array_values(array_filter(array_map(function ($uf) {
            return strtoupper(trim((string)$uf));
        }, $estados)));

        if (empty($competicoes_ids) || empty($estados)) {
            return [];
        }

        $placeholdersCompeticoes = implode(',', array_fill(0, count($competicoes_ids), '?'));
        $placeholdersEstados = implode(',', array_fill(0, count($estados), '?'));

        $sql = "
            SELECT 
                t.id_competicao, 
                c.fase, 
                COUNT(*) AS cnt
            FROM classificacao c
            JOIN temporadas t ON c.id_temporada = t.id
            JOIN times tm ON c.id_time = tm.id
            WHERE t.id_competicao IN ($placeholdersCompeticoes)
              AND tm.estado IN ($placeholdersEstados)
            GROUP BY t.id_competicao, c.fase
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($competicoes_ids, $estados));

        $res = [];

        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cid = (int)$r['id_competicao'];
            $fase = (string)$r['fase'];
            $cnt = (int)$r['cnt'];

            if (!isset($res[$cid])) {
                $res[$cid] = [];
            }

            $res[$cid][$fase] = $cnt;
        }

        return $res;
    }
}

/* =========================================
   MAPAS AUXILIARES
========================================= */

if (!function_exists('mapaPosicoesSerieA')) {
    function mapaPosicoesSerieA(): array
    {
        return [
            1 => 1.00,
            2 => 0.80,
            3 => 0.75,
            4 => 0.725,
            5 => 0.70,
            6 => 0.69,
            7 => 0.68,
            8 => 0.67,
            9 => 0.66,
            10 => 0.65,
            11 => 0.64,
            12 => 0.63,
            13 => 0.62,
            14 => 0.61,
            15 => 0.60,
            16 => 0.59,
            17 => 0.58,
            18 => 0.57,
            19 => 0.56,
            20 => 0.55,
            21 => 0.54,
            22 => 0.53,
            23 => 0.52,
            24 => 0.51
        ];
    }
}

if (!function_exists('mapaPosicoesSerieB')) {
    function mapaPosicoesSerieB(): array
    {
        return [
            1 => 1.00,
            2 => 0.80,
            3 => 0.75,
            4 => 0.725,
            5 => 0.70,
            6 => 0.69,
            7 => 0.68,
            8 => 0.67,
            9 => 0.66,
            10 => 0.65,
            11 => 0.64,
            12 => 0.63,
            13 => 0.62,
            14 => 0.61,
            15 => 0.60,
            16 => 0.59,
            17 => 0.58,
            18 => 0.57,
            19 => 0.56,
            20 => 0.55
        ];
    }
}

if (!function_exists('obterNomeCompeticaoPontuacao')) {
    function obterNomeCompeticaoPontuacao(PDO $pdo, int $idCompeticao): ?string
    {
        static $cacheNomes = [];

        if ($idCompeticao <= 0) {
            return null;
        }

        if (array_key_exists($idCompeticao, $cacheNomes)) {
            return $cacheNomes[$idCompeticao];
        }

        $stmt = $pdo->prepare("
            SELECT nome 
            FROM competicoes 
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$idCompeticao]);
        $nome = $stmt->fetchColumn();

        $cacheNomes[$idCompeticao] = $nome ? (string)$nome : null;

        return $cacheNomes[$idCompeticao];
    }
}

/* =========================================
   FUNÇÃO PRINCIPAL DE PONTUAÇÃO
========================================= */

if (!function_exists('getPontuacaoFinal')) {
    /**
     * Obtém a pontuação final de uma competição/fase,
     * aplicando regras dinâmicas quando necessário.
     */
    function getPontuacaoFinal(PDO $pdo, $id_competicao, $fase): int
    {
        static $cache = [];

        $id_competicao = (int)$id_competicao;
        $fase = (string)$fase;

        if ($id_competicao <= 0 || $fase === '') {
            return 0;
        }

        $cacheKey = "{$id_competicao}:{$fase}";

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $pontuacoesFase = carregarPontuacoesFase($pdo);

        $pf = function ($compId, $faseNome) use ($pontuacoesFase) {
            $k = (int)$compId . ':' . (string)$faseNome;
            return $pontuacoesFase[$k] ?? null;
        };

        /* =========================================
           REGRA 1: Copa do Mundo de Clubes (id=1)
        ========================================= */

        if ($id_competicao === 1) {
            $pontosCamp = $pf(1, 'Camp');

            if ($pontosCamp !== null) {
                switch ($fase) {
                    case 'Camp':
                        return $cache[$cacheKey] = (int)$pontosCamp;
                    case 'Vice':
                        return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                    case 'SF':
                        return $cache[$cacheKey] = (int)round($pontosCamp * 0.6);
                    case 'QF':
                        return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
                }
            }
        }

        /* =========================================
           REGRA 2: Copa Intercontinental (id=2)
        ========================================= */

        if ($id_competicao === 2 && $fase === 'Camp') {
            $mundial = $pf(1, 'Camp');

            if ($mundial !== null) {
                return $cache[$cacheKey] = (int)round($mundial * 0.8);
            }
        }

        /* =========================================
           REGRA 3: Copa Rio Internacional (id=3)
        ========================================= */

        if ($id_competicao === 3) {
            $mundial = $pf(1, 'Camp');

            if ($mundial === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCampRio = (int)round($mundial * 0.5);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCampRio;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCampRio * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCampRio * 0.6);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCampRio * 0.4);
            }
        }

        /* =========================================
           REGRA 4: Recopa Mundial (id=4)
        ========================================= */

        if ($id_competicao === 4 && $fase === 'Camp') {
            $mundial = $pf(1, 'Camp');

            if ($mundial !== null) {
                return $cache[$cacheKey] = (int)round($mundial * 0.3);
            }
        }

        /* =========================================
           REGRA 5: Copa Libertadores (id=5)
        ========================================= */

        if ($id_competicao === 5) {
            $mundial = $pf(1, 'Camp');

            if ($mundial === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCampLibertadores = (int)round($mundial * 0.8);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCampLibertadores;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCampLibertadores * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCampLibertadores * 0.7);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCampLibertadores * 0.6);
                case 'OF':
                    return $cache[$cacheKey] = (int)round($pontosCampLibertadores * 0.5);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCampLibertadores * 0.4);
                case 'Pre3':
                    return $cache[$cacheKey] = (int)round($pontosCampLibertadores * 0.2);
                case 'Pre2':
                    return $cache[$cacheKey] = (int)round($pontosCampLibertadores * 0.15);
                case 'Pre1':
                    return $cache[$cacheKey] = (int)round($pontosCampLibertadores * 0.10);
            }
        }

        /* =========================================
           REGRA 6: Copa dos Campeões Sul-Americanos (id=6)
        ========================================= */

        if ($id_competicao === 6 && $fase === 'Camp') {
            $mundial = $pf(1, 'Camp');

            if ($mundial !== null) {
                return $cache[$cacheKey] = (int)round($mundial * 0.8);
            }
        }

        /* =========================================
           REGRA 7: Copa Sul-Americana (id=7)
        ========================================= */

        if ($id_competicao === 7) {
            $libertadores = $pf(5, 'Camp');

            if ($libertadores === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCampSulAmericana = (int)round($libertadores * 0.5);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCampSulAmericana;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCampSulAmericana * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCampSulAmericana * 0.7);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCampSulAmericana * 0.6);
                case 'OF':
                    return $cache[$cacheKey] = (int)round($pontosCampSulAmericana * 0.5);
                case '2F':
                    return $cache[$cacheKey] = (int)round($pontosCampSulAmericana * 0.4);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCampSulAmericana * 0.2);
                case 'Playoff':
                    return $cache[$cacheKey] = (int)round($pontosCampSulAmericana * 0.45);
                case 'Grupo':
                    return $cache[$cacheKey] = (int)round($pontosCampSulAmericana * 0.4);
            }
        }

        /* =========================================
           REGRA 8: Supercopa da Libertadores (id=8)
        ========================================= */

        if ($id_competicao === 8) {
            $libertadores = $pf(5, 'Camp');

            if ($libertadores === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCampSupercopaLibertadores = (int)round($libertadores * 0.5);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCampSupercopaLibertadores;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCampSupercopaLibertadores * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCampSupercopaLibertadores * 0.7);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCampSupercopaLibertadores * 0.6);
                case 'OF':
                    return $cache[$cacheKey] = (int)round($pontosCampSupercopaLibertadores * 0.5);
                case '2F':
                    return $cache[$cacheKey] = (int)round($pontosCampSupercopaLibertadores * 0.4);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCampSupercopaLibertadores * 0.3);
            }
        }

        /* =========================================
           REGRA 9: Copa Mercosul (id=9)
        ========================================= */

        if ($id_competicao === 9) {
            $libertadores = $pf(5, 'Camp');

            if ($libertadores === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCampMercosul = (int)round($libertadores * 0.5);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCampMercosul;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCampMercosul * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCampMercosul * 0.7);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCampMercosul * 0.6);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCampMercosul * 0.4);
            }
        }

        /* =========================================
           REGRA 10: Copa Conmebol (id=10)
        ========================================= */

        if ($id_competicao === 10) {
            $libertadores = $pf(5, 'Camp');

            if ($libertadores === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCampConmebol = (int)round($libertadores * 0.4);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCampConmebol;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCampConmebol * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCampConmebol * 0.7);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCampConmebol * 0.6);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCampConmebol * 0.4);
            }
        }

        /* =========================================
           REGRA 11: Recopa Sul-Americana (id=11)
        ========================================= */

        if ($id_competicao === 11 && $fase === 'Camp') {
            $libertadores = $pf(5, 'Camp');

            if ($libertadores !== null) {
                return $cache[$cacheKey] = (int)round($libertadores * 0.3);
            }
        }

        /* =========================================
           REGRA 12: Copa Ouro Sul-Americana (id=12)
        ========================================= */

        if ($id_competicao === 12) {
            $libertadores = $pf(5, 'Camp');

            if ($libertadores === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)round($libertadores * 0.15);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
            }
        }

        /* =========================================
           REGRA 13: Copa Master Supercopa (id=13)
        ========================================= */

        if ($id_competicao === 13) {
            $libertadores = $pf(5, 'Camp');

            if ($libertadores === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)round($libertadores * 0.15);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
            }
        }

        /* =========================================
           REGRA 14: Copa Master Conmebol (id=14)
        ========================================= */

        if ($id_competicao === 14) {
            $libertadores = $pf(5, 'Camp');

            if ($libertadores === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)round($libertadores * 0.15);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
            }
        }

        /* =========================================
           REGRA 15: Copa Levain/Suruga (id=15)
        ========================================= */

        if ($id_competicao === 15 && $fase === 'Camp') {
            $copa = $pf(1, 'Camp');

            if ($copa !== null) {
                return $cache[$cacheKey] = (int)round($copa * 0.15);
            }
        }

        /* =========================================
           REGRA 16: Torneio dos Campeões (id=16)
        ========================================= */

        if ($id_competicao === 16) {
            $brasileiro = $pf(19, 'Camp');

            if ($brasileiro === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)round($brasileiro * 0.25);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case '3º':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.6);
                case '4º':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
            }
        }

        /* =========================================
           REGRA 17: Taça Brasil (id=17)
        ========================================= */

        if ($id_competicao === 17) {
            $brasileiro = $pf(19, 'Camp');

            if ($brasileiro === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)$brasileiro;

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.7);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.6);
                case 'OF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.5);
                case 'Regional':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
                case 'Eliminator':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.3);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.2);
                case 'Pre':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.1);
            }
        }

        /* =========================================
           REGRA 18: Roberto Gomes Pedrosa (id=18)
        ========================================= */

        if ($id_competicao === 18) {
            $brasileiro = $pf(19, 'Camp');

            if ($brasileiro === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)$brasileiro;

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.7);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
            }
        }

        /* =========================================
           REGRA 19: Campeonato Brasileiro Série A (id=19)
        ========================================= */

        if ($id_competicao === 19) {
            $brasileiro = $pf(19, 'Camp');

            if ($brasileiro === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)$brasileiro;

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.7);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.6);
                case 'Principal':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.5);
                case 'Grupo':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
                case 'Eliminator':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.3);
            }

            if (preg_match('/^(\d+)°$/', $fase, $match)) {
                $pos = (int)$match[1];
                $map = mapaPosicoesSerieA();
                $factor = $map[$pos] ?? null;

                if ($factor !== null) {
                    return $cache[$cacheKey] = (int)round($pontosCamp * $factor);
                }
            }
        }

        /* =========================================
           REGRA 20: Campeonato Brasileiro Série B (id=20)
        ========================================= */

        if ($id_competicao === 20) {
            $brasileiro = $pf(19, 'Camp');

            if ($brasileiro === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)round($brasileiro * 0.50);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.7);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.6);
                case 'Principal':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.5);
                case 'Grupo':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
                case 'Eliminator':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.3);
            }

            if (preg_match('/^(\d+)°$/', $fase, $match)) {
                $pos = (int)$match[1];
                $map = mapaPosicoesSerieB();
                $factor = $map[$pos] ?? null;

                if ($factor !== null) {
                    return $cache[$cacheKey] = (int)round($pontosCamp * $factor);
                }
            }
        }

        /* =========================================
           REGRA 21: Campeonato Brasileiro Série C (id=21)
        ========================================= */

        if ($id_competicao === 21) {
            $serieB = $pf(20, 'Camp');

            if ($serieB === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)round($serieB * 0.5);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.75);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.7);
                case 'Principal':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.6);
                case 'Grupo':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.5);
                case 'Eliminator':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.3);
                case 'Reb':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.55);
            }
        }

        /* =========================================
           REGRA 22: Campeonato Brasileiro Série D (id=22)
        ========================================= */

        if ($id_competicao === 22) {
            $serieC = $pf(21, 'Camp');

            if ($serieC === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)round($serieC * 0.5);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.75);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.7);
                case 'Principal':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.6);
                case 'Grupo':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.5);
                case 'Eliminator':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.3);
            }
        }

        /* =========================================
           REGRA 23: Copa do Brasil (id=23)
        ========================================= */

        if ($id_competicao === 23) {
            $brasileiro = $pf(19, 'Camp');

            if ($brasileiro === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)round($brasileiro * 0.75);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.7);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.6);
                case 'OF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.5);
                case '16avos':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
                case '32avos':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.3);
                case '64avos':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.2);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.1);
            }
        }

        /* =========================================
           REGRA 24: Supercopa do Brasil (id=24)
        ========================================= */

        if ($id_competicao === 24 && $fase === 'Camp') {
            $copa = $pf(23, 'Camp');

            if ($copa !== null) {
                return $cache[$cacheKey] = (int)round($copa * 0.5);
            }
        }

        /* =========================================
           REGRA 25: Copa dos Campeões (id=25)
        ========================================= */

        if ($id_competicao === 25) {
            $brasileiro = $pf(19, 'Camp');

            if ($brasileiro === null) {
                return $cache[$cacheKey] = 0;
            }

            $pontosCamp = (int)round($brasileiro * 0.5);

            switch ($fase) {
                case 'Camp':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.7);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.6);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
            }
        }

        /* =========================================
           REGRA 26-33: COMPETIÇÕES REGIONAIS
        ========================================= */

        if ($id_competicao >= 26 && $id_competicao <= 33) {
            $regionais = [
                26 => [34, 35],
                27 => [41, 42, 43, 45, 47, 49, 46, 54],
                28 => [41, 42, 43, 45, 47, 49, 46],
                29 => [39, 40, 38],
                30 => [36, 39, 40, 38],
                31 => [51, 50, 52, 53],
                32 => [54, 49, 55, 48],
                33 => [54, 55, 51, 50, 52, 53],
            ];

            $adicionais = [
                26 => 1,
                27 => 5,
                28 => 5,
                29 => 2,
                30 => 3,
                31 => 3,
                32 => 3,
                33 => 5,
            ];

            $cacheKeyBase = "regional_pontosCamp_{$id_competicao}";

            if (!isset($cache[$cacheKeyBase])) {
                $idsEstaduais = $regionais[$id_competicao] ?? [];
                $pontuacoesEstaduais = [];

                foreach ($idsEstaduais as $id_estadual) {
                    $p = $pf($id_estadual, 'Camp');

                    if ($p === null) {
                        $p = $pf($id_estadual, '1º');
                    }

                    if ($p !== null) {
                        $pontuacoesEstaduais[] = (int)$p;
                    }
                }

                if (empty($pontuacoesEstaduais)) {
                    $cache[$cacheKeyBase] = 0;
                } else {
                    rsort($pontuacoesEstaduais);

                    $maior = $pontuacoesEstaduais[0];
                    $extras = array_slice($pontuacoesEstaduais, 1, $adicionais[$id_competicao]);
                    $bonus = array_sum(array_map(fn($p) => $p * 0.2, $extras));

                    $cache[$cacheKeyBase] = (int)round($maior + $bonus);
                }
            }

            $pontosCamp = $cache[$cacheKeyBase];

            switch ($fase) {
                case 'Camp':
                case '1º':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                case '2º':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.8);
                case 'SF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.6);
                case 'QF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.5);
                case 'OF':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
                case 'Principal':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.4);
                case 'Eliminator':
                case 'Grupo':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.3);
                case '1F':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.2);
                case 'Pre':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.1);
                default:
                    return $cache[$cacheKey] = 0;
            }
        }

        /* =========================================
           REGRA 34: CAMPEONATOS ESTADUAIS
        ========================================= */

        if ($id_competicao >= 34 && $id_competicao <= 60) {
            $nomeCompeticao = obterNomeCompeticaoPontuacao($pdo, $id_competicao);

            if (!$nomeCompeticao) {
                return $cache[$cacheKey] = 0;
            }

            $estadoMap = [
                'Paulista' => 'SP',
                'Carioca' => 'RJ',
                'Mineiro' => 'MG',
                'Gaúcho' => 'RS',
                'Paranaense' => 'PR',
                'Baiano' => 'BA',
                'Pernambucano' => 'PE',
                'Cearense' => 'CE',
                'Brasiliense' => 'DF',
                'Goiano' => 'GO',
                'Matogrossense' => 'MT',
                'Mato-Grossense' => 'MT',
                'Sul-Mato-Grossense' => 'MS',
                'Alagoano' => 'AL',
                'Paraense' => 'PA',
                'Maranhense' => 'MA',
                'Piauiense' => 'PI',
                'Potiguar' => 'RN',
                'Sergipano' => 'SE',
                'Tocantinense' => 'TO',
                'Acreano' => 'AC',
                'Amazonense' => 'AM',
                'Roraimense' => 'RR',
                'Rondoniense' => 'RO',
                'Amapaense' => 'AP',
                'Capixaba' => 'ES',
                'Catarinense' => 'SC',
                'Paraibano' => 'PB',
            ];

            $estado = null;

            foreach ($estadoMap as $chave => $sigla) {
                if (strpos($nomeCompeticao, $chave) !== false) {
                    $estado = $sigla;
                    break;
                }
            }

            if (!$estado) {
                return $cache[$cacheKey] = 0;
            }

            $cacheKeyBase = "estadual_indice_b_{$estado}";

            if (!isset($cache[$cacheKeyBase])) {
                $indice_campeao = 150;
                $indice_vice = (int)round($indice_campeao * 0.75);
                $indice_sf = (int)round($indice_vice * 0.75);

                $indice_campeaoB = (int)round($indice_campeao * 0.5);
                $indice_viceB = (int)round($indice_campeaoB * 0.75);
                $indice_sfB = (int)round($indice_viceB * 0.75);

                $indice_campeaoC = (int)round($indice_campeaoB * 0.5);
                $indice_viceC = (int)round($indice_campeaoC * 0.75);
                $indice_sfC = (int)round($indice_viceC * 0.75);

                $indice_campeaoD = (int)round($indice_campeaoC * 0.5);
                $indice_viceD = (int)round($indice_campeaoD * 0.75);
                $indice_sfD = (int)round($indice_viceD * 0.75);

                $indice_campeaobr = (int)round($indice_campeao * 0.75);
                $indice_vicebr = (int)round($indice_campeaobr * 0.75);
                $indice_sfbr = (int)round($indice_vicebr * 0.75);

                $brasileiro_id = 19;
                $taca_brasil_id = 17;
                $rgp_id = 18;
                $serieb_id = 20;
                $seriec_id = 21;
                $seried_id = 22;
                $copa_br_id = 23;

                $ids_a = [$brasileiro_id, $taca_brasil_id, $rgp_id];

                $competicoesParaTemporadas = array_merge(
                    $ids_a,
                    [$serieb_id, $seriec_id, $seried_id, $copa_br_id, $id_competicao]
                );

                $temporadas_counts = coletarTemporadas($competicoesParaTemporadas, $pdo);

                $competicoes_para_participacao = array_merge(
                    $ids_a,
                    [$serieb_id, $seriec_id, $seried_id, $copa_br_id, $id_competicao]
                );

                $participacoes = coletarParticipacoesPorCompeticao(
                    $competicoes_para_participacao,
                    [$estado],
                    $pdo
                );

                $sumFases = function (array $ids, array $fases) use ($participacoes) {
                    $total = 0;

                    foreach ($ids as $id) {
                        foreach ($fases as $f) {
                            $total += $participacoes[$id][$f] ?? 0;
                        }
                    }

                    return $total;
                };

                /* Série A / Taça Brasil / Robertão */
                $total_temporadas_a = 0;

                foreach ($ids_a as $idtemp) {
                    $total_temporadas_a += $temporadas_counts[$idtemp] ?? 0;
                }

                if ($total_temporadas_a === 0) {
                    $total_temporadas_a = 1;
                }

                $titulos_estado_a = $sumFases($ids_a, ['Camp']);
                $vice_estado_a = $sumFases($ids_a, ['Vice']);

                $sf_estado_a = 0;

                foreach ($ids_a as $cid) {
                    $sf_estado_a +=
                        ($participacoes[$cid]['SF'] ?? 0) +
                        ($participacoes[$cid]['3º'] ?? 0) +
                        ($participacoes[$cid]['4º'] ?? 0);
                }

                /* Série B */
                $total_temporadas_b = $temporadas_counts[$serieb_id] ?? 0;
                if ($total_temporadas_b === 0) $total_temporadas_b = 1;

                $titulos_estado_b = $participacoes[$serieb_id]['Camp'] ?? 0;
                $vice_estado_b = $participacoes[$serieb_id]['Vice'] ?? 0;
                $sf_estado_b =
                    ($participacoes[$serieb_id]['SF'] ?? 0) +
                    ($participacoes[$serieb_id]['3º'] ?? 0) +
                    ($participacoes[$serieb_id]['4º'] ?? 0);

                /* Série C */
                $total_temporadas_c = $temporadas_counts[$seriec_id] ?? 0;
                if ($total_temporadas_c === 0) $total_temporadas_c = 1;

                $titulos_estado_c = $participacoes[$seriec_id]['Camp'] ?? 0;
                $vice_estado_c = $participacoes[$seriec_id]['Vice'] ?? 0;
                $sf_estado_c = $participacoes[$seriec_id]['SF'] ?? 0;

                /* Série D */
                $total_temporadas_d = $temporadas_counts[$seried_id] ?? 0;
                if ($total_temporadas_d === 0) $total_temporadas_d = 1;

                $titulos_estado_d = $participacoes[$seried_id]['Camp'] ?? 0;
                $vice_estado_d = $participacoes[$seried_id]['Vice'] ?? 0;
                $sf_estado_d = $participacoes[$seried_id]['SF'] ?? 0;

                /* Copa do Brasil */
                $total_temporadas_br = $temporadas_counts[$copa_br_id] ?? 0;
                if ($total_temporadas_br === 0) $total_temporadas_br = 1;

                $titulos_estado_br = $participacoes[$copa_br_id]['Camp'] ?? 0;
                $vice_estado_br = $participacoes[$copa_br_id]['Vice'] ?? 0;
                $sf_estado_br = $participacoes[$copa_br_id]['SF'] ?? 0;

                /* Temporadas do Estadual */
                $total_temporadas_estado = $temporadas_counts[$id_competicao] ?? 0;
                if ($total_temporadas_estado === 0) $total_temporadas_estado = 1;

                $indice_A = 0;

                $indice_A += (int)round(($titulos_estado_a / $total_temporadas_a) * $indice_campeao);
                $indice_A += (int)round(($vice_estado_a / $total_temporadas_a) * $indice_vice);
                $indice_A += (int)round(($sf_estado_a / ($total_temporadas_a * 2)) * $indice_sf);

                $indice_A += (int)round(($titulos_estado_b / $total_temporadas_b) * $indice_campeaoB);
                $indice_A += (int)round(($vice_estado_b / $total_temporadas_b) * $indice_viceB);
                $indice_A += (int)round(($sf_estado_b / ($total_temporadas_b * 2)) * $indice_sfB);

                $indice_A += (int)round(($titulos_estado_c / $total_temporadas_c) * $indice_campeaoC);
                $indice_A += (int)round(($vice_estado_c / $total_temporadas_c) * $indice_viceC);
                $indice_A += (int)round(($sf_estado_c / ($total_temporadas_c * 2)) * $indice_sfC);

                $indice_A += (int)round(($titulos_estado_d / $total_temporadas_d) * $indice_campeaoD);
                $indice_A += (int)round(($vice_estado_d / $total_temporadas_d) * $indice_viceD);
                $indice_A += (int)round(($sf_estado_d / ($total_temporadas_d * 2)) * $indice_sfD);

                $indice_A += (int)round(($titulos_estado_br / $total_temporadas_br) * $indice_campeaobr);
                $indice_A += (int)round(($vice_estado_br / $total_temporadas_br) * $indice_vicebr);
                $indice_A += (int)round(($sf_estado_br / ($total_temporadas_br * 2)) * $indice_sfbr);

                $indice_B = $indice_A / $total_temporadas_estado;

                $pontosBrasileiroCamp = $pf(19, 'Camp');
                $dezPorCento = $pontosBrasileiroCamp !== null
                    ? (int)round($pontosBrasileiroCamp * 0.10)
                    : 0;

                $pontosCamp = $dezPorCento + (int)round(100 * $indice_B);

                $cache[$cacheKeyBase] = $pontosCamp;
            }

            $pontosCamp = $cache[$cacheKeyBase];

            switch ($fase) {
                case 'Camp':
                case '1º':
                    return $cache[$cacheKey] = $pontosCamp;
                case 'Vice':
                case '2º':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.50);
                case '3º':
                    return $cache[$cacheKey] = (int)round($pontosCamp * 0.25);
                default:
                    return $cache[$cacheKey] = 0;
            }
        }

        /* =========================================
           FALLBACK: PONTUAÇÃO DIRETA
        ========================================= */

        $rowPontos = $pf($id_competicao, $fase);

        return $cache[$cacheKey] = $rowPontos !== null ? (int)$rowPontos : 0;
    }
}