<?php
/* =========================================
   INDEX_NOTICIAS.PHP
   Bloco de Destaques da Página Principal
========================================= */

require_once __DIR__ . '/../estrutura/conexaodb.php';
require_once __DIR__ . '/../estrutura/gera-ranking.php';

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

if (!function_exists('eIndexNoticias')) {
    function eIndexNoticias($valor)
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('caminhoImagemIndexNoticias')) {
    function caminhoImagemIndexNoticias($caminho, $fallback = 'assets/images/escudo_padrao.png')
    {
        if (empty($caminho)) {
            return $fallback;
        }

        $caminho = trim((string)$caminho);

        if (
            str_starts_with($caminho, 'http://') ||
            str_starts_with($caminho, 'https://') ||
            str_starts_with($caminho, 'data:')
        ) {
            return eIndexNoticias($caminho);
        }

        /*
          Este arquivo é incluído no index.php, portanto os caminhos devem ser
          relativos à raiz do projeto.
        */
        return eIndexNoticias(ltrim($caminho, '/'));
    }
}

if (!function_exists('formatarNumeroIndexNoticias')) {
    function formatarNumeroIndexNoticias($valor)
    {
        if (!is_numeric($valor)) {
            return '0';
        }

        return number_format((float)$valor, 0, '', '.');
    }
}

/* =========================================
   GARANTIA DE VARIÁVEIS
========================================= */

$noticia_principal = $noticia_principal ?? null;
$noticias_cards = $noticias_cards ?? [];
$artigos = $artigos ?? [];

/* =========================================
   RANKING TOP 3
========================================= */

$top3 = [];

if (isset($pdo)) {
    $rankingCompleto = gerarRankingCompleto($pdo);
    $top3 = array_slice($rankingCompleto, 0, 3);
}

$destaquesEstatisticas = [
    'top3' => $top3
];
?>

