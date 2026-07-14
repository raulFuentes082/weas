// OTEC Platform — JS global

// Confirmaciones de eliminación más estilizadas
document.addEventListener('DOMContentLoaded', () => {
    // Marcar enlace activo en navbar
    const links = document.querySelectorAll('.navbar-links a, .sidebar nav a');
    links.forEach(link => {
        if (link.href === window.location.href) link.classList.add('active');
    });

    // Auto-cerrar alertas después de 5s
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });
});
