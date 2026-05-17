<?php

require_once '../estrutura/conexaodb.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Time não encontrado.");
}

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

function caminhoImagem($caminho, $fallback = '../assets/images/escudo_padrao.png')
{
    if (empty($caminho)) {
        return $fallback;
    }

    $caminho = trim($caminho);

    if (
        str_starts_with($caminho, 'http://') ||
        str_starts_with($caminho, 'https://') ||
        str_starts_with($caminho, 'data:')
    ) {
        return htmlspecialchars($caminho);
    }

    return '../' . htmlspecialchars(ltrim($caminho, '/'));
}

function formatarData($data)
{
    if (empty($data) || $data === '0000-00-00') {
        return 'Data desconhecida';
    }

    $timestamp = strtotime($data);

    if (!$timestamp) {
        return 'Data desconhecida';
    }

    return date('d/m/Y', $timestamp);
}

function formatarNumero($numero)
{
    if ($numero === null || $numero === '' || !is_numeric($numero)) {
        return 'Não informado';
    }

    return number_format((int)$numero, 0, ',', '.');
}

function textoOuPadrao($texto, $padrao = 'Não informado')
{
    return !empty($texto) ? htmlspecialchars($texto) : $padrao;
}

function pluralizarCompeticao($nomeCompeticao, $quantidade)
{
    if ($quantidade <= 1) {
        return $nomeCompeticao;
    }

    if (strpos($nomeCompeticao, 'Campeonato ') === 0) {
        return 'Campeonatos ' . substr($nomeCompeticao, 11);
    }

    if (strpos($nomeCompeticao, 'Copa ') === 0) {
        return 'Copas ' . substr($nomeCompeticao, 5);
    }

    if (strpos($nomeCompeticao, 'Taça ') === 0) {
        return 'Taças ' . substr($nomeCompeticao, 5);
    }

    if (strpos($nomeCompeticao, 'Torneio ') === 0) {
        return 'Torneios ' . substr($nomeCompeticao, 8);
    }

    return $nomeCompeticao;
}

/* =========================================
   CONSULTA PRINCIPAL DO TIME
========================================= */

$subQueryRanking = "
    SELECT 
        cl.id_time, 
        SUM(cl.pontos) AS total
    FROM classificacao cl
    INNER JOIN temporadas tp ON cl.id_temporada = tp.id
    INNER JOIN competicoes c ON tp.id_competicao = c.id
    WHERE cl.nacional = 1
    GROUP BY cl.id_time
";

$stmt = $pdo->prepare("
    SELECT 
        t.*,
        COALESCE(r.total, 0) AS pontos_ranking,
        da.divisao AS divisao_atual
    FROM times t
    LEFT JOIN ($subQueryRanking) r ON t.id = r.id_time
    LEFT JOIN divisao_atual da ON da.id_time = t.id
    WHERE t.id = ?
    LIMIT 1
");

$stmt->execute([$id]);
$time = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$time) {
    die("Time não encontrado.");
}

$extinto = !empty($time['extinto']);

$divisaoAtual = !$extinto && !empty($time['divisao_atual'])
    ? strtoupper(trim($time['divisao_atual']))
    : '';

$urlVoltar = 'times_estado.php?uf=' . urlencode($time['estado']);

if ($extinto) {
    $urlVoltar .= '&extintos=1';
}

$escudo = caminhoImagem($time['escudo']);
$fundacao = formatarData($time['fundacao']);
$capacidade = formatarNumero($time['capacidade']);
$pontosRanking = number_format((float)$time['pontos_ranking'], 0, ',', '.');

/* =========================================
   CONSULTA DE TÍTULOS DINÂMICOS
========================================= */

