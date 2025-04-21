document.addEventListener('DOMContentLoaded', () => {
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const closeBtn = document.querySelector('.close');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    const galleryImages = document.querySelectorAll('.gallery-image');

    let currentIndex = 0;

    // Открытие лайтбокса с анимацией
    galleryImages.forEach((image, index) => {
        image.addEventListener('click', () => {
            currentIndex = index;
            lightbox.style.display = 'flex';
            lightboxImg.src = image.src;
        });
    });

    // Закрытие лайтбокса с анимацией
    closeBtn.addEventListener('click', () => {
        lightbox.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            lightbox.style.display = 'none';
            lightbox.style.animation = '';
        }, 300);
    });

    // Закрытие лайтбокса при клике вне изображения
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            lightbox.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                lightbox.style.display = 'none';
                lightbox.style.animation = '';
            }, 300);
        }
    });

    // Переключение на предыдущее изображение
    prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
        lightboxImg.src = galleryImages[currentIndex].src;
    });

    // Переключение на следующее изображение
    nextBtn.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % galleryImages.length;
        lightboxImg.src = galleryImages[currentIndex].src;
    });

    // Переключение изображений с помощью клавиатуры
    document.addEventListener('keydown', (e) => {
        if (lightbox.style.display === 'flex') {
            if (e.key === 'ArrowLeft') {
                currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
                lightboxImg.src = galleryImages[currentIndex].src;
            } else if (e.key === 'ArrowRight') {
                currentIndex = (currentIndex + 1) % galleryImages.length;
                lightboxImg.src = galleryImages[currentIndex].src;
            } else if (e.key === 'Escape') {
                lightbox.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    lightbox.style.display = 'none';
                    lightbox.style.animation = '';
                }, 300);
            }
        }
    });
});