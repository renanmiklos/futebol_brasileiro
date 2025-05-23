

// Script para filtrar tabela times

function filtrarTabela() {
        const input = document.getElementById("filtro-nome");
        const filtro = input.value.toLowerCase();
        const tabela = document.getElementById("tabela-times");
        const linhas = tabela.getElementsByTagName("tr");

        for (let i = 1; i < linhas.length; i++) { // Começa em 1 para pular o cabeçalho
            const celulaNome = linhas[i].getElementsByTagName("td")[1]; // A coluna "Nome" é a segunda (índice 1)
            if (celulaNome) {
                const texto = celulaNome.textContent || celulaNome.innerText;
                if (texto.toLowerCase().indexOf(filtro) > -1) {
                    linhas[i].style.display = "";
                } else {
                    linhas[i].style.display = "none";
                }
            }
        }
    }


    document.getElementById('id_competicao').addEventListener('change', async function () {
    const id = this.value;

    // Carrega temporadas
    const resTemp = await fetch('admin-process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'acao=get_temporadas_por_competicao&id_competicao=' + encodeURIComponent(id)
    });
    const htmlTemp = await resTemp.text();
    document.getElementById('id_temporada').innerHTML = htmlTemp;

    // Carrega fases
    const resFase = await fetch('admin-process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'acao=get_fases_por_competicao&id_competicao=' + encodeURIComponent(id)
    });
    const htmlFase = await resFase.text();
    document.getElementById('fase').innerHTML = htmlFase;
});

// Captura o ID do time selecionado no datalist
document.getElementById('input_time').addEventListener('input', function () {
    const input = this.value.toLowerCase();
    const datalist = document.getElementById('lista_times').options;
    let idSelecionado = "";
    for (let opt of datalist) {
        if (opt.value.toLowerCase() === input) {
            idSelecionado = opt.dataset.id;
            break;
        }
    }
    document.getElementById('id_time').value = idSelecionado;
});

