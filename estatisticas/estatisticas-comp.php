<?php
require_once '../estrutura/conexaodb.php';

// Captura e normaliza o título recebido via GET
$item = isset($_GET['item']) ? urldecode($_GET['item']) : '';
$tituloNormalizado = mb_strtolower(trim($item));
$tituloOriginal = $item ?: 'Estatística não especificada';

$descricao = '';
$dados = [];
$tabela_estatisticas = [];
$id_competicao = null;

// Determina os dados com base no título
switch ($tituloNormalizado) {
    case 'era da taça brasil (1959 - 1968)':
        $descricao = "A Taça Brasil foi o primeiro campeonato nacional oficial do futebol brasileiro, realizado entre 1959 e 1968. Foi organizado pela CBF e reunia os campeões estaduais das diversas federações do país.";
        $dados = [
            "Primeira edição em 1959",
            "Última edição em 1968",
            "Formato de eliminatória simples",
            "Clubes participantes eram os campeões estaduais",
            "Palmeiras foi o maior campeão com 2 títulos"
        ];
        $id_competicao = [17];
        break;

    case 'era do torneio roberto gomes pedrosa (1967 - 1970)':
        $descricao = "O Torneio Roberto Gomes Pedrosa foi um torneio disputado por clubes brasileiros entre 1967 e 1970. Era considerado uma espécie de campeonato prévio ao Campeonato Nacional.";
        $dados = [
            "Realizado entre 1967 e 1970",
            "Também conhecido como 'Taça Rio'",
            "Participavam clubes brasileiros e estrangeiros convidados",
            "Formato misto com fase regional e final",
            "Cruzeiro foi o maior campeão com 2 títulos"
        ];
        $id_competicao = [18];
        break;

    case 'brasileirão unificado (1959 - ...)':
        $descricao = "O Campeonato Brasileiro, também chamado de Brasileirão, é o principal torneio nacional do futebol brasileiro, reunindo clubes de todo o país desde sua criação em 1959.";
        $dados = [
            "Principal campeonato nacional do Brasil",
            "Disputado anualmente desde 1959",
            "Formato moderno iniciado em 2003",
            "Sistema de pontos corridos desde então",
            "Palmeiras é o maior campeão"
        ];
        $id_competicao = [17, 18, 19];
        break;

    case 'brasileirão (1971 - ...)':
        $descricao = "Campeonato Brasileiro disputado entre 1971 e os anos seguintes, sendo uma evolução dos formatos anteriores até a consolidação do formato de pontos corridos em 2003.";
        $dados = [
            "Sucessor do Torneio Roberto Gomes Pedrosa",
            "Disputado desde 1971",
            "Formato variou até 2002",
            "Reuniu os principais clubes do Brasil",
            "Santos e Corinthians são alguns dos maiores campeões"
        ];
        $id_competicao = [19];
        break;

    case 'brasileirão pontos corridos (2003 - ...)':
        $descricao = "A partir de 2003, o Campeonato Brasileiro adotou o formato de pontos corridos, similar ao modelo europeu, aumentando a competitividade e o número de jogos.";
        $dados = [
            "Formato adotado desde 2003",
            "Sistema de pontos corridos com todos contra todos",
            "Maior número de jogos por temporada",
            "Palmeiras é o maior campeão nesse formato",
            "Mais transparência e equilíbrio na competição"
        ];
        $id_competicao = 19;
        $ano_inicio = 2003;
        break;

    // === COMPETIÇÕES INTERNACIONAIS ===

    case 'copa do mundo de clubes (2000 - 2024)':
        $descricao = "A Copa do Mundo de Clubes é a competição mais prestigiada do futebol mundial, reunindo os campeões continentais e o campeão local do país sede.";
        $dados = [
            "Substituiu a Copa Intercontinental em 2000",
            "Organizada pela FIFA",
            "Participam 7 clubes de cada confederação",
            "Formato com eliminatórias diretas",
            "Santos e Corinthians representaram bem o Brasil"
        ];
        $id_competicao = [1];
        break;

    case 'copa intercontinental (1960 - 1999)':
        $descricao = "A Copa Intercontinental era disputada entre o campeão da Europa e o campeão da América do Sul. Hoje substituída pela Copa do Mundo de Clubes.";
        $dados = [
            "Disputada entre 1960 e 2004",
            "Enfrentavam-se o campeão da Europa e da América do Sul",
            "Formato de ida e volta",
            "Peñarol, Santos e Boca Juniors foram destaques",
            "Brasil tem 8 títulos conquistados"
        ];
        $id_competicao = [2];
        break;

    case 'libertadores da américa (1960 - ...)':
        $descricao = "A Copa Libertadores da América é a principal competição sul-americana de clubes, promovida pela CONMEBOL desde 1960.";
        $dados = [
            "Criada em 1960 como Taça Libertadores da América",
            "Participam clubes da CONMEBOL e convidados",
            "Formato de grupos e mata-mata",
            "River Plate e Boca Juniors lideram número de títulos",
            "Brasil é o país com mais títulos: 22"
        ];
        $id_competicao = [5];
        break;

    case 'copa sul-americana (2002 - ...)':
        $descricao = "A Copa Sul-Americana é a segunda competição mais importante da CONMEBOL, criada em 2002 como sucessora da Supercopa Libertadores.";
        $dados = [
            "Criada em 2002",
            "Segunda competição mais importante da América do Sul",
            "Participação de times de toda a CONMEBOL",
            "Formato similar à antiga Copa UEFA",
            "Internacional e Athletico Paranaense são campeões brasileiros"
        ];
        $id_competicao = [7];
        break;

    default:
        $descricao = '';
        $dados = [];
        $id_competicao = null;
}

