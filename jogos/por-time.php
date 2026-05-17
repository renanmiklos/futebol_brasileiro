<?php
require_once '../estrutura/conexaodb.php';

// Carregar competições com jogos
$stmt_comp = $pdo->prepare("
    SELECT DISTINCT c.id, c.nome
    FROM competicoes c
    INNER JOIN temporadas t ON t.id_competicao = c.id
    INNER JOIN jogos j ON j.id_temporada = t.id
    ORDER BY c.nome
");
$stmt_comp->execute();
$competicoes = $stmt_comp->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resultados por Time - Futebol Brasileiro</title>
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="css-jogos/por-time.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

  <?php include '../estrutura/header2.php'; ?>

  <main>
    <section class="secao-por-time">
      <div class="container">
        <a href="jogos.php" class="voltar-link">← Voltar para Resultados</a>
        <h1>Resultados por Time</h1>

        <form id="form-busca" class="form-busca-time">
          <div class="grupo-campos">
            <div class="campo">
              <label for="competicao">Competição:</label>
              <select name="competicao" id="competicao" required>
                <option value="">Selecione</option>
                <option value="internacionais">.todos os Internacionais</option>
                <?php foreach ($competicoes as $comp): ?>
                  <option value="<?= $comp['id'] ?>">
                    <?= htmlspecialchars($comp['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="campo">
              <label for="time">Time:</label>
              <select name="time" id="time" disabled>
                <option value="">Selecione uma competição primeiro</option>
              </select>
            </div>

            <div class="campo-botao">
              <button type="submit" id="btn-pesquisar" disabled>Pesquisar</button>
            </div>
          </div>
        </form>

        <div id="tabela-container"></div>
        <div id="mensagem"></div>
      </div>
    </section>
  </main>

  <?php include '../estrutura/footer2.php'; ?>

  <script>
    const competicaoSelect = document.getElementById('competicao');
    const timeSelect = document.getElementById('time');
    const form = document.getElementById('form-busca');
    const tabelaContainer = document.getElementById('tabela-container');
    const mensagemDiv = document.getElementById('mensagem');
    const btnPesquisar = document.getElementById('btn-pesquisar');

    // 1. Carregar times ao mudar competição
    competicaoSelect.addEventListener('change', function () {
      const compId = this.value;
      timeSelect.disabled = true;
      btnPesquisar.disabled = true;
      timeSelect.innerHTML = '<option value="">Carregando...</option>';

      if (!compId) {
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Selecione uma competição primeiro</option>';
        return;
      }

      fetch(`ajax-times.php?competicao=${encodeURIComponent(compId)}`)
        .then(response => response.json())
        .then(times => {
          let options = '<option value="">Selecione um time</option>';
          times.forEach(time => {
            options += `<option value="${time.id}">${time.nome}</option>`;
          });
          timeSelect.innerHTML = options;
          timeSelect.disabled = false;
        })
        .catch(() => {
          timeSelect.innerHTML = '<option value="">Erro ao carregar times</option>';
          timeSelect.disabled = true;
        });
    });

    // 2. Enviar busca ao clicar em "Pesquisar"
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      const compId = competicaoSelect.value;
      const timeId = timeSelect.value;

      if (!compId || !timeId) return;

      tabelaContainer.innerHTML = '<p>Buscando resultados...</p>';
      mensagemDiv.innerHTML = '';

      fetch(`ajax-resultados-time.php?competicao=${compId}&time=${timeId}`)
        .then(response => response.json())
        .then(data => {
          if (data.erro) {
            mensagemDiv.innerHTML = `<p class="mensagem">${data.erro}</p>`;
            tabelaContainer.innerHTML = '';
            return;
          }

          if (data.length === 0) {
            mensagemDiv.innerHTML = '<p class="mensagem">Nenhum jogo encontrado para este time nesta competição.</p>';
            tabelaContainer.innerHTML = '';
            return;
          }

          let table = `
            <div class="tabela-resultados">
              <table>
                <thead>
                  <tr>
                    <th>Clube</th>
                    <th>J</th>
                    <th>V</th>
                    <th>E</th>
                    <th>D</th>
                    <th>GP</th>
                    <th>GC</th>
                    <th>SG</th>
                    <th>Último</th>
                  </tr>
                </thead>
                <tbody>
          `;

          data.forEach(r => {
            const sg = r.gols_pro - r.gols_contra;
            const ultimoAno = r.ultima_data ? new Date(r.ultima_data).getFullYear() : '—';
            
            // Montar célula do clube com escudo (se houver)
            let clubeHTML = '';
            if (r.rival_escudo) {
              // Corrigir caminho: remover "/" inicial se existir e adicionar "../"
              const escudoPath = '../' + r.rival_escudo.replace(/^\//, '');
              clubeHTML = `
                <img src="${escudoPath}" alt="${r.rival_nome}" class="escudo-pequeno" onerror="this.style.display='none'">
                ${r.rival_nome}
              `;
            } else {
              clubeHTML = r.rival_nome || '—';
            }

            table += `
              <tr>
                <td>${clubeHTML}</td>
                <td>${r.jogos}</td>
                <td>${r.vitorias}</td>
                <td>${r.empates}</td>
                <td>${r.derrotas}</td>
                <td>${r.gols_pro}</td>
                <td>${r.gols_contra}</td>
                <td>${sg}</td>
                <td>${ultimoAno}</td>
              </tr>
            `;
          });

          table += `
                </tbody>
              </table>
            </div>
          `;

          tabelaContainer.innerHTML = table;
          mensagemDiv.innerHTML = '';
        })
        .catch(() => {
          mensagemDiv.innerHTML = '<p class="mensagem">Erro ao carregar resultados.</p>';
          tabelaContainer.innerHTML = '';
        });
    });

    // 3. Habilitar botão só quando time for selecionado
    timeSelect.addEventListener('change', function () {
      btnPesquisar.disabled = !this.value;
    });
  </script>
</body>
</html>