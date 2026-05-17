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

function eGaleriaFotos($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function caminhoImagemGaleriaFotos($caminho, $fallback = '../assets/images/escudo_padrao.png')
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
        return eGaleriaFotos($caminho);
    }

    /*
      Como este arquivo está dentro da pasta historia,
      imagens salvas como assets/... precisam subir um nível.
    */
    return '../' . eGaleriaFotos(ltrim($caminho, '/'));
}

/* =========================================
   BUSCAR BANCOS DE FOTOS
   Primeira imagem de cada álbum
========================================= */

$sql = "
    SELECT 
        b.id AS banco_id, 
        b.nome, 
        b.descricao, 
        f.caminho_imagem
    FROM bancos_de_fotos b
    LEFT JOIN (
        SELECT 
            f1.banco_id, 
            f1.caminho_imagem
        FROM fotos f1
        INNER JOIN (
            SELECT 
                banco_id, 
                MIN(id) AS primeira_foto_id
            FROM fotos
            GROUP BY banco_id
        ) primeiras ON primeiras.primeira_foto_id = f1.id
    ) f ON b.id = f.banco_id
    ORDER BY 
        b.data_criacao DESC,
        b.id DESC
";

$stmt = $pdo->query($sql);
$bancos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Galeria de Fotos - Futebol Brasileiro</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-historia/galeria-fotos.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-galeria-fotos">
        <div class="galeria-container">

            <a href="historia.php" class="voltar-link">
                ← Voltar para História
            </a>

            <section class="hero-galeria-fotos">
                <span class="eyebrow">Galeria</span>

                <h1>Galeria de Fotos</h1>

                <p>
                    Aqui estão registrados momentos marcantes da história do futebol brasileiro,
                    reunidos em álbuns temáticos com imagens históricas, clubes, competições,
                    personagens e memórias do nosso futebol.
                </p>
            </section>

            <?php if (!empty($bancos)): ?>
                <section class="galeria-lista">
                    <?php foreach ($bancos as $banco): ?>
                        <?php
                            $nomeBanco = $banco['nome'] ?? 'Álbum sem título';
                            $descricaoBanco = $banco['descricao'] ?? '';
                            $imagemBanco = caminhoImagemGaleriaFotos($banco['caminho_imagem'] ?? '');
                        ?>

                        <article class="galeria-card">
                            <a 
                                href="detalhes-galeria-fotos.php?banco_id=<?= (int)$banco['banco_id'] ?>" 
                                class="galeria-card-link"
                            >
                                <div class="imagem-preview-wrapper">
                                    <img
                                        src="<?= $imagemBanco ?>"
                                        alt="<?= eGaleriaFotos($nomeBanco) ?>"
                                        class="imagem-preview"
                                        loading="lazy"
                                        onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                    >
                                </div>

                                <div class="galeria-card-conteudo">
                                    <h2><?= eGaleriaFotos($nomeBanco) ?></h2>

                                    <?php if (!empty($descricaoBanco)): ?>
                                        <p><?= eGaleriaFotos($descricaoBanco) ?></p>
                                    <?php else: ?>
                                        <p>Álbum de fotos históricas do futebol brasileiro.</p>
                                    <?php endif; ?>

                                    <span class="botao">
                                        Ver Fotos
                                    </span>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <section class="card-mensagem-vazia">
                    <p class="mensagem-vazia">
                        Nenhum álbum foi encontrado no momento.
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

<script src="js-historia/historia.js"></script>

</body>
</html>