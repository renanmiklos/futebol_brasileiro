<?php
require_once '../estrutura/conexaodb.php';

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function imagemSegura(?string $caminho): ?string {
    if (!$caminho) {
        return null;
    }

    $caminho = trim($caminho);

    if ($caminho === '') {
        return null;
    }

    if (filter_var($caminho, FILTER_VALIDATE_URL)) {
        return $caminho;
    }

    return '../' . ltrim($caminho, '/');
}

function plural($numero, $singular, $plural) {
    return ((int) $numero === 1) ? $singular : $plural;
}

/* =========================================
   CATEGORIAS
========================================= */

$categoriasInfo = [
    'Internacional' => [
        'titulo' => 'Internacionais',
        'descricao' => 'Competições disputadas por clubes brasileiros em cenário continental ou mundial.',
        'icone' => '🌎'
    ],
    'Nacional' => [
        'titulo' => 'Nacionais',
        'descricao' => 'Torneios de abrangência nacional, reunindo clubes de diferentes estados do país.',
        'icone' => '🏆'
    ],
    'Regional' => [
        'titulo' => 'Regionais',
        'descricao' => 'Competições tradicionais entre clubes de uma mesma região brasileira.',
        'icone' => '🗺️'
    ],
    'Estadual' => [
        'titulo' => 'Estaduais',
        'descricao' => 'Campeonatos organizados por federações estaduais, base histórica do futebol brasileiro.',
        'icone' => '🏟️'
    ],
    'Amistosos' => [
        'titulo' => 'Amistosos',
        'descricao' => 'Torneios e jogos especiais sem caráter oficial de competição principal.',
        'icone' => '⭐'
    ],
];

$categorias = array_keys($categoriasInfo);

$categoriaSelecionada = isset($_GET['tipo']) ? trim($_GET['tipo']) : null;

if ($categoriaSelecionada && !in_array($categoriaSelecionada, $categorias, true)) {
    $categoriaSelecionada = null;
}

/* =========================================
   VARIÁVEIS PRINCIPAIS
========================================= */

$competicoes = [];
$estatisticas = null;
$destaqueCompeticao = null;
$resumoCategorias = [];
$campeoesInternacionais = [];

/* =========================================
   RESUMO DAS CATEGORIAS
========================================= */

