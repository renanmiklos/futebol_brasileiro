<?php
/* =========================================
   RANKING-CALCULO.PHP
   Cálculos centrais da área Ranking
   Futebol Brasileiro
========================================= */

/*
  Este arquivo concentra as funções responsáveis por gerar:
  - Ranking geral dos clubes
  - Ranking por região
  - Ranking por competições específicas
  - Ranking das federações estaduais

  Ele depende de:
  - conexaodb.php
  - calcula-pontuacoes.php
  - ranking-config.php
  - ranking-funcoes.php
*/

/* =========================================
   GARANTIA DA FUNÇÃO getPontuacaoFinal()
========================================= */

if (!function_exists('getPontuacaoFinal')) {
    /*
      Esta função deve vir de:
      estrutura/calcula-pontuacoes.php

      Mantemos este bloqueio apenas para evitar erro silencioso caso
      o arquivo não tenha sido incluído corretamente.
    */
    throw new Exception('A função getPontuacaoFinal() não foi encontrada. Verifique se calcula-pontuacoes.php foi incluído.');
}

/* =========================================
   GERAR RANKING GERAL
========================================= */

if (!function_exists('gerarRankingGeral')) {
    function gerarRankingGeral(PDO $pdo): array
    {
        $sql = "
            SELECT 
                t.id,
                t.nome,
                t.estado,
                t.escudo,
                tp.id_competicao,
                c.tipo AS tipo_competicao,
                cl.fase
            FROM classificacao cl
            INNER JOIN temporadas tp ON cl.id_temporada = tp.id
            INNER JOIN competicoes c ON tp.id_competicao = c.id
            INNER JOIN times t ON cl.id_time = t.id
            WHERE cl.nacional = 1
              AND t.extinto = 0
            ORDER BY 
                t.id ASC,
                tp.id_competicao ASC,
                cl.fase ASC
        ";

        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return montarRankingClubesPorTipo($pdo, $resultados);
    }
}

/* =========================================
   GERAR RANKING POR REGIÃO
========================================= */

if (!function_exists('gerarRankingPorRegiao')) {
    function gerarRankingPorRegiao(PDO $pdo, array $estados): array
    {
        $estados = array_values(array_filter(array_map(function ($uf) {
            return strtoupper(trim((string)$uf));
        }, $estados)));

        if (empty($estados)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($estados), '?'));

        $sql = "
            SELECT 
                t.id,
                t.nome,
                t.estado,
                t.escudo,
                tp.id_competicao,
                c.tipo AS tipo_competicao,
                cl.fase
            FROM classificacao cl
            INNER JOIN temporadas tp ON cl.id_temporada = tp.id
            INNER JOIN competicoes c ON tp.id_competicao = c.id
            INNER JOIN times t ON cl.id_time = t.id
            WHERE cl.nacional = 1
              AND t.extinto = 0
              AND t.estado IN ($placeholders)
            ORDER BY 
                t.id ASC,
                tp.id_competicao ASC,
                cl.fase ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($estados);

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return montarRankingClubesPorTipo($pdo, $resultados);
    }
}

/* =========================================
   MONTAR RANKING DE CLUBES POR TIPO
   Usado no ranking geral e rankings regionais
========================================= */

if (!function_exists('montarRankingClubesPorTipo')) {
    function montarRankingClubesPorTipo(PDO $pdo, array $resultados): array
    {
        $ranking = [];

        foreach ($resultados as $linha) {
            $idTime = (int)($linha['id'] ?? 0);

            if ($idTime <= 0) {
                continue;
            }

            if (!isset($ranking[$idTime])) {
                $ranking[$idTime] = criarBaseClubeRanking($linha);
            }

            $idCompeticao = (int)($linha['id_competicao'] ?? 0);
            $fase = (string)($linha['fase'] ?? '');

            $pontosRaw = getPontuacaoFinal($pdo, $idCompeticao, $fase);
            $pontos = is_numeric($pontosRaw) ? (int)round((float)$pontosRaw) : 0;

            if ($pontos <= 0) {
                continue;
            }

            $tipo = normalizarTipoCompeticaoRanking($linha['tipo_competicao'] ?? 'Nacional');

            somarPontosPorTipoRanking($ranking[$idTime], $tipo, $pontos);
        }

        /*
          Garante que o total seja a soma explícita das categorias.
          Isso evita divergência caso algum ajuste futuro altere a soma parcial.
        */
        foreach ($ranking as $idTime => $clube) {
            $ranking[$idTime]['total'] =
                (int)($clube['internacionais'] ?? 0) +
                (int)($clube['nacionais'] ?? 0) +
                (int)($clube['regionais'] ?? 0) +
                (int)($clube['estaduais'] ?? 0);
        }

        return ordenarRankingClubes($ranking);
    }
}

/* =========================================
   GERAR RANKING POR COMPETIÇÕES
   Usado em ranking-nac.php e ranking-int.php
========================================= */

