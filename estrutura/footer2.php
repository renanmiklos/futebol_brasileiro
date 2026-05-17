<footer class="rodape">
    <div class="rodape-container">
        <div class="rodape-brand">
            <img src="../assets/images/logo.png" alt="Logo Futebol Brasileiro" class="rodape-logo">
            <div>
                <strong>Futebol Brasileiro</strong>
                <span>História, clubes, ranking, jogos e estatísticas</span>
            </div>
        </div>

        <div class="rodape-links">
            <a href="../index.php">Home</a>
            <a href="../noticias/noticias.php">Notícias</a>
            <a href="../times/times.php">Times</a>
            <a href="../estatisticas/ranking-introducao.php">Ranking</a>
            <a href="../estatisticas/estatisticas.php">Estatísticas</a>
        </div>

        <div class="rodape-admin">
            <button onclick="mostrarLinkAdmin()" class="btn-link-admin" type="button">
                Área Administrativa
            </button>

            <span id="link-admin-revelado" class="link-admin-revelado">
                <a href="../admin/admin.php" class="admin-link">Acessar Painel</a>
            </span>
        </div>

        <p class="rodape-copy">
            &copy; <?= date('Y') ?> Futebol Brasileiro. Todos os direitos reservados.
        </p>
    </div>
</footer>

<script src="../estrutura/js-estrutura/footer.js"></script>