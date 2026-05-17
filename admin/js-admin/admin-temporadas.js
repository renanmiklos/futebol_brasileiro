/* ========================================
   ADMIN-TEMPORADAS.JS
   Funcionalidades da página admin-temporadas.php
   Futebol Brasileiro
======================================== */

document.addEventListener("DOMContentLoaded", function () {
  inicializarFiltroTemporadas();
  inicializarFiltroFotosTemporadas();
  inicializarModalEditarTemporada();
  inicializarModalEditarFotoTemporada();
  inicializarFechamentoModais();
});

/* ========================================
   FILTRO DA TABELA DE TEMPORADAS
======================================== */

function inicializarFiltroTemporadas() {
  const inputFiltro = document.getElementById("filtro-temporadas");
  const tabela = document.getElementById("tabela-temporadas");

  if (!inputFiltro || !tabela) return;

  inputFiltro.addEventListener("input", function () {
    filtrarTabelaAdmin(inputFiltro, tabela);
  });
}

/* ========================================
   FILTRO DA TABELA DE FOTOS
======================================== */

function inicializarFiltroFotosTemporadas() {
  const inputFiltro = document.getElementById("filtro-fotos-temporadas");
  const tabela = document.getElementById("tabela-fotos-temporadas");

  if (!inputFiltro || !tabela) return;

  inputFiltro.addEventListener("input", function () {
    filtrarTabelaAdmin(inputFiltro, tabela);
  });
}

/* ========================================
   MODAL DE EDIÇÃO DE TEMPORADA
======================================== */

function inicializarModalEditarTemporada() {
  const botoesEditar = document.querySelectorAll(".btn-editar-temporada");
  const modal = document.getElementById("modal-editar-temporada");

  if (!botoesEditar.length || !modal) return;

  botoesEditar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const id = botao.dataset.id;

      if (!id) {
        alert("ID da temporada não encontrado.");
        return;
      }

      carregarTemporadaParaEdicao(id, modal);
    });
  });
}

function carregarTemporadaParaEdicao(id, modal) {
  const formData = new FormData();
  formData.append("acao", "get_temporada");
  formData.append("id", id);

  fetch("admin-process.php", {
    method: "POST",
    body: formData
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Erro ao buscar dados da temporada.");
      }

      return response.json();
    })
    .then(function (temporada) {
      if (temporada.erro) {
        alert(temporada.erro);
        return;
      }

      preencherFormularioEditarTemporada(temporada);
      abrirModalAdmin(modal);
    })
    .catch(function (error) {
      console.error(error);
      alert("Não foi possível carregar os dados da temporada.");
    });
}

function preencherFormularioEditarTemporada(temporada) {
  setValorCampo("edit-temporada-id", temporada.id);
  setValorCampo("edit-temporada-id-competicao", temporada.id_competicao);
  setValorCampo("edit-temporada-ano", temporada.ano);
  setValorCampo("edit-temporada-descricao", temporada.descricao);
}

/* ========================================
   MODAL DE EDIÇÃO DE FOTO DE TEMPORADA
======================================== */

function inicializarModalEditarFotoTemporada() {
  const botoesEditar = document.querySelectorAll(".btn-editar-foto-temporada");
  const modal = document.getElementById("modal-editar-foto-temporada");

  if (!botoesEditar.length || !modal) return;

  botoesEditar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const id = botao.dataset.id;

      if (!id) {
        alert("ID da foto não encontrado.");
        return;
      }

      carregarFotoTemporadaParaEdicao(id, modal);
    });
  });
}

function carregarFotoTemporadaParaEdicao(id, modal) {
  const formData = new FormData();
  formData.append("acao", "get_foto");
  formData.append("id", id);

  fetch("admin-process.php", {
    method: "POST",
    body: formData
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Erro ao buscar dados da foto.");
      }

      return response.json();
    })
    .then(function (foto) {
      if (foto.erro) {
        alert(foto.erro);
        return;
      }

      preencherFormularioEditarFotoTemporada(foto);
      abrirModalAdmin(modal);
    })
    .catch(function (error) {
      console.error(error);
      alert("Não foi possível carregar os dados da foto.");
    });
}

function preencherFormularioEditarFotoTemporada(foto) {
  setValorCampo("edit-foto-id", foto.id);
  setValorCampo("edit-foto-titulo", foto.titulo);
  setValorCampo("edit-foto-descricao", foto.descricao);
  setValorCampo("edit-foto-caminho", foto.caminho_imagem);
  setValorCampo("edit-foto-id-temporada", foto.id_temporada);
  setValorCampo("edit-foto-id-competicao", foto.id_competicao);
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