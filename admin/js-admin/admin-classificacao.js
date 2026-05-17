/* ========================================
   ADMIN-CLASSIFICACAO.JS
   Funcionalidades da página admin-classificacao.php
   Futebol Brasileiro
======================================== */

document.addEventListener("DOMContentLoaded", function () {
  inicializarFiltroClassificacoes();
  inicializarCarregamentoTemporadas();
  inicializarModalEditarClassificacao();
  inicializarFechamentoModais();
});

/* ========================================
   FILTRO DA TABELA
======================================== */

function inicializarFiltroClassificacoes() {
  const inputFiltro = document.getElementById("filtro-classificacoes");
  const tabela = document.getElementById("tabela-classificacoes");

  if (!inputFiltro || !tabela) return;

  inputFiltro.addEventListener("input", function () {
    filtrarTabelaAdmin(inputFiltro, tabela);
  });
}

/* ========================================
   CARREGAR TEMPORADAS POR COMPETIÇÃO
======================================== */

function inicializarCarregamentoTemporadas() {
  const selectsCompeticao = document.querySelectorAll("[data-carregar-temporadas]");

  selectsCompeticao.forEach(function (selectCompeticao) {
    selectCompeticao.addEventListener("change", function () {
      const seletorDestino = selectCompeticao.dataset.carregarTemporadas;
      const selectTemporada = document.querySelector(seletorDestino);

      if (!selectTemporada) return;

      carregarTemporadasPorCompeticao(
        selectCompeticao.value,
        selectTemporada
      );
    });
  });
}

function carregarTemporadasPorCompeticao(idCompeticao, selectTemporada, temporadaSelecionada = "") {
  if (!selectTemporada) return;

  if (!idCompeticao) {
    selectTemporada.innerHTML = "<option value=''>Selecione uma competição primeiro</option>";
    return;
  }

  selectTemporada.innerHTML = "<option value=''>Carregando...</option>";

  const formData = new FormData();
  formData.append("acao", "get_temporadas_por_competicao");
  formData.append("id_competicao", idCompeticao);

  fetch("admin-process.php", {
    method: "POST",
    body: formData
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Erro ao carregar temporadas.");
      }

      return response.text();
    })
    .then(function (html) {
      selectTemporada.innerHTML = html;

      if (temporadaSelecionada !== "") {
        selectTemporada.value = String(temporadaSelecionada);
      }
    })
    .catch(function (error) {
      console.error(error);
      selectTemporada.innerHTML = "<option value=''>Erro ao carregar temporadas</option>";
    });
}

/* ========================================
   MODAL DE EDIÇÃO DE CLASSIFICAÇÃO
======================================== */

function inicializarModalEditarClassificacao() {
  const botoesEditar = document.querySelectorAll(".btn-editar-classificacao");
  const modal = document.getElementById("modal-editar-classificacao");

  if (!botoesEditar.length || !modal) return;

  botoesEditar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const id = botao.dataset.id;

      if (!id) {
        alert("ID da classificação não encontrado.");
        return;
      }

      carregarClassificacaoParaEdicao(id, modal);
    });
  });
}

function carregarClassificacaoParaEdicao(id, modal) {
  const formData = new FormData();
  formData.append("acao", "get_classificacao");
  formData.append("id", id);

  fetch("admin-process.php", {
    method: "POST",
    body: formData
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Erro ao buscar dados da classificação.");
      }

      return response.json();
    })
    .then(function (classificacao) {
      if (classificacao.erro) {
        alert(classificacao.erro);
        return;
      }

      preencherFormularioEditarClassificacao(classificacao, modal);
    })
    .catch(function (error) {
      console.error(error);
      alert("Não foi possível carregar os dados da classificação.");
    });
}

function preencherFormularioEditarClassificacao(classificacao, modal) {
  const selectCompeticao = document.getElementById("edit-id-competicao");
  const selectTemporada = document.getElementById("edit-id-temporada");

  setValorCampo("edit-classificacao-id", classificacao.id);
  setValorCampo("edit-id-time", classificacao.id_time);
  setValorCampo("edit-fase", classificacao.fase);
  setValorCampo("edit-nacional", classificacao.nacional);

  setValorCampo("edit-vitorias", classificacao.vitorias);
  setValorCampo("edit-empates", classificacao.empates);
  setValorCampo("edit-derrotas", classificacao.derrotas);
  setValorCampo("edit-gp", classificacao.gp);
  setValorCampo("edit-gc", classificacao.gc);
  setValorCampo("edit-pontos-marcados", classificacao.pontos_marcados);

  if (selectCompeticao && selectTemporada) {
    /*
      O botão da tabela já possui data-id-competicao, mas o retorno AJAX
      do get_classificacao não traz id_competicao diretamente.
      Por isso, buscamos o botão correspondente como fallback.
    */
    const botaoOrigem = document.querySelector(
      `.btn-editar-classificacao[data-id="${classificacao.id}"]`
    );

    const idCompeticao = botaoOrigem?.dataset.idCompeticao || "";
    const idTemporada = classificacao.id_temporada || "";

    selectCompeticao.value = idCompeticao;

    carregarTemporadasPorCompeticao(idCompeticao, selectTemporada, idTemporada);
  }

  abrirModalAdmin(modal);
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