<?php
/* =========================================
   GERA-RANKING.PHP
   Compatibilidade com chamadas antigas do ranking
   Futebol Brasileiro
========================================= */

/*
  Este arquivo foi mantido para compatibilidade com páginas antigas
  que ainda chamam gerarRankingCompleto($pdo).

  A lógica principal do ranking agora está centralizada em:
  estatisticas/includes-ranking/ranking-calculo.php
*/

require_once __DIR__ . '/conexaodb.php';
require_once __DIR__ . '/calcula-pontuacoes.php';

require_once __DIR__ . '/../estatisticas/includes-ranking/ranking-config.php';
require_once __DIR__ . '/../estatisticas/includes-ranking/ranking-funcoes.php';
require_once __DIR__ . '/../estatisticas/includes-ranking/ranking-calculo.php';

if (!function_exists('gerarRankingCompleto')) {
    function gerarRankingCompleto(PDO $pdo): array
    {
        return gerarRankingGeral($pdo);
    }
}