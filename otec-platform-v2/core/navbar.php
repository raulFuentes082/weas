<?php
// core/navbar.php — Recibe $base_path relativo desde el archivo que lo incluye
// Uso: $base_path = '../'; include '../core/navbar.php';
if (!isset($base_path)) $base_path = '../';
?>
<nav class="navbar">
    <div class="navbar-inner">
        <a href="<?php echo $base_path; ?>index.php" class="navbar-brand">OTEC<span> Platform</span></a>
        <ul class="navbar-links">
            <li><a href="<?php echo $base_path; ?>user/dashboard.php">Inicio</a></li>
            <li><a href="<?php echo $base_path; ?>courses/courses-hub.php">Cursos</a></li>
            <li><a href="<?php echo $base_path; ?>enrollments/my-courses.php">Mis Cursos</a></li>
            <li><a href="<?php echo $base_path; ?>user/profile.php">Perfil</a></li>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li><a href="<?php echo $base_path; ?>admin/admin-dashboard.php">Admin</a></li>
            <?php endif; ?>
            <li><a href="<?php echo $base_path; ?>auth/logout.php" class="btn-logout">Salir</a></li>
        </ul>
    </div>
</nav>
