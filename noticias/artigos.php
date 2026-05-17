<?php
require_once '../estrutura/conexaodb.php';

/* =========================================
   VERIFICAÇÃO DE CONEXÃO
========================================= */

if (!isset($pdo)) {
    die('Erro: Conexão com o banco de dados não estabelecida.');
}

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

function eArtigos($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function caminhoImagemArtigos($caminho, $fallback = '../assets/images/escudo_padrao.png')
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
        return eArtigos($caminho);
    }

    /*
      Como este arquivo está dentro da pasta noticias,
      caminhos como assets/... precisam subir um nível.
    */
    return '../' . eArtigos(ltrim($caminho, '/'));
}

function formatarDataArtigos($data)
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

/* =========================================
   CATEGORIAS DISPONÍVEIS
========================================= */

$categoriasDisponiveis = [
    'Campeonatos',
    'Times',
    'Jogadores',
    'Estádios'
];

/* =========================================
   CAPTURA DOS FILTROS
========================================= */

$categoria = isset($_GET['categoria']) ? trim((string)$_GET['categoria']) : '';
$pesquisa = isset($_GET['pesquisa']) ? trim((string)$_GET['pesquisa']) : '';

if ($categoria === 'Ver Todos') {
    $categoria = '';
}

if (!empty($categoria) && !in_array($categoria, $categoriasDisponiveis, true)) {
    $categoria = '';
}

/* =========================================
   CONSULTA DINÂMICA
========================================= */

$where = [];
$params = [];

if (!empty($categoria)) {
    $where[] = "categoria = :categoria";
    $params[':categoria'] = $categoria;
}

