/* ========================================
   ADMIN-COMPETICOES.JS
   Funcionalidades da página admin-competicoes.php
   Futebol Brasileiro
======================================== */

document.addEventListener("DOMContentLoaded", function () {
  inicializarFiltroCompeticoes();
  inicializarFiltroFotosCompeticoes();
  inicializarSlugCompeticao();
  inicializarModalEditarCompeticao();
  inicializarModalEditarFotoCompeticao();
  inicializarFechamentoModais();
});

/* ========================================
   FILTRO DA TABELA DE COMPETIÇÕES
======================================== */

function inicializarFiltroCompeticoes() {
  const inputFiltro = document.getElementById("filtro-competicoes");
  const tabela = document.getElementById("tabela-competicoes");

  if (!inputFiltro || !tabela) return;

  inputFiltro.addEventListener("input", function () {
    filtrarTabelaAdmin(inputFiltro, tabela);
  });
}

/* ========================================
   FILTRO DA TABELA DE FOTOS
======================================== */

function inicializarFiltroFotosCompeticoes() {
  const inputFiltro = document.getElementById("filtro-fotos-competicoes");
  const tabela = document.getElementById("tabela-fotos-competicoes");

  if (!inputFiltro || !tabela) return;

  inputFiltro.addEventListener("input", function () {
    filtrarTabelaAdmin(inputFiltro, tabela);
  });
}

/* ========================================
   GERAR SLUG AUTOMÁTICO
======================================== */

function inicializarSlugCompeticao() {
  const nome = document.getElementById("nome");
  const slug = document.getElementById("slug");

  if (!nome || !slug) return;

  nome.addEventListener("input", function () {
    if (slug.dataset.editadoManualmente === "1") return;

    slug.value = gerarSlugAdmin(nome.value);
  });

  slug.addEventListener("input", function () {
    slug.dataset.editadoManualmente = "1";
  });
}

/* ========================================
   MODAL DE EDIÇÃO DE COMPETIÇÃO
======================================== */

function inicializarModalEditarCompeticao() {
  const botoesEditar = document.querySelectorAll(".btn-editar-competicao");
  const modal = document.getElementById("modal-editar-competicao");

  if (!botoesEditar.length || !modal) return;

  botoesEditar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const id = botao.dataset.id;

      if (!id) {
        alert("ID da competição não encontrado.");
        return;
      }

      carregarCompeticaoParaEdicao(id, modal);
    });
  });
}

function carregarCompeticaoParaEdicao(id, modal) {
  const formData = new FormData();
  formData.append("acao", "get_competicao");
  formData.append("id", id);

  fetch("admin-process.php", {
    method: "POST",
    body: formData
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Erro ao buscar dados da competição.");
      }

      return response.json();
    })
    .then(function (competicao) {
      if (competicao.erro) {
        alert(competicao.erro);
        return;
      }

      preencherFormularioEditarCompeticao(competicao);
      abrirModalAdmin(modal);
    })
    .catch(function (error) {
      console.error(error);
      alert("Não foi possível carregar os dados da competição.");
    });
}

function preencherFormularioEditarCompeticao(competicao) {
  setValorCampo("edit-competicao-id", competicao.id);
  setValorCampo("edit-competicao-nome", competicao.nome);
  setValorCampo("edit-competicao-slug", competicao.slug);
  setValorCampo("edit-competicao-tipo", competicao.tipo);
  setValorCampo("edit-competicao-amistoso", competicao.amistoso);
}

/* ========================================
   MODAL DE EDIÇÃO DE FOTO DE COMPETIÇÃO
======================================== */

function inicializarModalEditarFotoCompeticao() {
  const botoesEditar = document.querySelectorAll(".btn-editar-foto-competicao");
  const modal = document.getElementById("modal-editar-foto-competicao");

  if (!botoesEditar.length || !modal) return;

  botoesEditar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const id = botao.dataset.id;

      if (!id) {
        alert("ID da foto não encontrado.");
        return;
      }

      carregarFotoCompeticaoParaEdicao(id, modal);
    });
  });
}

function carregarFotoCompeticaoParaEdicao(id, modal) {
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

      preencherFormularioEditarFotoCompeticao(foto);
      abrirModalAdmin(modal);
    })
    .catch(function (error) {
      console.error(error);
      alert("Não foi possível carregar os dados da foto.");
    });
}

function preencherFormularioEditarFotoCompeticao(foto) {
  setValorCampo("edit-foto-id", foto.id);
  setValorCampo("edit-foto-titulo", foto.titulo);
  setValorCampo("edit-foto-descricao", foto.descricao);
  setValorCampo("edit-foto-caminho", foto.caminho_imagem);
  setValorCampo("edit-foto-id-competicao", foto.id_competicao);
  setValorCampo("edit-foto-id-temporada", foto.id_temporada);
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

function gerarSlugAdmin(valor) {
  return String(valor || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ç/g, "c")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

function normalizarTextoAdmin(valor) {
  return String(valor || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .trim();
}