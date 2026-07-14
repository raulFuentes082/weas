<?php
require_once "../core/auth-check.php";
require_once "../config/database.php";

$user_id = $_SESSION["user_id"];

$sql  = "SELECT c.id_curso, c.nombre, c.title, c.description, c.ruta, e.enrolled_at
         FROM enrollments e
         JOIN cursos c ON e.id_curso = c.id_curso
         WHERE e.id_user = ?
         ORDER BY e.enrolled_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$base_path = '../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cursos — OTEC</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>

<?php include '../core/navbar.php'; ?>

<div class="page-title">
    <div class="container">
        <div>
            <h1>Mis cursos</h1>
            <p>Cursos en los que estás inscrito</p>
        </div>
        <a href="../courses/courses-hub.php" class="btn-outline btn-sm">Ver catálogo</a>
    </div>
</div>

<div class="page-wrapper">
    <?php if (empty($courses)): ?>
        <div class="card" style="text-align:center; padding:50px;">
            <h3 style="margin-bottom:12px;">Aún no tienes cursos inscritos</h3>
            <p style="margin-bottom:20px;">Explora el catálogo y regístrate en los cursos que te interesen.</p>
            <a href="../courses/courses-hub.php" class="btn">Explorar cursos</a>
        </div>
    <?php else: ?>
        <div class="cards-grid">
            <?php foreach ($courses as $course): ?>
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <h3><?php echo htmlspecialchars($course['title'] ?: $course['nombre']); ?></h3>
                    <span class="badge badge-alumno">Inscrito</span>
                </div>
                <p style="font-size:.88rem; margin-bottom:6px;">
                    <?php echo htmlspecialchars(substr($course['description'], 0, 90)); echo strlen($course['description']) > 90 ? '…' : ''; ?>
                </p>
                <p class="text-sm text-grey" style="margin-bottom:16px;">
                    Inscrito el <?php echo date('d/m/Y', strtotime($course['enrolled_at'])); ?>
                </p>
                <a href="<?php echo htmlspecialchars($course['ruta']); ?>" class="btn btn-sm">Ir al curso</a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="../assets/js.js"></script>
</body>
</html>
