
<?php
function gerarCamposClassificacao($times)
{
    ob_start(); // Começa a capturar a saída HTML
?>
    <div class="form-campo">
        <label>Fase:</label>
        <select name="fase[]" required>
            <option value="">Selecione</option>
            <!-- Campeonato -->
            <option value="Camp">Campeão</option>
            <option value="Vice">Vice</option>
            <option value="Final">Final</option>
            <option value="Disputa3">Disputa 3º lugar</option>
            <option value="SF">Semifinal</option>
            <option value="QF">Quartas de Final</option>
            <option value="OF">Oitavas de Final</option>
            <option value="16avos">16 Avos de Final</option>
            <option value="32 avos">32 Avos de Final</option>
            <option value="64avos">64 Avos de Final</option>
            <option value="Eliminator">Eliminatória</option>

            <!-- Posições -->
            <option value="1º">1º Lugar</option>
            <option value="2º">2º Lugar</option>
            <option value="3º">3º Lugar</option>
            <option value="4º">4º Lugar</option>
            <option value="5º">5º Lugar</option>
            <option value="6º">6º Lugar</option>
            <option value="7º">7º Lugar</option>
            <option value="8º">8º Lugar</option>
            <option value="9º">9º Lugar</option>
            <option value="10º">10º Lugar</option>
            <option value="11º">11º Lugar</option>
            <option value="12º">12º Lugar</option>
            <option value="13º">13º Lugar</option>
            <option value="14º">14º Lugar</option>
            <option value="15º">15º Lugar</option>
            <option value="16º">16º Lugar</option>
            <option value="17º">17º Lugar</option>
            <option value="18º">18º Lugar</option>
            <option value="19º">19º Lugar</option>
            <option value="20º">20º Lugar</option>
            <option value="21º">21º Lugar</option>
            <option value="22º">22º Lugar</option>
            <option value="23º">23º Lugar</option>
            <option value="24º">24º Lugar</option>
            <option value="25º">25º Lugar</option>

            <!-- Regional -->
            <option value="Regional">Regional</option>
            <option value="ZonaClassificacao">Zona de Classificação</option>
            <option value="ZonaRebaixamento">Zona de Rebaixamento</option>
            <option value="Playoff">Playoff</option>

            <!-- Grupo -->
            <option value="Grupo">Grupo</option>
            <option value="FaseDeGrupos">Fase de Grupos</option>

            <!-- Pré-temporada -->
            <option value="Pre">Pré</option>
            <option value="Pre1">Pré 1ª Fase</option>
            <option value="Pre2">Pré 2ª Fase</option>
            <option value="Pre3">Pré 3ª Fase</option>

            <!-- Outros -->
            <option value="Ida">Jogo de Ida</option>
            <option value="Volta">Jogo de Volta</option>
        </select>
    </div>
    <div class="form-campo">
        <label>Time:</label>
        <input list="lista_times" class="input_time" placeholder="Digite o nome do time" required>
        <datalist id="lista_times">
            <?php foreach ($times as $t): ?>
                <option data-id="<?= $t['id'] ?>" value="<?= htmlspecialchars($t['nome']) ?>"></option>
            <?php endforeach; ?>
        </datalist>
        <input type="hidden" name="id_time[]" class="id_time">
    </div>
    <div class="form-campo">
        <label>Brasileiro?</label>
        <select name="nacional[]" required>
            <option value="1">Sim</option>
            <option value="0">Não</option>
        </select>
    </div>
    <div class="form-campo">
        <label>Vitórias:</label>
        <input type="number" name="vitorias[]" min="0" value="0" required>
    </div>
    <div class="form-campo">
        <label>Empates:</label>
        <input type="number" name="empates[]" min="0" value="0" required>
    </div>
    <div class="form-campo">
        <label>Derrotas:</label>
        <input type="number" name="derrotas[]" min="0" value="0" required>
    </div>
    <div class="form-campo">
        <label>Gols Pró:</label>
        <input type="number" name="gp[]" min="0" value="0" required>
    </div>
    <div class="form-campo">
        <label>Gols Contra:</label>
        <input type="number" name="gc[]" min="0" value="0" required>
    </div>
<?php
    return ob_get_clean(); // Retorna o conteúdo HTML como string
}
?>

<?php include 'admin-process.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel Administrativo</title>
  <link rel="stylesheet" href="css-admin/admin.css">
  <link rel="stylesheet" href="../estrutura/css-estrutura/header.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto :wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../estrutura/header2.php'; ?>

<!-- Feedback -->
<?php if (isset($_SESSION['sucesso'])): ?>
    <div class="feedback"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
    <?php unset($_SESSION['sucesso']); ?>
<?php endif; ?>

<h1>Painel de Administração</h1>

