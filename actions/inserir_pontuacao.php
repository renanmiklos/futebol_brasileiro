<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        INSERT INTO pontuacoes_fase (id_competicao, fase, pontos)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $_POST['id_competicao'],
        $_POST['fase'],
        $_POST['pontos']
    ]);

    echo "✅ Pontuação da fase inserida com sucesso.";

} catch (PDOException $e) {
    echo "❌ Erro ao inserir pontuação: " . $e->getMessage();
}
?>
