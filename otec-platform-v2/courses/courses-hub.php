<?php
require_once "../core/auth-check.php";
require_once "../config/database.php";

// CORRECCIÓN: también se verifica si el usuario ya está inscrito
$user_id = $_SESSION["user_id"];

$sql  = "SELECT id_curso, nombre, title, description, estado FROM cursos WHERE estado = 'activo'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener ids de cursos en los que el usuario ya está inscrito
$inscritos = [];
try {
    $stmtE = $conn->prepare("SELECT id_curso FROM enrollments WHERE id_user = ?");
    $stmtE->execute([$user_id]);
    $inscritos = array_column($stmtE->fetchAll(), 'id_curso');
} catch (PDOException $e) {}

$base_path = '../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos — OTEC</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>

<?php include '../core/navbar.php'; ?>

<div class="page-title">
    <div class="container">
        <div>
            <h1>Catálogo de cursos</h1>
            <p>Explora y únete a los cursos disponibles</p>
        </div>
        <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'docente'): ?>
        <a href="create-course.php" class="btn">+ Nuevo curso</a>
        <?php endif; ?>
    </div>
</div>

<div class="page-wrapper">
    <?php if (empty($cursos)): ?>
        <div class="card" style="text-align:center; padding:50px;">
            <h3 style="margin-bottom:10px;">Aún no hay cursos disponibles</h3>
            <p>El administrador publicará cursos próximamente.</p>
        </div>
    <?php else: ?>
        <div class="cards-grid">
            <?php foreach ($cursos as $cur):
                $activo    = ($cur['estado'] === 'activo');
                $inscrito  = in_array($cur['id_curso'], $inscritos);
            ?>
            <div class="card" style="<?php echo !$activo ? 'opacity:.5;' : ''; ?>">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                    <h3><?php echo htmlspecialchars($cur['title'] ?: $cur['nombre']); ?></h3>
                    <?php if ($inscrito): ?>
                        <span class="badge badge-alumno">Inscrito</span>
                    <?php endif; ?>
                </div>
                <p style="margin-bottom:18px; font-size:.9rem;">
                    <?php echo htmlspecialchars(substr($cur['description'], 0, 100)); echo strlen($cur['description']) > 100 ? '…' : ''; ?>
                </p>
                <?php if ($activo): ?>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <?php if ($inscrito): ?>
                            <a href="<?php echo htmlspecialchars($cur['ruta']); ?>" class="btn btn-sm">Ir al curso</a>
                        <?php else: ?>
                            <a href="../enrollments/enroll.php?id_curso=<?php echo $cur['id_curso']; ?>" class="btn btn-sm">Inscribirse</a>
                        <?php endif; ?>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="edit-course.php?nombre=<?php echo urlencode($cur['nombre']); ?>" class="btn-outline btn-sm">Editar</a>
                            <a href="delete-course.php?nombre=<?php echo urlencode($cur['nombre']); ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Eliminar este curso?')">Eliminar</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="../assets/js.js"></script>
</body>
</html>
