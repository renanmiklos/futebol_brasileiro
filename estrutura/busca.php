<?php
require_once __DIR__ . '/conexaodb.php';

/* =========================================
   VERIFICAÇÃO DE CONEXÃO
========================================= */

if (!isset($pdo)) {
    die('Erro: Conexão com o banco de dados não estabelecida.');
}

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

function eBusca($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function formatarDataBusca($data)
{
    if (empty($data)) {
        return 'Data não informada';
    }

    $timestamp = strtotime((string)$data);

    if (!$timestamp) {
        return 'Data não informada';
    }

    return date('d/m/Y', $timestamp);
}

function gerarResumoBusca($texto, $limite = 220)
{
    $texto = trim(strip_tags((string)$texto));
    $texto = preg_replace('/\s+/', ' ', $texto);

    if ($texto === '') {
        return 'Sem resumo disponível.';
    }

    if (mb_strlen($texto, 'UTF-8') <= $limite) {
        return $texto;
    }

    return mb_substr($texto, 0, $limite, 'UTF-8') . '...';
}

function linkResultadoBusca(array $item)
{
    $id = (int)($item['id'] ?? 0);
    $tipo = $item['tipo'] ?? '';

    if ($tipo === 'noticia') {
        return '../noticias/detalhes_noticia.php?id=' . $id;
    }

    if ($tipo === 'artigo') {
        return '../noticias/artigos_detalhes.php?id=' . $id;
    }

    return '../index.php';
}

function labelTipoBusca($tipo)
{
    return match ($tipo) {
        'noticia' => 'Notícia',
        'artigo' => 'Artigo',
        default => 'Resultado',
    };
}

/* =========================================
   CAPTURA DA QUERY
========================================= */

$query = isset($_GET['query']) ? trim((string)$_GET['query']) : '';

/*
  Evita pesquisas absurdamente longas.
  Não muda a lógica, apenas protege a página.
*/
if (mb_strlen($query, 'UTF-8') > 120) {
    $query = mb_substr($query, 0, 120, 'UTF-8');
}

$resultados = [];

/* =========================================
   BUSCA
========================================= */

if ($query !== '') {
    $searchTerm = '%' . $query . '%';

    /*
      Notícias
    */
    $stmtNoticias = $pdo->prepare("
        SELECT 
            id, 
            titulo, 
            subtitulo,
            conteudo, 
            data_publicacao, 
            'noticia' AS tipo
        FROM noticias
        WHERE titulo LIKE :busca
           OR subtitulo LIKE :busca
           OR conteudo LIKE :busca
        ORDER BY data_publicacao DESC, id DESC
    ");

    $stmtNoticias->execute([
        ':busca' => $searchTerm
    ]);

    $noticias = $stmtNoticias->fetchAll(PDO::FETCH_ASSOC);

    /*
      Artigos
    */
    $stmtArtigos = $pdo->prepare("
        SELECT 
            id, 
            titulo, 
            subtitulo,
            conteudo, 
            data_publicacao, 
            'artigo' AS tipo
        FROM artigos
        WHERE titulo LIKE :busca
           OR subtitulo LIKE :busca
           OR conteudo LIKE :busca
        ORDER BY data_publicacao DESC, id DESC
    ");

    $stmtArtigos->execute([
        ':busca' => $searchTerm
    ]);

    $artigos = $stmtArtigos->fetchAll(PDO::FETCH_ASSOC);

    /*
      Combina e ordena por data.
    */
    $resultados = array_merge($noticias, $artigos);

    usort($resultados, function ($a, $b) {
        $dataA = strtotime((string)($a['data_publicacao'] ?? '')) ?: 0;
        $dataB = strtotime((string)($b['data_publicacao'] ?? '')) ?: 0;

        if ($dataA === $dataB) {
            return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
        }

        return $dataB <=> $dataA;
    });
}

$totalResultados = count($resultados);
$tituloPagina = $query !== ''
    ? 'Busca por "' . $query . '" - Futebol Brasileiro'
    : 'Busca - Futebol Brasileiro';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= eBusca($tituloPagina) ?></title>

    <link rel="stylesheet" href="css-estrutura/header.css">
    <link rel="stylesheet" href="css-estrutura/footer.css">
    <link rel="stylesheet" href="../assets/css/busca.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include __DIR__ . '/header2.php'; ?>

<main>
    <section class="secao-busca">
        <div class="busca-container">

            <a href="../index.php" class="voltar-link">
                ← Voltar para Página Principal
            </a>

            <section class="hero-busca">
                <span class="eyebrow">Busca</span>

                <h1>Resultados da Busca</h1>

                <?php if ($query === ''): ?>
                    <p>
                        Digite um termo na barra de busca para encontrar notícias e artigos
                        relacionados ao futebol brasileiro.
                    </p>
                <?php else: ?>
                    <p>
                        Resultado da pesquisa por
                        <strong>“<?= eBusca($query) ?>”</strong>.
                    </p>

                    <div class="busca-meta">
                        <span>
                            <?= $totalResultados ?>
                            <?= $totalResultados === 1 ? 'resultado encontrado' : 'resultados encontrados' ?>
                        </span>
                    </div>
                <?php endif; ?>
            </section>

            <section class="card-busca-form">
                <form action="busca.php" method="GET" class="form-busca">
                    <label for="query">Pesquisar no site</label>

                    <div class="campo-busca">
                        <input
                            type="text"
                            id="query"
                            name="query"
                            value="<?= eBusca($query) ?>"
                            placeholder="Digite uma notícia, artigo, clube, competição..."
                            autocomplete="off"
                        >

                        <button type="submit">
                            Buscar
                        </button>
                    </div>
                </form>
            </section>

            <?php if ($query === ''): ?>

                <section class="card-mensagem-vazia">
                    <p class="mensagem-vazia">
                        Nenhuma pesquisa foi realizada ainda.
                    </p>
                </section>

            <?php elseif (empty($resultados)): ?>

                <section class="card-mensagem-vazia">
                    <p class="mensagem-vazia">
                        Nenhum resultado encontrado para
                        <strong>“<?= eBusca($query) ?>”</strong>.
                        Tente pesquisar por outro termo.
                    </p>
                </section>

            <?php else: ?>

                <section class="card-resultados-busca">
                    <div class="titulo-bloco-busca">
                        <h2>Resultados encontrados</h2>

                        <span>
                            <?= $totalResultados ?>
                            <?= $totalResultados === 1 ? 'item' : 'itens' ?>
                        </span>
                    </div>

                    <div class="resultados-lista">
                        <?php foreach ($resultados as $item): ?>
                            <?php
                                $titulo = $item['titulo'] ?? 'Resultado sem título';
                                $subtitulo = $item['subtitulo'] ?? '';
                                $conteudo = $item['conteudo'] ?? '';
                                $tipo = $item['tipo'] ?? '';
                                $data = formatarDataBusca($item['data_publicacao'] ?? '');
                                $resumoBase = !empty($subtitulo) ? $subtitulo : $conteudo;
                                $resumo = gerarResumoBusca($resumoBase);
                                $link = linkResultadoBusca($item);
                                $labelTipo = labelTipoBusca($tipo);
                            ?>

                            <article class="resultado-item">
                                <a href="<?= eBusca($link) ?>" class="resultado-link">
                                    <div class="resultado-topo">
                                        <span class="badge-tipo">
                                            <?= eBusca($labelTipo) ?>
                                        </span>

                                        <span class="data-resultado">
                                            <?= eBusca($data) ?>
                                        </span>
                                    </div>

                                    <h3><?= eBusca($titulo) ?></h3>

                                    <p><?= eBusca($resumo) ?></p>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

            <?php endif; ?>

        </div>
    </section>
</main>

<?php include __DIR__ . '/footer2.php'; ?>

</body>
</html>