if ($pesquisa !== '') {
    $where[] = "(titulo LIKE :pesquisa OR subtitulo LIKE :pesquisa)";
    $params[':pesquisa'] = '%' . $pesquisa . '%';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT 
        id, 
        titulo, 
        subtitulo, 
        imagem, 
        categoria,
        data_publicacao 
    FROM artigos
    $whereClause
    ORDER BY data_publicacao DESC, id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ultimosArtigos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   TEXTO DE CONTEXTO
========================================= */

if ($pesquisa !== '') {
    $tituloLista = 'Resultados da Pesquisa';
    $descricaoHero = 'Resultados encontrados para a pesquisa realizada nos títulos e subtítulos dos artigos.';
} elseif (!empty($categoria)) {
    $tituloLista = 'Artigos da Categoria';
    $descricaoHero = 'Exibindo artigos da categoria ' . $categoria . '.';
} else {
    $tituloLista = 'Últimos Artigos';
    $descricaoHero = 'O futebol brasileiro é repleto de momentos marcantes, personagens lendários e histórias que merecem ser contadas.';
}

$queryLimpar = !empty($categoria)
    ? 'categoria=' . urlencode($categoria)
    : '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Artigos - Futebol Brasileiro</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-noticias/artigos.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-artigos">
        <div class="container">

            <!-- Menu Lateral -->
            <aside class="menu-lateral menu-artigos">
                <div class="menu-bloco">
                    <h2>Categorias</h2>

                    <ul>
                        <?php foreach ($categoriasDisponiveis as $cat): ?>
                            <li>
                                <a 
                                    href="artigos.php?categoria=<?= urlencode($cat) ?>"
                                    class="<?= $categoria === $cat ? 'ativo' : '' ?>"
                                >
                                    <?= eArtigos($cat) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>

                        <li>
                            <a 
                                href="artigos.php"
                                class="<?= empty($categoria) ? 'ativo' : '' ?>"
                            >
                                Ver Todos
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Conteúdo Principal -->
            <div class="conteudo-artigos">

                <section class="hero-artigos">
                    <span class="eyebrow">Artigos</span>

                    <h1>Artigos Históricos do Futebol Brasileiro</h1>

                    <p><?= eArtigos($descricaoHero) ?></p>

                    <?php if (!empty($categoria) || $pesquisa !== ''): ?>
                        <div class="filtros-ativos">
                            <?php if (!empty($categoria)): ?>
                                <span>Categoria: <?= eArtigos($categoria) ?></span>
                            <?php endif; ?>

                            <?php if ($pesquisa !== ''): ?>
                                <span>Pesquisa: <?= eArtigos($pesquisa) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Barra de Pesquisa -->
                <section class="card-pesquisa-artigos">
                    <form method="GET" class="barra-pesquisa">
                        <?php if (!empty($categoria)): ?>
                            <input 
                                type="hidden" 
                                name="categoria" 
                                value="<?= eArtigos($categoria) ?>"
                            >
                        <?php endif; ?>

                        <input
                            type="text"
                            name="pesquisa"
                            placeholder="Pesquisar títulos e subtítulos..."
                            value="<?= eArtigos($pesquisa) ?>"
                            autocomplete="off"
                        >

                        <button type="submit">
                            Pesquisar
                        </button>

                        <?php if ($pesquisa !== ''): ?>
                            <a
                                href="artigos.php<?= !empty($queryLimpar) ? '?' . eArtigos($queryLimpar) : '' ?>"
                                class="btn-limpar"
                            >
                                Limpar
                            </a>
                        <?php endif; ?>
                    </form>
                </section>

                <section class="card-lista-artigos">
                    <div class="titulo-bloco-artigos">
                        <h2><?= eArtigos($tituloLista) ?></h2>

                        <span>
                            <?= count($ultimosArtigos) ?> <?= count($ultimosArtigos) === 1 ? 'artigo' : 'artigos' ?>
                        </span>
                    </div>

                    <?php if (!empty($ultimosArtigos)): ?>
                        <div class="grade-artigos">
                            <?php foreach ($ultimosArtigos as $artigo): ?>
                                <?php
                                    $idArtigo = (int)($artigo['id'] ?? 0);
                                    $titulo = $artigo['titulo'] ?? 'Artigo sem título';
                                    $subtitulo = $artigo['subtitulo'] ?? '';
                                    $imagem = caminhoImagemArtigos($artigo['imagem'] ?? '');
                                    $categoriaArtigo = $artigo['categoria'] ?? '';
                                    $dataPublicacao = formatarDataArtigos($artigo['data_publicacao'] ?? '');
                                ?>

                                <article class="card-artigo">
                                    <a 
                                        href="artigos_detalhes.php?id=<?= $idArtigo ?>" 
                                        class="card-artigo-link"
                                    >
                                        <div class="imagem-artigo-wrapper">
                                            <img
                                                src="<?= $imagem ?>"
                                                alt="<?= eArtigos($titulo) ?>"
                                                class="imagem-artigo"
                                                loading="lazy"
                                                onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                            >
                                        </div>

                                        <div class="info">
                                            <div class="meta-artigo-card">
                                                <span><?= eArtigos($dataPublicacao) ?></span>

                                                <?php if (!empty($categoriaArtigo)): ?>
                                                    <span><?= eArtigos($categoriaArtigo) ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <h3><?= eArtigos($titulo) ?></h3>

                                            <?php if (!empty($subtitulo)): ?>
                                                <p><?= eArtigos($subtitulo) ?></p>
                                            <?php else: ?>
                                                <p>Leia este artigo histórico sobre o futebol brasileiro.</p>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="card-mensagem-vazia">
                            <p class="mensagem-vazia">
                                Nenhum artigo encontrado
                                <?php if ($pesquisa !== ''): ?>
                                    para a pesquisa "<strong><?= eArtigos($pesquisa) ?></strong>".
                                <?php elseif (!empty($categoria)): ?>
                                    para a categoria "<strong><?= eArtigos($categoria) ?></strong>".
                                <?php else: ?>
                                    no momento.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </section>

            </div>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

<div id="voltar-ao-topo">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e1e1e"
        stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 19V5M5 12l7-7 7 7" />
    </svg>

    <span class="tooltip-text">Voltar ao Topo</span>
</div>

<script src="../noticias/js/noticias.js"></script>

</body>
</html>