<?php
/* =========================================
   RANKING-FUNCOES.PHP
   Funções auxiliares da área Ranking
   Futebol Brasileiro
========================================= */

/*
  Este arquivo concentra funções reutilizáveis.
  Ele não deve executar consultas principais de ranking,
  exceto consultas auxiliares como divisões atuais e última atualização.
*/

/* =========================================
   ESCAPE HTML
========================================= */

if (!function_exists('eRanking')) {
    function eRanking($valor)
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

/* =========================================
   CAMINHO DO ESCUDO
========================================= */

if (!function_exists('caminhoEscudoRanking')) {
    function caminhoEscudoRanking($escudo, $fallback = '../assets/images/escudo_padrao.png')
    {
        if (empty($escudo)) {
            return $fallback;
        }

        $escudo = trim((string)$escudo);

        if (
            str_starts_with($escudo, 'http://') ||
            str_starts_with($escudo, 'https://') ||
            str_starts_with($escudo, 'data:')
        ) {
            return eRanking($escudo);
        }

        /*
          Como os arquivos de ranking ficam dentro da pasta estatisticas,
          caminhos como assets/... precisam subir um nível.
        */
        return '../' . eRanking(ltrim($escudo, '/'));
    }
}

/* =========================================
   FORMATAÇÃO DE NÚMEROS
========================================= */

if (!function_exists('formatarNumeroRanking')) {
    function formatarNumeroRanking($valor)
    {
        if (!is_numeric($valor)) {
            return '0';
        }

        return number_format((float)$valor, 0, '', '.');
    }
}

/* =========================================
   FORMATAÇÃO DE DATA
========================================= */

if (!function_exists('formatarDataRanking')) {
    function formatarDataRanking($data)
    {
        if (empty($data)) {
            return '';
        }

        $timestamp = strtotime((string)$data);

        if (!$timestamp) {
            return '';
        }

        return date('d/m/Y', $timestamp);
    }
}

/* =========================================
   COLUNA EXISTE NO BANCO
========================================= */

if (!function_exists('colunaExisteRanking')) {
    function colunaExisteRanking(PDO $pdo, string $tabela, string $coluna): bool
    {
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?
            ");

            $stmt->execute([$tabela, $coluna]);

            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}

/* =========================================
   ÚLTIMA ATUALIZAÇÃO DO RANKING
========================================= */

if (!function_exists('obterUltimaAtualizacaoRanking')) {
    function obterUltimaAtualizacaoRanking(PDO $pdo): string
    {
        $colunasPossiveis = [
            ['tabela' => 'classificacao', 'alias' => 'cl', 'coluna' => 'updated_at'],
            ['tabela' => 'classificacao', 'alias' => 'cl', 'coluna' => 'data'],
            ['tabela' => 'classificacao', 'alias' => 'cl', 'coluna' => 'created_at'],
            ['tabela' => 'temporadas', 'alias' => 'tp', 'coluna' => 'updated_at'],
            ['tabela' => 'temporadas', 'alias' => 'tp', 'coluna' => 'data'],
            ['tabela' => 'temporadas', 'alias' => 'tp', 'coluna' => 'created_at'],
        ];

        $expressoes = [];

        foreach ($colunasPossiveis as $item) {
            if (colunaExisteRanking($pdo, $item['tabela'], $item['coluna'])) {
                $expressoes[] = "MAX({$item['alias']}.{$item['coluna']})";
            }
        }

        try {
            if (!empty($expressoes)) {
                $sql = "
                    SELECT GREATEST(" . implode(', ', $expressoes) . ") AS ultima
                    FROM classificacao cl
                    INNER JOIN temporadas tp ON cl.id_temporada = tp.id
                    INNER JOIN times t ON cl.id_time = t.id
                    WHERE cl.nacional = 1
                      AND t.extinto = 0
                ";

                $stmt = $pdo->query($sql);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!empty($row['ultima'])) {
                    $dataFormatada = formatarDataRanking($row['ultima']);

                    if (!empty($dataFormatada)) {
                        return $dataFormatada;
                    }
                }
            }
        } catch (Exception $e) {
            // Segue para fallback.
        }

        /*
          Fallback: se não houver coluna de data confiável,
          mostra a data atual do servidor no fuso de Lisboa.
        */
        return (new DateTime('now', new DateTimeZone('Europe/Lisbon')))->format('d/m/Y');
    }
}

/* =========================================
   CARREGAR DIVISÕES ATUAIS
========================================= */

if (!function_exists('carregarDivisoesAtuaisRanking')) {
    function carregarDivisoesAtuaisRanking(PDO $pdo): array
    {
        $mapeamento = [];

        try {
            $stmt = $pdo->query("
                SELECT
                    id_time,
                    divisao
                FROM divisao_atual
            ");

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $idTime = (int)($row['id_time'] ?? 0);
                $divisao = strtoupper(trim((string)($row['divisao'] ?? '')));

                if ($idTime > 0 && $divisao !== '') {
                    $mapeamento[$idTime] = $divisao;
                }
            }
        } catch (Exception $e) {
            // Se a tabela não existir ou falhar, retorna array vazio.
        }

        return $mapeamento;
    }
}

/* =========================================
   BUSCAR TIPO DA COMPETIÇÃO
   Mantida como fallback para usos específicos.
   O ideal é trazer c.tipo diretamente nas queries principais.
========================================= */

if (!function_exists('getTipoCompeticaoRanking')) {
    function getTipoCompeticaoRanking(PDO $pdo, int $idCompeticao): string
    {
        static $cache = [];

        if ($idCompeticao <= 0) {
            return 'Nacional';
        }

        if (!isset($cache[$idCompeticao])) {
            try {
                $stmt = $pdo->prepare("
                    SELECT tipo
                    FROM competicoes
                    WHERE id = ?
                    LIMIT 1
                ");

                $stmt->execute([$idCompeticao]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $cache[$idCompeticao] = !empty($row['tipo'])
                    ? (string)$row['tipo']
                    : 'Nacional';
            } catch (Exception $e) {
                $cache[$idCompeticao] = 'Nacional';
            }
        }

        return $cache[$idCompeticao];
    }
}

/* =========================================
   NORMALIZAR TIPO DE COMPETIÇÃO
========================================= */

if (!function_exists('normalizarTipoCompeticaoRanking')) {
    function normalizarTipoCompeticaoRanking($tipo): string
    {
        $tipo = trim((string)$tipo);

        $permitidos = [
            'Internacional',
            'Nacional',
            'Regional',
            'Estadual'
        ];

        if (in_array($tipo, $permitidos, true)) {
            return $tipo;
        }

        return 'Nacional';
    }
}

/* =========================================
   ORDENAR RANKING DE CLUBES
========================================= */

if (!function_exists('ordenarRankingClubes')) {
    function ordenarRankingClubes(array $ranking): array
    {
        $ranking = array_values($ranking);

        usort($ranking, function ($a, $b) {
            $cmp = ((int)($b['total'] ?? 0)) <=> ((int)($a['total'] ?? 0));
            if ($cmp !== 0) return $cmp;

            $cmp = ((int)($b['internacionais'] ?? 0)) <=> ((int)($a['internacionais'] ?? 0));
            if ($cmp !== 0) return $cmp;

            $cmp = ((int)($b['nacionais'] ?? 0)) <=> ((int)($a['nacionais'] ?? 0));
            if ($cmp !== 0) return $cmp;

            $cmp = ((int)($b['regionais'] ?? 0)) <=> ((int)($a['regionais'] ?? 0));
            if ($cmp !== 0) return $cmp;

            $cmp = ((int)($b['estaduais'] ?? 0)) <=> ((int)($a['estaduais'] ?? 0));
            if ($cmp !== 0) return $cmp;

            $nomeA = mb_strtolower(trim((string)($a['nome'] ?? '')));
            $nomeB = mb_strtolower(trim((string)($b['nome'] ?? '')));

            $cmp = strcmp($nomeA, $nomeB);
            if ($cmp !== 0) return $cmp;

            return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
        });

        return $ranking;
    }
}

/* =========================================
   ORDENAR RANKING DE COMPETIÇÕES
   Usado em ranking nacional/internacional.
========================================= */

if (!function_exists('ordenarRankingPorCompeticoes')) {
    function ordenarRankingPorCompeticoes(array $ranking): array
    {
        $ranking = array_values($ranking);

        usort($ranking, function ($a, $b) {
            $cmp = ((int)($b['total'] ?? 0)) <=> ((int)($a['total'] ?? 0));
            if ($cmp !== 0) return $cmp;

            $nomeA = mb_strtolower(trim((string)($a['nome'] ?? '')));
            $nomeB = mb_strtolower(trim((string)($b['nome'] ?? '')));

            $cmp = strcmp($nomeA, $nomeB);
            if ($cmp !== 0) return $cmp;

            return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
        });

        return $ranking;
    }
}

/* =========================================
   ORDENAR RANKING DE FEDERAÇÕES
========================================= */

if (!function_exists('ordenarRankingFederacoes')) {
    function ordenarRankingFederacoes(array $ranking): array
    {
        $ranking = array_values($ranking);

        usort($ranking, function ($a, $b) {
            $cmp = ((int)($b['total'] ?? 0)) <=> ((int)($a['total'] ?? 0));
            if ($cmp !== 0) return $cmp;

            $cmp = ((int)($b['internacionais'] ?? 0)) <=> ((int)($a['internacionais'] ?? 0));
            if ($cmp !== 0) return $cmp;

            $cmp = ((int)($b['nacionais'] ?? 0)) <=> ((int)($a['nacionais'] ?? 0));
            if ($cmp !== 0) return $cmp;

            $cmp = ((int)($b['regionais'] ?? 0)) <=> ((int)($a['regionais'] ?? 0));
            if ($cmp !== 0) return $cmp;

            $cmp = ((int)($b['estaduais'] ?? 0)) <=> ((int)($a['estaduais'] ?? 0));
            if ($cmp !== 0) return $cmp;

            return strcmp((string)($a['estado'] ?? ''), (string)($b['estado'] ?? ''));
        });

        return $ranking;
    }
}

/* =========================================
   LISTA DE IDS DAS COMPETIÇÕES CONFIGURADAS
========================================= */

if (!function_exists('extrairIdsCompeticoesRanking')) {
    function extrairIdsCompeticoesRanking(array $competicoes): array
    {
        $ids = [];

        foreach ($competicoes as $competicao) {
            if (isset($competicao['id'])) {
                $ids[] = (int)$competicao['id'];
            }

            if (isset($competicao['ids']) && is_array($competicao['ids'])) {
                foreach ($competicao['ids'] as $id) {
                    $ids[] = (int)$id;
                }
            }
        }

        $ids = array_values(array_unique(array_filter($ids, function ($id) {
            return $id > 0;
        })));

        return $ids;
    }
}

/* =========================================
   MAPA ID COMPETIÇÃO → COLUNA
========================================= */

if (!function_exists('mapearCompeticoesParaColunasRanking')) {
    function mapearCompeticoesParaColunasRanking(array $competicoes): array
    {
        $mapa = [];

        foreach ($competicoes as $competicao) {
            $coluna = $competicao['coluna'] ?? null;

            if (empty($coluna)) {
                continue;
            }

            if (isset($competicao['id'])) {
                $mapa[(int)$competicao['id']] = $coluna;
            }

            if (isset($competicao['ids']) && is_array($competicao['ids'])) {
                foreach ($competicao['ids'] as $id) {
                    $mapa[(int)$id] = $coluna;
                }
            }
        }

        return $mapa;
    }
}

/* =========================================
   CRIAR BASE DE CLUBE PARA RANKING
========================================= */

if (!function_exists('criarBaseClubeRanking')) {
    function criarBaseClubeRanking(array $linha, array $colunasExtras = []): array
    {
        $base = [
            'id' => (int)($linha['id'] ?? 0),
            'nome' => $linha['nome'] ?? '',
            'estado' => $linha['estado'] ?? '',
            'escudo' => $linha['escudo'] ?? '',
            'internacionais' => 0,
            'nacionais' => 0,
            'regionais' => 0,
            'estaduais' => 0,
            'total' => 0
        ];

        foreach ($colunasExtras as $coluna) {
            if (!isset($base[$coluna])) {
                $base[$coluna] = 0;
            }
        }

        return $base;
    }
}

/* =========================================
   CRIAR BASE DE FEDERAÇÃO
========================================= */

if (!function_exists('criarBaseFederacaoRanking')) {
    function criarBaseFederacaoRanking(string $estado): array
    {
        return [
            'estado' => $estado,
            'internacionais' => 0,
            'nacionais' => 0,
            'regionais' => 0,
            'estaduais' => 0,
            'total' => 0
        ];
    }
}

/* =========================================
   SOMAR PONTOS POR TIPO
========================================= */

if (!function_exists('somarPontosPorTipoRanking')) {
    function somarPontosPorTipoRanking(array &$item, string $tipo, int $pontos): void
    {
        switch ($tipo) {
            case 'Internacional':
                $item['internacionais'] = (int)($item['internacionais'] ?? 0) + $pontos;
                break;

            case 'Regional':
                $item['regionais'] = (int)($item['regionais'] ?? 0) + $pontos;
                break;

            case 'Estadual':
                $item['estaduais'] = (int)($item['estaduais'] ?? 0) + $pontos;
                break;

            case 'Nacional':
            default:
                $item['nacionais'] = (int)($item['nacionais'] ?? 0) + $pontos;
                break;
        }

        $item['total'] = (int)($item['total'] ?? 0) + $pontos;
    }
}