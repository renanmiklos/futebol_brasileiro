document.addEventListener("DOMContentLoaded", function () {
  // ===============
  // CARROSSEL PRINCIPAL (notícias/fotos)
  // ===============
  const carrosselItems = document.querySelectorAll(".carrossel-item");
  if (carrosselItems.length > 0) {
    let currentIndex = 0;

    function showNextSlideMain() {
      carrosselItems[currentIndex].classList.remove("active");
      currentIndex = (currentIndex + 1) % carrosselItems.length;
      carrosselItems[currentIndex].classList.add("active");
    }

    carrosselItems[0].classList.add("active");
    setInterval(showNextSlideMain, 5000);
  }

  // ===============
  // CARROSSEL SECUNDÁRIO (galeria de fotos)
  // ===============
  const carrossel2Items = document.querySelectorAll(".carrossel2-item");
  if (carrossel2Items.length > 0) {
    let currentIndex2 = 0;

    function showNextSlideGallery() {
      carrossel2Items[currentIndex2].classList.remove("active");
      currentIndex2 = (currentIndex2 + 1) % carrossel2Items.length;
      carrossel2Items[currentIndex2].classList.add("active");
    }

    carrossel2Items[0].classList.add("active");
    setInterval(showNextSlideGallery, 5000);
  } else {
    console.warn("Nenhum item encontrado para o carrossel de galeria de fotos.");
  }

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