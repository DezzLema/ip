let currentSlide = 1;

function updateSlide() {
    if (totalSlides === 0) return;
    
    // Обновляем радио-кнопку
    document.getElementById("slide-" + currentSlide).checked = true;
    
    // Обновляем активную точку
    document.querySelectorAll(".nav-dot").forEach((dot, index) => {
        if (index + 1 === currentSlide) {
            dot.classList.add("active");
        } else {
            dot.classList.remove("active");
        }
    });
    
    // Запускаем анимацию пазла
    restartPuzzleAnimation();
}

function restartPuzzleAnimation() {
    // Удаляем и добавляем класс анимации для перезапуска
    const currentSlideEl = document.querySelector(".slide-" + currentSlide);
    if (currentSlideEl) {
        const pieces = currentSlideEl.querySelectorAll(".puzzle-piece");
        pieces.forEach(piece => {
            piece.style.animation = "none";
            setTimeout(() => {
                piece.style.animation = "puzzleAssemble 0.8s ease-out forwards";
            }, 10);
        });
    }
}

function nextSlide() {
    if (totalSlides <= 1) return;
    
    if (currentSlide < totalSlides) {
        currentSlide++;
    } else {
        currentSlide = 1;
    }
    updateSlide();
}

function prevSlide() {
    if (totalSlides <= 1) return;
    
    if (currentSlide > 1) {
        currentSlide--;
    } else {
        currentSlide = totalSlides;
    }
    updateSlide();
}

function goToSlide(slideNumber) {
    if (totalSlides <= 1) return;
    
    currentSlide = slideNumber;
    updateSlide();
}

// Автопереключение слайдов каждые 5 секунд
let slideInterval;

function startAutoSlide() {
    if (totalSlides > 1) {
        slideInterval = setInterval(nextSlide, 5000);
    }
}

function stopAutoSlide() {
    clearInterval(slideInterval);
}

// Инициализация
document.addEventListener("DOMContentLoaded", function() {
    if (totalSlides > 0) {
        updateSlide();
        startAutoSlide();
        
        // Останавливаем автопереключение при наведении
        const gallery = document.querySelector(".pure-css-gallery");
        if (gallery) {
            gallery.addEventListener("mouseenter", stopAutoSlide);
            gallery.addEventListener("mouseleave", startAutoSlide);
        }
        
        // Добавляем обработчики клавиатуры
        document.addEventListener("keydown", function(e) {
            if (e.key === "ArrowLeft") {
                prevSlide();
            } else if (e.key === "ArrowRight") {
                nextSlide();
            }
        });
    }
});

// Функция для перезапуска анимации при клике на текущий слайд
document.querySelectorAll(".nav-dot").forEach(dot => {
    dot.addEventListener("click", function() {
        const slideNum = parseInt(this.className.match(/dot-(\\d+)/)[1]);
        if (slideNum === currentSlide) {
            restartPuzzleAnimation();
        }
    });
});