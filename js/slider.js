const slider = () => {
    let currentSlide = 0;
    const slides = document.querySelector('.slides');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = document.querySelectorAll('.slide').length;

    const updateSlider = () => {
        slides.style.transform = `translateX(-${currentSlide * 100}%)`;

        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });
    };

    const nextSlide = () => {
        currentSlide = (currentSlide + 1) % totalSlides; 
        updateSlider();
    };

    const prevSlide = () => {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides; 
        updateSlider();
    };

    const goToSlide = (index) => {
        currentSlide = index; 
        updateSlider();
    };

    document.querySelector('.next').addEventListener('click', nextSlide);
    document.querySelector('.prev').addEventListener('click', prevSlide);

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => goToSlide(index));
    });
};
slider();