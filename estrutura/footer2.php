<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css-estrutura/footer.css">
    <title>Footer</title>
</head>
<body>
    
    <footer class="rodape">
        <div class="rodape-container">
            <p>&copy; <?= date('Y') ?> Futebol Brasileiro. Todos os direitos reservados.</p>

            <p style="font-size: 0.9em;">
            <button onclick="mostrarLinkAdmin()" class="btn-link-admin">Área Administrativa</button>
            </p>

            <p id="link-admin-revelado" style="display: none; font-size: 0.8em;">
            <a href="../admin/admin.php" class="admin-link" style="color: #FFD700;">Acessar Painel</a>
            </p>
        </div>        
    </footer>

    <script src = "../estrutura/js-estrutura/footer.js"></script>

</body>
</html>