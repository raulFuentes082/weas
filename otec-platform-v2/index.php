<?php
session_start();
$logueado = isset($_SESSION["user_id"]);
$nombre   = $logueado ? $_SESSION["user_name"] : "";
$esAdmin  = $logueado && $_SESSION["user_role"] === "admin";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTEC Platform</title>
    <link rel="stylesheet" href="assets/css.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-brand">OTEC<span> Platform</span></a>
        <ul class="navbar-links">
            <?php if ($logueado): ?>
                <li><a href="user/dashboard.php">Inicio</a></li>
                <li><a href="courses/courses-hub.php">Cursos</a></li>
                <li><a href="enrollments/my-courses.php">Mis Cursos</a></li>
                <li><a href="user/profile.php">Perfil</a></li>
                <?php if ($esAdmin): ?>
                <li><a href="admin/admin-dashboard.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="auth/logout.php" class="btn-logout">Salir</a></li>
            <?php else: ?>
                <li><a href="auth/login.php">Iniciar sesión</a></li>
                <li><a href="auth/register.php" class="btn btn-sm">Registrarse</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="banner">
    <?php if ($logueado): ?>
        <h1>Bienvenido, <span><?php echo htmlspecialchars($nombre); ?></span> 👋</h1>
        <p>Continúa tu formación desde donde la dejaste.</p>
        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <a href="user/dashboard.php" class="btn">Mi Dashboard</a>
            <a href="courses/courses-hub.php" class="btn-outline">Ver cursos</a>
        </div>
    <?php else: ?>
        <h1>Bienvenido a <span>OTEC</span> Platform</h1>
        <p>Plataforma de formación y capacitación estudiantil. Aprende, certifícate y crece.</p>
        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <a href="auth/register.php" class="btn">Crear cuenta</a>
            <a href="auth/login.php" class="btn-outline">Iniciar sesión</a>
        </div>
    <?php endif; ?>
</div>

<section class="section-who" id="who">
    <div class="container">
        <h2>¿Quiénes somos?</h2>
        <p>Somos una OTEC estudiantil comprometida con la educación de calidad. Ofrecemos cursos prácticos y certificaciones reconocidas para impulsar tu desarrollo profesional.</p>
    </div>
</section>

<section style="padding: 70px 24px; background: var(--black);">
    <div class="container">
        <h2 style="color:var(--white); margin-bottom:8px;">Nuestros cursos</h2>
        <p style="margin-bottom:28px;">Explora nuestra oferta de capacitaciones disponibles.</p>
        <div class="cards-grid">
            <div class="card">
                <h3>📊 Excel Avanzado</h3>
                <p>Domina fórmulas, tablas dinámicas y automatización con macros.</p>
            </div>
            <div class="card">
                <h3>💻 Ofimática</h3>
                <p>Aprende las herramientas de oficina más usadas en el mundo laboral.</p>
            </div>
            <div class="card">
                <h3>🎯 Habilidades Blandas</h3>
                <p>Comunicación efectiva, trabajo en equipo y liderazgo.</p>
            </div>
        </div>
        <div style="text-align:center; margin-top:32px;">
            <a href="<?php echo $logueado ? 'courses/courses-hub.php' : 'auth/register.php'; ?>" class="btn">
                <?php echo $logueado ? 'Ver todos los cursos' : 'Inscribirme ahora'; ?>
            </a>
        </div>
    </div>
</section>

<footer style="background:var(--black-2); border-top:1px solid var(--black-5); padding:24px; text-align:center;">
    <p style="color:var(--grey-dark); font-size:0.85rem;">© <?php echo date('Y'); ?> OTEC Platform — Todos los derechos reservados</p>
</footer>

<script src="assets/js.js"></script>
</body>
</html>
