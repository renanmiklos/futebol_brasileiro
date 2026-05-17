<?php
// index_times.php

require_once 'estrutura/conexaodb.php';

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

function caminhoEscudoIndex($escudo)
{
    if (empty($escudo)) {
        return '';
    }

    $escudo = trim($escudo);

    if (
        str_starts_with($escudo, 'http://') ||
        str_starts_with($escudo, 'https://') ||
        str_starts_with($escudo, 'data:')
    ) {
        return htmlspecialchars($escudo);
    }

    return htmlspecialchars(ltrim($escudo, '/'));
}

function formatarNumeroIndex($valor)
{
    if ($valor === null || $valor === '' || !is_numeric($valor)) {
        return '0';
    }

    return number_format((int)$valor, 0, ',', '.');
}

function formatarTitulosIndex($anos)
{
    if (empty($anos)) {
        return '0';
    }

    $listaAnos = array_filter(array_map('trim', explode(',', $anos)));
    $quantidade = count($listaAnos);

    if ($quantidade === 0) {
        return '0';
    }

    return $quantidade . ' (' . htmlspecialchars(implode(', ', $listaAnos)) . ')';
}

/* =========================================
   CARREGAR CLUBES POR DIVISÃO ATUAL
========================================= */

$ordemDivisoes = ['A', 'B', 'C', 'D'];

$timesPorDivisao = [
    'A' => [],
    'B' => [],
    'C' => [],
    'D' => []
];

