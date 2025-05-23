
document.addEventListener("DOMContentLoaded", function () {
            const carrosselItems = document.querySelectorAll(".carrossel-item");
            let currentIndex = 0;

            function showNextSlide() {
                // Remove a classe 'active' do slide atual
                carrosselItems[currentIndex].classList.remove("active");

                // Calcula o próximo índice
                currentIndex = (currentIndex + 1) % carrosselItems.length;

                // Adiciona a classe 'active' ao próximo slide
                carrosselItems[currentIndex].classList.add("active");
            }

            // Define o intervalo para trocar os slides automaticamente (ex.: a cada 5 segundos)
            setInterval(showNextSlide, 5000);

            // Inicializa o primeiro slide como ativo
            if (carrosselItems.length > 0) {
                carrosselItems[0].classList.add("active");
            }
        });


// Carrossel da galeria de fotos

document.addEventListener("DOMContentLoaded", function () {
            const carrosselItems = document.querySelectorAll(".carrossel2-item");
            
            if (carrosselItems.length === 0) {
                console.warn("Nenhum item encontrado para o carrossel.");
                return;
            }

            let currentIndex = 0;

            function showNextSlide() {
                // Remove a classe 'active' do slide atual
                carrosselItems[currentIndex].classList.remove("active");

                // Calcula o próximo índice
                currentIndex = (currentIndex + 1) % carrosselItems.length;

                // Adiciona a classe 'active' ao próximo slide
                carrosselItems[currentIndex].classList.add("active");
            }

            // Troca de slide a cada 5 segundos
            setInterval(showNextSlide, 5000);

            // Garante que o primeiro slide esteja ativo
            carrosselItems[0].classList.add("active");
        });