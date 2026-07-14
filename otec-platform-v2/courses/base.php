<?php
// base.php — Plantilla base que se COPIA al crear cada curso.
// Cuando se ejecuta desde library/NombreCurso/NombreCurso.php,
// las rutas relativas apuntan 3 niveles arriba (../../../).
require_once "../../../config/database.php";
require_once "../../../core/auth-check.php";

$nombre = $_SESSION["user_name"];
$esAdmin = ($_SESSION["user_role"] === "admin" || $_SESSION["user_role"] === "docente");

// Detectar el nombre del curso a partir del nombre del archivo PHP actual
$Sname = basename($_SERVER['PHP_SELF']);
$name  = strtok($Sname, '.');   // Ej: "Excel_Avanzado"

// Buscar los datos del curso en la tabla cursos
$sqlT  = "SELECT id_curso, nombre, title, description FROM cursos";
$stmtT = $conn->prepare($sqlT);
$stmtT->execute();
$tis = $stmtT->fetchAll();

$curid       = null;
$cursoTitle  = $name;
$cursoDesc   = "";

foreach ($tis as $ti) {
    if ($ti['nombre'] == $name) {
        $cursoTitle = $ti['title'];
        $cursoDesc  = $ti['description'];
        $curid      = $ti['id_curso'];
    }
}

// Verificar si el usuario ya está inscrito
$inscrito = false;
if ($curid) {
    $stmtChk = $conn->prepare("SELECT id_enroll FROM enrollments WHERE id_user = ? AND id_curso = ?");
    $stmtChk->execute([$_SESSION["user_id"], $curid]);
    $inscrito = ($stmtChk->rowCount() > 0);
}

// Obtener videos del curso
$videos = [];
try {
    $sqlV  = "SELECT nombre, ruta FROM `$name`";
    $stmtV = $conn->prepare($sqlV);
    $stmtV->execute();
    $videos = $stmtV->fetchAll();
} catch (PDOException $e) {
    // La tabla aún no existe o está vacía
}

// Subir video (solo admin/docente)
$uploadMsg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["video"]) && $esAdmin) {
    $targetDir      = "../$name/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $fileName       = time() . "_" . basename($_FILES["video"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFilePath)) {
        $sqlIns  = "INSERT INTO `$name` (nombre, ruta) VALUES (:nombre, :ruta)";
        $stmtIns = $conn->prepare($sqlIns);
        $stmtIns->bindParam(':nombre', $fileName);
        $stmtIns->bindParam(':ruta',   $targetFilePath);
        $stmtIns->execute();
        $uploadMsg = "Video subido exitosamente.";
        header("Refresh:0");
        exit;
    } else {
        $uploadMsg = "Error al subir el video.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($cursoTitle); ?> — OTEC</title>
    <link rel="stylesheet" href="../../../assets/css.css">
</head>
<body>

<!-- Navbar manual con rutas a 3 niveles -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="../../../index.php" class="navbar-brand">OTEC<span> Platform</span></a>
        <ul class="navbar-links">
            <li><a href="../../../user/dashboard.php">Inicio</a></li>
            <li><a href="../../../courses/courses-hub.php">Cursos</a></li>
            <li><a href="../../../enrollments/my-courses.php">Mis Cursos</a></li>
            <li><a href="../../../user/profile.php">Perfil</a></li>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <li><a href="../../../admin/admin-dashboard.php">Admin</a></li>
            <?php endif; ?>
            <li><a href="../../../auth/logout.php" class="btn-logout">Salir</a></li>
        </ul>
    </div>
</nav>

<!-- Cabecera del curso -->
<div class="page-title">
    <div class="container">
        <div>
            <h1><?php echo htmlspecialchars($cursoTitle); ?></h1>
            <p><?php echo htmlspecialchars($cursoDesc); ?></p>
        </div>
        <?php if (!$inscrito && $curid): ?>
            <a href="../../../enrollments/enroll.php?id_curso=<?php echo $curid; ?>" class="btn">Inscribirse al curso</a>
        <?php elseif ($inscrito): ?>
            <span class="badge badge-alumno" style="font-size:.9rem; padding:8px 16px;">✓ Inscrito</span>
        <?php endif; ?>
    </div>
</div>

<div class="page-wrapper">

    <!-- Videos del curso -->
    <?php if (empty($videos)): ?>
        <div class="card" style="text-align:center; padding:40px; margin-bottom:28px;">
            <p>Este curso aún no tiene videos publicados.</p>
        </div>
    <?php else: ?>
        <h2 style="color:var(--white); margin-bottom:20px;">Clases del curso</h2>
        <div class="video-grid">
            <?php foreach ($videos as $i => $vid): ?>
            <div class="video-card">
                <video controls preload="metadata">
                    <source src="<?php echo htmlspecialchars($vid['ruta']); ?>" type="video/mp4">
                    Tu navegador no soporta video HTML5.
                </video>
                <p>Clase <?php echo $i + 1; ?> — <?php echo htmlspecialchars($vid['nombre']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Panel de administración del curso (solo admin/docente) -->
    <?php if ($esAdmin): ?>
    <div class="card mt-8">
        <h3 style="color:var(--yellow); margin-bottom:16px;">⚙ Administrar curso</h3>

        <?php if ($uploadMsg): ?>
            <div class="alert alert-<?php echo strpos($uploadMsg, 'Error') !== false ? 'error' : 'success'; ?> mb-4">
                <?php echo htmlspecialchars($uploadMsg); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" style="margin-bottom:20px;">
            <div class="form-group">
                <label>Subir nuevo video (MP4)</label>
                <input type="file" name="video" accept="video/*" required>
            </div>
            <button type="submit" class="btn">Subir video</button>
        </form>

        <div style="display:flex; gap:10px; flex-wrap:wrap; padding-top:16px; border-top:1px solid var(--black-5);">
            <a href="../../../courses/courses-hub.php" class="btn-outline btn-sm">← Volver a cursos</a>
        </div>
    </div>
    <?php else: ?>
    <div style="margin-top:32px;">
        <a href="../../../courses/courses-hub.php" class="btn-outline btn-sm">← Volver a cursos</a>
    </div>
    <?php endif; ?>

</div>

<script src="../../../assets/js.js"></script>
</body>
</html>
