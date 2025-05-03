<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
  header("Location: login.php");
  exit;
}

$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Inserir time
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'inserir_time') {
  $stmt = $pdo->prepare("
    INSERT INTO times (nome, nome_completo, estado, cidade, fundacao, estadio, capacidade, escudo, historia, titulos, extinto)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $_POST['nome'],
    $_POST['nome_completo'],
    $_POST['estado'],
    $_POST['cidade'],
    $_POST['fundacao'],
    $_POST['estadio'],
    $_POST['capacidade'],
    $_POST['escudo'],
    $_POST['historia'],
    $_POST['titulos'],
    $_POST['extinto']
  ]);
}

// Buscar todos os times cadastrados
$listaTimes = $pdo->query("SELECT id, nome, estado, extinto FROM times ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Inserir competição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'inserir_competicao') {
  $stmt = $pdo->prepare("INSERT INTO competicoes (nome, slug, tipo, amistoso) VALUES (?, ?, ?, ?)");
  $stmt->execute([$_POST['nome'], $_POST['slug'], $_POST['tipo'], $_POST['amistoso']]);
}

// Buscar as competições cadastradas
$listaCompeticoes = $pdo->query("SELECT id, nome, tipo, amistoso FROM competicoes ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Inserir temporada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'inserir_temporada') {
  $stmt = $pdo->prepare("INSERT INTO temporadas (id_competicao, ano, descricao) VALUES (?, ?, ?)");
  $stmt->execute([$_POST['id_competicao'], $_POST['ano'], $_POST['descricao']]);
}

// Mostrar as temporadas
$temporadasResumo = $pdo->query("
  SELECT 
    t.id_competicao,
    c.nome AS nome_competicao,
    MIN(t.ano) AS ano_inicio,
    MAX(t.ano) AS ano_fim,
    COUNT(*) AS total_temporadas
  FROM temporadas t
  INNER JOIN competicoes c ON c.id = t.id_competicao
  GROUP BY t.id_competicao
  ORDER BY c.nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Inserir pontuação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'inserir_pontuacao') {
  $stmt = $pdo->prepare("INSERT INTO pontuacoes_fase (id_competicao, fase, pontos) VALUES (?, ?, ?)");
  $stmt->execute([$_POST['id_competicao'], $_POST['fase'], $_POST['pontos']]);
}

