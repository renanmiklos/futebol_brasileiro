<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

function traduzFase($fase) {
    $fases = [
        'Camp' => 'Campeão',
        'Vice' => 'Vice-campeão',
        'SF'   => 'Semifinal',
        'QF'   => 'Quartas de Final',
        'OF'   => 'Oitavas de Final',
        '4F'   => 'Quarta Fase',
        '16avos'   => '16 avos',
        '3F'   => 'Terceira Fase',
        '32avos'   => '32 avos',
        '2F'   => 'Segunda Fase',
        '64avos'   => '64 avos',
        '1F'   => 'Primeira Fase',
        'Principal' => 'Principal',
        'Regional' => 'Regional',
        'Eliminator' => 'Eliminatória',
        'Grupo'=> 'Fase de Grupos',
        'Pre3' => 'Pré 3',
        'Pre2' => 'Pré 2',
        'Pre1' => 'Pré 1',
        'Pre' => 'Pré',
        'Reb'  => 'Rebaixado',
        '1' => '1º',
        '2' => '2º',
        '3º' => '3º',
        '4º' => '4º',
        '5º' => '5º',
        '6º' => '6º',
        '7º' => '7º',
        '8º' => '8º',
        '9º' => '9º',
        '10º' => '10º',
        '11º' => '11º',
        '12º' => '12º',
        '13º' => '13º',
        '14º' => '14º',
        '15º' => '15º',
        '16º' => '16º',
        '17º' => '17º',
        '18º' => '18º',
        '19º' => '19º',
        '20º' => '20º',
        '21º' => '21º',
        '22º' => '22º',
        '23º' => '23º',
        '24º' => '24º',
        '25º' => '25º'
    ];
    return $fases[$fase] ?? $fase;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_competicao = isset($_GET['id_competicao']) ? intval($_GET['id_competicao']) : 0;
    $ano = isset($_GET['ano']) ? intval($_GET['ano']) : 0;

    $stmtComp = $pdo->prepare("SELECT * FROM competicoes WHERE id = ?");
    $stmtComp->execute([$id_competicao]);
    $competicao = $stmtComp->fetch(PDO::FETCH_ASSOC);

    $stmtTemp = $pdo->prepare("SELECT * FROM temporadas WHERE id_competicao = ? AND ano = ?");
    $stmtTemp->execute([$id_competicao, $ano]);
    $temporada = $stmtTemp->fetch(PDO::FETCH_ASSOC);

    if (!$competicao || !$temporada) {
        die("Temporada não encontrada.");
    }

    $stmtClass = $pdo->prepare("
      SELECT c.fase, t.nome
      FROM classificacao c
      JOIN times t ON c.id_time = t.id
      WHERE c.id_temporada = ? AND c.nacional = 1
      ORDER BY 
        CASE c.fase
          WHEN 'Camp' THEN 1
          WHEN 'Vice' THEN 2
          WHEN 'SF' THEN 3
          WHEN 'QF' THEN 4
          WHEN 'OF' THEN 5
          WHEN '4F' THEN 6
          WHEN '3F' THEN 7
          WHEN '2F' THEN 8
          WHEN '1F' THEN 9
          WHEN 'Grupo' THEN 10
          WHEN 'Pré3' THEN 11
          WHEN 'Pré2' THEN 12
          WHEN 'Pré1' THEN 13
          WHEN 'Reb' THEN 99
          ELSE 100
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
  <?php include 'includes/header.php'; ?>

  <main>
    <section class="secao-temporada">
      <div class="container">
        <h1><?= htmlspecialchars($competicao['nome']) ?> – <?= $ano ?></h1>

        <?php if (!empty($temporada['descricao'])): ?>
          <div class="descricao">
            <p><?= nl2br(htmlspecialchars($temporada['descricao'])) ?></p>
          </div>
        <?php endif; ?>

        <h2>Classificação dos clubes brasileiros</h2>

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
                  <td><?= htmlspecialchars($linha['nome']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>Nenhum clube brasileiro classificado nessa edição.</p>
        <?php endif; ?>
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