$stmtTitulos = $pdo->prepare(<<<'SQL'
    SELECT 
        c.nome AS competicao,
        tp.ano,
        c.tipo
    FROM classificacao cl
    INNER JOIN temporadas tp ON tp.id = cl.id_temporada
    INNER JOIN competicoes c ON c.id = tp.id_competicao
    WHERE cl.id_time = :id_time
      AND (cl.fase = 'Camp' OR cl.fase = '1º')
    ORDER BY 
      CASE c.tipo 
        WHEN 'Internacional' THEN 1
        WHEN 'Nacional' THEN 2
        WHEN 'Regional' THEN 3
        WHEN 'Estadual' THEN 4
        ELSE 5
      END,
      CASE 
        WHEN c.tipo = 'Internacional' THEN
          IF(FIELD(c.nome,
            'Campeonato Mundial de Clubes',
            'Copa do Mundo de Clubes',
            'Copa Intercontinental',
            'Copa Rio Internacional',
            'Torneio Rivadávia Corrêa Meyer',
            'Recopa Mundial',
            'Copa Libertadores da América',
            'Copa dos Campeões Sulamericanos',
            'Copa Sul Americana',
            'Supercopa da Libertadores',
            'Copa Mercosul',
            'Recopa Sulamericana',
            'Copa Conmebol',
            'Copa Ouro Sul Americana',
            'Copa Master Supercopa',
            'Copa Master Conmebol',
            'Copa Levain/Suruga'
          ) = 0, 9999, FIELD(c.nome,
            'Campeonato Mundial de Clubes',
            'Copa do Mundo de Clubes',
            'Copa Intercontinental',
            'Copa Rio Internacional',
            'Torneio Rivadávia Corrêa Meyer',
            'Recopa Mundial',
            'Copa Libertadores da América',
            'Copa dos Campeões Sulamericanos',
            'Copa Sul Americana',
            'Supercopa da Libertadores',
            'Copa Mercosul',
            'Recopa Sulamericana',
            'Copa Conmebol',
            'Copa Ouro Sul Americana',
            'Copa Master Supercopa',
            'Copa Master Conmebol',
            'Copa Levain/Suruga'
          ))

        WHEN c.tipo = 'Nacional' THEN
          IF(FIELD(c.nome,
            'Taça Brasil',
            'Torneio Roberto Gomes Pedrosa',
            'Campeonato Brasileiro',
            'Torneio dos Campeões',
            'Copa do Brasil',
            'Supercopa do Brasil',
            'Copa dos Campeões',
            'Campeonato Brasileiro - Série B',
            'Campeonato Brasileiro - Série C',
            'Campeonato Brasileiro - Série D'
          ) = 0, 9999, FIELD(c.nome,
            'Taça Brasil',
            'Torneio Roberto Gomes Pedrosa',
            'Campeonato Brasileiro',
            'Torneio dos Campeões',
            'Copa do Brasil',
            'Supercopa do Brasil',
            'Copa dos Campeões',
            'Campeonato Brasileiro - Série B',
            'Campeonato Brasileiro - Série C',
            'Campeonato Brasileiro - Série D'
          ))

        ELSE 9999
      END,
      c.nome ASC,
      tp.ano ASC
SQL
);

$stmtTitulos->bindValue(':id_time', $id, PDO::PARAM_INT);
$stmtTitulos->execute();
$titulosDb = $stmtTitulos->fetchAll(PDO::FETCH_ASSOC);

$titulosAgrupados = [];

foreach ($titulosDb as $titulo) {
    $tipo = $titulo['tipo'] ?: 'Outros';
    $competicao = $titulo['competicao'];
    $ano = (int)$titulo['ano'];

    if (!isset($titulosAgrupados[$tipo])) {
        $titulosAgrupados[$tipo] = [];
    }

    if (!isset($titulosAgrupados[$tipo][$competicao])) {
        $titulosAgrupados[$tipo][$competicao] = [];
    }

    $titulosAgrupados[$tipo][$competicao][] = $ano;
}

/* =========================================
   GALERIA
========================================= */

$galeria = [];

if (!empty($time['time'])) {
    $galeria[] = [
        'imagem' => $time['time'],
        'legenda' => $time['legenda'] ?? ''
    ];
}

