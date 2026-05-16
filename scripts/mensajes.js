document.addEventListener("DOMContentLoaded", () => {
    const mensajes = document.querySelectorAll('.mensaje-exito, .mensaje-error');

    if (mensajes.length > 0) {
        setTimeout(() => {
            mensajes.forEach(msg => {
                msg.style.transition = "opacity 0.6s ease";
                msg.style.opacity = "0";

                setTimeout(() => msg.remove(), 600);
            });
        }, 2500);
    }
});
