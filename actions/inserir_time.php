<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        INSERT INTO times (
            nome, nome_completo, estado, cidade, fundacao, estadio, capacidade, escudo, historia, titulos, extinto
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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

    echo "✅ Time inserido com sucesso.";

} catch (PDOException $e) {
    echo "❌ Erro ao inserir time: " . $e->getMessage();
}
?>