// Consulta SQL dinâmica com base no tipo de competição
if (is_array($id_competicao)) {
    $ids_sql = implode(',', $id_competicao);

    $sql = "
        SELECT 
            t.nome AS nome_time,
            SUM(c.jogos) AS jogos,
            SUM(c.vitorias) AS vitorias,
            SUM(c.empates) AS empates,
            SUM(c.derrotas) AS derrotas,
            SUM(c.gp) AS gols_pro,
            SUM(c.gc) AS gols_contra,
            SUM(c.saldo) AS saldo,
            SUM(c.pontos) AS pontos
        FROM classificacao c
        INNER JOIN temporadas temp ON temp.id = c.id_temporada
        INNER JOIN times t ON t.id = c.id_time
        WHERE temp.id_competicao IN ($ids_sql)
        GROUP BY c.id_time
        ORDER BY SUM(c.pontos) DESC
    ";

    $stmt = $pdo->query($sql);
    if ($stmt !== false) {
        $tabela_estatisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} elseif (!empty($id_competicao)) {
    $sql = "
        SELECT 
            t.nome AS nome_time,
            SUM(c.jogos) AS jogos,
            SUM(c.vitorias) AS vitorias,
            SUM(c.empates) AS empates,
            SUM(c.derrotas) AS derrotas,
            SUM(c.gp) AS gols_pro,
            SUM(c.gc) AS gols_contra,
            SUM(c.saldo) AS saldo,
            SUM(c.pontos) AS pontos
        FROM classificacao c
        INNER JOIN temporadas temp ON temp.id = c.id_temporada
        INNER JOIN times t ON t.id = c.id_time
        WHERE temp.id_competicao = :id_competicao
        GROUP BY c.id_time
        ORDER BY SUM(c.pontos) DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_competicao' => $id_competicao]);
    $tabela_estatisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tituloOriginal) ?> - Futebol Brasileiro</title>
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/footer.css">
  <link rel="stylesheet" href="../estatisticas/css-estisticas/estatisticas-comp.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto :wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

  <?php include '../estrutura/header2.php'; ?>

  <main>
    <section class="secao-estatisticas">
      <div class="container">
        <aside class="menu-lateral">
          <h2>Detalhes</h2>
          <ul>
            <li><a href="#" class="ativo"><?= htmlspecialchars($tituloOriginal) ?></a></li>
          </ul>
        </aside>

        <div class="conteudo-estatisticas">
          <h1><?= htmlspecialchars($tituloOriginal) ?></h1>
          <p><?= htmlspecialchars($descricao) ?></p>

          <!-- Exibe a lista de dados -->
          <?php if (!empty($dados)): ?>
            <div class="lista">
              <div class="lista-estatisticas">
                <ul>
                  <?php foreach ($dados as $dado): ?>
                    <li><span><?= htmlspecialchars($dado) ?></span></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          <?php endif; ?>

          <!-- Exibe a tabela de estatísticas -->
          <?php if (!empty($tabela_estatisticas)): ?>
            <h2>Resumo Estatístico por Clube</h2>
            <div class="tabela-estatisticas">
              <table>
                <thead>
                  <tr>
                    <th>Pos</th>
                    <th>Clube</th>
                    <th>Jogos</th>
                    <th>Vitórias</th>
                    <th>Empates</th>
                    <th>Derrotas</th>
                    <th>Gols Pró</th>
                    <th>Gols Contra</th>
                    <th>Saldo</th>
                    <th>Pontos</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; foreach ($tabela_estatisticas as $linha): ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td><?= htmlspecialchars($linha['nome_time']) ?></td>
                      <td><?= $linha['jogos'] ?></td>
                      <td><?= $linha['vitorias'] ?></td>
                      <td><?= $linha['empates'] ?></td>
                      <td><?= $linha['derrotas'] ?></td>
                      <td><?= $linha['gols_pro'] ?></td>
                      <td><?= $linha['gols_contra'] ?></td>
                      <td><?= $linha['saldo'] ?></td>
                      <td><?= $linha['pontos'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </section>
  </main>

  <?php include '../estrutura/footer2.php'; ?>

</body>
</html>