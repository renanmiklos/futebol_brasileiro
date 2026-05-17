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

function eDetalheNoticia($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function nl2brSeguroDetalheNoticia($valor)
{
    return nl2br(eDetalheNoticia($valor));
}

function caminhoImagemDetalheNoticia($caminho, $fallback = '../assets/images/escudo_padrao.png')
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
        return eDetalheNoticia($caminho);
    }

    /*
      Como este arquivo está dentro da pasta noticias,
      caminhos como assets/... precisam subir um nível.
    */
    return '../' . eDetalheNoticia(ltrim($caminho, '/'));
}

function formatarDataDetalheNoticia($data)
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

$noticia = null;

if ($id > 0) {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            titulo,
            subtitulo,
            conteudo,
            imagem,
            data_publicacao
        FROM noticias
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
}

$tituloPagina = $noticia
    ? ($noticia['titulo'] ?? 'Notícia') . ' - Futebol Brasileiro'
    : 'Notícia não encontrada - Futebol Brasileiro';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= eDetalheNoticia($tituloPagina) ?></title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-noticias/detalhes_noticia.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-detalhe-noticia">
        <div class="container">

            <a href="noticias.php" class="voltar-link">
                ← Voltar para Notícias
            </a>

            <?php if ($noticia): ?>
                <?php
                    $titulo = $noticia['titulo'] ?? 'Notícia sem título';
                    $subtitulo = $noticia['subtitulo'] ?? '';
                    $conteudo = $noticia['conteudo'] ?? '';
                    $imagem = caminhoImagemDetalheNoticia($noticia['imagem'] ?? '');
                    $dataPublicacao = formatarDataDetalheNoticia($noticia['data_publicacao'] ?? '');
                ?>

                <article class="noticia-detalhe">

                    <section class="hero-detalhe-noticia">
                        <span class="eyebrow">Notícia</span>

                        <h1><?= eDetalheNoticia($titulo) ?></h1>

                        <?php if (!empty($subtitulo)): ?>
                            <p class="subtitulo-noticia">
                                <?= eDetalheNoticia($subtitulo) ?>
                            </p>
                        <?php endif; ?>

                        <div class="meta-noticia">
                            <span><?= eDetalheNoticia($dataPublicacao) ?></span>
                        </div>
                    </section>

                    <section class="imagem-detalhe-wrapper">
                        <img
                            src="<?= $imagem ?>"
                            alt="<?= eDetalheNoticia($titulo) ?>"
                            class="imagem-detalhe-noticia"
                            onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                        >
                    </section>

                    <section class="conteudo-noticia">
                        <?php if (!empty($conteudo)): ?>
                            <p><?= nl2brSeguroDetalheNoticia($conteudo) ?></p>
                        <?php else: ?>
                            <p>O conteúdo desta notícia ainda não foi cadastrado.</p>
                        <?php endif; ?>
                    </section>

                </article>

            <?php else: ?>

                <section class="hero-detalhe-noticia">
                    <span class="eyebrow">Notícias</span>

                    <h1>Notícia não encontrada</h1>

                    <p>
                        A notícia solicitada não existe, foi removida ou o endereço acessado está incorreto.
                    </p>
                </section>

                <section class="card-mensagem-vazia">
                    <p class="mensagem-vazia">
                        Volte para a página de notícias e escolha uma publicação disponível.
                    </p>
                </section>

            <?php endif; ?>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>