/* ========================================
   RANKING.JS
   Funcionalidades gerais da área Ranking
   Futebol Brasileiro
======================================== */

document.addEventListener("DOMContentLoaded", function () {
  /* ========================================
     FILTRO DA TABELA DE RANKING
  ======================================== */

  const inputFiltro = document.getElementById("filtro-time");
  const tabelaRanking = document.getElementById("ranking-table");

  if (inputFiltro && tabelaRanking) {
    inputFiltro.addEventListener("input", function () {
      filtrarTabelaRanking(inputFiltro, tabelaRanking);
    });
  }

  function filtrarTabelaRanking(input, tabela) {
    const filtro = normalizarTexto(input.value);
    const linhas = tabela.querySelectorAll("tbody tr");

    linhas.forEach(function (linha) {
      /*
        Em todas as tabelas do ranking, a coluna 1 é:
        - Clube, nos rankings de clubes;
        - Estado, no ranking de federações.
      */
      const celulaPrincipal = linha.querySelector("td:nth-child(2)");

      if (!celulaPrincipal) {
        linha.style.display = "";
        return;
      }

      const textoCelula = normalizarTexto(celulaPrincipal.textContent || "");

      linha.style.display = textoCelula.includes(filtro) ? "" : "none";
    });
  }

  function normalizarTexto(valor) {
    return String(valor || "")
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim();
  }

  /* ========================================
     ORDENAR TABELA AO CLICAR NO CABEÇALHO
  ======================================== */

  if (tabelaRanking) {
    const cabecalhos = tabelaRanking.querySelectorAll("thead th");

    cabecalhos.forEach(function (th, index) {
      th.setAttribute("tabindex", "0");
      th.setAttribute("role", "button");
      th.setAttribute("aria-sort", "none");

      th.addEventListener("click", function () {
        ordenarTabelaRanking(tabelaRanking, index, th);
      });

      th.addEventListener("keydown", function (event) {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          ordenarTabelaRanking(tabelaRanking, index, th);
        }
      });
    });
  }

  function ordenarTabelaRanking(tabela, indiceColuna, thSelecionado) {
    const tbody = tabela.querySelector("tbody");

    if (!tbody) {
      return;
    }

    const linhas = Array.from(tbody.querySelectorAll("tr"));

    const ordemAtual = thSelecionado.dataset.ordem || "desc";
    const novaOrdem = ordemAtual === "asc" ? "desc" : "asc";

    linhas.sort(function (linhaA, linhaB) {
      const celulaA = linhaA.children[indiceColuna];
      const celulaB = linhaB.children[indiceColuna];

      const valorA = extrairValorOrdenacao(celulaA);
      const valorB = extrairValorOrdenacao(celulaB);

      if (valorA.tipo === "numero" && valorB.tipo === "numero") {
        return novaOrdem === "asc"
          ? valorA.valor - valorB.valor
          : valorB.valor - valorA.valor;
      }

      return novaOrdem === "asc"
        ? valorA.valor.localeCompare(valorB.valor, "pt-BR")
        : valorB.valor.localeCompare(valorA.valor, "pt-BR");
    });

    tbody.innerHTML = "";
    linhas.forEach(function (linha) {
      tbody.appendChild(linha);
    });

    atualizarIndicadoresOrdenacao(tabela, thSelecionado, novaOrdem);

    /*
      Após ordenar, recalculamos a posição exibida na primeira coluna.
      Isso evita que a coluna "Pos" fique fora de ordem visual.
    */
    recalcularPosicoesRanking(tabela);
  }

  function extrairValorOrdenacao(celula) {
    if (!celula) {
      return {
        tipo: "texto",
        valor: ""
      };
    }

    const textoOriginal = (celula.textContent || "").trim();

    const textoNumerico = textoOriginal
      .replace(/\./g, "")
      .replace(/,/g, ".")
      .replace(/[^\d.-]/g, "");

    if (textoNumerico !== "" && !Number.isNaN(Number(textoNumerico))) {
      return {
        tipo: "numero",
        valor: Number(textoNumerico)
      };
    }

    return {
      tipo: "texto",
      valor: normalizarTexto(textoOriginal)
    };
  }

  function atualizarIndicadoresOrdenacao(tabela, thSelecionado, ordem) {
    const cabecalhos = tabela.querySelectorAll("thead th");

    cabecalhos.forEach(function (th) {
      th.dataset.ordem = "";
      th.setAttribute("aria-sort", "none");
      th.classList.remove("ordenado-asc", "ordenado-desc");
    });

    thSelecionado.dataset.ordem = ordem;
    thSelecionado.setAttribute(
      "aria-sort",
      ordem === "asc" ? "ascending" : "descending"
    );
    thSelecionado.classList.add(ordem === "asc" ? "ordenado-asc" : "ordenado-desc");
  }

  function recalcularPosicoesRanking(tabela) {
    const linhasVisiveis = tabela.querySelectorAll("tbody tr");

    linhasVisiveis.forEach(function (linha, index) {
      const celulaPosicao = linha.querySelector("td:first-child");

      if (celulaPosicao) {
        celulaPosicao.textContent = index + 1;
      }
    });
  }

  /* ========================================
     BOTÃO VOLTAR AO TOPO
     Caso seja usado futuramente em páginas do ranking
  ======================================== */

  const btnVoltarTopo = document.getElementById("voltar-ao-topo");

  if (btnVoltarTopo) {
    function alternarBotaoTopo() {
      if (window.scrollY > 300) {
        btnVoltarTopo.style.opacity = "1";
        btnVoltarTopo.style.visibility = "visible";
      } else {
        btnVoltarTopo.style.opacity = "0";
        btnVoltarTopo.style.visibility = "hidden";
      }
    }

    window.addEventListener("scroll", alternarBotaoTopo, { passive: true });

    btnVoltarTopo.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });

    alternarBotaoTopo();
  }
});