for ($i = 1; $i <= 10; $i++) {
    $campoImagem = 'extra' . $i;
    $campoLegenda = 'legenda' . $i;

    if (!empty($time[$campoImagem])) {
        $galeria[] = [
            'imagem' => $time[$campoImagem],
            'legenda' => $time[$campoLegenda] ?? ''
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($time['nome']) ?> - Detalhes | Futebol Brasileiro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-times/detalhes_time.css">
</head>
<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="detalhes-time">
        <div class="container">

            <a class="voltar-link" href="<?= htmlspecialchars($urlVoltar) ?>">
                ← Voltar para o estado
            </a>

            <div class="layout-detalhes-time">

                <aside class="coluna-perfil-time">

                    <div class="card-perfil-time">
                        <?php if (in_array($divisaoAtual, ['A', 'B', 'C', 'D'], true)): ?>
                            <span 
                                class="badge-serie serie-<?= strtolower($divisaoAtual) ?>"
                                title="Série <?= htmlspecialchars($divisaoAtual) ?> do Campeonato Brasileiro"
                            >
                                <?= htmlspecialchars($divisaoAtual) ?>
                            </span>
                        <?php endif; ?>

                        <img 
                            class="escudo-time" 
                            src="<?= $escudo ?>" 
                            alt="Escudo de <?= htmlspecialchars($time['nome']) ?>"
                            onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                        >

                        <span class="etiqueta-time">
                            <?= $extinto ? 'Clube extinto' : 'Clube em atividade' ?>
                        </span>

                        <h1><?= htmlspecialchars($time['nome_completo'] ?: $time['nome']) ?></h1>

                        <p class="nome-curto">
                            <?= htmlspecialchars($time['nome']) ?>
                        </p>
                    </div>

                </aside>

                <div class="coluna-conteudo-time">

                    <section class="card-dados-time card-dados-principal">
                        <h2>Informações</h2>

                        <div class="dados-time-lista">
                            <div class="dado-time">
                                <span>Fundação</span>
                                <strong><?= $fundacao ?></strong>
                            </div>

                            <div class="dado-time">
                                <span>Estado</span>
                                <strong><?= textoOuPadrao($time['estado']) ?></strong>
                            </div>

                            <div class="dado-time">
                                <span>Cidade</span>
                                <strong><?= textoOuPadrao($time['cidade']) ?></strong>
                            </div>

                            <div class="dado-time">
                                <span>Estádio</span>
                                <strong><?= textoOuPadrao($time['estadio']) ?></strong>
                            </div>

                            <div class="dado-time">
                                <span>Capacidade</span>
                                <strong><?= $capacidade ?></strong>
                            </div>

                            <div class="dado-time">
                                <span>Ranking Nacional</span>
                                <strong><?= $pontosRanking ?> pts</strong>
                            </div>

                            <?php if (in_array($divisaoAtual, ['A', 'B', 'C', 'D'], true)): ?>
                                <div class="dado-time destaque-divisao">
                                    <span>Divisão Atual</span>
                                    <strong>Série <?= htmlspecialchars($divisaoAtual) ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="bloco-historia-time">
                        <h2>História</h2>

                        <?php if (!empty($time['historia'])): ?>
                            <p><?= nl2br(htmlspecialchars($time['historia'])) ?></p>
                        <?php else: ?>
                            <p class="mensagem-vazia">História ainda não cadastrada para este clube.</p>
                        <?php endif; ?>
                    </section>

                    <section class="bloco-titulos-time">
                        <div class="titulo-bloco">
                            <h2>Títulos</h2>
                            <span>
                                <?= count($titulosDb) ?> conquista<?= count($titulosDb) === 1 ? '' : 's' ?> registrada<?= count($titulosDb) === 1 ? '' : 's' ?>
                            </span>
                        </div>

                        <?php if (empty($titulosAgrupados)): ?>
                            <p class="mensagem-vazia">Nenhum título registrado para este clube.</p>
                        <?php else: ?>
                            <div class="lista-titulos-time">
                                <?php foreach ($titulosAgrupados as $tipo => $competicoes): ?>
                                    <div class="grupo-titulos">
                                        <h3><?= htmlspecialchars($tipo) ?></h3>

                                        <?php foreach ($competicoes as $competicao => $anos): ?>
                                            <?php
                                                sort($anos);
                                                $quantidade = count($anos);
                                                $nomeCompeticao = pluralizarCompeticao($competicao, $quantidade);
                                                $anosTexto = implode(', ', $anos);
                                            ?>

                                            <div class="titulo-time-item">
                                                <strong>
                                                    <?= $quantidade ?> - <?= htmlspecialchars($nomeCompeticao) ?>
                                                </strong>

                                                <span class="anos-titulo-time">
                                                    <?= htmlspecialchars($anosTexto) ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="bloco-galeria-time">
                        <div class="titulo-bloco">
                            <h2>Galeria</h2>
                            <span>
                                <?= count($galeria) ?> imagem<?= count($galeria) === 1 ? '' : 's' ?>
                            </span>
                        </div>

                        <?php if (empty($galeria)): ?>
                            <p class="mensagem-vazia">Nenhuma imagem cadastrada para este clube.</p>
                        <?php else: ?>
                            <div class="grid-galeria-time">
                                <?php foreach ($galeria as $item): ?>
                                    <?php
                                        $imagemGaleria = caminhoImagem($item['imagem'], '');
                                    ?>

                                    <?php if (!empty($imagemGaleria)): ?>
                                        <figure class="foto-time-item">
                                            <img 
                                                src="<?= $imagemGaleria ?>" 
                                                alt="Imagem de <?= htmlspecialchars($time['nome']) ?>"
                                                loading="lazy"
                                            >

                                            <?php if (!empty($item['legenda'])): ?>
                                                <figcaption>
                                                    <?= htmlspecialchars($item['legenda']) ?>
                                                </figcaption>
                                            <?php endif; ?>
                                        </figure>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                </div>

            </div>

        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>