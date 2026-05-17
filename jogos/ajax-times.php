<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../estrutura/conexaodb.php';

$competicaoId = $_GET['competicao'] ?? null;

if (!$competicaoId) {
    echo json_encode([]);
    exit;
}

// Caso especial: "Todos os Internacionais"
if ($competicaoId === 'internacionais') {
    $stmt = $pdo->prepare("
        SELECT DISTINCT t.id, t.nome
        FROM times t
        INNER JOIN jogos j ON (t.id = j.id_time1 OR t.id = j.id_time2)
        INNER JOIN temporadas temp ON temp.id = j.id_temporada
        INNER JOIN competicoes c ON c.id = temp.id_competicao
        WHERE c.tipo = 'Internacional'
        ORDER BY t.nome
    ");
    $stmt->execute();
    $times = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($times);
    exit;
}

// Caso normal: competição específica
if (!is_numeric($competicaoId)) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT DISTINCT t.id, t.nome
    FROM times t
    INNER JOIN jogos j ON (t.id = j.id_time1 OR t.id = j.id_time2)
    INNER JOIN temporadas temp ON temp.id = j.id_temporada
    WHERE temp.id_competicao = ?
    ORDER BY t.nome
");
$stmt->execute([$competicaoId]);
$times = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($times);