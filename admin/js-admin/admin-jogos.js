/* ========================================
   ADMIN-JOGOS.JS
   Funcionalidades da página admin-jogos.php
   Futebol Brasileiro
======================================== */

document.addEventListener("DOMContentLoaded", function () {
  inicializarFiltroJogos();
  inicializarCarregamentoTemporadas();
  inicializarCamposTimesManuais();
  inicializarModalEditarJogo();
  inicializarFechamentoModais();
});

/* ========================================
   FILTRO DA TABELA DE JOGOS
======================================== */

function inicializarFiltroJogos() {
  const inputFiltro = document.getElementById("filtro-jogos");
  const tabela = document.getElementById("tabela-jogos");

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

      carregarTemporadasPorCompeticao(selectCompeticao.value, selectTemporada);
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
   CAMPOS DE TIMES MANUAIS
======================================== */

function inicializarCamposTimesManuais() {
  configurarCampoManual("id_time1", "nome_time1");
  configurarCampoManual("id_time2", "nome_time2");
  configurarCampoManual("edit-id-time1", "edit-nome-time1");
  configurarCampoManual("edit-id-time2", "edit-nome-time2");

  /*
    Também aplica o comportamento nas linhas de inserção múltipla.
    Como os campos não possuem IDs fixos, usamos os nomes.
  */
  document
    .querySelectorAll('select[name$="[id_time1]"], select[name$="[id_time2]"]')
    .forEach(function (selectTime) {
      selectTime.addEventListener("change", function () {
        const row = selectTime.closest("tr");
        if (!row) return;

        const isTime1 = selectTime.name.includes("[id_time1]");
        const inputManual = row.querySelector(
          isTime1 ? 'input[name$="[nome_time1]"]' : 'input[name$="[nome_time2]"]'
        );

        alternarCampoManual(selectTime, inputManual);
      });

      const row = selectTime.closest("tr");
      if (!row) return;

      const isTime1 = selectTime.name.includes("[id_time1]");
      const inputManual = row.querySelector(
        isTime1 ? 'input[name$="[nome_time1]"]' : 'input[name$="[nome_time2]"]'
      );

      alternarCampoManual(selectTime, inputManual);
    });
}

function configurarCampoManual(selectId, inputId) {
  const select = document.getElementById(selectId);
  const input = document.getElementById(inputId);

  if (!select || !input) return;

  select.addEventListener("change", function () {
    alternarCampoManual(select, input);
  });

  alternarCampoManual(select, input);
}

function alternarCampoManual(select, input) {
  if (!select || !input) return;

  if (select.value) {
    input.value = "";
    input.disabled = true;
    input.placeholder = "Usando time cadastrado";
  } else {
    input.disabled = false;
    input.placeholder = "Digite o nome manual";
  }
}

/* ========================================
   MODAL DE EDIÇÃO DE JOGO
======================================== */

function inicializarModalEditarJogo() {
  const botoesEditar = document.querySelectorAll(".btn-editar-jogo");
  const modal = document.getElementById("modal-editar-jogo");

  if (!botoesEditar.length || !modal) return;

  botoesEditar.forEach(function (botao) {
    botao.addEventListener("click", function () {
      const id = botao.dataset.id;

      if (!id) {
        alert("ID do jogo não encontrado.");
        return;
      }

      carregarJogoParaEdicao(id, modal);
    });
  });
}

function carregarJogoParaEdicao(id, modal) {
  const formData = new FormData();
  formData.append("acao", "get_jogo");
  formData.append("id", id);

  fetch("admin-process.php", {
    method: "POST",
    body: formData
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Erro ao buscar dados do jogo.");
      }

      return response.json();
    })
    .then(function (jogo) {
      if (jogo.erro) {
        alert(jogo.erro);
        return;
      }

      preencherFormularioEditarJogo(jogo, modal);
    })
    .catch(function (error) {
      console.error(error);
      alert("Não foi possível carregar os dados do jogo.");
    });
}

function preencherFormularioEditarJogo(jogo, modal) {
  const selectCompeticao = document.getElementById("edit-id-competicao");
  const selectTemporada = document.getElementById("edit-id-temporada");

  setValorCampo("edit-jogo-id", jogo.id);
  setValorCampo("edit-id-time1", jogo.id_time1);
  setValorCampo("edit-nome-time1", jogo.nome_time1);
  setValorCampo("edit-id-time2", jogo.id_time2);
  setValorCampo("edit-nome-time2", jogo.nome_time2);

  setValorCampo("edit-data", jogo.data);
  setValorCampo("edit-rodada", jogo.rodada);
  setValorCampo("edit-estadio", jogo.estadio);

  setValorCampo("edit-gols-time1", jogo.gols_time1);
  setValorCampo("edit-gols-time2", jogo.gols_time2);
  setValorCampo("edit-penaltis-time1", jogo.penaltis_time1);
  setValorCampo("edit-penaltis-time2", jogo.penaltis_time2);

  if (selectCompeticao && selectTemporada) {
    selectCompeticao.value = jogo.id_competicao || "";

    carregarTemporadasPorCompeticao(
      jogo.id_competicao || "",
      selectTemporada,
      jogo.id_temporada || ""
    );
  }

  configurarCampoManual("edit-id-time1", "edit-nome-time1");
  configurarCampoManual("edit-id-time2", "edit-nome-time2");

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