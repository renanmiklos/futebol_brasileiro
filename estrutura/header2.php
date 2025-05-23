<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="css-estrutura/header.css">
</head>
<body>
    
    <header class="site-header">
        <div class="header-container">
            <div class="logo-area">
                <img src="../assets/images/logo.png" alt="Logo" class="logo">
                <span class="logo-text">Futebol Brasileiro</span>
            </div>
            <div class="menu-area">
                <form class="search-bar" action="busca.php" method="GET">
                    <input class="input-search" type="text" name="query" placeholder="Buscar...">
                    <button class="botao-search" type="submit">🔍</button>
                </form>
                <nav class="menu-principal">
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../noticias/noticias.php">Notícias</a></li>
                        <li><a href="../historia/historia.php">História</a></li>
                        <li><a href="../times/times.php">Times</a></li>
                        <li><a href="../campeonatos/campeonatos.php">Campeonatos</a></li>
                        <li><a href="../estatisticas/ranking.php">Ranking</a></li>
                        <li><a href="../noticias/artigos.php">Artigos</a></li>
                        <li><a href="../estatisticas/estatisticas.php">Estatísticas</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

</body>
</html>