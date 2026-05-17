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

function eDetalhesFotos($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function caminhoImagemDetalhesFotos($caminho, $fallback = '../assets/images/escudo_padrao.png')
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
        return eDetalhesFotos($caminho);
    }

    /*
      Como este arquivo está dentro da pasta historia,
      caminhos como assets/... precisam subir um nível.
    */
    return '../' . eDetalhesFotos(ltrim($caminho, '/'));
}

function formatarDataDetalhesFotos($data)
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

/* =========================================
   CAPTURA E VALIDAÇÃO DO ID DO ÁLBUM
========================================= */

$banco_id = filter_input(INPUT_GET, 'banco_id', FILTER_VALIDATE_INT) ?: 0;

$banco = null;
$fotos = [];

if ($banco_id > 0) {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nome,
            descricao,
            data_criacao
        FROM bancos_de_fotos
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$banco_id]);
    $banco = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($banco) {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                banco_id,
                titulo,
                caminho_imagem,
                data_publicacao
            FROM fotos
            WHERE banco_id = ?
            ORDER BY 
                data_publicacao DESC,
                id DESC
        ");

        $stmt->execute([$banco_id]);
        $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$tituloPagina = $banco
    ? $banco['nome'] . ' - Galeria de Fotos'
    : 'Álbum não encontrado - Galeria de Fotos';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= eDetalhesFotos($tituloPagina) ?></title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-historia/detalhes-galeria-fotos.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-detalhes-galeria-fotos">
        <div class="detalhes-galeria-container">

            <a href="galeria-fotos.php" class="voltar-link">
                ← Voltar à Galeria
            </a>

            <?php if ($banco): ?>

                <section class="hero-detalhes-galeria">
                    <span class="eyebrow">Álbum de fotos</span>

                    <h1><?= eDetalhesFotos($banco['nome']) ?></h1>

                    <?php if (!empty($banco['descricao'])): ?>
                        <p><?= eDetalhesFotos($banco['descricao']) ?></p>
                    <?php else: ?>
                        <p>
                            Álbum com registros fotográficos da história do futebol brasileiro.
                        </p>
                    <?php endif; ?>

                    <div class="album-meta">
                        <span><?= count($fotos) ?> <?= count($fotos) === 1 ? 'foto' : 'fotos' ?></span>

                        <?php if (!empty($banco['data_criacao'])): ?>
                            <span>Criado em <?= eDetalhesFotos(formatarDataDetalhesFotos($banco['data_criacao'])) ?></span>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if (!empty($fotos)): ?>
                    <section class="grid-fotos">
                        <?php foreach ($fotos as $foto): ?>
                            <?php
                                $tituloFoto = $foto['titulo'] ?? 'Foto sem título';
                                $imagemFoto = caminhoImagemDetalhesFotos($foto['caminho_imagem'] ?? '');
                                $dataFoto = formatarDataDetalhesFotos($foto['data_publicacao'] ?? '');
                            ?>

                            <article class="foto-card">
                                <a 
                                    href="<?= $imagemFoto ?>" 
                                    target="_blank" 
                                    rel="noopener noreferrer" 
                                    class="foto-link"
                                >
                                    <div class="foto-imagem-wrapper">
                                        <img
                                            src="<?= $imagemFoto ?>"
                                            alt="<?= eDetalhesFotos($tituloFoto) ?>"
                                            loading="lazy"
                                            onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                        >
                                    </div>

                                    <div class="foto-info">
                                        <h2><?= eDetalhesFotos($tituloFoto) ?></h2>

                                        <?php if (!empty($dataFoto)): ?>
                                            <span><?= eDetalhesFotos($dataFoto) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </section>
                <?php else: ?>
                    <section class="card-mensagem-vazia">
                        <p class="mensagem-vazia">
                            Nenhuma foto encontrada neste álbum.
                        </p>
                    </section>
                <?php endif; ?>

            <?php else: ?>

                <section class="hero-detalhes-galeria">
                    <span class="eyebrow">Galeria</span>

                    <h1>Álbum não encontrado</h1>

                    <p>
                        O álbum solicitado não existe, foi removido ou o endereço acessado está incorreto.
                    </p>
                </section>

                <section class="card-mensagem-vazia">
                    <p class="mensagem-vazia">
                        Volte para a galeria principal e escolha um álbum disponível.
                    </p>
                </section>

            <?php endif; ?>

            <div class="acoes-galeria">
                <a href="galeria-fotos.php" class="botao">
                    Voltar à Galeria
                </a>
            </div>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>