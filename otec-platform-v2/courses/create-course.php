<?php
require_once "../core/auth-check.php";
require_once "../config/database.php";

// Solo admin o docente puede crear cursos
if (!in_array($_SESSION['user_role'], ['admin', 'docente'])) {
    header("Location: courses-hub.php");
    exit();
}

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = trim($_POST["name"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");

    if (!$name || !$descripcion) {
        $error = "Completa todos los campos.";
    } else {
        $spaces  = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
        $base    = './base.php';
        $direct  = "./library/$spaces";
        $rutanew = "./library/$spaces/$spaces.php";

        // Verificar que el nombre no exista ya
        $check = $conn->prepare("SELECT id_curso FROM cursos WHERE nombre = ?");
        $check->execute([$spaces]);
        if ($check->fetch()) {
            $error = "Ya existe un curso con ese nombre.";
        } elseif (!file_exists($base)) {
            $error = "No se encontró el archivo base.php necesario.";
        } else {
            try {
                $sql  = "INSERT INTO cursos (nombre, ruta, description, title, estado) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                // Tabla de videos del curso
                $sqlT  = "CREATE TABLE IF NOT EXISTS `$spaces` (id_video INT AUTO_INCREMENT PRIMARY KEY, nombre varchar(60) NOT NULL, ruta VARCHAR(255) NOT NULL)";
                $stmtT = $conn->prepare($sqlT);

                if (!is_dir($direct)) mkdir($direct, 0777, true);
                copy($base, "$direct/$spaces.php");

                $stmt->execute([$spaces, $rutanew, $descripcion, $name, "activo"]);
                $stmtT->execute();

                $success = "Curso \"$name\" creado exitosamente.";
            } catch (PDOException $e) {
                $error = "Error al crear el curso: " . $e->getMessage();
            }
        }
    }
}

$base_path = '../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Curso — OTEC</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>

<?php include '../core/navbar.php'; ?>

<div class="page-title">
    <div class="container">
        <div>
            <h1>Crear nuevo curso</h1>
            <p>Agrega un curso al catálogo de la plataforma</p>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <?php if ($success): ?>
        <div class="alert alert-success" style="max-width:460px; margin:0 auto 20px;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nombre del curso</label>
                <input type="text" name="name" placeholder="Ej: Excel Avanzado" required
                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" placeholder="Describe el contenido del curso..." required><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn w-full" style="justify-content:center;">Crear curso</button>
        </form>
        <div style="text-align:center; margin-top:16px;">
            <a href="courses-hub.php" class="text-grey text-sm">← Volver a cursos</a>
        </div>
    </div>
</div>

<script src="../assets/js.js"></script>
</body>
</html>
