<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../estrutura/conexaodb.php';

$competicaoId = $_GET['competicao'] ?? null;
$timeId = $_GET['time'] ?? null;

if (!$timeId || !is_numeric($timeId)) {
    echo json_encode(['erro' => 'Time inválido.']);
    exit;
}

// Verificar se o time existe
$stmt_check = $pdo->prepare("SELECT id, nome FROM times WHERE id = ?");
$stmt_check->execute([$timeId]);
$timeInfo = $stmt_check->fetch();

if (!$timeInfo) {
    echo json_encode(['erro' => 'Time não encontrado.']);
    exit;
}

// ===========================================
// MODO 1: "Todos os Internacionais"
// ===========================================
if ($competicaoId === 'internacionais') {
    $sql = "
        SELECT
            CASE 
                WHEN j.id_time1 = :time_id THEN COALESCE(t2.nome, j.nome_time2)
                ELSE COALESCE(t1.nome, j.nome_time1)
            END AS rival_nome,
            CASE 
                WHEN j.id_time1 = :time_id THEN t2.escudo
                ELSE t1.escudo
            END AS rival_escudo,
            COUNT(*) AS jogos,
            SUM(
                CASE 
                    WHEN (j.id_time1 = :time_id AND j.gols_time1 > j.gols_time2) 
                      OR (j.id_time2 = :time_id AND j.gols_time2 > j.gols_time1)
                    THEN 1 ELSE 0 END
            ) AS vitorias,
            SUM(CASE WHEN j.gols_time1 = j.gols_time2 THEN 1 ELSE 0 END) AS empates,
            SUM(
                CASE 
                    WHEN (j.id_time1 = :time_id AND j.gols_time1 < j.gols_time2) 
                      OR (j.id_time2 = :time_id AND j.gols_time2 < j.gols_time1)
                    THEN 1 ELSE 0 END
            ) AS derrotas,
            SUM(
                CASE 
                    WHEN j.id_time1 = :time_id THEN COALESCE(j.gols_time1, 0)
                    ELSE COALESCE(j.gols_time2, 0)
                END
            ) AS gols_pro,
            SUM(
                CASE 
                    WHEN j.id_time1 = :time_id THEN COALESCE(j.gols_time2, 0)
                    ELSE COALESCE(j.gols_time1, 0)
                END
            ) AS gols_contra,
            MAX(j.data) AS ultima_data
        FROM jogos j
        INNER JOIN temporadas t ON t.id = j.id_temporada
        INNER JOIN competicoes c ON c.id = t.id_competicao
        LEFT JOIN times t1 ON t1.id = j.id_time1
        LEFT JOIN times t2 ON t2.id = j.id_time2
        WHERE 
            c.tipo = 'Internacional'
            AND (j.id_time1 = :time_id OR j.id_time2 = :time_id)
            AND j.gols_time1 IS NOT NULL
            AND j.gols_time2 IS NOT NULL
        GROUP BY rival_nome, rival_escudo
        ORDER BY rival_nome
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':time_id' => $timeId]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultados);
    exit;
}

// ===========================================
// MODO 2: Competição específica
// ===========================================
if (!is_numeric($competicaoId)) {
    echo json_encode(['erro' => 'Competição inválida.']);
    exit;
}

$sql = "
    SELECT
        CASE 
            WHEN j.id_time1 = :time_id THEN COALESCE(t2.nome, j.nome_time2)
            ELSE COALESCE(t1.nome, j.nome_time1)
        END AS rival_nome,
        CASE 
            WHEN j.id_time1 = :time_id THEN t2.escudo
            ELSE t1.escudo
        END AS rival_escudo,
        COUNT(*) AS jogos,
        SUM(
            CASE 
                WHEN (j.id_time1 = :time_id AND j.gols_time1 > j.gols_time2) 
                  OR (j.id_time2 = :time_id AND j.gols_time2 > j.gols_time1)
                THEN 1 ELSE 0 END
        ) AS vitorias,
        SUM(CASE WHEN j.gols_time1 = j.gols_time2 THEN 1 ELSE 0 END) AS empates,
        SUM(
            CASE 
                WHEN (j.id_time1 = :time_id AND j.gols_time1 < j.gols_time2) 
                  OR (j.id_time2 = :time_id AND j.gols_time2 < j.gols_time1)
                THEN 1 ELSE 0 END
        ) AS derrotas,
        SUM(
            CASE 
                WHEN j.id_time1 = :time_id THEN COALESCE(j.gols_time1, 0)
                ELSE COALESCE(j.gols_time2, 0)
            END
        ) AS gols_pro,
        SUM(
            CASE 
                WHEN j.id_time1 = :time_id THEN COALESCE(j.gols_time2, 0)
                ELSE COALESCE(j.gols_time1, 0)
            END
        ) AS gols_contra,
        MAX(j.data) AS ultima_data
    FROM jogos j
    INNER JOIN temporadas t ON t.id = j.id_temporada
    LEFT JOIN times t1 ON t1.id = j.id_time1
    LEFT JOIN times t2 ON t2.id = j.id_time2
    WHERE 
        t.id_competicao = :comp_id
        AND (j.id_time1 = :time_id OR j.id_time2 = :time_id)
        AND j.gols_time1 IS NOT NULL
        AND j.gols_time2 IS NOT NULL
    GROUP BY rival_nome, rival_escudo
    ORDER BY rival_nome
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':comp_id' => $competicaoId,
    ':time_id' => $timeId
]);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($resultados);