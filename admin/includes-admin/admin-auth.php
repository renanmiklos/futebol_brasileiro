<?php
/* =========================================
   ADMIN-AUTH.PHP
   Proteção de acesso do Painel Administrativo
   Futebol Brasileiro
========================================= */

/*
  Este arquivo deve ser incluído no início de todas as páginas internas do admin.

  Uso:
  require_once __DIR__ . '/includes-admin/admin-auth.php';

  Ou, em arquivos dentro de subpastas, ajustar o caminho conforme necessário.
*/

/* =========================================
   DETECTAR AMBIENTE LOCAL
========================================= */

if (!function_exists('adminIsLocalhost')) {
    function adminIsLocalhost(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';

        return in_array($host, [
            'localhost',
            '127.0.0.1',
            'localhost:80',
            'localhost:8080',
            'localhost:3000'
        ], true);
    }
}

/* =========================================
   CONFIGURAR SESSÃO SEGURA
========================================= */

/*
  session_set_cookie_params() precisa ser chamado antes de session_start().
  Por isso, só aplicamos se a sessão ainda não tiver sido iniciada.
*/

if (session_status() === PHP_SESSION_NONE) {
    $isLocalhost = adminIsLocalhost();

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => !$isLocalhost,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

/* =========================================
   FUNÇÕES DE AUTENTICAÇÃO
========================================= */

if (!function_exists('adminEstaLogado')) {
    function adminEstaLogado(): bool
    {
        return isset($_SESSION['logado']) && $_SESSION['logado'] === true;
    }
}

if (!function_exists('adminRedirecionarLogin')) {
    function adminRedirecionarLogin(): void
    {
        header('Location: login.php');
        exit;
    }
}

if (!function_exists('adminLogoutForcado')) {
    function adminLogoutForcado(string $motivo = ''): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'] ?? '/',
                $params['domain'] ?? '',
                $params['secure'] ?? !adminIsLocalhost(),
                $params['httponly'] ?? true
            );
        }

        session_destroy();

        header('Location: login.php');
        exit;
    }
}

/* =========================================
   TEMPO MÁXIMO DE INATIVIDADE
========================================= */

/*
  Ajuste se quiser uma sessão mais longa ou mais curta.
  7200 segundos = 2 horas.
*/

$adminTempoMaximoInatividade = 7200;

if (adminEstaLogado()) {
    $agora = time();

    if (
        isset($_SESSION['admin_ultima_atividade']) &&
        ($agora - (int)$_SESSION['admin_ultima_atividade']) > $adminTempoMaximoInatividade
    ) {
        adminLogoutForcado('Sessão expirada por inatividade.');
    }

    $_SESSION['admin_ultima_atividade'] = $agora;
}

/* =========================================
   PROTEÇÃO DE ACESSO
========================================= */

if (!adminEstaLogado()) {
    adminRedirecionarLogin();
}

/* =========================================
   PROTEÇÃO EXTRA CONTRA SEQUESTRO SIMPLES
========================================= */

/*
  Mantém controle básico do IP e User-Agent.
  Em redes móveis ou VPNs, IP pode mudar.
  Por isso, usamos apenas validação mais leve do User-Agent por padrão.
*/

$adminUserAgentAtual = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (!isset($_SESSION['admin_user_agent'])) {
    $_SESSION['admin_user_agent'] = $adminUserAgentAtual;
} elseif ($_SESSION['admin_user_agent'] !== $adminUserAgentAtual) {
    adminLogoutForcado('Sessão inválida.');
}

/* =========================================
   REGENERAÇÃO PERIÓDICA DO ID DA SESSÃO
========================================= */

/*
  Reduz risco de fixação de sessão em sessões longas.
*/

$adminIntervaloRegeneracao = 900; // 15 minutos

if (
    !isset($_SESSION['admin_regenerado_em']) ||
    (time() - (int)$_SESSION['admin_regenerado_em']) > $adminIntervaloRegeneracao
) {
    session_regenerate_id(true);
    $_SESSION['admin_regenerado_em'] = time();
}

/* =========================================
   VARIÁVEIS ÚTEIS PARA AS PÁGINAS
========================================= */

$adminUsuarioLogado = $_SESSION['admin_usuario'] ?? 'admin';
$adminLoginEm = $_SESSION['admin_login_em'] ?? null;
$adminIpLogin = $_SESSION['admin_ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');