<section class="index-destaques">

    <div class="index-destaques-header">
        <span class="eyebrow">Destaques</span>

        <h1>Destaques</h1>

        <p>
            Confira as principais notícias, artigos recentes, ranking oficial e links úteis
            do futebol brasileiro.
        </p>
    </div>

    <section class="principal">
        <div class="conteudo">

            <div class="noticias-container">

                <!-- Notícia principal -->
                <article class="noticia-principal">
                    <?php if (!empty($noticia_principal)): ?>
                        <?php
                            $idPrincipal = (int)($noticia_principal['id'] ?? 0);
                            $tituloPrincipal = $noticia_principal['titulo'] ?? 'Notícia sem título';
                            $subtituloPrincipal = $noticia_principal['subtitulo'] ?? '';
                            $imagemPrincipal = caminhoImagemIndexNoticias($noticia_principal['imagem'] ?? '');
                            $linkPrincipal = 'noticias/detalhes_noticia.php?id=' . $idPrincipal;
                        ?>

                        <a href="<?= eIndexNoticias($linkPrincipal) ?>" class="noticia-principal-imagem">
                            <img
                                src="<?= $imagemPrincipal ?>"
                                alt="<?= eIndexNoticias($tituloPrincipal) ?>"
                                onerror="this.onerror=null; this.src='assets/images/escudo_padrao.png';"
                            >
                        </a>

                        <h2>
                            <a href="<?= eIndexNoticias($linkPrincipal) ?>">
                                <?= eIndexNoticias($tituloPrincipal) ?>
                            </a>
                        </h2>

                        <?php if (!empty($subtituloPrincipal)): ?>
                            <h4><?= eIndexNoticias($subtituloPrincipal) ?></h4>
                        <?php else: ?>
                            <h4>Leia a notícia completa sobre o futebol brasileiro.</h4>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="mensagem-vazia-index">
                            Nenhuma notícia principal cadastrada no momento.
                        </div>
                    <?php endif; ?>
                </article>

                <!-- Cards de notícias -->
                <?php if (!empty($noticias_cards)): ?>
                    <div class="cards-noticias">
                        <?php foreach ($noticias_cards as $card): ?>
                            <?php
                                $idCard = (int)($card['id'] ?? 0);
                                $tituloCard = $card['titulo'] ?? 'Notícia sem título';
                                $subtituloCard = $card['subtitulo'] ?? '';
                                $imagemCard = caminhoImagemIndexNoticias($card['imagem'] ?? '');
                                $linkCard = 'noticias/detalhes_noticia.php?id=' . $idCard;
                            ?>

                            <article class="card">
                                <a href="<?= eIndexNoticias($linkCard) ?>" class="card-imagem-link">
                                    <img
                                        src="<?= $imagemCard ?>"
                                        alt="<?= eIndexNoticias($tituloCard) ?>"
                                        onerror="this.onerror=null; this.src='assets/images/escudo_padrao.png';"
                                    >
                                </a>

                                <h4>
                                    <a href="<?= eIndexNoticias($linkCard) ?>">
                                        <?= eIndexNoticias($tituloCard) ?>
                                    </a>
                                </h4>

                                <?php if (!empty($subtituloCard)): ?>
                                    <h5><?= eIndexNoticias($subtituloCard) ?></h5>
                                <?php else: ?>
                                    <h5>Leia a notícia completa.</h5>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="mensagem-vazia-index">
                        Nenhuma notícia secundária cadastrada no momento.
                    </div>
                <?php endif; ?>

            </div>

            <!-- Barra lateral -->
            <aside class="barra-lateral">

                <!-- Carrossel de artigos -->
                <div class="carrossel-artigos">
                    <h3>Últimos Artigos</h3>

                    <?php if (!empty($artigos)): ?>
                        <div class="carrossel">
                            <?php foreach ($artigos as $index => $art): ?>
                                <?php
                                    $idArtigo = (int)($art['id'] ?? 0);
                                    $tituloArtigo = $art['titulo'] ?? 'Artigo sem título';
                                    $imagemArtigo = caminhoImagemIndexNoticias($art['imagem'] ?? '');
                                    $linkArtigo = 'noticias/artigos_detalhes.php?id=' . $idArtigo;
                                ?>

                                <div class="carrossel-item<?= $index === 0 ? ' active' : '' ?>">
                                    <a href="<?= eIndexNoticias($linkArtigo) ?>">
                                        <img
                                            src="<?= $imagemArtigo ?>"
                                            alt="<?= eIndexNoticias($tituloArtigo) ?>"
                                            onerror="this.onerror=null; this.src='assets/images/escudo_padrao.png';"
                                        >

                                        <div class="carrossel-caption">
                                            <h5><?= eIndexNoticias($tituloArtigo) ?></h5>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="mensagem-vazia-index">
                            Nenhum artigo cadastrado.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Ranking Top 3 -->
                <?php if (!empty($destaquesEstatisticas['top3'])): ?>
                    <div class="top3-ranking">
                        <h3>Ranking Oficial</h3>

                        <div class="ranking-lista">
                            <?php foreach ($destaquesEstatisticas['top3'] as $i => $clube): ?>
                                <?php
                                    $nomeClube = $clube['nome'] ?? 'Clube';
                                    $escudoClube = caminhoImagemIndexNoticias($clube['escudo'] ?? '');
                                    $totalClube = $clube['total'] ?? 0;
                                ?>

                                <div class="ranking-item">
                                    <span class="ranking-pos"><?= $i + 1 ?>º</span>

                                    <div class="ranking-nome">
                                        <img
                                            src="<?= $escudoClube ?>"
                                            alt="<?= eIndexNoticias($nomeClube) ?>"
                                            onerror="this.onerror=null; this.src='assets/images/escudo_padrao.png';"
                                        >

                                        <span><?= eIndexNoticias($nomeClube) ?></span>
                                    </div>

                                    <span class="ranking-pontos">
                                        <?= formatarNumeroIndexNoticias($totalClube) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botão para ranking completo -->
                <div class="ranking-button-container">
                    <a href="estatisticas/ranking-introducao.php" class="botao">
                        Ver Ranking Completo
                    </a>
                </div>

                <!-- Links úteis -->
                <div class="links-importantes">
                    <h3>Links Úteis</h3>

                    <ul>
                        <li>
                            <a href="campeonatos/competicao.php?slug=campeonato-brasileiro">
                                Campeonato Brasileiro
                            </a>
                        </li>

                        <li>
                            <a href="times/times.php?regiao=Times+Extintos">
                                Clubes Extintos
                            </a>
                        </li>

                        <li>
                            <a href="noticias/artigos.php?categoria=Jogadores">
                                Grandes Jogadores
                            </a>
                        </li>

                        <li>
                            <a href="noticias/artigos.php">
                                Ver Todos os Artigos
                            </a>
                        </li>
                    </ul>
                </div>

            </aside>

        </div>
    </section>

</section>