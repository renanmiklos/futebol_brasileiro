/* ========================================
   ESTATISTICAS-COMP.JS
   Filtro da tabela de estatísticas por clube
======================================== */

function normalizarTexto(texto) {
  return texto
    .toString()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim();
}

function filtrarTabela() {
  const input = document.getElementById('filtro-time');
  const tabela = document.getElementById('tabela-estatisticas-comp');

  if (!input || !tabela) {
    return;
  }

  const filtro = normalizarTexto(input.value);
  const linhas = tabela.querySelectorAll('tbody tr');

  linhas.forEach((linha) => {
    /*
      Coluna 0 = posição
      Coluna 1 = clube
    */
    const celulaClube = linha.querySelectorAll('td')[1];

    if (!celulaClube) {
      linha.style.display = '';
      return;
    }

    const textoClube = normalizarTexto(celulaClube.textContent || celulaClube.innerText || '');

    linha.style.display = textoClube.includes(filtro) ? '' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('filtro-time');

  if (input) {
    input.addEventListener('input', filtrarTabela);
  }
});