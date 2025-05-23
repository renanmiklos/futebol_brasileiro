<?php
session_start();

// Forçar HTTPS (opcional mas recomendado em produção)
if (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

// Gera ou mantém o token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(50));
}

// Configurações de segurança
$max_tentativas = 5;
$tempo_bloqueio = 300; // segundos (5 minutos)

// Inicializa tentativas se não existirem
if (!isset($_SESSION['login_tentativas'])) {
    $_SESSION['login_tentativas'] = 0;
    $_SESSION['login_ultimo_erro'] = time();
} else {
    // Se passou tempo suficiente, reinicia contagem
    if (time() - $_SESSION['login_ultimo_erro'] > $tempo_bloqueio) {
        $_SESSION['login_tentativas'] = 0;
    }
}

$usuario_correto = 'admin';
$senha_correta_hash = '$2y$10$0Vj9Uuys/veBtLxleADqbuVIXLjt20dZj9waenEqO4qPfB/3xAmYu'; // <<< Cole AQUI o hash gerado pelo script acima

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Requisição inválida (token CSRF ausente).");
    }

    // Verifica limite de tentativas
    if ($_SESSION['login_tentativas'] >= $max_tentativas) {
        $erro = "Muitas tentativas. Tente novamente em alguns minutos.";
    } else {
        $usuario = $_POST['usuario'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if ($usuario === $usuario_correto && password_verify($senha, $senha_correta_hash)) {
            $_SESSION['logado'] = true;
            $_SESSION['login_tentativas'] = 0; // Reseta tentativas após sucesso
            header("Location: admin.php");
            exit;
        } else {
            $erro = "Usuário ou senha incorretos.";
            $_SESSION['login_tentativas']++;
            $_SESSION['login_ultimo_erro'] = time();
        }
    }
}

// Incluir a conexão com o banco
require_once '../estrutura/conexaodb.php';

// Variável para armazenar a data da última atualização
$data_ultima_atualizacao = "Não disponível";

try {
    // Buscar a data da notícia mais recente
    $stmt = $pdo->query("SELECT data_publicacao FROM noticias ORDER BY data_publicacao DESC LIMIT 1");
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['data_publicacao'])) {
        // Formatar a data para d/m/Y
        $data_ultima_atualizacao = date('d/m/Y', strtotime($row['data_publicacao']));
    }
} catch (PDOException $e) {
    $data_ultima_atualizacao = "Erro ao carregar";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Admin</title>
  <link rel="stylesheet" href="css-admin/login.css">
</head>
<body>
  <div class="container">
    
    <!-- Área da Logo -->
    <div class="logo-container">
      <img src="../assets/images/logo.png" alt="Logo do Sistema" class="logo-img">
      <h2>Bem-vindo ao Painel Administrativo</h2>
    </div>

    <!-- Formulário de Login -->
    <div class="login-container">
      <h1>Login Administrativo</h1>
      
      <?php if (isset($erro)): ?>
        <p class="erro"><?= $erro ?></p>
      <?php endif; ?>
      
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <label>Usuário:</label>
        <input type="text" name="usuario" required>

        <label>Senha:</label>
        <input type="password" name="senha" required>

        <div class="botoes">
          <button type="submit">Entrar</button>
          <a class="voltar" href="../index.php">Voltar</a>
        </div>
      </form>

      <!-- Informação de Segurança -->
      <div class="seguranca-info">
        Este site utiliza conexão segura (HTTPS).
      </div>
    </div>

    <!-- Rodapé -->
    <footer class="rodape">
      &copy; <?= date('Y') ?> Sistema Admin | Desenvolvido por web-conecta.com | Última atualização: <?= $data_ultima_atualizacao ?>
    </footer>

  </div>
</body>
</html>