<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: ../admin/login.php");
    exit;
}

require_once '../estrutura/conexaodb.php';

// Ativar modo de exceção do PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Feedback flash
    function setFlashMessage($key, $message) {
        $_SESSION[$key] = $message;
    }

    // Processar ações via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'get_temporadas_por_competicao':
                $id = filter_input(INPUT_POST, 'id_competicao', FILTER_VALIDATE_INT);
                if (!$id) exit("<option value=''>Competição inválida</option>");
                $stmt = $pdo->prepare("SELECT id, ano FROM temporadas WHERE id_competicao = ? ORDER BY ano DESC");
                $stmt->execute([$id]);
                echo "<option value=''>Selecione</option>";
                while ($t = $stmt->fetch()) {
                    echo "<option value='{$t['id']}'>{$t['ano']}</option>";
                }
                exit;

            case 'get_fases_por_competicao':
                $id = filter_input(INPUT_POST, 'id_competicao', FILTER_VALIDATE_INT);
                if (!$id) exit("<option value=''>Competição inválida</option>");
                $stmt = $pdo->prepare("SELECT DISTINCT fase FROM pontuacoes_fase WHERE id_competicao = ? ORDER BY fase");
                $stmt->execute([$id]);
                echo "<option value=''>Selecione</option>";
                while ($f = $stmt->fetch()) {
                    $fase = htmlspecialchars($f['fase']);
                    echo "<option value='$fase'>$fase</option>";
                }
                exit;

            case 'inserir_time':
                $stmt = $pdo->prepare("
                    INSERT INTO times (nome, nome_completo, estado, cidade, fundacao, estadio, capacidade, escudo, historia, titulos, extinto)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['nome'],
                    $_POST['nome_completo'],
                    $_POST['estado'],
                    $_POST['cidade'],
                    $_POST['fundacao'],
                    $_POST['estadio'],
                    $_POST['capacidade'] ?: null,
                    $_POST['escudo'] ?: null,
                    $_POST['historia'] ?: null,
                    $_POST['titulos'] ?: null,
                    $_POST['extinto']
                ]);
                setFlashMessage('sucesso', "Time adicionado com sucesso!");
                break;

            case 'inserir_competicao':
                $stmt = $pdo->prepare("INSERT INTO competicoes (nome, slug, tipo, amistoso) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_POST['nome'], $_POST['slug'], $_POST['tipo'], $_POST['amistoso']]);
                setFlashMessage('sucesso', "Competição adicionada com sucesso!");
                break;

            case 'inserir_temporada':
                $stmt = $pdo->prepare("INSERT INTO temporadas (id_competicao, ano, descricao) VALUES (?, ?, ?)");
                $stmt->execute([$_POST['id_competicao'], $_POST['ano'], $_POST['descricao'] ?: null]);
                setFlashMessage('sucesso', "Temporada adicionada com sucesso!");
                break;

            case 'inserir_pontuacao':
                $stmt = $pdo->prepare("INSERT INTO pontuacoes_fase (id_competicao, fase, pontos) VALUES (?, ?, ?)");
                $stmt->execute([$_POST['id_competicao'], $_POST['fase'], $_POST['pontos']]);
                setFlashMessage('sucesso', "Pontuação salva com sucesso!");
                break;

            case 'inserir_classificacao':
                $id_temporada = filter_input(INPUT_POST, 'id_temporada', FILTER_VALIDATE_INT);
                $id_time = filter_input(INPUT_POST, 'id_time', FILTER_VALIDATE_INT);
                $fase = $_POST['fase'];
                $nacional = $_POST['nacional'];

                if (!$id_temporada || !$id_time) {
                    throw new Exception("ID Temporada ou ID Time inválido.");
                }

                $stmt = $pdo->prepare("SELECT id_competicao FROM temporadas WHERE id = ?");
                $stmt->execute([$id_temporada]);
                $comp = $stmt->fetch();

                if (!$comp) {
                    throw new Exception("Temporada não encontrada.");
                }

                $id_competicao = $comp['id_competicao'];

                $stmt = $pdo->prepare("SELECT pontos FROM pontuacoes_fase WHERE id_competicao = ? AND fase = ?");
                $stmt->execute([$id_competicao, $fase]);
                $pont = $stmt->fetch();
                $pontos = $pont ? $pont['pontos'] : 0;

                $stmt = $pdo->prepare("INSERT INTO classificacao (id_temporada, id_time, fase, nacional, pontos) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$id_temporada, $id_time, $fase, $nacional, $pontos]);
                setFlashMessage('sucesso', "Classificação adicionada com sucesso!");
                break;

            case 'corrigir_classificacao':
                $corrigidos = 0;
                $stmt = $pdo->query("SELECT c.id, c.id_temporada, c.fase, t.id_competicao FROM classificacao c JOIN temporadas t ON t.id = c.id_temporada");
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
                        $corrigidos++;
                    }
                }
                setFlashMessage('sucesso', "$corrigidos classificações corrigidas com sucesso.");
                break;
        }

        header("Location: admin.php");
        exit;
    }

    // Buscar todos os times cadastrados
    $listaTimes = $pdo->query("SELECT id, nome, estado, extinto FROM times ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Buscar todas as competições cadastradas
    $listaCompeticoes = $pdo->query("SELECT id, nome, tipo, amistoso FROM competicoes ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Resumo de temporadas
    $temporadasResumo = $pdo->query("
        SELECT 
            t.id_competicao,
            c.nome AS nome_competicao,
            MIN(t.ano) AS ano_inicio,
            MAX(t.ano) AS ano_fim,
            COUNT(*) AS total_temporadas
        FROM temporadas t
        INNER JOIN competicoes c ON c.id = t.id_competicao
        GROUP BY t.id_competicao
        ORDER BY c.nome ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Pontuações por competição (única consulta para evitar queries no loop)
    $pontuacoesPorCompeticao = $pdo->query("
        SELECT id_competicao, fase FROM pontuacoes_fase
    ")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

    // Competições pontuadas e mapeamento de pontuações
    $competicoesPontuadas = $pdo->query("SELECT id, nome FROM competicoes ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    $dadosPontuacoes = $pdo->query("SELECT id_competicao, fase, pontos FROM pontuacoes_fase")->fetchAll(PDO::FETCH_ASSOC);
    $pontuacoesMap = [];
    foreach ($dadosPontuacoes as $linha) {
        $pontuacoesMap[$linha['id_competicao']][$linha['fase']] = $linha['pontos'];
    }

} catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}