<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

try {
    $sql  = "SELECT id_user, name, email, role, created_at FROM users WHERE id_user = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) { session_destroy(); header("Location: ../auth/login.php"); exit(); }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Contar cursos inscritos
$totalCursos = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM enrollments WHERE id_user = ?");
    $stmt->execute([$user_id]);
    $totalCursos = $stmt->fetch()['total'];
} catch (PDOException $e) {}

$base_path = '../';
$initial = strtoupper(substr($user['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil — OTEC</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>

<?php include '../core/navbar.php'; ?>

<div class="page-title">
    <div class="container">
        <div>
            <h1>Mi Perfil</h1>
            <p>Gestiona tu información personal</p>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <!-- Cabecera perfil -->
    <div class="profile-header">
        <div class="profile-avatar"><?php echo $initial; ?></div>
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['name']); ?></h2>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            <p>
                <span class="badge badge-<?php echo $user['role']; ?>" style="margin-top:6px; display:inline-block;">
                    <?php echo ucfirst($user['role']); ?>
                </span>
            </p>
        </div>
        <div style="margin-left:auto; text-align:right;">
            <div class="stat-card" style="min-width:100px; display:inline-block;">
                <div class="stat-num"><?php echo $totalCursos; ?></div>
                <div class="stat-label">Cursos</div>
            </div>
        </div>
    </div>

    <!-- Datos -->
    <div class="card mb-4">
        <h3 style="margin-bottom:16px; color:var(--yellow);">Información de la cuenta</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
                <p class="text-sm" style="color:var(--grey-dark); text-transform:uppercase; letter-spacing:.8px; margin-bottom:4px;">Nombre</p>
                <p style="color:var(--white); font-weight:500;"><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <div>
                <p class="text-sm" style="color:var(--grey-dark); text-transform:uppercase; letter-spacing:.8px; margin-bottom:4px;">Correo</p>
                <p style="color:var(--white); font-weight:500;"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div>
                <p class="text-sm" style="color:var(--grey-dark); text-transform:uppercase; letter-spacing:.8px; margin-bottom:4px;">Rol</p>
                <p style="color:var(--white); font-weight:500;"><?php echo ucfirst($user['role']); ?></p>
            </div>
            <div>
                <p class="text-sm" style="color:var(--grey-dark); text-transform:uppercase; letter-spacing:.8px; margin-bottom:4px;">Miembro desde</p>
                <p style="color:var(--white); font-weight:500;"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="profile-links">
        <a href="edit-profile.php" class="btn">Editar perfil</a>
        <a href="../enrollments/my-courses.php" class="btn-outline">Mis cursos</a>
        <a href="../user/dashboard.php" class="btn-outline">Dashboard</a>
        <a href="../auth/logout.php" style="color:var(--danger); margin-left:auto; font-size:.9rem; align-self:center;">Cerrar sesión</a>
    </div>
</div>

<script src="../assets/js.js"></script>
</body>
</html>