<div class="painel-bloco">
    <!-- Adicionar Time -->
    <div class="painel-coluna">
        <h2>Adicionar Time</h2>
        <form method="POST">
            <input type="hidden" name="acao" value="inserir_time">
            <label>Nome:</label>
            <input type="text" name="nome" required>
            <label>Nome Completo:</label>
            <input type="text" name="nome_completo" required>

            <div class="linha-form">
                <div class="campo-curto">
                    <label>Estado</label>
                    <select name="estado" required>
                        <option value="">Selecione</option>
                        <?php
                        $estadosBrasileiros = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                        foreach ($estadosBrasileiros as $uf): ?>
                            <option value="<?= $uf ?>"><?= $uf ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo-longo">
                    <label>Cidade:</label>
                    <input type="text" name="cidade" required>
                </div>
            </div>

            <label>Fundação:</label>
            <input type="date" name="fundacao" required>
            <label>Estádio:</label>
            <input type="text" name="estadio">
            <label>Capacidade:</label>
            <input type="number" name="capacidade">
            <label>Escudo (URL):</label>
            <input type="text" name="escudo">
            <label>História:</label>
            <textarea name="historia"></textarea>
            <label>Títulos:</label>
            <textarea name="titulos"></textarea>
            <label>Extinto?</label>
            <select name="extinto">
                <option value="0">Não</option>
                <option value="1">Sim</option>
            </select>
            <button type="submit">Salvar Time</button>
        </form>
    </div>

    <!-- Times Cadastrados -->
    <div class="painel-coluna">
        <h2>Times Cadastrados</h2>
        <div class="pesquisa-times">
            <input 
                type="text" 
                id="filtro-nome" 
                placeholder="Pesquisar pelo nome do time..." 
                onkeyup="filtrarTabela()"
                class="input-pesquisa"
            >
        </div>
        <div class="com-scroll-1">
            <table class="tabela-listagem" id="tabela-times">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Estado</th>
                        <th>Extinto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listaTimes as $time): ?>
                        <tr>
                            <td><?= $time['id'] ?? '-' ?></td>
                            <td><?= htmlspecialchars($time['nome']) ?></td>
                            <td><?= htmlspecialchars($time['estado'] ?? '') ?></td>
                            <td><?= $time['extinto'] ? 'Sim' : 'Não' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="painel-bloco">
    <!-- Adicionar Competição -->
    <div class="painel-coluna">
        <h2>Adicionar Competição</h2>
        <form method="POST">
            <input type="hidden" name="acao" value="inserir_competicao">
            <label>Nome:</label>
            <input type="text" name="nome" required>
            <label>Slug:</label>
            <input type="text" name="slug" required>
            <label>Tipo:</label>
            <input type="text" name="tipo" required>
            <label>Amistosa?</label>
            <select name="amistoso">
                <option value="0">Não</option>
                <option value="1">Sim</option>
            </select>
            <button type="submit">Salvar Competição</button>
        </form>
    </div>

    <!-- Competições Cadastradas -->
    <div class="painel-coluna">
        <h2>Competições Cadastradas</h2>
        <div class="com-scroll">
            <table class="tabela-listagem">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Amistosa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listaCompeticoes as $comp): ?>
                        <tr>
                            <td><?= $comp['id'] ?? '-' ?></td>
                            <td><?= htmlspecialchars($comp['nome'] ?? '') ?></td>
                            <td><?= htmlspecialchars($comp['tipo'] ?? '') ?></td>
                            <td><?= $comp['amistoso'] ? 'Sim' : 'Não' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="painel-bloco">
    <!-- Adicionar Temporada -->
    <div class="painel-coluna">
        <h2>Adicionar Temporada</h2>
        <form method="POST">
            <input type="hidden" name="acao" value="inserir_temporada">
            <label>ID Competição:</label>
            <input type="number" name="id_competicao" required>
            <label>Ano:</label>
            <input type="number" name="ano" required>
            <label>Descrição:</label>
            <textarea name="descricao"></textarea>
            <button type="submit">Salvar Temporada</button>
        </form>
    </div>

    <!-- Temporadas Cadastradas -->
    <div class="painel-coluna">
        <h2>Competições e Temporadas</h2>
        <div class="com-scroll">
            <table class="tabela-listagem">
                <thead>
                    <tr>
                        <th>ID Competição</th>
                        <th>Nome</th>
                        <th>Período</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($temporadasResumo as $resumo): ?>
                        <tr>
                            <td><?= $resumo['id_competicao'] ?? '-' ?></td>
                            <td><?= htmlspecialchars($resumo['nome_competicao'] ?? '') ?></td>
                            <td><?= $resumo['ano_inicio'] ?> – <?= $resumo['ano_fim'] == date('Y') ? '...' : $resumo['ano_fim'] ?></td>
                            <td><?= $resumo['total_temporadas'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="painel-bloco">
    <!-- Adicionar Pontuação -->
    <div class="painel-coluna">
        <h2>Adicionar Pontuação por Fase</h2>
        <form method="POST">
            <input type="hidden" name="acao" value="inserir_pontuacao">
            <label>ID Competição:</label>
            <input type="number" name="id_competicao" required>
            <label>Fase:</label>
            <input type="text" name="fase" required>
            <label>Pontos:</label>
            <input type="number" name="pontos" required>
            <button type="submit">Salvar Pontuação</button>
        </form>
    </div>

    <!-- Pontuações Cadastradas -->
    <div class="painel-coluna">
        <h2>Pontuações por Competição</h2>
        <div class="com-scroll">
            <?php
            $ordemFases = [
                'Camp', 'Vice', 'SF', 'QF', 'OF', '4F', '16avos', '3F', '32 avos', '2F', '64avos', '1F',
                'Principal', 'Grupo', 'Regional', 'Eliminator','1º','2º',
                '3º', '4º', '5º', '6º', '7º', '8º', '9º', '10º',
                '11º', '12º', '13º', '14º', '15º', '16º', '17º', '18º', '19º', '20º',
                '21º', '22º', '23º', '24º', '25º',
                'Pre3', 'Pre2', 'Pre1', 'Pre', 'Reb'
            ];
            ?>
            <?php foreach ($competicoesPontuadas as $comp): ?>
                <?php
                $fasesBrutas = $pontuacoesPorCompeticao[$comp['id']] ?? [];
                if (!$fasesBrutas) continue;
                $fasesOrdenadas = array_filter($ordemFases, function($fase) use ($fasesBrutas) {
                    return in_array($fase, array_column($fasesBrutas, 'fase'));
                });
                ?>
                <h3><?= htmlspecialchars($comp['nome']) ?></h3>
                <table class="tabela-listagem">
                    <tr>
                        <th>Competição / Fase</th>
                        <?php foreach ($fasesOrdenadas as $fase): ?>
                            <th><?= htmlspecialchars($fase) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><?= htmlspecialchars($comp['nome']) ?></td>
                        <?php foreach ($fasesOrdenadas as $fase): ?>
                            <td><?= $pontuacoesMap[$comp['id']][$fase] ?? '-' ?></td>
                        <?php endforeach; ?>
                    </tr>
                </table>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Formulário de Classificação -->
<?php
$competicoes = $pdo->query("SELECT id, nome FROM competicoes ORDER BY id")->fetchAll();
$times = $pdo->query("SELECT id, nome FROM times ORDER BY nome")->fetchAll();
?>
<form method="POST" action="admin-process.php" class="form-classificacao">
    <input type="hidden" name="acao" value="inserir_classificacao">
    <h2>Adicionar Classificação</h2>

    <!-- Linha 1: Competição e Temporada -->
    <div class="form-linha">
        <div class="form-campo">
            <label for="id_competicao">Competição:</label>
            <select name="id_competicao" id="id_competicao" required>
                <option value="">Selecione</option>
                <?php foreach ($competicoes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-campo">
            <label for="id_temporada">Temporada:</label>
            <select name="id_temporada" id="id_temporada" required>
                <option value="">Selecione uma competição</option>
            </select>
        </div>
    </div>

    <!-- Linha de múltiplas classificações -->
    <div id="bloco-classificacoes">
        <!-- A primeira linha é exibida por padrão -->
        <div class="form-linha linha-classificacao">
            <?= gerarCamposClassificacao($times) ?>
        </div>
    </div>

    <button type="button" id="btn-adicionar-linha" class="botao-salvar-classificacao">+ Adicionar Linha</button>
    <button type="submit" class="botao-salvar-classificacao">Salvar Todas as Classificações</button>
</form>

<form method="POST" class="corrigir">
    <input type="hidden" name="acao" value="corrigir_classificacao">
    <button type="submit" class="botao-correcao">Corrigir Pontuação das Classificações</button>
</form>

<p class="link-sair"><a href="logout.php">Sair do Painel</a></p>

<script src="../admin/js-admin/admin.js"></script>
<script>
document.getElementById('btn-adicionar-linha').addEventListener('click', function () {
    const container = document.getElementById('bloco-classificacoes');
    if (container.children.length >= 10) {
        alert('Você só pode inserir até 10 classificações.');
        return;
    }
    const novaLinha = document.querySelector('.linha-classificacao').cloneNode(true);
    novaLinha.querySelectorAll('input, select').forEach(campo => {
        if (campo.type === 'text' || campo.tagName === 'SELECT') {
            campo.value = '';
        } else if (campo.type === 'hidden') {
            campo.value = '';
        }
    });
    container.appendChild(novaLinha);
});
</script>
</body>
</html>