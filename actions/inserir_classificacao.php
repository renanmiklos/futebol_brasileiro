<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se todos os parâmetros foram recebidos
    if (!isset($_GET['id_temporada'], $_GET['id_time'], $_GET['fase'], $_GET['nacional'])) {
        die("Parâmetros incompletos.");
    }

    $id_temporada = intval($_GET['id_temporada']);
    $id_time = intval($_GET['id_time']);
    $fase = $_GET['fase'];
    $nacional = intval($_GET['nacional']);

    // Descobre qual é a competição da temporada
    $stmt = $pdo->prepare("SELECT id_competicao FROM temporadas WHERE id = ?");
    $stmt->execute([$id_temporada]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("❌ Temporada não encontrada.");
    }

    $id_competicao = $row['id_competicao'];

    // Busca a pontuação da fase para essa competição
    $stmtPontos = $pdo->prepare("SELECT pontos FROM pontuacoes_fase WHERE id_competicao = ? AND fase = ?");
    $stmtPontos->execute([$id_competicao, $fase]);
    $pontoRow = $stmtPontos->fetch(PDO::FETCH_ASSOC);

    $pontos = $pontoRow ? intval($pontoRow['pontos']) : 0;

    // Insere o clube na classificação com pontuação
    $stmtInsert = $pdo->prepare("
        INSERT INTO classificacao (id_temporada, id_time, fase, nacional, pontos)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([$id_temporada, $id_time, $fase, $nacional, $pontos]);

    echo "✅ Classificação inserida com sucesso. Pontos atribuídos: $pontos";

} catch (PDOException $e) {
    echo "❌ Erro ao inserir classificação: " . $e->getMessage();
}
?>
