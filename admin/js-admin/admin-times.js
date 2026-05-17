/* ========================================
   ADMIN-TIMES.JS
   Funcionalidades da página admin-times.php
   Futebol Brasileiro
======================================== */

document.addEventListener("DOMContentLoaded", function () {
  inicializarFiltroTimes();
  inicializarModalEditarTime();
  inicializarFechamentoModais();
});

/* ========================================
   FILTRO DA TABELA DE TIMES
======================================== */

function inicializarFiltroTimes() {
  const inputFiltro = document.getElementById("filtro-times");
  const tabela = document.getElementById("tabela-times");

  if (!inputFiltro || !tabela) return;

  inputFiltro.addEventListener("input", function () {
    const filtro = normalizarTextoAdmin(inputFiltro.value);
    const linhas = tabela.querySelectorAll("tbody tr");

    linhas.forEach(function (linha) {
      const textoLinha = normalizarTextoAdmin(linha.textContent || "");

      linha.style.display = textoLinha.includes(filtro) ? "" : "none";
    });
  });
}

/* ========================================
   MODAL DE EDIÇÃO DE TIME
======================================== */

function inicializarModalEditarTime() {
  const botoesEditar = document.querySelectorAll(".btn-editar-time");
  const modal = document.getElementById("modal-editar-time");

  if (!botoesEditar.length || !modal) return;

  botoesEditar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const id = botao.dataset.id;

      if (!id) {
        alert("ID do time não encontrado.");
        return;
      }

      carregarTimeParaEdicao(id, modal);
    });
  });
}

function carregarTimeParaEdicao(id, modal) {
  const formData = new FormData();
  formData.append("acao", "get_time");
  formData.append("id", id);

  fetch("admin-process.php", {
    method: "POST",
    body: formData
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Erro ao buscar dados do time.");
      }

      return response.json();
    })
    .then(function (time) {
      if (time.erro) {
        alert(time.erro);
        return;
      }

      preencherFormularioEditarTime(time);
      abrirModalAdmin(modal);
    })
    .catch(function (error) {
      console.error(error);
      alert("Não foi possível carregar os dados do time.");
    });
}

function preencherFormularioEditarTime(time) {
  setValorCampo("edit-id", time.id);
  setValorCampo("edit-nome", time.nome);
  setValorCampo("edit-nome-completo", time.nome_completo);
  setValorCampo("edit-estado", time.estado);
  setValorCampo("edit-cidade", time.cidade);
  setValorCampo("edit-fundacao", time.fundacao);
  setValorCampo("edit-estadio", time.estadio);
  setValorCampo("edit-capacidade", time.capacidade);
  setValorCampo("edit-extinto", time.extinto);
  setValorCampo("edit-escudo", time.escudo);
  setValorCampo("edit-time", time.time);
  setValorCampo("edit-legenda", time.legenda);
  setValorCampo("edit-historia", time.historia);
  setValorCampo("edit-titulos", time.titulos);

  for (let i = 1; i <= 10; i++) {
    setValorCampo(`edit-extra${i}`, time[`extra${i}`]);
    setValorCampo(`edit-legenda${i}`, time[`legenda${i}`]);
  }
}

/* ========================================
   MODAIS
======================================== */

function inicializarFechamentoModais() {
  const botoesFechar = document.querySelectorAll("[data-modal-close]");

  botoesFechar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const modalId = botao.dataset.modalClose;
      const modal = document.getElementById(modalId);

      if (modal) {
        fecharModalAdmin(modal);
      }
    });
  });

  window.addEventListener("click", function (event) {
    if (event.target.classList.contains("modal")) {
      fecharModalAdmin(event.target);
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      document.querySelectorAll(".modal").forEach(function (modal) {
        fecharModalAdmin(modal);
      });
    }
  });
}

function abrirModalAdmin(modal) {
  modal.style.display = "block";
}

function fecharModalAdmin(modal) {
  modal.style.display = "none";
}

/* ========================================
   HELPERS
======================================== */

function setValorCampo(id, valor) {
  const campo = document.getElementById(id);

  if (!campo) return;

  campo.value = valor ?? "";
}

function normalizarTextoAdmin(valor) {
  return String(valor || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .trim();
}