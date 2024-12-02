
    document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector('.sidebar');
    const bodyContent = document.body; // Todo el contenido dinámico del body

    function adjustSidebarHeight() {
    const contentHeight = bodyContent.scrollHeight; // Altura total del body
    const viewportHeight = window.innerHeight; // Altura de la ventana
    sidebar.style.height = Math.max(contentHeight, viewportHeight) + 'px';
}

    // Ajusta al cargar y redimensionar la ventana
    window.addEventListener('load', adjustSidebarHeight);
    window.addEventListener('resize', adjustSidebarHeight);

    // Observa cambios en el contenido del body
    const observer = new MutationObserver(adjustSidebarHeight);
    observer.observe(bodyContent, { childList: true, subtree: true });
});
