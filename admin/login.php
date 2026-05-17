<?php
/* =========================================
   LOGIN.PHP
   Painel Administrativo - Futebol Brasileiro
========================================= */

/* =========================================
   CONFIGURAÇÕES INICIAIS
========================================= */

$isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', [
    'localhost',
    '127.0.0.1',
    'localhost:80',
    'localhost:8080'
], true);

/*
  Força HTTPS apenas em produção.
  No XAMPP/local, isso pode quebrar o acesso.
*/
if (
    !$isLocalhost &&
    (!isset($_SERVER['HTTPS']) || strtolower((string)$_SERVER['HTTPS']) !== 'on')
) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

/*
  Cookies de sessão mais seguros.
  session_set_cookie_params precisa vir antes de session_start().
*/
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => !$isLocalhost,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

/* =========================================
   FUNÇÕES AUXILIARES
========================================= */

function eAdminLogin($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function gerarTokenCsrfAdmin(): string
{
    return bin2hex(random_bytes(50));
}

function obterIpClienteAdmin(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'ip_desconhecido';
}

/* =========================================
   CONEXÃO COM BANCO
========================================= */

require_once __DIR__ . '/../estrutura/conexaodb.php';

if (!isset($pdo)) {
    die('Erro: Conexão com o banco de dados não estabelecida.');
}

/* =========================================
   TOKEN CSRF
========================================= */

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = gerarTokenCsrfAdmin();
}

/* =========================================
   CONFIGURAÇÕES DE LOGIN
========================================= */

$maxTentativas = 5;
$tempoBloqueio = 300; // 5 minutos

/*
  Credenciais atuais.
  Em uma etapa futura, o ideal é mover isso para uma tabela usuarios_admin.
*/
$usuarioCorreto = 'admin';
$senhaCorretaHash = '$2y$10$0Vj9Uuys/veBtLxleADqbuVIXLjt20dZj9waenEqO4qPfB/3xAmYu';

$erro = null;

/* =========================================
   CONTROLE DE TENTATIVAS
========================================= */

if (!isset($_SESSION['login_tentativas'])) {
    $_SESSION['login_tentativas'] = 0;
}

if (!isset($_SESSION['login_ultimo_erro'])) {
    $_SESSION['login_ultimo_erro'] = 0;
}

/*
  Se passou o tempo de bloqueio, libera novas tentativas.
*/
if (
    $_SESSION['login_ultimo_erro'] > 0 &&
    time() - (int)$_SESSION['login_ultimo_erro'] > $tempoBloqueio
) {
    $_SESSION['login_tentativas'] = 0;
    $_SESSION['login_ultimo_erro'] = 0;
}

$tentativasRestantes = max(0, $maxTentativas - (int)$_SESSION['login_tentativas']);

/* =========================================
   PROCESSAMENTO DO LOGIN
========================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfPost = $_POST['csrf_token'] ?? '';

    if (
        empty($csrfPost) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], (string)$csrfPost)
    ) {
        $erro = 'Requisição inválida. Atualize a página e tente novamente.';
    } elseif ((int)$_SESSION['login_tentativas'] >= $maxTentativas) {
        $erro = 'Muitas tentativas incorretas. Tente novamente em alguns minutos.';
    } else {
        $usuario = trim((string)($_POST['usuario'] ?? ''));
        $senha = (string)($_POST['senha'] ?? '');

        $loginValido = (
            hash_equals($usuarioCorreto, $usuario) &&
            password_verify($senha, $senhaCorretaHash)
        );

        if ($loginValido) {
            session_regenerate_id(true);

            $_SESSION['logado'] = true;
            $_SESSION['admin_usuario'] = $usuarioCorreto;
            $_SESSION['admin_login_em'] = time();
            $_SESSION['admin_ip'] = obterIpClienteAdmin();

            $_SESSION['login_tentativas'] = 0;
            $_SESSION['login_ultimo_erro'] = 0;

            $_SESSION['csrf_token'] = gerarTokenCsrfAdmin();

            header('Location: admin.php');
            exit;
        }

        $_SESSION['login_tentativas']++;
        $_SESSION['login_ultimo_erro'] = time();

        $tentativasRestantes = max(0, $maxTentativas - (int)$_SESSION['login_tentativas']);

        if ($tentativasRestantes > 0) {
            $erro = 'Usuário ou senha incorretos. Tentativas restantes: ' . $tentativasRestantes . '.';
        } else {
            $erro = 'Muitas tentativas incorretas. Tente novamente em alguns minutos.';
        }
    }
}

/* =========================================
   ÚLTIMA ATUALIZAÇÃO DO SITE
========================================= */

$dataUltimaAtualizacao = 'Não disponível';

try {
    $stmt = $pdo->query("
        SELECT data_publicacao 
        FROM noticias 
        ORDER BY data_publicacao DESC 
        LIMIT 1
    ");

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['data_publicacao'])) {
        $timestamp = strtotime((string)$row['data_publicacao']);

        if ($timestamp) {
            $dataUltimaAtualizacao = date('d/m/Y', $timestamp);
        }
    }
} catch (PDOException $e) {
    $dataUltimaAtualizacao = 'Erro ao carregar';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Painel Administrativo</title>

    <link rel="stylesheet" href="css-admin/login.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

<main class="pagina-login">
    <div class="container">

        <section class="logo-container">
            <img 
                src="../assets/images/logo.png" 
                alt="Logo do Sistema" 
                class="logo-img"
                onerror="this.style.display='none';"
            >

            <h2>Bem-vindo ao Painel Administrativo</h2>

            <p>
                Área restrita para gerenciamento do portal Futebol Brasileiro.
            </p>
        </section>

        <section class="login-container">
            <span class="eyebrow">Admin</span>

            <h1>Login Administrativo</h1>

            <?php if (!empty($erro)): ?>
                <p class="erro">
                    <?= eAdminLogin($erro) ?>
                </p>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <input 
                    type="hidden" 
                    name="csrf_token" 
                    value="<?= eAdminLogin($_SESSION['csrf_token']) ?>"
                >

                <div class="campo-form">
                    <label for="usuario">Usuário</label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        required
                        autocomplete="username"
                        autofocus
                    >
                </div>

                <div class="campo-form">
                    <label for="senha">Senha</label>

                    <input
                        type="password"
                        id="senha"
                        name="senha"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <div class="botoes">
                    <button type="submit">
                        Entrar
                    </button>

                    <a class="voltar" href="../index.php">
                        Voltar
                    </a>
                </div>
            </form>

            <div class="seguranca-info">
                <?php if ($isLocalhost): ?>
                    Ambiente local detectado. Em produção, utilize conexão segura HTTPS.
                <?php else: ?>
                    Este site utiliza conexão segura HTTPS.
                <?php endif; ?>
            </div>
        </section>

        <footer class="rodape">
            &copy; <?= date('Y') ?> Sistema Admin |
            Desenvolvido por web-conecta.com |
            Última atualização: <?= eAdminLogin($dataUltimaAtualizacao) ?>
        </footer>

    </div>
</main>

</body>
</html>