/* ========================================
   NOTICIAS.JS
   Funcionalidades gerais da área Notícias
======================================== */

document.addEventListener("DOMContentLoaded", function () {
  /* ========================================
     BOTÃO "VOLTAR AO TOPO"
  ======================================== */

  const btnVoltarTopo = document.getElementById("voltar-ao-topo");

  if (!btnVoltarTopo) {
    return;
  }

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
});