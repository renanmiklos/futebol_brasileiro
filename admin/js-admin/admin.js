/* ========================================
   ADMIN.JS
   Funções globais do Painel Administrativo
   Futebol Brasileiro
======================================== */

document.addEventListener("DOMContentLoaded", function () {
  inicializarConfirmacoesGlobais();
  inicializarFeedbackAutoHide();
  inicializarAtalhosDeModal();
});

/* ========================================
   CONFIRMAÇÕES GLOBAIS
======================================== */

function inicializarConfirmacoesGlobais() {
  /*
    Mantido de forma leve.
    A maior parte das confirmações já está diretamente nos forms.
    Este bloco serve para elementos futuros com data-confirm.
  */
  document.querySelectorAll("[data-confirm]").forEach(function (elemento) {
    elemento.addEventListener("click", function (event) {
      const mensagem = elemento.dataset.confirm || "Tem certeza?";

      if (!confirm(mensagem)) {
        event.preventDefault();
      }
    });
  });
}

/* ========================================
   FEEDBACK AUTO HIDE
======================================== */

function inicializarFeedbackAutoHide() {
  const feedback = document.querySelector(".feedback");

  if (!feedback) return;

  setTimeout(function () {
    feedback.style.opacity = "0";
    feedback.style.transform = "translateY(-6px)";
    feedback.style.transition = "opacity 0.35s ease, transform 0.35s ease";
  }, 4500);

  setTimeout(function () {
    feedback.style.display = "none";
  }, 5000);
}

/* ========================================
   MODAIS - FUNÇÕES GLOBAIS OPCIONAIS
======================================== */

function inicializarAtalhosDeModal() {
  /*
    Os arquivos específicos de cada página controlam seus próprios modais.
    Este bloco apenas adiciona suporte genérico para botões com:
    data-modal-open="id-do-modal"
  */

  document.querySelectorAll("[data-modal-open]").forEach(function (botao) {
    botao.addEventListener("click", function () {
      const modalId = botao.dataset.modalOpen;
      const modal = document.getElementById(modalId);

      if (modal) {
        abrirModalGlobalAdmin(modal);
      }
    });
  });

  document.querySelectorAll("[data-modal-close-global]").forEach(function (botao) {
    botao.addEventListener("click", function () {
      const modalId = botao.dataset.modalCloseGlobal;
      const modal = document.getElementById(modalId);

      if (modal) {
        fecharModalGlobalAdmin(modal);
      }
    });
  });
}

function abrirModalGlobalAdmin(modal) {
  modal.style.display = "block";
}

function fecharModalGlobalAdmin(modal) {
  modal.style.display = "none";
}

/* ========================================
   HELPERS GLOBAIS
   Podem ser usados por scripts específicos se necessário
======================================== */

window.AdminUtils = {
  normalizarTexto: function (valor) {
    return String(valor || "")
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim();
  },

  setValorCampo: function (id, valor) {
    const campo = document.getElementById(id);

    if (!campo) return;

    campo.value = valor ?? "";
  },

  filtrarTabela: function (input, tabela) {
    if (!input || !tabela) return;

    const filtro = window.AdminUtils.normalizarTexto(input.value);
    const linhas = tabela.querySelectorAll("tbody tr");

    linhas.forEach(function (linha) {
      const textoLinha = window.AdminUtils.normalizarTexto(linha.textContent || "");

      linha.style.display = textoLinha.includes(filtro) ? "" : "none";
    });
  },

  abrirModal: function (modal) {
    if (modal) {
      modal.style.display = "block";
    }
  },

  fecharModal: function (modal) {
    if (modal) {
      modal.style.display = "none";
    }
  }
};