foreach ($categorias as $categoria) {
    if ($categoria === 'Amistosos') {
        $stmtResumo = $pdo->prepare("
            SELECT COUNT(*) 
            FROM competicoes 
            WHERE amistoso = 1
        ");
        $stmtResumo->execute();
    } else {
        $stmtResumo = $pdo->prepare("
            SELECT COUNT(*) 
            FROM competicoes 
            WHERE tipo = ? 
              AND amistoso = 0
        ");
        $stmtResumo->execute([$categoria]);
    }

    $resumoCategorias[$categoria] = (int) $stmtResumo->fetchColumn();
}

/* =========================================
   COMPETIÇÃO EM DESTAQUE
========================================= */

if (!$categoriaSelecionada) {
    $stmtDestaque = $pdo->prepare("
        SELECT 
            c.id,
            c.nome,
            c.slug,
            c.tipo,
            c.amistoso,
            MAX(t.ano) AS ultimo_ano
        FROM competicoes c
        INNER JOIN temporadas t ON t.id_competicao = c.id
        WHERE c.amistoso = 0
        GROUP BY c.id, c.nome, c.slug, c.tipo, c.amistoso
        ORDER BY ultimo_ano DESC, c.nome ASC
        LIMIT 1
    ");
    $stmtDestaque->execute();
    $comp = $stmtDestaque->fetch(PDO::FETCH_ASSOC);

    if ($comp) {
        $idComp = (int) $comp['id'];
        $ultimoAno = (int) $comp['ultimo_ano'];

        $stmtEdicoes = $pdo->prepare("
            SELECT COUNT(*) 
            FROM temporadas 
            WHERE id_competicao = ?
        ");
        $stmtEdicoes->execute([$idComp]);
        $totalEdicoes = (int) $stmtEdicoes->fetchColumn();

        $stmtCampeoes = $pdo->prepare("
            SELECT COUNT(DISTINCT cl.id_time)
            FROM classificacao cl
            INNER JOIN temporadas t ON t.id = cl.id_temporada
            WHERE t.id_competicao = ?
              AND (cl.fase = 'Camp' OR cl.fase = '1º')
        ");
        $stmtCampeoes->execute([$idComp]);
        $totalCampeoes = (int) $stmtCampeoes->fetchColumn();

        $stmtUltimoCampeao = $pdo->prepare("
            SELECT 
                tm.id,
                tm.nome,
                tm.escudo
            FROM classificacao cl
            INNER JOIN temporadas t ON t.id = cl.id_temporada
            INNER JOIN times tm ON tm.id = cl.id_time
            WHERE t.id_competicao = ?
              AND t.ano = ?
              AND (cl.fase = 'Camp' OR cl.fase = '1º')
            ORDER BY cl.id ASC
            LIMIT 1
        ");
        $stmtUltimoCampeao->execute([$idComp, $ultimoAno]);
        $ultimoCampeao = $stmtUltimoCampeao->fetch(PDO::FETCH_ASSOC);

        $stmtMaiorCampeao = $pdo->prepare("
            SELECT 
                tm.id,
                tm.nome,
                tm.escudo,
                COUNT(*) AS total_titulos,
                MAX(t.ano) AS ultimo_titulo
            FROM classificacao cl
            INNER JOIN temporadas t ON t.id = cl.id_temporada
            INNER JOIN times tm ON tm.id = cl.id_time
            WHERE t.id_competicao = ?
              AND (cl.fase = 'Camp' OR cl.fase = '1º')
            GROUP BY tm.id, tm.nome, tm.escudo
            ORDER BY total_titulos DESC, ultimo_titulo DESC, tm.nome ASC
            LIMIT 1
        ");
        $stmtMaiorCampeao->execute([$idComp]);
        $maiorCampeao = $stmtMaiorCampeao->fetch(PDO::FETCH_ASSOC);

        $stmtFoto = $pdo->prepare("
            SELECT caminho_imagem
            FROM fotos
            WHERE id_competicao = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmtFoto->execute([$idComp]);
        $imagemCompeticao = imagemSegura($stmtFoto->fetchColumn() ?: null);

        $destaqueCompeticao = [
            'id' => $idComp,
            'nome' => $comp['nome'],
            'slug' => $comp['slug'],
            'tipo' => $comp['tipo'],
            'ultimo_ano' => $ultimoAno,
            'total_edicoes' => $totalEdicoes,
            'total_campeoes' => $totalCampeoes,
            'ultimo_campeao' => $ultimoCampeao ?: null,
            'maior_campeao' => $maiorCampeao ?: null,
            'imagem' => $imagemCompeticao,
        ];
    }
}

/* =========================================
   COMPETIÇÕES POR CATEGORIA
========================================= */

if ($categoriaSelecionada) {
    if ($categoriaSelecionada === 'Amistosos') {
        $stmtCompeticoes = $pdo->prepare("
            SELECT *
            FROM competicoes
            WHERE amistoso = 1
            ORDER BY nome ASC
        ");
        $stmtCompeticoes->execute();
    } else {
        $stmtCompeticoes = $pdo->prepare("
            SELECT *
            FROM competicoes
            WHERE tipo = ?
              AND amistoso = 0
            ORDER BY nome ASC
        ");
        $stmtCompeticoes->execute([$categoriaSelecionada]);
    }

    $competicoes = $stmtCompeticoes->fetchAll(PDO::FETCH_ASSOC);
    $idsComp = array_map('intval', array_column($competicoes, 'id'));

    if (!empty($idsComp)) {
        $placeholders = implode(',', array_fill(0, count($idsComp), '?'));

        $stmtTemp = $pdo->prepare("
            SELECT COUNT(*)
            FROM temporadas
            WHERE id_competicao IN ($placeholders)
        ");
        $stmtTemp->execute($idsComp);
        $totalTemporadas = (int) $stmtTemp->fetchColumn();

        $stmtTitulos = $pdo->prepare("
            SELECT COUNT(*)
            FROM classificacao cl
            INNER JOIN temporadas t ON t.id = cl.id_temporada
            WHERE t.id_competicao IN ($placeholders)
              AND (cl.fase = 'Camp' OR cl.fase = '1º')
        ");
        $stmtTitulos->execute($idsComp);
        $totalTitulos = (int) $stmtTitulos->fetchColumn();

        $stmtClubes = $pdo->prepare("
            SELECT COUNT(DISTINCT cl.id_time)
            FROM classificacao cl
            INNER JOIN temporadas t ON t.id = cl.id_temporada
            WHERE t.id_competicao IN ($placeholders)
              AND (cl.fase = 'Camp' OR cl.fase = '1º')
        ");
        $stmtClubes->execute($idsComp);
        $clubesDistintos = (int) $stmtClubes->fetchColumn();

        $estatisticas = [
            'competicoes' => count($idsComp),
            'temporadas' => $totalTemporadas,
            'titulos' => $totalTitulos,
            'clubes_distintos' => $clubesDistintos,
        ];

        if ($categoriaSelecionada === 'Internacional') {
            $stmtCampeoesInternacionais = $pdo->prepare("
                SELECT 
                    tm.id,
                    tm.nome,
                    tm.escudo,
                    COUNT(*) AS total_titulos,
                    MAX(t.ano) AS ultimo_titulo
                FROM classificacao cl
                INNER JOIN temporadas t ON t.id = cl.id_temporada
                INNER JOIN times tm ON tm.id = cl.id_time
                WHERE t.id_competicao IN ($placeholders)
                  AND (cl.fase = 'Camp' OR cl.fase = '1º')
                GROUP BY tm.id, tm.nome, tm.escudo
                ORDER BY total_titulos DESC, ultimo_titulo DESC, tm.nome ASC
            ");
            $stmtCampeoesInternacionais->execute($idsComp);
            $campeoesInternacionais = $stmtCampeoesInternacionais->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        $estatisticas = [
            'competicoes' => 0,
            'temporadas' => 0,
            'titulos' => 0,
            'clubes_distintos' => 0,
        ];
    }
}

$tituloPagina = $categoriaSelecionada
    ? 'Competições ' . $categoriasInfo[$categoriaSelecionada]['titulo']
    : 'Campeonatos';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= e($tituloPagina) ?> - Futebol Brasileiro</title>

  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-campeonatos/campeonatos.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
</head>

<body>
  <?php include '../estrutura/header2.php'; ?>

  <main>
    <section class="secao-campeonatos">
      <div class="container-campeonatos">

        <aside class="menu-lateral-campeonatos">
          <div class="menu-card">
            <h2>Tipos de Competições</h2>

            <ul class="menu-categorias">
              <?php foreach ($categorias as $categoria): ?>
                <li>
                  <a
                    href="?tipo=<?= urlencode($categoria) ?>"
                    class="<?= ($categoriaSelecionada === $categoria) ? 'ativo' : '' ?>"
                  >
                    <span class="categoria-icone"><?= e($categoriasInfo[$categoria]['icone']) ?></span>
                    <span class="categoria-texto"><?= e($categoriasInfo[$categoria]['titulo']) ?></span>
                    <span class="categoria-contador"><?= (int) $resumoCategorias[$categoria] ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <div class="menu-card">
            <h2>Conteúdo Especial</h2>

            <ul class="menu-categorias menu-artigos">
              <li>
                <a href="../noticias/artigos.php?categoria=Campeonatos">
                  <span class="categoria-icone">📚</span>
                  <span class="categoria-texto">Artigos sobre campeonatos</span>
                </a>
              </li>
            </ul>
          </div>
        </aside>

        <div class="conteudo-campeonatos">

          <div class="hero-campeonatos">
            <span class="eyebrow">Futebol Brasileiro</span>

            <h1><?= e($tituloPagina) ?></h1>

            <?php if ($categoriaSelecionada): ?>
              <p>
                <?= e($categoriasInfo[$categoriaSelecionada]['descricao']) ?>
              </p>

              <a href="campeonatos.php" class="botao-voltar-campeonatos">
                ← Voltar para Campeonatos
              </a>
            <?php else: ?>
              <p>
                Explore os principais campeonatos disputados por clubes brasileiros, organizados por abrangência,
                tradição e relevância histórica: torneios internacionais, nacionais, regionais, estaduais e amistosos.
              </p>
            <?php endif; ?>
          </div>

          <?php if ($categoriaSelecionada): ?>

            <?php if ($categoriaSelecionada !== 'Internacional'): ?>
              <div class="card-estatisticas">
                <div class="stat-item">
                  <span class="stat-num"><?= (int) $estatisticas['competicoes'] ?></span>
                  <span class="stat-label"><?= plural($estatisticas['competicoes'], 'Competição', 'Competições') ?></span>
                </div>

                <div class="stat-item">
                  <span class="stat-num"><?= (int) $estatisticas['temporadas'] ?></span>
                  <span class="stat-label"><?= plural($estatisticas['temporadas'], 'Temporada', 'Temporadas') ?></span>
                </div>

                <div class="stat-item">
                  <span class="stat-num"><?= (int) $estatisticas['clubes_distintos'] ?></span>
                  <span class="stat-label"><?= plural($estatisticas['clubes_distintos'], 'Clube campeão', 'Clubes campeões') ?></span>
                </div>
              </div>
            <?php endif; ?>

            <?php if ($categoriaSelecionada === 'Internacional'): ?>
              <div class="bloco-campeoes-internacionais">
                <div class="titulo-bloco">
                  <div>
                    <span class="eyebrow">Clubes campeões</span>
                    <h2>Campeões internacionais brasileiros</h2>
                  </div>
                </div>

                <?php if (!empty($campeoesInternacionais)): ?>
                  <div class="grid-campeoes-internacionais grid-campeoes-internacionais-7">
                    <?php foreach ($campeoesInternacionais as $campeao): ?>
                      <?php
                      $escudoCampeao = imagemSegura($campeao['escudo'] ?? null);
                      ?>
                      <a
                        class="card-campeao-internacional"
                        href="../times/detalhes_time.php?id=<?= (int) $campeao['id'] ?>"
                        title="<?= e($campeao['nome']) ?>"
                      >
                        <?php if ($escudoCampeao): ?>
                          <img
                            src="<?= e($escudoCampeao) ?>"
                            alt="Escudo de <?= e($campeao['nome']) ?>"
                            loading="lazy"
                            onerror="this.style.display='none'"
                          >
                        <?php endif; ?>

                        <strong><?= e($campeao['nome']) ?></strong>

                        <span>
                          <?= (int) $campeao['total_titulos'] ?>
                          <?= plural($campeao['total_titulos'], 'título', 'títulos') ?>
                        </span>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="mensagem-vazia">
                    <strong>Nenhum campeão internacional encontrado.</strong>
                    <p>Não há títulos internacionais registrados para exibição no momento.</p>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="bloco-lista-competicoes">
              <div class="titulo-bloco">
                <div>
                  <span class="eyebrow">Lista de competições</span>
                  <h2><?= e($categoriasInfo[$categoriaSelecionada]['titulo']) ?></h2>
                </div>
              </div>

              <?php if (!empty($competicoes)): ?>
                <div class="grid-competicoes">
                  <?php foreach ($competicoes as $comp): ?>
                    <a class="card-competicao" href="competicao.php?slug=<?= urlencode($comp['slug']) ?>">
                      <span class="card-competicao-tipo">
                        <?= e($categoriaSelecionada === 'Amistosos' ? 'Amistoso' : $comp['tipo']) ?>
                      </span>

                      <strong><?= e($comp['nome']) ?></strong>

                      <span class="card-competicao-link">Ver histórico completo →</span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="mensagem-vazia">
                  <strong>Nenhuma competição cadastrada.</strong>
                  <p>Não há competições disponíveis para esta categoria no momento.</p>
                </div>
              <?php endif; ?>
            </div>

          <?php else: ?>

            <div class="grid-resumo-categorias">
              <?php foreach ($categorias as $categoria): ?>
                <a href="?tipo=<?= urlencode($categoria) ?>" class="card-categoria">
                  <span class="card-categoria-icone"><?= e($categoriasInfo[$categoria]['icone']) ?></span>

                  <span class="card-categoria-conteudo">
                    <strong><?= e($categoriasInfo[$categoria]['titulo']) ?></strong>
                    <small><?= e($categoriasInfo[$categoria]['descricao']) ?></small>
                  </span>

                  <span class="card-categoria-numero">
                    <?= (int) $resumoCategorias[$categoria] ?>
                  </span>
                </a>
              <?php endforeach; ?>
            </div>

            <?php if ($destaqueCompeticao): ?>
              <div class="destaque-competicao-rico">
                <div class="destaque-topo">
                  <div>
                    <span class="eyebrow">Competição em destaque</span>
                    <h2><?= e($destaqueCompeticao['nome']) ?></h2>
                  </div>

                  <span class="comp-tipo"><?= e($destaqueCompeticao['tipo']) ?></span>
                </div>

                <div class="destaque-corpo">
                  <?php if (!empty($destaqueCompeticao['imagem'])): ?>
                    <div class="comp-imagem">
                      <img
                        src="<?= e($destaqueCompeticao['imagem']) ?>"
                        alt="<?= e($destaqueCompeticao['nome']) ?>"
                        loading="lazy"
                        onerror="this.parentElement.style.display='none'"
                      >
                    </div>
                  <?php endif; ?>

                  <div class="comp-conteudo">
                    <div class="comp-dados">
                      <div class="dado-item">
                        <span class="dado-label">Última edição</span>
                        <span class="dado-valor"><?= (int) $destaqueCompeticao['ultimo_ano'] ?></span>
                      </div>

                      <div class="dado-item">
                        <span class="dado-label">Edições</span>
                        <span class="dado-valor"><?= (int) $destaqueCompeticao['total_edicoes'] ?></span>
                      </div>

                      <div class="dado-item">
                        <span class="dado-label">Campeões</span>
                        <span class="dado-valor"><?= (int) $destaqueCompeticao['total_campeoes'] ?></span>
                      </div>
                    </div>

                    <div class="destaque-campeoes">
                      <?php if (!empty($destaqueCompeticao['ultimo_campeao'])): ?>
                        <div class="ultimo-campeao">
                          <span class="subtitulo">Último campeão</span>

                          <div class="clube-campeao">
                            <?php
                            $escudoUltimoCampeao = imagemSegura($destaqueCompeticao['ultimo_campeao']['escudo'] ?? null);
                            ?>

                            <?php if ($escudoUltimoCampeao): ?>
                              <img
                                src="<?= e($escudoUltimoCampeao) ?>"
                                alt="Escudo de <?= e($destaqueCompeticao['ultimo_campeao']['nome']) ?>"
                                loading="lazy"
                                onerror="this.style.display='none'"
                              >
                            <?php endif; ?>

                            <span>
                              <?= e($destaqueCompeticao['ultimo_campeao']['nome']) ?>
                              <small><?= (int) $destaqueCompeticao['ultimo_ano'] ?></small>
                            </span>
                          </div>
                        </div>
                      <?php endif; ?>

                      <?php if (!empty($destaqueCompeticao['maior_campeao'])): ?>
                        <div class="ultimo-campeao maior-campeao">
                          <span class="subtitulo">Maior campeão</span>

                          <div class="clube-campeao">
                            <?php
                            $escudoMaiorCampeao = imagemSegura($destaqueCompeticao['maior_campeao']['escudo'] ?? null);
                            ?>

                            <?php if ($escudoMaiorCampeao): ?>
                              <img
                                src="<?= e($escudoMaiorCampeao) ?>"
                                alt="Escudo de <?= e($destaqueCompeticao['maior_campeao']['nome']) ?>"
                                loading="lazy"
                                onerror="this.style.display='none'"
                              >
                            <?php endif; ?>

                            <span>
                              <?= e($destaqueCompeticao['maior_campeao']['nome']) ?>
                              <small>
                                <?= (int) $destaqueCompeticao['maior_campeao']['total_titulos'] ?>
                                <?= plural($destaqueCompeticao['maior_campeao']['total_titulos'], 'título', 'títulos') ?>
                              </small>
                            </span>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>

                    <a href="competicao.php?slug=<?= urlencode($destaqueCompeticao['slug']) ?>" class="botao-destaque">
                      Ver histórico completo
                    </a>
                  </div>
                </div>
              </div>
            <?php endif; ?>

          <?php endif; ?>

        </div>
      </div>
    </section>
  </main>

  <?php include '../estrutura/footer2.php'; ?>
</body>
</html>