document.addEventListener("DOMContentLoaded", () => {

    const slides = document.querySelectorAll(".slide");
    let index = 0;

    function cambiarSlide() {
        slides[index].classList.remove("active");
        index = (index + 1) % slides.length;
        slides[index].classList.add("active");
    }

    setInterval(cambiarSlide, 7000);
});