$stmtDivisoes = $pdo->query("
    SELECT 
        t.id,
        t.nome,
        t.escudo,
        da.divisao
    FROM divisao_atual da
    INNER JOIN times t ON da.id_time = t.id
    WHERE t.extinto = 0
      AND da.divisao IN ('A', 'B', 'C', 'D')
    ORDER BY 
        FIELD(da.divisao, 'A', 'B', 'C', 'D'),
        t.nome ASC
");

$clubesDivisoes = $stmtDivisoes->fetchAll(PDO::FETCH_ASSOC);

foreach ($clubesDivisoes as $clube) {
    $divisao = strtoupper(trim($clube['divisao']));

    if (isset($timesPorDivisao[$divisao])) {
        $timesPorDivisao[$divisao][] = $clube;
    }
}

$clubesCarrossel = [];

foreach ($ordemDivisoes as $divisao) {
    foreach ($timesPorDivisao[$divisao] as $clube) {
        $clubesCarrossel[] = $clube;
    }
}

/* =========================================
   FUNÇÃO PARA GERAR CARD DE DIVISÃO
========================================= */

function gerarGradeEscudosDivisao($divisao, $timesPorDivisao)
{
    if (empty($timesPorDivisao[$divisao])) {
        return '<p class="mensagem-vazia">Nenhum clube encontrado nesta divisão.</p>';
    }

    $html = '<div class="grade-escudos-divisao">';

    foreach ($timesPorDivisao[$divisao] as $time) {
        $id = (int)$time['id'];
        $nome = htmlspecialchars($time['nome']);
        $escudo = caminhoEscudoIndex($time['escudo']);

        $html .= '<a class="escudo-divisao-link" href="times/detalhes_time.php?id=' . urlencode($id) . '" title="' . $nome . '">';

        if (!empty($escudo)) {
            $html .= '<img src="' . $escudo . '" alt="Escudo de ' . $nome . '" loading="lazy" onerror="this.onerror=null; this.src=\'assets/images/escudo_padrao.png\';">';
        } else {
            $html .= '<span class="sem-escudo-divisao">' . $nome . '</span>';
        }

        $html .= '</a>';
    }

    $html .= '</div>';

    return $html;
}

/* =========================================
   FUNÇÃO PARA OBTER DESTAQUE POR COMPETIÇÃO
========================================= */

function obterDadosCompeticao($pdo, $idCompeticao, $anoInicio = null)
{
    $filtroAnoPrincipal = "";
    $filtroAnoSub = "";

    if ($anoInicio) {
        $filtroAnoPrincipal = " AND temp.ano >= :ano_inicio ";
        $filtroAnoSub = " AND temp2.ano >= :ano_inicio ";
    }

    $sql = "
        SELECT 
            t.id AS id_time,
            t.nome AS nome_time,
            t.escudo,
            COUNT(DISTINCT temp.ano) AS participacoes,

            SUM(
                CASE 
                    WHEN c.fase = 'Camp' OR c.fase = '1º' THEN 1 
                    ELSE 0 
                END
            ) AS qtd_titulos,

            COALESCE(
                (
                    SELECT GROUP_CONCAT(temp2.ano ORDER BY temp2.ano SEPARATOR ', ')
                    FROM classificacao cl2
                    INNER JOIN temporadas temp2 ON temp2.id = cl2.id_temporada
                    WHERE cl2.id_time = t.id
                      AND temp2.id_competicao = :id_competicao
                      $filtroAnoSub
                      AND (cl2.fase = 'Camp' OR cl2.fase = '1º')
                ), ''
            ) AS anos_titulos,

            SUM(
                CASE 
                    WHEN c.fase IN ('Camp', '1º', 'Vice', 'SF', '3º', '4º') THEN 1 
                    ELSE 0 
                END
            ) AS top4,

            MAX(temp.ano) AS ultima_participacao

        FROM classificacao c
        INNER JOIN temporadas temp ON temp.id = c.id_temporada
        INNER JOIN times t ON t.id = c.id_time
        WHERE temp.id_competicao = :id_competicao
          $filtroAnoPrincipal
        GROUP BY t.id, t.nome, t.escudo
        ORDER BY 
            participacoes DESC,
            qtd_titulos DESC,
            top4 DESC,
            ultima_participacao DESC,
            t.nome ASC
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_competicao', $idCompeticao, PDO::PARAM_INT);

    if ($anoInicio) {
        $stmt->bindValue(':ano_inicio', $anoInicio, PDO::PARAM_INT);
    }

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =========================================
   COMPETIÇÕES DE DESTAQUE
========================================= */

$competicoes = [
    'copa_mundo_clubes' => [
        'id' => 1,
        'nome' => 'Copa do Mundo de Clubes',
        'titulo' => 'Clube brasileiro com mais participações na Copa do Mundo de Clubes',
        'classe' => 'top-mundial'
    ],
    'libertadores' => [
        'id' => 5,
        'nome' => 'Copa Libertadores',
        'titulo' => 'Clube brasileiro com mais participações na Copa Libertadores',
        'classe' => 'top-libertadores'
    ],
    'sulamericana' => [
        'id' => 7,
        'nome' => 'Copa Sul-Americana',
        'titulo' => 'Clube brasileiro com mais participações na Copa Sul-Americana',
        'classe' => 'top-sulamericana'
    ],
    'serie_a' => [
        'id' => 19,
        'nome' => 'Campeonato Brasileiro Série A',
        'titulo' => 'Clube com mais participações no Campeonato Brasileiro Série A',
        'classe' => 'top-campeonatos'
    ],
    'serie_b' => [
        'id' => 20,
        'nome' => 'Campeonato Brasileiro Série B',
        'titulo' => 'Clube com mais participações no Campeonato Brasileiro Série B',
        'classe' => 'top-campeonatos'
    ],
    'serie_c' => [
        'id' => 21,
        'nome' => 'Campeonato Brasileiro Série C',
        'titulo' => 'Clube com mais participações no Campeonato Brasileiro Série C',
        'classe' => 'top-campeonatos'
    ],
    'serie_d' => [
        'id' => 22,
        'nome' => 'Campeonato Brasileiro Série D',
        'titulo' => 'Clube com mais participações no Campeonato Brasileiro Série D',
        'classe' => 'top-campeonatos'
    ],
    'copadobrasil' => [
        'id' => 23,
        'nome' => 'Copa do Brasil',
        'titulo' => 'Clube com mais participações na Copa do Brasil',
        'classe' => 'top-copadobrasil'
    ]
];

$dadosCompeticoes = [];

foreach ($competicoes as $key => $competicao) {
    $dadosCompeticoes[$key] = obterDadosCompeticao($pdo, $competicao['id']);
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Times - Futebol Brasileiro</title>

    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/index_times.css">
</head>
<body>

<main>

    <h1>Times</h1>

    <p class="descricao">
        Explore clubes, histórias, conquistas e participações dos times do futebol brasileiro.
    </p>

    <section class="carrossel-container">
        <?php if (!empty($clubesCarrossel)): ?>
            <div class="marquee-wrapper" aria-label="Carrossel contínuo de escudos dos clubes brasileiros">
                <div class="marquee" role="list" aria-hidden="false">
                    <div class="marquee-track" id="marquee-track-1">
                        <?php foreach ($clubesCarrossel as $clube): ?>
                            <?php
                                $idClube = (int)$clube['id'];
                                $nomeClube = htmlspecialchars($clube['nome']);
                                $escudoClube = caminhoEscudoIndex($clube['escudo']);
                            ?>

                            <div class="marquee-item" role="listitem">
                                <a href="times/detalhes_time.php?id=<?= urlencode($idClube) ?>" title="<?= $nomeClube ?>">
                                    <?php if (!empty($escudoClube)): ?>
                                        <img
                                            src="<?= $escudoClube ?>"
                                            alt="<?= $nomeClube ?> - escudo"
                                            loading="lazy"
                                            decoding="async"
                                            width="120"
                                            height="120"
                                            fetchpriority="low"
                                            class="escudo-marquee"
                                            onerror="this.onerror=null; this.src='assets/images/escudo_padrao.png';"
                                        >
                                    <?php else: ?>
                                        <div class="sem-escudo">
                                            <?= $nomeClube ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="marquee-caption">
                                        <?= $nomeClube ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p class="mensagem-vazia">
                Nenhum clube encontrado nas divisões nacionais.
            </p>
        <?php endif; ?>
    </section>

    <section class="principal principal-times">
        <div class="conteudo-times">

            <aside class="barra-lateral-esquerda">

                <div class="bloco-divisoes">
                    <h2>Divisões Nacionais</h2>

                    <?php foreach ($ordemDivisoes as $divisao): ?>
                        <div class="tabela-divisao">
                            <div class="cabecalho-divisao">
                                <h3>Série <?= htmlspecialchars($divisao) ?></h3>
                                <span><?= count($timesPorDivisao[$divisao]) ?> clubes</span>
                            </div>

                            <?= gerarGradeEscudosDivisao($divisao, $timesPorDivisao) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <a href="times/times.php" class="botao botao-ver-todos-times">
                    Ver todos os clubes
                </a>

            </aside>

            <div class="conteudo-direita">

                <section class="hero-times">
                    <span class="eyebrow">Clubes do Brasil</span>

                    <h2>Histórias, escudos e campanhas dos principais clubes brasileiros</h2>

                    <p>
                        Navegue pelos clubes em atividade, consulte divisões nacionais, veja destaques por competição
                        e acesse a página individual de cada time.
                    </p>
                </section>

                <section class="grid-destaques-times">
                    <?php foreach ($competicoes as $key => $competicao): ?>
                        <?php $linha = $dadosCompeticoes[$key] ?? null; ?>

                        <article class="card-destaque-time <?= htmlspecialchars($competicao['classe']) ?>">
                            <div class="titulo-bloco-times">
                                <h2><?= htmlspecialchars($competicao['titulo']) ?></h2>
                                <span><?= htmlspecialchars($competicao['nome']) ?></span>
                            </div>

                            <?php if ($linha): ?>
                                <?php
                                    $escudoLinha = caminhoEscudoIndex($linha['escudo']);
                                    $titulosFormatados = formatarTitulosIndex($linha['anos_titulos']);
                                ?>

                                <a 
                                    href="times/detalhes_time.php?id=<?= urlencode((int)$linha['id_time']) ?>" 
                                    class="clube-destaque-link"
                                >
                                    <div class="clube-destaque-identidade">
                                        <?php if (!empty($escudoLinha)): ?>
                                            <img 
                                                src="<?= $escudoLinha ?>" 
                                                alt="Escudo de <?= htmlspecialchars($linha['nome_time']) ?>"
                                                loading="lazy"
                                                onerror="this.onerror=null; this.src='assets/images/escudo_padrao.png';"
                                            >
                                        <?php else: ?>
                                            <span class="sem-escudo-destaque">
                                                <?= htmlspecialchars($linha['nome_time']) ?>
                                            </span>
                                        <?php endif; ?>

                                        <strong><?= htmlspecialchars($linha['nome_time']) ?></strong>
                                    </div>

                                    <div class="metricas-destaque-time">
                                        <div class="metrica-time">
                                            <span>Participações</span>
                                            <strong><?= formatarNumeroIndex($linha['participacoes']) ?></strong>
                                        </div>

                                        <div class="metrica-time">
                                            <span>Títulos</span>
                                            <strong><?= $titulosFormatados ?></strong>
                                        </div>

                                        <div class="metrica-time">
                                            <span>Última participação</span>
                                            <strong><?= htmlspecialchars($linha['ultima_participacao'] ?: '—') ?></strong>
                                        </div>
                                    </div>
                                </a>
                            <?php else: ?>
                                <p class="mensagem-vazia">
                                    Nenhum dado encontrado para <?= htmlspecialchars($competicao['nome']) ?>.
                                </p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </section>

            </div>

        </div>
    </section>

</main>

<script>
(function() {
    const marquee = document.querySelector('.marquee');
    const track1 = document.getElementById('marquee-track-1');
    const wrapper = document.querySelector('.marquee-wrapper');

    if (!marquee || !track1 || !wrapper) return;

    let track2 = null;

    function setupMarquee() {
        if (track2) {
            track2.remove();
            track2 = null;
        }

        track2 = track1.cloneNode(true);
        track2.id = 'marquee-track-2';
        track2.setAttribute('aria-hidden', 'true');

        track2.querySelectorAll('a').forEach(a => {
            a.tabIndex = -1;
            a.setAttribute('aria-hidden', 'true');
        });

        marquee.appendChild(track2);

        requestAnimationFrame(() => {
            const trackWidth = track1.scrollWidth;

            /*
              Quanto maior o número, mais rápido.
              Sugestões:
              520 = moderado
              620 = rápido
              700 = bem rápido
            */
            const speedPixelsPerSecond = 120;

            const durationSeconds = Math.max(12, trackWidth / speedPixelsPerSecond);

            marquee.style.setProperty('--marquee-distance', trackWidth + 'px');
            marquee.style.setProperty('--marquee-duration', durationSeconds + 's');

            marquee.classList.add('animate');
        });
    }

    function pauseMarquee() {
        marquee.style.animationPlayState = 'paused';
    }

    function playMarquee() {
        marquee.style.animationPlayState = 'running';
    }

    wrapper.addEventListener('touchstart', pauseMarquee, { passive: true });
    wrapper.addEventListener('touchend', playMarquee);
    wrapper.addEventListener('focusin', pauseMarquee);
    wrapper.addEventListener('focusout', playMarquee);

    window.addEventListener('load', setupMarquee);

    window.addEventListener('resize', () => {
        marquee.classList.remove('animate');
        marquee.style.animation = 'none';

        requestAnimationFrame(() => {
            marquee.style.animation = '';
            setupMarquee();
        });
    });
})();
</script>

</body>
</html>