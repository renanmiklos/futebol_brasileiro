<?php
/* =========================================
   LOGOUT.PHP
   Encerramento seguro da sessão administrativa
========================================= */

$isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', [
    'localhost',
    '127.0.0.1',
    'localhost:80',
    'localhost:8080'
], true);

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
   LIMPAR VARIÁVEIS DA SESSÃO
========================================= */

$_SESSION = [];

/* =========================================
   REMOVER COOKIE DA SESSÃO
========================================= */

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'] ?? '/',
        $params['domain'] ?? '',
        $params['secure'] ?? !$isLocalhost,
        $params['httponly'] ?? true
    );
}

/* =========================================
   DESTRUIR SESSÃO
========================================= */

session_destroy();

/* =========================================
   REDIRECIONAR PARA LOGIN
========================================= */

header('Location: login.php');
exit;