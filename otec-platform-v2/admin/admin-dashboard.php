<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}
if ($_SESSION["user_role"] !== "admin") {
    header("Location: ../user/dashboard.php");
    exit();
}

try {
    $usuarios     = $conn->query("SELECT COUNT(*) as total FROM users")->fetch()["total"];
    $cursos       = $conn->query("SELECT COUNT(*) as total FROM cursos")->fetch()["total"];
    $reports      = $conn->query("SELECT COUNT(*) as total FROM reports")->fetch()["total"];
    $inscripciones= $conn->query("SELECT COUNT(*) as total FROM enrollments")->fetch()["total"];
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Últimos reportes
$ultimosReports = [];
try {
    $ultimosReports = $conn->query("SELECT name, title, datet FROM reports ORDER BY datet DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — OTEC</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2>OTEC Admin</h2>
            <p><?php echo htmlspecialchars($_SESSION["user_name"]); ?></p>
        </div>
        <nav>
            <a href="admin-dashboard.php" class="active">📊 Dashboard</a>
            <a href="manage-users.php">👥 Usuarios</a>
            <a href="../courses/courses-hub.php">📚 Cursos</a>
            <a href="reports.php">📋 Reportes</a>
            <a href="../user/profile.php">👤 Mi perfil</a>
        </nav>
        <div class="sidebar-logout">
            <a href="../user/dashboard.php">← Ir al sitio</a>
            <br><br>
            <a href="../auth/logout.php">🚪 Cerrar sesión</a>
        </div>
    </aside>

    <!-- Contenido -->
    <main class="admin-content">
        <div class="admin-header">
            <h1>Panel de Administración</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["user_name"]); ?></p>
        </div>

        <!-- Stats -->
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:20px; margin-bottom:40px;">
            <div class="stat-card">
                <div class="stat-num"><?php echo $usuarios; ?></div>
                <div class="stat-label">Usuarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?php echo $cursos; ?></div>
                <div class="stat-label">Cursos</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?php echo $inscripciones; ?></div>
                <div class="stat-label">Inscripciones</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?php echo $reports; ?></div>
                <div class="stat-label">Reportes</div>
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:40px;">
            <div class="card">
                <h3 style="color:var(--yellow); margin-bottom:12px;">Accesos rápidos</h3>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <a href="manage-users.php" class="btn-outline btn-sm" style="text-align:center;">Gestionar usuarios</a>
                    <a href="../courses/create-course.php" class="btn-outline btn-sm" style="text-align:center;">Crear nuevo curso</a>
                    <a href="reports.php" class="btn-outline btn-sm" style="text-align:center;">Ver reportes</a>
                </div>
            </div>

            <!-- Últimos reportes -->
            <div class="card">
                <h3 style="color:var(--yellow); margin-bottom:16px;">Últimos reportes</h3>
                <?php if (empty($ultimosReports)): ?>
                    <p class="text-grey text-sm">No hay reportes aún.</p>
                <?php else: ?>
                    <?php foreach ($ultimosReports as $r): ?>
                    <div style="padding:8px 0; border-bottom:1px solid var(--black-5);">
                        <p style="color:var(--white); font-size:.9rem; font-weight:500;"><?php echo htmlspecialchars($r['title']); ?></p>
                        <p class="text-sm text-grey"><?php echo htmlspecialchars($r['name']); ?> · <?php echo date('d/m/Y', strtotime($r['datet'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                    <div style="margin-top:12px;"><a href="reports.php" class="text-sm">Ver todos →</a></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script src="../assets/js.js"></script>
</body>
</html>
