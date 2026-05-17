<?php
require_once '../estrutura/conexaodb.php';

/* =========================================
   DEFINIÇÃO DE REGIÕES E ESTADOS
   ========================================= */

$ufsBrasil = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
    'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
    'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
];

$regioes = [
    'Sudeste'        => ['SP', 'RJ', 'MG', 'ES'],
    'Sul'            => ['RS', 'PR', 'SC'],
    'Nordeste'       => ['BA', 'PE', 'CE', 'RN', 'MA', 'PB', 'PI', 'AL', 'SE'],
    'Centro-Oeste'   => ['DF', 'GO', 'MT', 'MS'],
    'Norte'          => ['AM', 'PA', 'AC', 'RO', 'RR', 'AP', 'TO'],
    'Times Extintos' => $ufsBrasil,
];

/* =========================================
   REGIÃO SELECIONADA
   ========================================= */

$regiaoSelecionada = isset($_GET['regiao']) ? urldecode(trim($_GET['regiao'])) : null;

if ($regiaoSelecionada && !isset($regioes[$regiaoSelecionada])) {
    $regiaoSelecionada = null;
}

$estadosPorRegiao = $regiaoSelecionada ? $regioes[$regiaoSelecionada] : [];

$regiaoInfo = null;

/* =========================================
   DADOS DA REGIÃO SELECIONADA
   Exceto categoria especial "Times Extintos"
   ========================================= */

if ($regiaoSelecionada && $regiaoSelecionada !== 'Times Extintos') {
    $estadosLista = $regioes[$regiaoSelecionada];
    $placeholders = implode(',', array_fill(0, count($estadosLista), '?'));

    // Total de clubes ativos ranquiados
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM times
        WHERE id BETWEEN 1 AND 2065
          AND estado IN ($placeholders)
          AND (extinto IS NULL OR extinto = 0)
    ");
    $stmt->execute($estadosLista);
    $totalClubesAtivos = (int) $stmt->fetchColumn();

    // Títulos da Copa Libertadores da América
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM classificacao cl
        INNER JOIN temporadas tp ON tp.id = cl.id_temporada
        INNER JOIN competicoes c ON c.id = tp.id_competicao
        INNER JOIN times tm ON tm.id = cl.id_time
        WHERE tm.estado IN ($placeholders)
          AND c.nome = 'Copa Libertadores da América'
          AND (cl.fase = 'Camp' OR cl.fase = '1º')
          AND (tm.extinto IS NULL OR tm.extinto = 0)
    ");
    $stmt->execute($estadosLista);
    $titulosLibertadores = (int) $stmt->fetchColumn();

    // Títulos do Campeonato Brasileiro
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM classificacao cl
        INNER JOIN temporadas tp ON tp.id = cl.id_temporada
        INNER JOIN competicoes c ON c.id = tp.id_competicao
        INNER JOIN times tm ON tm.id = cl.id_time
        WHERE tm.estado IN ($placeholders)
          AND c.nome = 'Campeonato Brasileiro'
          AND (cl.fase = 'Camp' OR cl.fase = '1º')
          AND (tm.extinto IS NULL OR tm.extinto = 0)
    ");
    $stmt->execute($estadosLista);
    $titulosBrasileirao = (int) $stmt->fetchColumn();

    $regiaoInfo = [
        'estados'       => count($estadosLista),
        'clubes_ativos' => $totalClubesAtivos,
        'libertadores'  => $titulosLibertadores,
        'brasileirao'   => $titulosBrasileirao,
    ];
}

/* =========================================
   TIME DO DIA
   Sorteia diariamente um clube entre os principais clubes ranquiados
   ========================================= */

$clubeDoDia = null;
$titulosAgrupados = [];

