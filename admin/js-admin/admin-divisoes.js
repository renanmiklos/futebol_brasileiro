/* ========================================
   ADMIN-DIVISOES.JS
   Funcionalidades da página admin-divisoes.php
   Futebol Brasileiro
======================================== */

document.addEventListener("DOMContentLoaded", function () {
  inicializarFiltroDivisoes();
  inicializarFiltroSemDivisao();
});

/* ========================================
   FILTRO DA TABELA DE DIVISÕES
======================================== */

function inicializarFiltroDivisoes() {
  const inputFiltro = document.getElementById("filtro-divisoes");
  const tabela = document.getElementById("tabela-divisoes");

  if (!inputFiltro || !tabela) return;

  inputFiltro.addEventListener("input", function () {
    filtrarTabelaAdmin(inputFiltro, tabela);
  });
}

/* ========================================
   FILTRO DA TABELA DE CLUBES SEM DIVISÃO
======================================== */

function inicializarFiltroSemDivisao() {
  const inputFiltro = document.getElementById("filtro-sem-divisao");
  const tabela = document.getElementById("tabela-sem-divisao");

  if (!inputFiltro || !tabela) return;

  inputFiltro.addEventListener("input", function () {
    filtrarTabelaAdmin(inputFiltro, tabela);
  });
}

/* ========================================
   HELPER DE FILTRO
======================================== */

function filtrarTabelaAdmin(input, tabela) {
  const filtro = normalizarTextoAdmin(input.value);
  const linhas = tabela.querySelectorAll("tbody tr");

  linhas.forEach(function (linha) {
    const textoLinha = normalizarTextoAdmin(linha.textContent || "");

    linha.style.display = textoLinha.includes(filtro) ? "" : "none";
  });
}

function normalizarTextoAdmin(valor) {
  return String(valor || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .trim();
}