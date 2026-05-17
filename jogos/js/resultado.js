document.addEventListener("DOMContentLoaded", function () {
    // ===============
  // BOTÃO "VOLTAR AO TOPO"
  // ===============
  const btn = document.getElementById("voltar-ao-topo");
  if (btn) {
    window.addEventListener("scroll", function () {
      if (window.scrollY > 300) {
        btn.style.opacity = "1";
        btn.style.visibility = "visible";
      } else {
        btn.style.opacity = "0";
        btn.style.visibility = "hidden";
      }
    });

    btn.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });
  }
});