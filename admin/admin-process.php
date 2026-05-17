<?php
/* =========================================
   ADMIN-PROCESS.PHP
   Controlador central do Painel Administrativo
   Futebol Brasileiro
========================================= */

/* =========================================
   INCLUDES DO ADMIN
========================================= */

require_once __DIR__ . '/includes-admin/admin-auth.php';
require_once __DIR__ . '/includes-admin/admin-funcoes.php';
require_once __DIR__ . '/includes-admin/admin-opcoes.php';

/* =========================================
   DEPENDÊNCIAS DO SISTEMA
========================================= */

require_once __DIR__ . '/../estrutura/conexaodb.php';
require_once __DIR__ . '/../estrutura/calcula-pontuacoes.php';

if (!isset($pdo)) {
    die('Erro: Conexão com o banco de dados não estabelecida.');
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* =========================================
   FUNÇÕES ESPECÍFICAS DO PROCESSAMENTO
========================================= */

if (!function_exists('limparNumeroOpcionalAdmin')) {
    function limparNumeroOpcionalAdmin($valor)
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return is_numeric($valor) ? (int)$valor : null;
    }
}

if (!function_exists('boolIntAdmin')) {
    function boolIntAdmin($valor): int
    {
        return (int)((string)$valor === '1');
    }
}

if (!function_exists('obterIdCompeticaoPorTemporadaAdmin')) {
    function obterIdCompeticaoPorTemporadaAdmin(PDO $pdo, int $idTemporada): ?int
    {
        if ($idTemporada <= 0) {
            return null;
        }

        $stmt = $pdo->prepare("
            SELECT id_competicao 
            FROM temporadas 
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$idTemporada]);
        $idCompeticao = $stmt->fetchColumn();

        return $idCompeticao ? (int)$idCompeticao : null;
    }
}

