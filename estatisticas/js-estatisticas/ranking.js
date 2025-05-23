function ordenar(coluna) {
      const tabela = document.getElementById("ranking-table");
      const linhas = Array.from(tabela.rows).slice(1);
      const corpo = tabela.tBodies[0];
      const tipoNumero = (coluna !== 1 && coluna !== 2);

      linhas.sort((a, b) => {
        let valA = a.cells[coluna].innerText;
        let valB = b.cells[coluna].innerText;

        if (tipoNumero) {
          valA = parseInt(valA) || 0;
          valB = parseInt(valB) || 0;
          return valB - valA;
        } else {
          return valA.localeCompare(valB);
        }
      });

      linhas.forEach(l => corpo.appendChild(l));
    }