if (!$regiaoSelecionada) {
    $seed = date('Y-m-d');
    mt_srand(crc32($seed));

    $stmt = $pdo->prepare("
        SELECT id, nome, escudo, estado
        FROM times
        WHERE id BETWEEN 1 AND 265
          AND estado IN (
              'AC','AL','AP','AM','BA','CE','DF','ES','GO',
              'MA','MT','MS','MG','PA','PB','PR','PE','PI',
              'RJ','RN','RS','RO','RR','SC','SP','SE','TO'
          )
          AND (extinto IS NULL OR extinto = 0)
        ORDER BY id ASC
    ");
    $stmt->execute();
    $clubesTop = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($clubesTop)) {
        $clubeDoDia = $clubesTop[mt_rand(0, count($clubesTop) - 1)];

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

        $stmtTitulos->bindValue(':id_time', $clubeDoDia['id'], PDO::PARAM_INT);
        $stmtTitulos->execute();
        $todosTitulos = $stmtTitulos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($todosTitulos as $titulo) {
            $nomeCompeticao = $titulo['competicao'];
            $ano = (int) $titulo['ano'];

            if (!isset($titulosAgrupados[$nomeCompeticao])) {
                $titulosAgrupados[$nomeCompeticao] = [
                    'anos' => []
                ];
            }

            $titulosAgrupados[$nomeCompeticao]['anos'][] = $ano;
        }
    }
}

/* =========================================
   FUNÇÕES AUXILIARES DE EXIBIÇÃO
   ========================================= */