if (!function_exists('gerarRankingPorCompeticoes')) {
    function gerarRankingPorCompeticoes(PDO $pdo, array $competicoes, array $colunas): array
    {
        $idsCompeticoes = extrairIdsCompeticoesRanking($competicoes);

        if (empty($idsCompeticoes)) {
            return [];
        }

        $mapaIdParaColuna = mapearCompeticoesParaColunasRanking($competicoes);

        $colunasExtras = [];

        foreach ($colunas as $coluna) {
            $key = $coluna['key'] ?? '';

            if (!empty($key) && $key !== 'total') {
                $colunasExtras[] = $key;
            }
        }

        $placeholders = implode(',', array_fill(0, count($idsCompeticoes), '?'));

        $sql = "
            SELECT 
                t.id,
                t.nome,
                t.estado,
                t.escudo,
                tp.id_competicao,
                cl.fase
            FROM classificacao cl
            INNER JOIN temporadas tp ON cl.id_temporada = tp.id
            INNER JOIN competicoes c ON tp.id_competicao = c.id
            INNER JOIN times t ON cl.id_time = t.id
            WHERE cl.nacional = 1
              AND t.extinto = 0
              AND tp.id_competicao IN ($placeholders)
            ORDER BY 
                t.id ASC,
                tp.id_competicao ASC,
                cl.fase ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($idsCompeticoes);

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ranking = [];

        foreach ($resultados as $linha) {
            $idTime = (int)($linha['id'] ?? 0);

            if ($idTime <= 0) {
                continue;
            }

            if (!isset($ranking[$idTime])) {
                $ranking[$idTime] = criarBaseClubeRanking($linha, $colunasExtras);
            }

            $idCompeticao = (int)($linha['id_competicao'] ?? 0);
            $colunaDestino = $mapaIdParaColuna[$idCompeticao] ?? null;

            if (empty($colunaDestino)) {
                continue;
            }

            $fase = (string)($linha['fase'] ?? '');

            $pontosRaw = getPontuacaoFinal($pdo, $idCompeticao, $fase);
            $pontos = is_numeric($pontosRaw) ? (int)round((float)$pontosRaw) : 0;

            if ($pontos <= 0) {
                continue;
            }

            if (!isset($ranking[$idTime][$colunaDestino])) {
                $ranking[$idTime][$colunaDestino] = 0;
            }

            $ranking[$idTime][$colunaDestino] += $pontos;
            $ranking[$idTime]['total'] += $pontos;
        }

        /*
          Recalcula o total com base nas colunas exibidas.
          Isso garante que o ranking nacional/internacional seja exatamente
          a soma das colunas visíveis.
        */
        foreach ($ranking as $idTime => $clube) {
            $total = 0;

            foreach ($colunasExtras as $coluna) {
                $total += (int)($clube[$coluna] ?? 0);
            }

            $ranking[$idTime]['total'] = $total;
        }

        return ordenarRankingPorCompeticoes($ranking);
    }
}

/* =========================================
   GERAR RANKING DAS FEDERAÇÕES
========================================= */

if (!function_exists('gerarRankingFederacoes')) {
    function gerarRankingFederacoes(PDO $pdo, array $estadosPermitidos = []): array
    {
        $estadosPermitidos = array_values(array_filter(array_map(function ($uf) {
            return strtoupper(trim((string)$uf));
        }, $estadosPermitidos)));

        $params = [];
        $filtroEstadosSql = '';

        if (!empty($estadosPermitidos)) {
            $placeholders = implode(',', array_fill(0, count($estadosPermitidos), '?'));
            $filtroEstadosSql = "AND t.estado IN ($placeholders)";
            $params = $estadosPermitidos;
        }

        $sql = "
            SELECT 
                t.estado,
                tp.id_competicao,
                c.tipo AS tipo_competicao,
                cl.fase
            FROM classificacao cl
            INNER JOIN temporadas tp ON cl.id_temporada = tp.id
            INNER JOIN competicoes c ON tp.id_competicao = c.id
            INNER JOIN times t ON cl.id_time = t.id
            WHERE cl.nacional = 1
              AND t.extinto = 0
              $filtroEstadosSql
            ORDER BY 
                t.estado ASC,
                tp.id_competicao ASC,
                cl.fase ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ranking = [];

        foreach ($resultados as $linha) {
            $estado = strtoupper(trim((string)($linha['estado'] ?? '')));

            if ($estado === '') {
                continue;
            }

            if (!empty($estadosPermitidos) && !in_array($estado, $estadosPermitidos, true)) {
                continue;
            }

            if (!isset($ranking[$estado])) {
                $ranking[$estado] = criarBaseFederacaoRanking($estado);
            }

            $idCompeticao = (int)($linha['id_competicao'] ?? 0);
            $fase = (string)($linha['fase'] ?? '');

            $pontosRaw = getPontuacaoFinal($pdo, $idCompeticao, $fase);
            $pontos = is_numeric($pontosRaw) ? (int)round((float)$pontosRaw) : 0;

            if ($pontos <= 0) {
                continue;
            }

            $tipo = normalizarTipoCompeticaoRanking($linha['tipo_competicao'] ?? 'Nacional');

            somarPontosPorTipoRanking($ranking[$estado], $tipo, $pontos);
        }

        foreach ($ranking as $estado => $dados) {
            $ranking[$estado]['total'] =
                (int)($dados['internacionais'] ?? 0) +
                (int)($dados['nacionais'] ?? 0) +
                (int)($dados['regionais'] ?? 0) +
                (int)($dados['estaduais'] ?? 0);
        }

        return ordenarRankingFederacoes($ranking);
    }
}

/* =========================================
   GERAR HASH DO RANKING
   Útil para cache/controle futuro de atualização
========================================= */

if (!function_exists('gerarHashRanking')) {
    function gerarHashRanking(array $ranking, array $chaves = []): string
    {
        if (empty($chaves)) {
            $chaves = [
                'id',
                'estado',
                'total',
                'internacionais',
                'nacionais',
                'regionais',
                'estaduais'
            ];
        }

        $dados = array_map(function ($item) use ($chaves) {
            $linha = [];

            foreach ($chaves as $chave) {
                $linha[$chave] = $item[$chave] ?? null;
            }

            return $linha;
        }, $ranking);

        return hash('sha256', json_encode($dados));
    }
}