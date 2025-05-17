<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

function traduzFase($fase) {
    $fases = [
        'Camp' => 'Campeão', 'Vice' => 'Vice-campeão', 'SF'   => 'Semifinal',
        'QF'   => 'Quartas de Final',  'OF'   => 'Oitavas de Final',  '4F'   => 'Quarta Fase',
        '16avos'   => '16 avos',  '3F'   => 'Terceira Fase',   '32avos'   => '32 avos',
        '2F'   => 'Segunda Fase',  '64avos'   => '64 avos',  '1F'   => 'Primeira Fase',
        'Principal' => 'Principal', 'Regional' => 'Regional', 'Eliminator' => 'Eliminatória',
        'Grupo'=> 'Fase de Grupos', 'Pre3' => 'Pré 3', 'Pre2' => 'Pré 2',    'Pre1' => 'Pré 1',
        'Pre' => 'Pré',  'Reb'  => 'Rebaixado', '1' => '1º', '2' => '2º',   '3º' => '3º',
        '4º' => '4º',  '5º' => '5º',  '6º' => '6º',  '7º' => '7º', '8º' => '8º', '9º' => '9º',
        '10º' => '10º', '11º' => '11º', '12º' => '12º',  '13º' => '13º', '14º' => '14º',
        '15º' => '15º', '16º' => '16º',  '17º' => '17º', '18º' => '18º', '19º' => '19º',
        '20º' => '20º', '21º' => '21º',  '22º' => '22º','23º' => '23º', '24º' => '24º', '25º' => '25º'
    ];
    return $fases[$fase] ?? $fase;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_competicao = isset($_GET['id_competicao']) ? intval($_GET['id_competicao']) : 0;
    $ano = isset($_GET['ano']) ? intval($_GET['ano']) : 0;

    // Buscar competição
    $stmtComp = $pdo->prepare("SELECT * FROM competicoes WHERE id = ?");
    $stmtComp->execute([$id_competicao]);
    $competicao = $stmtComp->fetch(PDO::FETCH_ASSOC);

    // Buscar temporada
    $stmtTemp = $pdo->prepare("SELECT * FROM temporadas WHERE id_competicao = ? AND ano = ?");
    $stmtTemp->execute([$id_competicao, $ano]);
    $temporada = $stmtTemp->fetch(PDO::FETCH_ASSOC);

    if (!$competicao || !$temporada) {
        die("Temporada não encontrada.");
    }

    // Buscar foto associada à temporada
    $stmtFoto = $pdo->prepare("SELECT caminho_imagem, titulo FROM fotos WHERE id_temporada = ? ORDER BY data_publicacao DESC LIMIT 1");
    $stmtFoto->execute([$temporada['id']]);
    $foto = $stmtFoto->fetch(PDO::FETCH_ASSOC);

    // Buscar classificação
    $stmtClass = $pdo->prepare("
      SELECT c.fase, t.nome, t.escudo
      FROM classificacao c
      JOIN times t ON c.id_time = t.id
      WHERE c.id_temporada = ? AND c.nacional = 1
      ORDER BY  
        CASE c.fase
          WHEN 'Camp' THEN 1          WHEN '1º' THEN 1          WHEN 'Vice' THEN 2        WHEN '2º' THEN 2
          WHEN '3º' THEN 3            WHEN '4º' THEN 4          WHEN '5º' THEN 5          WHEN '6º' THEN 6
          WHEN '7º' THEN 7            WHEN '8º' THEN 8          WHEN '9º' THEN 9          WHEN '10º' THEN 10
          WHEN '11º' THEN 11          WHEN '12º' THEN 12        WHEN '13º' THEN 13        WHEN '14º' THEN 14
          WHEN '15º' THEN 15          WHEN '16º' THEN 16        WHEN '17º' THEN 17        WHEN '18º' THEN 18
          WHEN '19º' THEN 19          WHEN '20º' THEN 20        WHEN '21º' THEN 21        WHEN '22º' THEN 22
          WHEN '23º' THEN 23          WHEN '24º' THEN 24        WHEN 'SF' THEN 25         WHEN 'QF' THEN 26
          WHEN 'OF' THEN 27           WHEN '4F' THEN 28         WHEN '16avos' THEN 29     WHEN '3F' THEN 30
          WHEN '32avos' THEN 31       WHEN '2F' THEN 32         WHEN '64avos' THEN 33     WHEN '1F' THEN 34
          WHEN 'Principal' THEN 35    WHEN 'Grupo' THEN 36      WHEN 'Regional' THEN 37   WHEN 'Eliminator' THEN 38
          WHEN 'Pre3' THEN 38         WHEN 'Pre2' THEN 39       WHEN 'Pre1' THEN 40       WHEN 'Pre' THEN 41
          WHEN 'Reb' THEN 42          ELSE 99
        END
    ");
    $stmtClass->execute([$temporada['id']]);
    $classificacao = $stmtClass->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= $competicao['nome'] ?> - <?= $ano ?> | Futebol Brasileiro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/temporada.css">
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

  <main>
    <section class="secao-temporada">
      <div class="container">
        <a href="competicao.php?slug=<?= urlencode($competicao['slug']) ?>" class="voltar-link">← Voltar para <?= htmlspecialchars($competicao['nome']) ?></a>
        <h1><?= htmlspecialchars($competicao['nome']) ?> – <?= $ano ?></h1>

        <div class="flex-container">
          <div class="conteudo-esquerdo">
            <!-- Descrição da temporada -->
            <?php if (!empty($temporada['descricao'])): ?>
              <div class="descricao">
                <p><?= nl2br(htmlspecialchars($temporada['descricao'])) ?></p>
              </div>
            <?php endif; ?>
          </div>

          <div class="classificacao-direita">
            <!-- Exibe a foto, se houver -->
            <?php if (!empty($foto)): ?>
              <div class="foto-temporada">
                <img src="<?= htmlspecialchars($foto['caminho_imagem']) ?>" alt="<?= htmlspecialchars($foto['titulo']) ?>" class="imagem-temporada">
                <p class="legenda"><?= htmlspecialchars($foto['titulo']) ?></p>
              </div>
            <?php endif; ?>
            <h2>Classificação</h2>
            <?php if (!empty($classificacao)): ?>
              <table class="tabela-classificacao">
                <thead>
                  <tr>
                    <th>Fase</th>
                    <th>Clube</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($classificacao as $linha): ?>
                    <tr>
                      <td><?= traduzFase($linha['fase']) ?></td>
                      <td>
                        <?php if (!empty($linha['escudo'])): ?>
                          <img src="<?= htmlspecialchars($linha['escudo']) ?>" alt="Escudo de <?= htmlspecialchars($linha['nome']) ?>" class="escudo-clube" style="height: 20px; vertical-align: middle; margin-right: 5px;">
                        <?php endif; ?>
                        <?= htmlspecialchars($linha['nome']) ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p>Nenhum clube brasileiro classificado nessa edição.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="rodape">
  <div class="rodape-container">
    <p>&copy; <?= date('Y') ?> Futebol Brasileiro. Todos os direitos reservados.</p>

    <p style="font-size: 0.9em;">
      <button onclick="mostrarLinkAdmin()" class="btn-link-admin">Área Administrativa</button>
    </p>

    <p id="link-admin-revelado" style="display: none; font-size: 0.8em;">
      <a href="admin.php" class="admin-link" style="color: #FFD700;">Acessar Painel</a>
    </p>
  </div>

  <script>
    function mostrarLinkAdmin() {
      const link = document.getElementById('link-admin-revelado');
      link.style.display = 'block';
    }
  </script>
</footer>

</body>
</html>