function pluralizar($quantidade, $singular, $plural)
{
    return $quantidade === 1 ? $singular : $plural;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Clubes por Estado - Futebol Brasileiro</title>

    <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
    <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
    <link rel="stylesheet" href="css-times/times.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>

<?php include '../estrutura/header2.php'; ?>

<main>
    <section class="secao-times">
        <div class="container">

            <!-- Menu Lateral -->
            <aside class="menu-lateral">
                <h2>Regiões</h2>

                <ul>
                    <?php foreach ($regioes as $regiao => $estados): ?>
                        <li>
                            <a 
                                href="times.php?regiao=<?= urlencode($regiao) ?>"
                                class="<?= $regiaoSelecionada === $regiao ? 'ativo' : '' ?>"
                            >
                                <?= htmlspecialchars($regiao) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <!-- Conteúdo Principal -->
            <div class="conteudo-times">

                <h1>Clubes do Futebol Brasileiro</h1>

                <p>
                    O futebol no Brasil é marcado por rivalidades regionais, paixões locais e histórias que atravessam gerações.
                    Aqui você encontra os clubes de todos os estados do país, organizados de forma simples e prática.
                </p>

                <?php if ($regiaoSelecionada): ?>

                    <?php if ($regiaoSelecionada === 'Times Extintos'): ?>
                        <h2>Clubes Extintos por Estado</h2>
                        <p class="texto-apoio">
                            Selecione um estado para visualizar os clubes extintos cadastrados.
                        </p>
                    <?php else: ?>
                        <h2>Estados da Região <?= htmlspecialchars($regiaoSelecionada) ?></h2>
                    <?php endif; ?>

                    <div class="lista-estados">
                        <ul>
                            <?php foreach ($estadosPorRegiao as $uf): ?>
                                <?php
                                    $urlEstado = 'times_estado.php?uf=' . urlencode($uf);

                                    if ($regiaoSelecionada === 'Times Extintos') {
                                        $urlEstado .= '&extintos=1';
                                    }
                                ?>

                                <li>
                                    <a href="<?= htmlspecialchars($urlEstado) ?>">
                                        <?= htmlspecialchars($uf) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <?php if ($regiaoInfo): ?>
                        <?php
                            $mapaRegiao = strtolower(str_replace(' ', '-', $regiaoSelecionada));
                            $mapaPath = "../assets/images/{$mapaRegiao}.png";
                        ?>

                        <div class="card-regiao-info">
                            <div class="info-texto">
                                <h3><?= htmlspecialchars($regiaoSelecionada) ?></h3>

                                <p>
                                    <?= $regiaoInfo['estados'] ?>
                                    <?= pluralizar($regiaoInfo['estados'], 'estado', 'estados') ?>
                                </p>

                                <p>
                                    <?= $regiaoInfo['clubes_ativos'] ?>
                                    <?= pluralizar($regiaoInfo['clubes_ativos'], 'clube ranquiado', 'clubes ranquiados') ?>
                                </p>

                                <?php if ($regiaoInfo['libertadores'] > 0): ?>
                                    <p>
                                        <?= $regiaoInfo['libertadores'] ?>
                                        <?= pluralizar($regiaoInfo['libertadores'], 'título da Copa Libertadores', 'títulos da Copa Libertadores') ?>
                                    </p>
                                <?php endif; ?>

                                <?php if ($regiaoInfo['brasileirao'] > 0): ?>
                                    <p>
                                        <?= $regiaoInfo['brasileirao'] ?>
                                        <?= pluralizar($regiaoInfo['brasileirao'], 'título do Campeonato Brasileiro', 'títulos do Campeonato Brasileiro') ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="info-mapa">
                                <img
                                    src="<?= htmlspecialchars($mapaPath) ?>"
                                    alt="Mapa da Região <?= htmlspecialchars($regiaoSelecionada) ?>"
                                    loading="lazy"
                                    onerror="this.style.display='none';"
                                >
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>

                    <div class="conteudo-inicial">
                        <div class="instrucao">
                            <p>
                                Escolha uma região no menu ao lado para ver os estados disponíveis.
                            </p>
                        </div>

                        <div class="time-do-dia">
                            <?php if ($clubeDoDia): ?>

                                <div class="clube-card-destaque">
                                    <h2>Time do Dia</h2>

                                    <div class="clube-logo-grande">
                                        <?php if (!empty($clubeDoDia['escudo'])): ?>
                                            <img
                                                src="<?= htmlspecialchars('../' . ltrim($clubeDoDia['escudo'], '/')) ?>"
                                                alt="Escudo de <?= htmlspecialchars($clubeDoDia['nome']) ?>"
                                                onerror="this.onerror=null; this.src='../assets/images/escudo_padrao.png';"
                                            >
                                        <?php else: ?>
                                            <img
                                                src="../assets/images/escudo_padrao.png"
                                                alt="Escudo padrão"
                                            >
                                        <?php endif; ?>

                                        <div>
                                            <h3><?= htmlspecialchars($clubeDoDia['nome']) ?></h3>
                                            <p><?= htmlspecialchars($clubeDoDia['estado']) ?></p>
                                        </div>
                                    </div>

                                    <div class="clube-titulos-detalhados">
                                        <?php if (!empty($titulosAgrupados)): ?>

                                            <?php foreach ($titulosAgrupados as $nomeCompeticao => $dados): ?>
                                                <?php
                                                    $anos = $dados['anos'];
                                                    sort($anos);

                                                    $quantidade = count($anos);

                                                    if ($quantidade === 1) {
                                                        $textoTitulo = "1 – " . htmlspecialchars($nomeCompeticao) . " (" . $anos[0] . ")";
                                                    } else {
                                                        $nomePlural = strpos($nomeCompeticao, 'Campeonato ') === 0
                                                            ? 'Campeonatos ' . substr($nomeCompeticao, 11)
                                                            : $nomeCompeticao;

                                                        $textoTitulo = $quantidade . " – " . htmlspecialchars($nomePlural) . " (" . implode(', ', $anos) . ")";
                                                    }
                                                ?>

                                                <div class="titulo-item">
                                                    <?= $textoTitulo ?>
                                                </div>
                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <div class="titulo-item">
                                                <em>Nenhum título registrado</em>
                                            </div>

                                        <?php endif; ?>
                                    </div>

                                    <a 
                                        href="times_estado.php?uf=<?= urlencode($clubeDoDia['estado']) ?>" 
                                        class="botao-secundario"
                                    >
                                        Ver clubes de <?= htmlspecialchars($clubeDoDia['estado']) ?>
                                    </a>
                                </div>

                            <?php else: ?>

                                <p>Nenhum clube disponível para o Time do Dia.</p>

                            <?php endif; ?>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </section>
</main>

<?php include '../estrutura/footer2.php'; ?>

</body>
</html>