if (!function_exists('calcularPontosClassificacaoAdmin')) {
    function calcularPontosClassificacaoAdmin(PDO $pdo, int $idTemporada, string $fase): int
    {
        $idCompeticao = obterIdCompeticaoPorTemporadaAdmin($pdo, $idTemporada);

        if (!$idCompeticao || $fase === '') {
            return 0;
        }

        if (function_exists('getPontuacaoFinal')) {
            return (int)getPontuacaoFinal($pdo, $idCompeticao, $fase);
        }

        $stmt = $pdo->prepare("
            SELECT pontos 
            FROM pontuacoes_fase 
            WHERE id_competicao = ? 
              AND fase = ?
            LIMIT 1
        ");

        $stmt->execute([$idCompeticao, $fase]);
        $pontos = $stmt->fetchColumn();

        return $pontos !== false ? (int)$pontos : 0;
    }
}

if (!function_exists('redirecionarDepoisFotoAdmin')) {
    function redirecionarDepoisFotoAdmin(): void
    {
        if (isset($_POST['voltar_para']) && $_POST['voltar_para'] === 'competicoes') {
            redirectAdmin('admin-competicoes.php');
        }

        if (isset($_POST['voltar_para']) && $_POST['voltar_para'] === 'temporadas') {
            redirectAdmin('admin-temporadas.php');
        }

        if (!empty($_POST['id_competicao'])) {
            redirectAdmin('admin-competicoes.php');
        }

        redirectAdmin('admin-temporadas.php');
    }
}

if (!function_exists('setMensagemAdminProcess')) {
    function setMensagemAdminProcess(string $mensagem, string $chave = 'sucesso'): void
    {
        setFlashAdmin($chave, $mensagem);
    }
}

/* =========================================
   PROCESSAMENTO POST
========================================= */

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
        $acao = (string)$_POST['acao'];

        switch ($acao) {

            /* =========================================
               AJAX: TEMPORADAS POR COMPETIÇÃO
            ========================================= */

            case 'get_temporadas_por_competicao':
                $idCompeticao = postIntAdmin('id_competicao');

                if (!$idCompeticao) {
                    echo "<option value=''>Competição inválida</option>";
                    exit;
                }

                $stmt = $pdo->prepare("
                    SELECT id, ano 
                    FROM temporadas 
                    WHERE id_competicao = ? 
                    ORDER BY ano DESC
                ");

                $stmt->execute([$idCompeticao]);

                echo "<option value=''>Selecione</option>";

                while ($temporada = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $id = (int)$temporada['id'];
                    $ano = eAdmin($temporada['ano']);
                    echo "<option value='{$id}'>{$ano}</option>";
                }

                exit;

            /* =========================================
               AJAX: FASES POR COMPETIÇÃO
            ========================================= */

            case 'get_fases_por_competicao':
                $idCompeticao = postIntAdmin('id_competicao');

                if (!$idCompeticao) {
                    echo "<option value=''>Competição inválida</option>";
                    exit;
                }

                $stmt = $pdo->prepare("
                    SELECT DISTINCT fase 
                    FROM pontuacoes_fase 
                    WHERE id_competicao = ? 
                    ORDER BY fase ASC
                ");

                $stmt->execute([$idCompeticao]);

                echo "<option value=''>Selecione</option>";

                while ($fase = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $valor = eAdmin($fase['fase']);
                    echo "<option value='{$valor}'>{$valor}</option>";
                }

                exit;

            /* =========================================
               TIMES
            ========================================= */

            case 'inserir_time':
                $colunasTime = [
                    'nome',
                    'nome_completo',
                    'estado',
                    'cidade',
                    'fundacao',
                    'estadio',
                    'capacidade',
                    'escudo',
                    'historia',
                    'titulos',
                    'extinto',
                    'time',
                    'legenda',
                    'extra1',
                    'legenda1',
                    'extra2',
                    'legenda2',
                    'extra3',
                    'legenda3',
                    'extra4',
                    'legenda4',
                    'extra5',
                    'legenda5',
                    'extra6',
                    'legenda6',
                    'extra7',
                    'legenda7',
                    'extra8',
                    'legenda8',
                    'extra9',
                    'legenda9',
                    'extra10',
                    'legenda10'
                ];

                $valores = [];

                foreach ($colunasTime as $coluna) {
                    if ($coluna === 'extinto') {
                        $valores[] = boolIntAdmin(postAdmin('extinto', 0));
                    } elseif ($coluna === 'capacidade') {
                        $valores[] = limparNumeroOpcionalAdmin(postAdmin('capacidade'));
                    } else {
                        $valores[] = postTextoAdmin($coluna);
                    }
                }

                $placeholders = implode(', ', array_fill(0, count($colunasTime), '?'));

                $stmt = $pdo->prepare("
                    INSERT INTO times (" . implode(', ', $colunasTime) . ")
                    VALUES ($placeholders)
                ");

                $stmt->execute($valores);

                setMensagemAdminProcess('Time adicionado com sucesso!');
                redirectAdmin('admin-times.php');

            case 'editar_time':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID do time inválido.');
                }

                $colunasTime = [
                    'nome',
                    'nome_completo',
                    'estado',
                    'cidade',
                    'fundacao',
                    'estadio',
                    'capacidade',
                    'escudo',
                    'historia',
                    'titulos',
                    'extinto',
                    'time',
                    'legenda',
                    'extra1',
                    'legenda1',
                    'extra2',
                    'legenda2',
                    'extra3',
                    'legenda3',
                    'extra4',
                    'legenda4',
                    'extra5',
                    'legenda5',
                    'extra6',
                    'legenda6',
                    'extra7',
                    'legenda7',
                    'extra8',
                    'legenda8',
                    'extra9',
                    'legenda9',
                    'extra10',
                    'legenda10'
                ];

                $sets = [];
                $valores = [];

                foreach ($colunasTime as $coluna) {
                    $sets[] = "{$coluna} = ?";

                    if ($coluna === 'extinto') {
                        $valores[] = boolIntAdmin(postAdmin('extinto', 0));
                    } elseif ($coluna === 'capacidade') {
                        $valores[] = limparNumeroOpcionalAdmin(postAdmin('capacidade'));
                    } else {
                        $valores[] = postTextoAdmin($coluna);
                    }
                }

                $valores[] = $id;

                $stmt = $pdo->prepare("
                    UPDATE times 
                    SET " . implode(', ', $sets) . "
                    WHERE id = ?
                ");

                $stmt->execute($valores);

                setMensagemAdminProcess('Time atualizado com sucesso!');
                redirectAdmin('admin-times.php');

            case 'excluir_time':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID do time inválido.');
                }

                $stmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM classificacao 
                    WHERE id_time = ?
                ");

                $stmt->execute([$id]);

                if ((int)$stmt->fetchColumn() > 0) {
                    setMensagemAdminProcess('Não é possível excluir o time, pois ele está associado a classificações. Remova as classificações primeiro.');
                } else {
                    $stmt = $pdo->prepare("
                        DELETE FROM times 
                        WHERE id = ?
                    ");

                    $stmt->execute([$id]);

                    setMensagemAdminProcess('Time excluído com sucesso!');
                }

                redirectAdmin('admin-times.php');

            case 'get_time':
                $id = postIntAdmin('id');

                if (!$id) {
                    responderJsonAdmin(['erro' => 'ID inválido']);
                }

                $stmt = $pdo->prepare("
                    SELECT * 
                    FROM times 
                    WHERE id = ?
                ");

                $stmt->execute([$id]);
                $time = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$time) {
                    responderJsonAdmin(['erro' => 'Time não encontrado']);
                }

                responderJsonAdmin($time);

            /* =========================================
               COMPETIÇÕES
            ========================================= */

            case 'inserir_competicao':
                $tipo = postTextoAdmin('tipo');

                if ($tipo !== null && function_exists('adminTipoCompeticaoExiste') && !adminTipoCompeticaoExiste($tipo)) {
                    throw new Exception('Tipo de competição inválido.');
                }

                $stmt = $pdo->prepare("
                    INSERT INTO competicoes (nome, slug, tipo, amistoso) 
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->execute([
                    postTextoAdmin('nome'),
                    postTextoAdmin('slug'),
                    $tipo,
                    boolIntAdmin(postAdmin('amistoso', 0))
                ]);

                setMensagemAdminProcess('Competição adicionada com sucesso!');
                redirectAdmin('admin-competicoes.php');

            case 'editar_competicao':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID da competição inválido.');
                }

                $tipo = postTextoAdmin('tipo');

                if ($tipo !== null && function_exists('adminTipoCompeticaoExiste') && !adminTipoCompeticaoExiste($tipo)) {
                    throw new Exception('Tipo de competição inválido.');
                }

                $stmt = $pdo->prepare("
                    UPDATE competicoes 
                    SET nome = ?, slug = ?, tipo = ?, amistoso = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    postTextoAdmin('nome'),
                    postTextoAdmin('slug'),
                    $tipo,
                    boolIntAdmin(postAdmin('amistoso', 0)),
                    $id
                ]);

                setMensagemAdminProcess('Competição atualizada com sucesso!');
                redirectAdmin('admin-competicoes.php');

            case 'excluir_competicao':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID da competição inválido.');
                }

                $stmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM temporadas 
                    WHERE id_competicao = ?
                ");

                $stmt->execute([$id]);

                if ((int)$stmt->fetchColumn() > 0) {
                    setMensagemAdminProcess('Não é possível excluir a competição, pois ela possui temporadas associadas. Remova as temporadas primeiro.');
                } else {
                    $stmt = $pdo->prepare("
                        DELETE FROM competicoes 
                        WHERE id = ?
                    ");

                    $stmt->execute([$id]);

                    setMensagemAdminProcess('Competição excluída com sucesso!');
                }

                redirectAdmin('admin-competicoes.php');

            case 'get_competicao':
                $id = postIntAdmin('id');

                if (!$id) {
                    responderJsonAdmin(['erro' => 'ID inválido']);
                }

                $stmt = $pdo->prepare("
                    SELECT * 
                    FROM competicoes 
                    WHERE id = ?
                ");

                $stmt->execute([$id]);
                $competicao = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$competicao) {
                    responderJsonAdmin(['erro' => 'Competição não encontrada']);
                }

                responderJsonAdmin($competicao);

            /* =========================================
               TEMPORADAS
            ========================================= */

            case 'inserir_temporada':
                $stmt = $pdo->prepare("
                    INSERT INTO temporadas (id_competicao, ano, descricao) 
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([
                    postIntAdmin('id_competicao'),
                    postIntAdmin('ano'),
                    postTextoAdmin('descricao')
                ]);

                setMensagemAdminProcess('Temporada adicionada com sucesso!');
                redirectAdmin('admin-temporadas.php');

            case 'editar_temporada':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID da temporada inválido.');
                }

                $stmt = $pdo->prepare("
                    UPDATE temporadas 
                    SET id_competicao = ?, ano = ?, descricao = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    postIntAdmin('id_competicao'),
                    postIntAdmin('ano'),
                    postTextoAdmin('descricao'),
                    $id
                ]);

                setMensagemAdminProcess('Temporada atualizada com sucesso!');
                redirectAdmin('admin-temporadas.php');

            case 'excluir_temporada':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID da temporada inválido.');
                }

                $stmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM classificacao 
                    WHERE id_temporada = ?
                ");

                $stmt->execute([$id]);

                if ((int)$stmt->fetchColumn() > 0) {
                    setMensagemAdminProcess('Não é possível excluir a temporada, pois ela possui classificações associadas. Remova as classificações primeiro.');
                } else {
                    $stmt = $pdo->prepare("
                        DELETE FROM temporadas 
                        WHERE id = ?
                    ");

                    $stmt->execute([$id]);

                    setMensagemAdminProcess('Temporada excluída com sucesso!');
                }

                redirectAdmin('admin-temporadas.php');

            case 'get_temporada':
                $id = postIntAdmin('id');

                if (!$id) {
                    responderJsonAdmin(['erro' => 'ID inválido']);
                }

                $stmt = $pdo->prepare("
                    SELECT * 
                    FROM temporadas 
                    WHERE id = ?
                ");

                $stmt->execute([$id]);
                $temporada = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$temporada) {
                    responderJsonAdmin(['erro' => 'Temporada não encontrada']);
                }

                responderJsonAdmin($temporada);

            /* =========================================
               PONTUAÇÕES
            ========================================= */

            case 'inserir_pontuacao':
                $stmt = $pdo->prepare("
                    INSERT INTO pontuacoes_fase (id_competicao, fase, pontos) 
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([
                    postIntAdmin('id_competicao'),
                    postTextoAdmin('fase'),
                    postIntAdmin('pontos', 0)
                ]);

                setMensagemAdminProcess('Pontuação salva com sucesso!');
                redirectAdmin('admin-pontuacoes.php');

            case 'editar_pontuacao':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID da pontuação inválido.');
                }

                $stmt = $pdo->prepare("
                    UPDATE pontuacoes_fase 
                    SET id_competicao = ?, fase = ?, pontos = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    postIntAdmin('id_competicao'),
                    postTextoAdmin('fase'),
                    postIntAdmin('pontos', 0),
                    $id
                ]);

                setMensagemAdminProcess('Pontuação atualizada com sucesso!');
                redirectAdmin('admin-pontuacoes.php');

            case 'excluir_pontuacao':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID da pontuação inválido.');
                }

                $stmt = $pdo->prepare("
                    DELETE FROM pontuacoes_fase 
                    WHERE id = ?
                ");

                $stmt->execute([$id]);

                setMensagemAdminProcess('Pontuação excluída com sucesso!');
                redirectAdmin('admin-pontuacoes.php');

            case 'get_pontuacao':
                $id = postIntAdmin('id');

                if (!$id) {
                    responderJsonAdmin(['erro' => 'ID inválido']);
                }

                $stmt = $pdo->prepare("
                    SELECT * 
                    FROM pontuacoes_fase 
                    WHERE id = ?
                ");

                $stmt->execute([$id]);
                $pontuacao = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$pontuacao) {
                    responderJsonAdmin(['erro' => 'Pontuação não encontrada']);
                }

                responderJsonAdmin($pontuacao);

            /* =========================================
               CLASSIFICAÇÕES
            ========================================= */

            case 'inserir_classificacao':
                $idTemporada = postIntAdmin('id_temporada');
                $idTime = postIntAdmin('id_time');
                $fase = postTextoAdmin('fase', '');

                if (!$idTemporada || !$idTime || $fase === null) {
                    throw new Exception('Temporada, time ou fase inválidos.');
                }

                $pontos = calcularPontosClassificacaoAdmin($pdo, $idTemporada, $fase);

                $stmt = $pdo->prepare("
                    INSERT INTO classificacao (
                        id_temporada, id_time, fase, nacional, pontos, 
                        vitorias, empates, derrotas, gp, gc, pontos_marcados
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $idTemporada,
                    $idTime,
                    $fase,
                    boolIntAdmin(postAdmin('nacional', 1)),
                    $pontos,
                    postIntAdmin('vitorias', 0),
                    postIntAdmin('empates', 0),
                    postIntAdmin('derrotas', 0),
                    postIntAdmin('gp', 0),
                    postIntAdmin('gc', 0),
                    postIntAdmin('pontos_marcados', 0)
                ]);

                setMensagemAdminProcess('Classificação adicionada com sucesso!');
                redirectAdmin('admin-classificacao.php');

            case 'editar_classificacao':
                $id = postIntAdmin('id');
                $idTemporada = postIntAdmin('id_temporada');
                $idTime = postIntAdmin('id_time');
                $fase = postTextoAdmin('fase', '');

                if (!$id || !$idTemporada || !$idTime || $fase === null) {
                    throw new Exception('Dados inválidos para edição da classificação.');
                }

                $pontos = calcularPontosClassificacaoAdmin($pdo, $idTemporada, $fase);

                $stmt = $pdo->prepare("
                    UPDATE classificacao 
                    SET 
                        id_temporada = ?, 
                        id_time = ?, 
                        fase = ?, 
                        nacional = ?, 
                        pontos = ?, 
                        vitorias = ?, 
                        empates = ?, 
                        derrotas = ?, 
                        gp = ?, 
                        gc = ?, 
                        pontos_marcados = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $idTemporada,
                    $idTime,
                    $fase,
                    boolIntAdmin(postAdmin('nacional', 1)),
                    $pontos,
                    postIntAdmin('vitorias', 0),
                    postIntAdmin('empates', 0),
                    postIntAdmin('derrotas', 0),
                    postIntAdmin('gp', 0),
                    postIntAdmin('gc', 0),
                    postIntAdmin('pontos_marcados', 0),
                    $id
                ]);

                setMensagemAdminProcess('Classificação atualizada com sucesso!');
                redirectAdmin('admin-classificacao.php');

            case 'excluir_classificacao':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID da classificação inválido.');
                }

                $stmt = $pdo->prepare("
                    DELETE FROM classificacao 
                    WHERE id = ?
                ");

                $stmt->execute([$id]);

                setMensagemAdminProcess('Classificação excluída com sucesso!');
                redirectAdmin('admin-classificacao.php');

            case 'get_classificacao':
                $id = postIntAdmin('id');

                if (!$id) {
                    responderJsonAdmin(['erro' => 'ID inválido']);
                }

                $stmt = $pdo->prepare("
                    SELECT * 
                    FROM classificacao 
                    WHERE id = ?
                ");

                $stmt->execute([$id]);
                $classificacao = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$classificacao) {
                    responderJsonAdmin(['erro' => 'Classificação não encontrada']);
                }

                responderJsonAdmin($classificacao);

            case 'corrigir_classificacao':
                $corrigidos = 0;

                $stmt = $pdo->query("
                    SELECT 
                        cl.id, 
                        cl.id_temporada, 
                        cl.fase
                    FROM classificacao cl
                    INNER JOIN temporadas t ON t.id = cl.id_temporada
                ");

                $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($dados as $linha) {
                    $idClassificacao = (int)$linha['id'];
                    $idTemporada = (int)$linha['id_temporada'];
                    $fase = (string)$linha['fase'];

                    $pontos = calcularPontosClassificacaoAdmin($pdo, $idTemporada, $fase);

                    $update = $pdo->prepare("
                        UPDATE classificacao 
                        SET pontos = ? 
                        WHERE id = ?
                    ");

                    $update->execute([$pontos, $idClassificacao]);
                    $corrigidos++;
                }

                setMensagemAdminProcess("{$corrigidos} classificações corrigidas com sucesso.");
                redirectAdmin('admin-classificacao.php');

            /* =========================================
               FOTOS
            ========================================= */

            case 'inserir_foto_manual':
                $titulo = postTextoAdmin('titulo');
                $caminho = postTextoAdmin('caminho_imagem');
                $tipo = postTextoAdmin('tipo');

                if (!$titulo || !$caminho) {
                    setMensagemAdminProcess('Título e URL da imagem são obrigatórios.', 'sucesso_foto');
                    redirectAdmin('admin-fotos.php');
                }

                $idTemporada = null;
                $idCompeticao = null;

                if ($tipo === 'temporada') {
                    $idTemporada = postIntAdmin('id_temporada');

                    if (!$idTemporada) {
                        setMensagemAdminProcess('Temporada inválida.', 'sucesso_foto');
                        redirectAdmin('admin-fotos.php');
                    }
                } elseif ($tipo === 'competicao') {
                    $idCompeticao = postIntAdmin('id_competicao');

                    if (!$idCompeticao) {
                        setMensagemAdminProcess('Competição inválida.', 'sucesso_foto');
                        redirectAdmin('admin-fotos.php');
                    }
                } else {
                    setMensagemAdminProcess('Tipo inválido.', 'sucesso_foto');
                    redirectAdmin('admin-fotos.php');
                }

                $stmt = $pdo->prepare("
                    INSERT INTO fotos (titulo, caminho_imagem, id_temporada, id_competicao) 
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->execute([$titulo, $caminho, $idTemporada, $idCompeticao]);

                setMensagemAdminProcess('Foto adicionada com sucesso!', 'sucesso_foto');
                redirectAdmin('admin-fotos.php');

            case 'inserir_foto':
                $stmt = $pdo->prepare("
                    INSERT INTO fotos (
                        titulo, descricao, caminho_imagem, id_temporada, id_competicao
                    ) VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    postTextoAdmin('titulo'),
                    postTextoAdmin('descricao'),
                    postTextoAdmin('caminho_imagem'),
                    postIntAdmin('id_temporada'),
                    postIntAdmin('id_competicao')
                ]);

                setMensagemAdminProcess('Foto adicionada com sucesso!');
                redirecionarDepoisFotoAdmin();

            case 'editar_foto':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID da foto inválido.');
                }

                $stmt = $pdo->prepare("
                    UPDATE fotos 
                    SET 
                        titulo = ?, 
                        descricao = ?, 
                        caminho_imagem = ?, 
                        id_temporada = ?, 
                        id_competicao = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    postTextoAdmin('titulo'),
                    postTextoAdmin('descricao'),
                    postTextoAdmin('caminho_imagem'),
                    postIntAdmin('id_temporada'),
                    postIntAdmin('id_competicao'),
                    $id
                ]);

                setMensagemAdminProcess('Foto atualizada com sucesso!');
                redirecionarDepoisFotoAdmin();

            case 'excluir_foto':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID da foto inválido.');
                }

                $stmt = $pdo->prepare("
                    DELETE FROM fotos 
                    WHERE id = ?
                ");

                $stmt->execute([$id]);

                setMensagemAdminProcess('Foto excluída com sucesso!');
                redirecionarDepoisFotoAdmin();

            case 'get_foto':
                $id = postIntAdmin('id');

                if (!$id) {
                    responderJsonAdmin(['erro' => 'ID inválido']);
                }

                $stmt = $pdo->prepare("
                    SELECT * 
                    FROM fotos 
                    WHERE id = ?
                ");

                $stmt->execute([$id]);
                $foto = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$foto) {
                    responderJsonAdmin(['erro' => 'Foto não encontrada']);
                }

                responderJsonAdmin($foto);

            /* =========================================
               DIVISÕES
            ========================================= */

            case 'adicionar_divisao_individual':
                $idTime = postIntAdmin('id_time');
                $divisao = strtoupper((string)postTextoAdmin('divisao', ''));

                if (!$idTime) {
                    setMensagemAdminProcess('Clube inválido.', 'sucesso_divisao');
                } elseif (function_exists('adminDivisaoExiste') && !adminDivisaoExiste($divisao)) {
                    setMensagemAdminProcess('Divisão inválida.', 'sucesso_divisao');
                } else {
                    $stmt = $pdo->prepare("
                        REPLACE INTO divisao_atual (id_time, divisao) 
                        VALUES (?, ?)
                    ");

                    $stmt->execute([$idTime, $divisao]);

                    setMensagemAdminProcess('Divisão salva com sucesso!', 'sucesso_divisao');
                }

                redirectAdmin('admin-divisoes.php');

            case 'remover_divisao':
                $idTime = postIntAdmin('id_time');

                if ($idTime) {
                    $stmt = $pdo->prepare("
                        DELETE FROM divisao_atual 
                        WHERE id_time = ?
                    ");

                    $stmt->execute([$idTime]);

                    setMensagemAdminProcess('Divisão removida com sucesso!', 'sucesso_divisao');
                } else {
                    setMensagemAdminProcess('Clube inválido.', 'sucesso_divisao');
                }

                redirectAdmin('admin-divisoes.php');

            /* =========================================
               JOGOS
            ========================================= */

            case 'inserir_jogo':
                $idTemporada = postIntAdmin('id_temporada');

                if (!$idTemporada) {
                    throw new Exception('Temporada inválida.');
                }

                $idTime1 = postIntAdmin('id_time1');
                $idTime2 = postIntAdmin('id_time2');

                $stmt = $pdo->prepare("
                    INSERT INTO jogos (
                        id_temporada, id_time1, nome_time1, id_time2, nome_time2,
                        data, rodada, estadio, gols_time1, gols_time2, penaltis_time1, penaltis_time2
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $idTemporada,
                    $idTime1,
                    $idTime1 ? null : postTextoAdmin('nome_time1'),
                    $idTime2,
                    $idTime2 ? null : postTextoAdmin('nome_time2'),
                    postTextoAdmin('data'),
                    postTextoAdmin('rodada'),
                    postTextoAdmin('estadio'),
                    limparNumeroOpcionalAdmin(postAdmin('gols_time1')),
                    limparNumeroOpcionalAdmin(postAdmin('gols_time2')),
                    limparNumeroOpcionalAdmin(postAdmin('penaltis_time1')),
                    limparNumeroOpcionalAdmin(postAdmin('penaltis_time2'))
                ]);

                setMensagemAdminProcess('Jogo adicionado com sucesso!');
                redirectAdmin('admin-jogos.php');

            case 'inserir_varios_jogos':
                if (!isset($_POST['jogos']) || !is_array($_POST['jogos'])) {
                    throw new Exception('Nenhum jogo foi enviado.');
                }

                $pdo->beginTransaction();

                try {
                    $sucesso = 0;

                    foreach ($_POST['jogos'] as $dados) {
                        $idTemporada = filter_var($dados['id_temporada'] ?? null, FILTER_VALIDATE_INT);

                        if (!$idTemporada) {
                            continue;
                        }

                        $idTime1 = !empty($dados['id_time1']) ? (int)$dados['id_time1'] : null;
                        $idTime2 = !empty($dados['id_time2']) ? (int)$dados['id_time2'] : null;

                        $nomeTime1 = $idTime1 ? null : trim((string)($dados['nome_time1'] ?? ''));
                        $nomeTime2 = $idTime2 ? null : trim((string)($dados['nome_time2'] ?? ''));

                        if ($idTime1 === null && $nomeTime1 === '' && $idTime2 === null && $nomeTime2 === '') {
                            continue;
                        }

                        $stmt = $pdo->prepare("
                            INSERT INTO jogos (
                                id_temporada, id_time1, nome_time1, id_time2, nome_time2,
                                data, rodada, estadio, gols_time1, gols_time2, penaltis_time1, penaltis_time2
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");

                        $stmt->execute([
                            $idTemporada,
                            $idTime1,
                            $nomeTime1 !== '' ? $nomeTime1 : null,
                            $idTime2,
                            $nomeTime2 !== '' ? $nomeTime2 : null,
                            trim((string)($dados['data'] ?? '')) ?: null,
                            trim((string)($dados['rodada'] ?? '')) ?: null,
                            trim((string)($dados['estadio'] ?? '')) ?: null,
                            limparNumeroOpcionalAdmin($dados['gols_time1'] ?? null),
                            limparNumeroOpcionalAdmin($dados['gols_time2'] ?? null),
                            limparNumeroOpcionalAdmin($dados['penaltis_time1'] ?? null),
                            limparNumeroOpcionalAdmin($dados['penaltis_time2'] ?? null)
                        ]);

                        $sucesso++;
                    }

                    if ($sucesso === 0) {
                        throw new Exception('Nenhum jogo válido foi preenchido.');
                    }

                    $pdo->commit();

                    setMensagemAdminProcess("{$sucesso} jogo(s) adicionado(s) com sucesso!");
                    redirectAdmin('admin-jogos.php');
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }

            case 'editar_jogo':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID do jogo inválido.');
                }

                $idTime1 = postIntAdmin('id_time1');
                $idTime2 = postIntAdmin('id_time2');

                $stmt = $pdo->prepare("
                    UPDATE jogos SET
                        id_temporada = ?,
                        id_time1 = ?, 
                        nome_time1 = ?,
                        id_time2 = ?, 
                        nome_time2 = ?,
                        data = ?, 
                        rodada = ?, 
                        estadio = ?,
                        gols_time1 = ?, 
                        gols_time2 = ?, 
                        penaltis_time1 = ?, 
                        penaltis_time2 = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    postIntAdmin('id_temporada'),
                    $idTime1,
                    $idTime1 ? null : postTextoAdmin('nome_time1'),
                    $idTime2,
                    $idTime2 ? null : postTextoAdmin('nome_time2'),
                    postTextoAdmin('data'),
                    postTextoAdmin('rodada'),
                    postTextoAdmin('estadio'),
                    limparNumeroOpcionalAdmin(postAdmin('gols_time1')),
                    limparNumeroOpcionalAdmin(postAdmin('gols_time2')),
                    limparNumeroOpcionalAdmin(postAdmin('penaltis_time1')),
                    limparNumeroOpcionalAdmin(postAdmin('penaltis_time2')),
                    $id
                ]);

                setMensagemAdminProcess('Jogo atualizado com sucesso!');
                redirectAdmin('admin-jogos.php');

            case 'excluir_jogo':
                $id = postIntAdmin('id');

                if (!$id) {
                    throw new Exception('ID do jogo inválido.');
                }

                $stmt = $pdo->prepare("
                    DELETE FROM jogos 
                    WHERE id = ?
                ");

                $stmt->execute([$id]);

                setMensagemAdminProcess('Jogo excluído com sucesso!');
                redirectAdmin('admin-jogos.php');

            case 'get_jogo':
                $id = postIntAdmin('id');

                if (!$id) {
                    responderJsonAdmin(['erro' => 'ID inválido']);
                }

                $stmt = $pdo->prepare("
                    SELECT 
                        j.*,
                        t.id_competicao
                    FROM jogos j
                    INNER JOIN temporadas t ON j.id_temporada = t.id
                    WHERE j.id = ?
                    LIMIT 1
                ");

                $stmt->execute([$id]);
                $jogo = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$jogo) {
                    responderJsonAdmin(['erro' => 'Jogo não encontrado']);
                }

                if (!empty($jogo['data'])) {
                    $jogo['data'] = str_replace(' ', 'T', (string)$jogo['data']);
                }

                responderJsonAdmin($jogo);

            default:
                setMensagemAdminProcess('Ação não reconhecida.');
                redirectAdmin('admin.php');
        }
    }

    /* =========================================
       DADOS AUXILIARES PARA ARQUIVOS QUE AINDA
       INCLUEM admin-process.php DIRETAMENTE
    ========================================= */

    $listaTimes = $pdo->query("
        SELECT id, nome, estado, extinto 
        FROM times 
        ORDER BY nome ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $listaCompeticoes = $pdo->query("
        SELECT id, nome, tipo, amistoso 
        FROM competicoes 
        ORDER BY nome ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $temporadasResumo = $pdo->query("
        SELECT 
            t.id_competicao,
            c.nome AS nome_competicao,
            MIN(t.ano) AS ano_inicio,
            MAX(t.ano) AS ano_fim,
            COUNT(*) AS total_temporadas
        FROM temporadas t
        INNER JOIN competicoes c ON c.id = t.id_competicao
        GROUP BY t.id_competicao, c.nome
        ORDER BY c.nome ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $pontuacoesPorCompeticao = $pdo->query("
        SELECT id_competicao, fase 
        FROM pontuacoes_fase
    ")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

    $competicoesPontuadas = $pdo->query("
        SELECT id, nome 
        FROM competicoes 
        ORDER BY nome ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $dadosPontuacoes = $pdo->query("
        SELECT id_competicao, fase, pontos 
        FROM pontuacoes_fase
    ")->fetchAll(PDO::FETCH_ASSOC);

    $pontuacoesMap = [];

    foreach ($dadosPontuacoes as $linha) {
        $idCompeticao = (int)$linha['id_competicao'];
        $fase = (string)$linha['fase'];
        $pontuacoesMap[$idCompeticao][$fase] = (int)$linha['pontos'];
    }
} catch (PDOException $e) {
    die('Erro no banco de dados: ' . eAdmin($e->getMessage()));
} catch (Exception $e) {
    die('Erro: ' . eAdmin($e->getMessage()));
}