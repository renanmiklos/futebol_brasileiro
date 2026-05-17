<header class="site-header">
    <div class="header-container">
        <a href="../index.php" class="logo-area" aria-label="Ir para a página inicial">
            <img src="../assets/images/logo.png" alt="Logo Futebol Brasileiro" class="logo">

            <div class="brand-text" aria-label="Futebol Brasileiro">
                <span class="brand-main">Futebol</span>
                <span class="brand-highlight">Brasileiro</span>
            </div>
        </a>

        <button class="menu-toggle" id="menuToggle" type="button" aria-label="Abrir menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="menu-area" id="menuArea">
            <form class="search-bar" action="../estrutura/busca.php" method="GET">
                <input
                    class="input-search"
                    type="text"
                    name="query"
                    placeholder="Buscar no portal..."
                    aria-label="Buscar no portal"
                >
                <button class="botao-search" type="submit" aria-label="Pesquisar">🔍</button>
            </form>

            <nav class="menu-principal" id="menuPrincipal" aria-label="Menu principal">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../noticias/noticias.php">Notícias</a></li>
                    <li><a href="../historia/historia.php">História</a></li>
                    <li><a href="../times/times.php">Times</a></li>
                    <li><a href="../campeonatos/campeonatos.php">Campeonatos</a></li>
                    <li><a href="../estatisticas/ranking-introducao.php">Ranking</a></li>
                    <li><a href="../noticias/artigos.php">Artigos</a></li>
                    <li><a href="../estatisticas/estatisticas.php">Estatísticas</a></li>
                    <li><a href="../jogos/jogos.php">Jogos</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <script>
        (function () {
            const menuToggle = document.getElementById('menuToggle');
            const menuArea = document.getElementById('menuArea');

            if (!menuToggle || !menuArea) return;

            menuToggle.addEventListener('click', function () {
                const aberto = menuArea.classList.toggle('ativo');

                menuToggle.classList.toggle('ativo', aberto);
                menuToggle.setAttribute('aria-expanded', aberto ? 'true' : 'false');
                menuToggle.setAttribute('aria-label', aberto ? 'Fechar menu' : 'Abrir menu');
            });

            document.addEventListener('click', function (event) {
                const clicouFora = !menuArea.contains(event.target) && !menuToggle.contains(event.target);

                if (clicouFora && menuArea.classList.contains('ativo')) {
                    menuArea.classList.remove('ativo');
                    menuToggle.classList.remove('ativo');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.setAttribute('aria-label', 'Abrir menu');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && menuArea.classList.contains('ativo')) {
                    menuArea.classList.remove('ativo');
                    menuToggle.classList.remove('ativo');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.setAttribute('aria-label', 'Abrir menu');
                }
            });
        })();
    </script>
</header>