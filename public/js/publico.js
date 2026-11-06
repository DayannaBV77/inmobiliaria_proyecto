// Galería de detalle: al hacer clic en una miniatura cambia la imagen principal.
document.querySelectorAll('.miniatura').forEach(function (miniatura) {
    miniatura.addEventListener('click', function () {
        document.getElementById('imagenPrincipal').src = miniatura.dataset.full || miniatura.src;
    });
});

// Cierra el menú móvil de Bootstrap al hacer clic en un enlace.
document.querySelectorAll('.navbar-public .nav-link').forEach(function (enlace) {
    enlace.addEventListener('click', function () {
        const menu = document.getElementById('menuPublico');
        if (menu && menu.classList.contains('show')) {
            bootstrap.Collapse.getOrCreateInstance(menu).hide();
        }
    });
});
