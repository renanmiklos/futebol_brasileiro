<?php
$host = 'localhost';
$dbname = 'futebol';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        INSERT INTO temporadas (id_competicao, ano, descricao)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $_POST['id_competicao'],
        $_POST['ano'],
        $_POST['descricao']
    ]);

    echo "✅ Temporada inserida com sucesso.";

} catch (PDOException $e) {
    echo "❌ Erro ao inserir temporada: " . $e->getMessage();
}
?>
