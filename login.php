<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usuario = $_POST['usuario'];
  $senha = $_POST['senha'];

  if ($usuario === 'admin' && $senha === '1234') {
    $_SESSION['logado'] = true;
    header("Location: admin.php");
    exit;
  } else {
    $erro = "Usuário ou senha incorretos.";
  }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Admin</title>
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
  <div class="login-container">
    <h1>Login Administrativo</h1>
    <?php if (isset($erro)): ?>
      <p class="erro"><?= $erro ?></p>
    <?php endif; ?>
    <form method="POST">
      <label>Usuário:</label>
      <input type="text" name="usuario" required>
      <label>Senha:</label>
      <input type="password" name="senha" required>
      <div class="botoes">
        <button type="submit">Entrar</button>
        <a class="voltar" href="index.php">Voltar</a>
      </div>
    </form>
  </div>
</body>
</html>
