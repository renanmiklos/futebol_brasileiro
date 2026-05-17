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

function eNoticias($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function caminhoImagemNoticias($caminho, $fallback = '../assets/images/escudo_padrao.png')
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
        return eNoticias($caminho);
    }

    /*
      Como este arquivo está dentro da pasta noticias,
      caminhos como assets/... precisam subir um nível.
    */
    return '../' . eNoticias(ltrim($caminho, '/'));
}

function formatarDataNoticias($data)
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
   BUSCAR NOTÍCIAS
========================================= */

$stmt = $pdo->query("
    SELECT 
        id,
        titulo,
        subtitulo,
        imagem,
        data_publicacao
    FROM noticias
    ORDER BY data_publicacao DESC, id DESC
");

$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notícias - Futebol Brasileiro</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-noticias/noticias.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-noticias">
        <div class="container">

            <section class="hero-noticias">
                <span class="eyebrow">Notícias</span>

                <h1>Últimas Notícias</h1>

                <p>
                    Acompanhe as principais novidades, análises, bastidores e acontecimentos recentes
                    do futebol brasileiro.
                </p>
            </section>

            <?php if (!empty($noticias)): ?>
                <section class="grade-noticias">
                    <?php foreach ($noticias as $noticia): ?>
                        <?php
                            $idNoticia = (int)($noticia['id'] ?? 0);
                            $titulo = $noticia['titulo'] ?? 'Notícia sem título';
                            $subtitulo = $noticia['subtitulo'] ?? '';
                            $imagem = caminhoImagemNoticias($noticia['imagem'] ?? '');
                            $dataPublicacao = formatarDataNoticias($noticia['data_publicacao'] ?? '');
                        ?>

                        <article class="card-noticia">
                            <a href="detalhes_noticia.php?id=<?= $idNoticia ?>" class="card-noticia-link">
                                <div class="imagem-noticia-wrapper">
                                    <img
                                        src="<?= $imagem ?>"
                                        alt="<?= eNoticias($titulo) ?>"
                                        class="imagem-noticia"
                                        loading="lazy"
                                        onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                    >
                                </div>

                                <div class="info">
                                    <span class="data-noticia">
                                        <?= eNoticias($dataPublicacao) ?>
                                    </span>

                                    <h2><?= eNoticias($titulo) ?></h2>

                                    <?php if (!empty($subtitulo)): ?>
                                        <p><?= eNoticias($subtitulo) ?></p>
                                    <?php else: ?>
                                        <p>Leia a notícia completa sobre o futebol brasileiro.</p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <section class="card-mensagem-vazia">
                    <p class="mensagem-vazia">
                        Nenhuma notícia foi encontrada no momento.
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