<?php
$pdo = new PDO("mysql:host=localhost;dbname=futebol;charset=utf8", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Busca todas as classificações
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
  }
}

echo "Classificações corrigidas com sucesso.";
