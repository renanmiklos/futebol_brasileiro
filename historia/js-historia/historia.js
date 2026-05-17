/* ========================================
   HISTORIA.JS
   Funcionalidades da área História
   Futebol Brasileiro
======================================== */

document.addEventListener('DOMContentLoaded', function () {
  /* ========================================
     ANIMAÇÕES COM INTERSECTION OBSERVER
  ======================================== */

  const elementosAnimados = document.querySelectorAll(
    '.timeline-item[data-aos], .clube-card, .galeria-card, .foto-card, .miniatura-item, .miniatura-video'
  );

  if ('IntersectionObserver' in window && elementosAnimados.length > 0) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 }
    );

    elementosAnimados.forEach((item) => observer.observe(item));
  } else {
    elementosAnimados.forEach((item) => item.classList.add('visible'));
  }

  /* ========================================
     ELEMENTOS DOS MODAIS
     Só existem na página historia.php
  ======================================== */

  const modalFoto = document.getElementById('modal-foto');
  const modalVideo = document.getElementById('modal-video');
  const modalIframe = document.getElementById('modal-iframe');
  const modalVideoTitulo = document.getElementById('modal-video-titulo');
  const modalTitulo = document.getElementById('modal-titulo');

  const carouselSlides = document.getElementById('carousel-slides');
  const carouselPrev = document.getElementById('carousel-prev');
  const carouselNext = document.getElementById('carousel-next');
  const carouselIndicators = document.getElementById('carousel-indicators');

  const possuiModalFotos =
    modalFoto &&
    carouselSlides &&
    carouselPrev &&
    carouselNext &&
    carouselIndicators;

  const possuiModalVideos =
    modalVideo &&
    modalIframe &&
    modalVideoTitulo;

  let currentSlide = 0;
  let slides = [];

  /* ========================================
     FUNÇÕES AUXILIARES
  ======================================== */

  function escapeHtml(valor) {
    return String(valor ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function abrirModal(elemento) {
    if (!elemento) return;
    elemento.style.display = 'flex';
    document.body.classList.add('modal-aberto');
  }

  function fecharModais() {
    if (modalFoto) {
      modalFoto.style.display = 'none';
    }

    if (modalVideo) {
      modalVideo.style.display = 'none';
    }

    if (modalIframe) {
      modalIframe.src = '';
    }

    currentSlide = 0;
    slides = [];

    if (carouselSlides) {
      carouselSlides.innerHTML = '';
      carouselSlides.style.transform = 'translateX(0)';
    }

    if (carouselIndicators) {
      carouselIndicators.innerHTML = '';
    }

    document.body.classList.remove('modal-aberto');
  }

  /* ========================================
     CARROSSEL DO MODAL DE FOTOS
  ======================================== */

  function atualizarIndicadores() {
    if (!carouselIndicators) return;

    const indicators = carouselIndicators.querySelectorAll('.indicator');

    indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === currentSlide);
    });
  }

  function mostrarSlide(index) {
    if (!possuiModalFotos || slides.length === 0) return;

    currentSlide = (index + slides.length) % slides.length;
    carouselSlides.style.transform = `translateX(-${currentSlide * 100}%)`;

    atualizarIndicadores();
  }

  function criarIndicadores() {
    if (!carouselIndicators) return;

    carouselIndicators.innerHTML = '';

    slides.forEach((_, index) => {
      const indicator = document.createElement('button');
      indicator.type = 'button';
      indicator.className = 'indicator';
      indicator.setAttribute('aria-label', `Ir para a foto ${index + 1}`);

      indicator.addEventListener('click', () => mostrarSlide(index));

      carouselIndicators.appendChild(indicator);
    });

    atualizarIndicadores();
  }

  if (possuiModalFotos) {
    carouselPrev.addEventListener('click', () => {
      mostrarSlide(currentSlide - 1);
    });

    carouselNext.addEventListener('click', () => {
      mostrarSlide(currentSlide + 1);
    });
  }

  /* ========================================
     FECHAMENTO DOS MODAIS
  ======================================== */

  document.querySelectorAll('.modal-close').forEach((btn) => {
    btn.addEventListener('click', fecharModais);
  });

  window.addEventListener('click', (event) => {
    if (
      (modalFoto && event.target === modalFoto) ||
      (modalVideo && event.target === modalVideo)
    ) {
      fecharModais();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      fecharModais();
    }

    if (possuiModalFotos && modalFoto.style.display === 'flex') {
      if (event.key === 'ArrowLeft') {
        mostrarSlide(currentSlide - 1);
      }

      if (event.key === 'ArrowRight') {
        mostrarSlide(currentSlide + 1);
      }
    }
  });

  /* ========================================
     MODAL DE FOTOS
     Usado na página historia.php
  ======================================== */

  if (possuiModalFotos) {
    document.querySelectorAll('.miniatura-item a').forEach((link) => {
      link.addEventListener('click', async (event) => {
        const item = link.closest('.miniatura-item');

        if (!item || !item.dataset.bancoId) {
          return;
        }

        event.preventDefault();

        const bancoId = item.dataset.bancoId;
        const nomeBanco = item.dataset.nomeBanco || 'Galeria';

        try {
          const response = await fetch(`api/get-fotos.php?banco_id=${encodeURIComponent(bancoId)}`);

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
          }

          const contentType = response.headers.get('content-type') || '';

          if (!contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não é JSON:', text);
            alert('Erro: o servidor retornou uma resposta inválida.');
            return;
          }

          const data = await response.json();

          if (data.error) {
            alert(`Erro: ${data.error}`);
            return;
          }

          if (!Array.isArray(data) || data.length === 0) {
            alert('Nenhuma foto encontrada neste álbum.');
            return;
          }

          carouselSlides.innerHTML = '';
          slides = data;

          slides.forEach((foto) => {
            const caminhoImagem = escapeHtml(foto.caminho_imagem || '');
            const tituloFoto = escapeHtml(foto.titulo || '');

            const slide = document.createElement('div');
            slide.className = 'carousel-slide';

            slide.innerHTML = `
              <div class="slide-com-titulo">
                <img src="${caminhoImagem}" alt="${tituloFoto}">
                ${tituloFoto ? `<div class="slide-titulo-overlay">${tituloFoto}</div>` : ''}
              </div>
            `;

            carouselSlides.appendChild(slide);
          });

          criarIndicadores();
          mostrarSlide(0);

          if (modalTitulo) {
            modalTitulo.textContent = `Galeria: ${nomeBanco}`;
          }

          abrirModal(modalFoto);
        } catch (error) {
          console.error('Erro ao carregar fotos:', error);
          alert('Não foi possível carregar as fotos. Verifique o console para detalhes.');
        }
      });
    });
  }

  /* ========================================
     MODAL DE VÍDEOS
     Usado na página historia.php, caso ainda existam miniaturas locais
  ======================================== */

  if (possuiModalVideos) {
    document.querySelectorAll('.miniatura-video a').forEach((link) => {
      link.addEventListener('click', async (event) => {
        const titulo = link.querySelector('p')?.textContent?.trim() || '';

        if (!titulo) {
          return;
        }

        event.preventDefault();

        try {
          const response = await fetch(`api/get-videos.php?titulo=${encodeURIComponent(titulo)}`);

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
          }

          const data = await response.json();

          if (data.error) {
            alert(`Erro: ${data.error}`);
            return;
          }

          if (!Array.isArray(data) || data.length === 0) {
            alert('Nenhum vídeo encontrado.');
            return;
          }

          const video = data[0];

          if (video.youtube_id) {
            modalIframe.src = `https://www.youtube.com/embed/${encodeURIComponent(video.youtube_id)}?autoplay=1`;
            modalVideoTitulo.textContent = video.titulo || 'Vídeo do Futebol Brasileiro';
            abrirModal(modalVideo);
          } else {
            alert('Vídeo não disponível.');
          }
        } catch (error) {
          console.error('Erro ao carregar vídeo:', error);
          alert('Não foi possível carregar o vídeo. Verifique o console para detalhes.');
        }
      });
    });
  }

  /* ========================================
     BOTÃO VOLTAR AO TOPO
  ======================================== */

  const btnVoltarTopo = document.getElementById('voltar-ao-topo');

  if (btnVoltarTopo) {
    function alternarBotaoTopo() {
      if (window.scrollY > 300) {
        btnVoltarTopo.style.opacity = '1';
        btnVoltarTopo.style.visibility = 'visible';
      } else {
        btnVoltarTopo.style.opacity = '0';
        btnVoltarTopo.style.visibility = 'hidden';
      }
    }

    window.addEventListener('scroll', alternarBotaoTopo, { passive: true });

    btnVoltarTopo.addEventListener('click', function () {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });

    alternarBotaoTopo();
  }
});