// Buscar competições com pontuações
$competicoesPontuadas = $pdo->query("SELECT id, nome FROM competicoes ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Buscar todas as pontuações em um array [id_competicao][fase] => pontos
$dadosPontuacoes = $pdo->query("SELECT id_competicao, fase, pontos FROM pontuacoes_fase")->fetchAll(PDO::FETCH_ASSOC);
$pontuacoesMap = [];
foreach ($dadosPontuacoes as $linha) {
  $pontuacoesMap[$linha['id_competicao']][$linha['fase']] = $linha['pontos'];
}

// Inserir classificação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'inserir_classificacao') {
  $id_temporada = $_POST['id_temporada'];
  $id_time = $_POST['id_time'];
  $fase = $_POST['fase'];
  $nacional = $_POST['nacional'];

  $stmt = $pdo->prepare("SELECT id_competicao FROM temporadas WHERE id = ?");
  $stmt->execute([$id_temporada]);
  $comp = $stmt->fetch();
  $id_competicao = $comp['id_competicao'];

  $stmt = $pdo->prepare("SELECT pontos FROM pontuacoes_fase WHERE id_competicao = ? AND fase = ?");
  $stmt->execute([$id_competicao, $fase]);
  $pont = $stmt->fetch();
  $pontos = $pont ? $pont['pontos'] : 0;

  $stmt = $pdo->prepare("INSERT INTO classificacao (id_temporada, id_time, fase, nacional, pontos) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$id_temporada, $id_time, $fase, $nacional, $pontos]);
}

// Corrigir classificações existentes (preencher pontos corretamente)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'corrigir_classificacao') {
  $corrigidos = 0;

  $stmt = $pdo->query("
    SELECT c.id, c.id_temporada, c.fase, t.id_competicao
    FROM classificacao c
    JOIN temporadas t ON t.id = c.id_temporada
  ");
  $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($dados as $linha) {
    $id = $linha['id'];
    $id_competicao = $linha['id_competicao'];
    $fase = $linha['fase'];

    $stmt2 = $pdo->prepare("SELECT pontos FROM pontuacoes_fase WHERE id_competicao = ? AND fase = ?");
    $stmt2->execute([$id_competicao, $fase]);
    $ponto = $stmt2->fetchColumn();

    if ($ponto !== false) {
      $update = $pdo->prepare("UPDATE classificacao SET pontos = ? WHERE id = ?");
      $update->execute([$ponto, $id]);
      $corrigidos++;
    }
  }

  echo "<p style='text-align:center;color:#FFD700;'>$corrigidos classificações corrigidas com sucesso.</p>";
}

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel Administrativo</title>
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<header class="site-header">
        <div class="header-container">
            <div class="logo-area">
                <img src="assets/images/logo.png" alt="Logo" class="logo">
                <span class="logo-text">Futebol Brasileiro</span>
            </div>
            <div class="menu-area">
                <form class="search-bar" action="busca.php" method="GET">
                    <input type="text" name="query" placeholder="Buscar...">
                    <button type="submit">🔍</button>
                </form>
                <nav class="menu-principal">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="noticias.php">Notícias</a></li>
                        <li><a href="historia.php">História</a></li>
                        <li><a href="times.php">Times</a></li>
                        <li><a href="campeonatos.php">Campeonatos</a></li>
                        <li><a href="ranking.php">Ranking</a></li>
                        <li><a href="artigos.php">Artigos</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

  <h1>Painel de Administração</h1>
  <div class="painel-bloco">
    <div class="painel-coluna">
        <h2>Adicionar Time</h2>
        <form method="POST">
        <input type="hidden" name="acao" value="inserir_time">
        <label>Nome:</label><input type="text" name="nome" required>
        <label>Nome Completo:</label><input type="text" name="nome_completo" required>
        <label>Estado:</label><input type="text" name="estado" required>
        <label>Cidade:</label><input type="text" name="cidade" required>
        <label>Fundação:</label><input type="date" name="fundacao" required>
        <label>Estádio:</label><input type="text" name="estadio">
        <label>Capacidade:</label><input type="number" name="capacidade">
        <label>Escudo (URL):</label><input type="text" name="escudo">
        <label>História:</label><textarea name="historia"></textarea>
        <label>Títulos:</label><textarea name="titulos"></textarea>
        <label>Extinto?</label>
        <select name="extinto"><option value="0">Não</option><option value="1">Sim</option></select>
        <button type="submit">Salvar Time</button>
        </form>
    </div>

    <div class="painel-coluna">
        <h2>Times Cadastrados</h2>
        <div class="com-scroll-1">
        <table class="tabela-listagem">
            <thead>
            <tr><th>ID</th><th>Nome</th><th>Estado</th><th>Extinto</th></tr>
            </thead>
            <tbody>
            <?php foreach ($listaTimes as $time): ?>
                <tr>
                <td><?= $time['id'] ?></td>
                <td><?= htmlspecialchars($time['nome']) ?></td>
                <td><?= htmlspecialchars($time['estado']) ?></td>
                <td><?= $time['extinto'] ? 'Sim' : 'Não' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
  </div>

  <div class="painel-bloco">
    <div class="painel-coluna">
        <h2>Adicionar Competição</h2>
        <form method="POST">
        <input type="hidden" name="acao" value="inserir_competicao">
        <label>Nome:</label><input type="text" name="nome" required>
        <label>Slug:</label><input type="text" name="slug" required>
        <label>Tipo:</label><input type="text" name="tipo" required>
        <label>Amistosa?</label>
        <select name="amistoso"><option value="0">Não</option><option value="1">Sim</option></select>
        <button type="submit">Salvar Competição</button>
        </form>
    </div>

    <div class="painel-coluna">
        <h2>Competições Cadastradas</h2>
        <div class="com-scroll">
        <table class="tabela-listagem">
            <thead>
            <tr><th>ID</th><th>Nome</th><th>Tipo</th><th>Amistosa</th></tr>
            </thead>
            <tbody>
            <?php foreach ($listaCompeticoes as $comp): ?>
                <tr>
                <td><?= $comp['id'] ?></td>
                <td><?= htmlspecialchars($comp['nome']) ?></td>
                <td><?= htmlspecialchars($comp['tipo']) ?></td>
                <td><?= $comp['amistoso'] ? 'Sim' : 'Não' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
  </div>

  <div class="painel-bloco">
    <div class="painel-coluna">
        <h2>Adicionar Temporada</h2>
        <form method="POST">
        <input type="hidden" name="acao" value="inserir_temporada">
        <label>ID Competição:</label><input type="number" name="id_competicao" required>
        <label>Ano:</label><input type="number" name="ano" required>
        <label>Descrição:</label><textarea name="descricao"></textarea>
        <button type="submit">Salvar Temporada</button>
        </form>
    </div>

    <div class="painel-coluna">
        <h2>Competições e Temporadas</h2>
        <div class="com-scroll">
        <table class="tabela-listagem">
            <thead>
            <tr><th>ID Competição</th><th>Nome</th><th>Período</th><th>Total</th></tr>
            </thead>
            <tbody>
            <?php foreach ($temporadasResumo as $resumo): ?>
                <tr>
                <td><?= $resumo['id_competicao'] ?></td>
                <td><?= htmlspecialchars($resumo['nome_competicao']) ?></td>
                <td><?= $resumo['ano_inicio'] ?> – <?= $resumo['ano_fim'] == date('Y') ? '...' : $resumo['ano_fim'] ?></td>
                <td><?= $resumo['total_temporadas'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
  </div>

  <div class="painel-bloco">
    <div class="painel-coluna">
        <h2>Adicionar Pontuação por Fase</h2>
        <form method="POST">
            <input type="hidden" name="acao" value="inserir_pontuacao">
            <label>ID Competição:</label><input type="number" name="id_competicao" required>
            <label>Fase:</label><input type="text" name="fase" required>
            <label>Pontos:</label><input type="number" name="pontos" required>
            <button type="submit">Salvar Pontuação</button>
        </form>
    </div>

    <div class="painel-coluna">
        <h2>Pontuações por Competição</h2>
        <div class="com-scroll">

        <?php
        $ordemFases = [
          'Camp', 'Vice', 'SF', 'QF', 'OF', '4F', '16avos', '3F', '32 avos', '2F', '64avos', '1F',
          'Principal', 'Grupo', 'Regional', 'Eliminator','1º','2º',
          '3º', '4º', '5º', '6º', '7º', '8º', '9º', '10º',
          '11º', '12º', '13º', '14º', '15º', '16º', '17º', '18º', '19º', '20º',
          '21º', '22º', '23º', '24º', '25º',
          'Pre3', 'Pre2', 'Pre1', 'Pre', 'Reb'
        ];
        ?>

        <?php foreach ($competicoesPontuadas as $comp): ?>
            <?php
            $fasesComp = $pdo->prepare("SELECT fase FROM pontuacoes_fase WHERE id_competicao = ?");
            $fasesComp->execute([$comp['id']]);
            $fasesBrutas = $fasesComp->fetchAll(PDO::FETCH_COLUMN);

            if (!$fasesBrutas) continue;

            $fasesOrdenadas = array_filter($ordemFases, function($fase) use ($fasesBrutas) {
                return in_array($fase, $fasesBrutas);
            });
            ?>

            <h3 style="margin-top: 20px;"><?= htmlspecialchars($comp['nome']) ?></h3>
            <table class="tabela-listagem">
                <tr>
                    <th>Competição / Fase</th>
                    <?php foreach ($fasesOrdenadas as $fase): ?>
                        <th><?= htmlspecialchars($fase) ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($comp['nome']) ?></td>
                    <?php foreach ($fasesOrdenadas as $fase): ?>
                        <td><?= $pontuacoesMap[$comp['id']][$fase] ?? '-' ?></td>
                    <?php endforeach; ?>
                </tr>
            </table>

        <?php endforeach; ?>

        </div>
    </div>
  </div>

  <form method="POST">
    <h2>Adicionar Classificação</h2>
    <input type="hidden" name="acao" value="inserir_classificacao">
    <label>ID Temporada:</label><input type="number" name="id_temporada" required>
    <label>ID Clube:</label><input type="number" name="id_time" required>
    <label>Fase:</label><input type="text" name="fase" required>
    <label>Brasileiro?</label>
    <select name="nacional">
        <option value="1">Sim</option>
        <option value="0">Não</option>
    </select>
    <button type="submit">Salvar Classificação</button>
  </form>

  <div style="text-align: center; margin-top: 40px;">
    <form method="POST">
      <input type="hidden" name="acao" value="corrigir_classificacao">
      <button type="submit" style="background-color: darkred;">Corrigir Pontuação das Classificações</button>
    </form>
  </div>


  <p style="text-align:center;"><a href="logout.php" style="color:#FFD700;">Sair do Painel</a></p>
  
</body>
</html>
