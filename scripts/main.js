document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.tarjeta-blog-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            window.location.href = "noticia-detalles.php?id=" + id;
        });
    });
});
