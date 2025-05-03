<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        INSERT INTO competicoes (nome, slug, tipo, amistoso)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['nome'],
        $_POST['slug'],
        $_POST['tipo'],
        $_POST['amistoso']
    ]);

    echo "✅ Competição inserida com sucesso.";

} catch (PDOException $e) {
    echo "❌ Erro ao inserir competição: " . $e->getMessage();
}
?>
