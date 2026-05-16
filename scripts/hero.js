document.addEventListener("DOMContentLoaded", () => {

    const slides = document.querySelectorAll(".hero-slide");
    let index = 0;

    if (slides.length === 0) return;

    // Mostrar la primera imagen
    slides[0].classList.add("active");

    function showNextSlide() {
        slides[index].classList.remove("active");
        index = (index + 1) % slides.length;
        slides[index].classList.add("active");
    }

    setInterval(showNextSlide, 4000); // Cambia cada 4 segundos
});
