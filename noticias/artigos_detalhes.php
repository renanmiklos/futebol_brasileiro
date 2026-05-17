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

function eArtigoDetalhe($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function nl2brSeguroArtigoDetalhe($valor)
{
    return nl2br(eArtigoDetalhe($valor));
}

function caminhoImagemArtigoDetalhe($caminho, $fallback = '../assets/images/escudo_padrao.png')
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
        return eArtigoDetalhe($caminho);
    }

    /*
      Como este arquivo está dentro da pasta noticias,
      caminhos como assets/... precisam subir um nível.
    */
    return '../' . eArtigoDetalhe(ltrim($caminho, '/'));
}

function formatarDataArtigoDetalhe($data)
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
   CAPTURA E VALIDAÇÃO DO ID
========================================= */

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

$artigo = null;

if ($id > 0) {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            titulo,
            subtitulo,
            conteudo,
            imagem,
            categoria,
            data_publicacao
        FROM artigos
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);
    $artigo = $stmt->fetch(PDO::FETCH_ASSOC);
}

$tituloPagina = $artigo
    ? ($artigo['titulo'] ?? 'Artigo') . ' - Futebol Brasileiro'
    : 'Artigo não encontrado - Futebol Brasileiro';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= eArtigoDetalhe($tituloPagina) ?></title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-noticias/artigos_detalhes.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-artigo-detalhe">
        <div class="container">

            <a href="artigos.php" class="voltar-link">
                ← Voltar para Artigos
            </a>

            <?php if ($artigo): ?>
                <?php
                    $titulo = $artigo['titulo'] ?? 'Artigo sem título';
                    $subtitulo = $artigo['subtitulo'] ?? '';
                    $conteudo = $artigo['conteudo'] ?? '';
                    $imagem = caminhoImagemArtigoDetalhe($artigo['imagem'] ?? '');
                    $categoria = $artigo['categoria'] ?? '';
                    $dataPublicacao = formatarDataArtigoDetalhe($artigo['data_publicacao'] ?? '');
                ?>

                <article class="artigo-detalhe">

                    <section class="hero-artigo-detalhe">
                        <span class="eyebrow">Artigo</span>

                        <h1><?= eArtigoDetalhe($titulo) ?></h1>

                        <?php if (!empty($subtitulo)): ?>
                            <p class="subtitulo-artigo">
                                <?= eArtigoDetalhe($subtitulo) ?>
                            </p>
                        <?php endif; ?>

                        <div class="meta-artigo">
                            <span>Publicado em <?= eArtigoDetalhe($dataPublicacao) ?></span>

                            <?php if (!empty($categoria)): ?>
                                <span><?= eArtigoDetalhe($categoria) ?></span>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="imagem-artigo-detalhe-wrapper">
                        <img
                            src="<?= $imagem ?>"
                            alt="<?= eArtigoDetalhe($titulo) ?>"
                            class="imagem-artigo-detalhe"
                            onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                        >
                    </section>

                    <section class="conteudo-artigo">
                        <?php if (!empty($conteudo)): ?>
                            <p><?= nl2brSeguroArtigoDetalhe($conteudo) ?></p>
                        <?php else: ?>
                            <p>O conteúdo deste artigo ainda não foi cadastrado.</p>
                        <?php endif; ?>
                    </section>

                </article>

            <?php else: ?>

                <section class="hero-artigo-detalhe">
                    <span class="eyebrow">Artigos</span>

                    <h1>Artigo não encontrado</h1>

                    <p>
                        O artigo solicitado não existe, foi removido ou o endereço acessado está incorreto.
                    </p>
                </section>

                <section class="card-mensagem-vazia">
                    <p class="mensagem-vazia">
                        Volte para a página de artigos e escolha uma publicação disponível.
                    </p>
                </section>

            <?php endif; ?>

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