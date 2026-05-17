/* ========================================
   ADMIN-PONTUACOES.JS
   Funcionalidades da página admin-pontuacoes.php
   Futebol Brasileiro
======================================== */

document.addEventListener("DOMContentLoaded", function () {
  inicializarFiltroPontuacoes();
  inicializarModalEditarPontuacao();
  inicializarFechamentoModais();
});

/* ========================================
   FILTRO DA TABELA
======================================== */

function inicializarFiltroPontuacoes() {
  const inputFiltro = document.getElementById("filtro-pontuacoes");
  const tabela = document.getElementById("tabela-pontuacoes");

  if (!inputFiltro || !tabela) return;

  inputFiltro.addEventListener("input", function () {
    filtrarTabelaAdmin(inputFiltro, tabela);
  });
}

/* ========================================
   MODAL DE EDIÇÃO DE PONTUAÇÃO
======================================== */

function inicializarModalEditarPontuacao() {
  const botoesEditar = document.querySelectorAll(".btn-editar-pontuacao");
  const modal = document.getElementById("modal-editar-pontuacao");

  if (!botoesEditar.length || !modal) return;

  botoesEditar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const id = botao.dataset.id;

      if (!id) {
        alert("ID da pontuação não encontrado.");
        return;
      }

      carregarPontuacaoParaEdicao(id, modal);
    });
  });
}

function carregarPontuacaoParaEdicao(id, modal) {
  const formData = new FormData();
  formData.append("acao", "get_pontuacao");
  formData.append("id", id);

  fetch("admin-process.php", {
    method: "POST",
    body: formData
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Erro ao buscar dados da pontuação.");
      }

      return response.json();
    })
    .then(function (pontuacao) {
      if (pontuacao.erro) {
        alert(pontuacao.erro);
        return;
      }

      preencherFormularioEditarPontuacao(pontuacao);
      abrirModalAdmin(modal);
    })
    .catch(function (error) {
      console.error(error);
      alert("Não foi possível carregar os dados da pontuação.");
    });
}

function preencherFormularioEditarPontuacao(pontuacao) {
  setValorCampo("edit-pontuacao-id", pontuacao.id);
  setValorCampo("edit-pontuacao-id-competicao", pontuacao.id_competicao);
  setValorCampo("edit-pontuacao-fase", pontuacao.fase);
  setValorCampo("edit-pontuacao-pontos", pontuacao.pontos);
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

function filtrarTabelaAdmin(input, tabela) {
  const filtro = normalizarTextoAdmin(input.value);
  const linhas = tabela.querySelectorAll("tbody tr");

  linhas.forEach(function (linha) {
    const textoLinha = normalizarTextoAdmin(linha.textContent || "");

    linha.style.display = textoLinha.includes(filtro) ? "" : "none";
  });
}

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