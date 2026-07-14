<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$success = "";
$error   = "";

// Obtener datos del usuario
try {
    $sql  = "SELECT id_user, name, email, created_at FROM users WHERE id_user = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_destroy();
        header("Location: ../auth/login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Procesar formulario de contacto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title    = trim($_POST["form_title"] ?? "");
    $msg      = trim($_POST["form_msg"] ?? "");
    $file_name = "";

    if (!$title || !$msg) {
        $error = "Por favor completa el título y el mensaje.";
    } else {
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
            $img_type = mime_content_type($_FILES["file"]["tmp_name"]);
            if (strpos($img_type, "image") === false) {
                $error = "Solo se permiten archivos de imagen.";
            } else {
                $folder = "../user/uploads/";
                if (!is_dir($folder)) mkdir($folder, 0777, true);
                $file_name = time() . "_" . basename($_FILES["file"]["name"]);
                move_uploaded_file($_FILES["file"]["tmp_name"], $folder . $file_name);
            }
        }

        if (!$error) {
            $sql  = "INSERT INTO reports (name, email, title, msg, file) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user["name"], $user["email"], $title, $msg, $file_name]);
            $success = "¡Mensaje enviado correctamente! Te responderemos pronto.";
        }
    }
}

// Obtener cursos del usuario
$myCourses = [];
try {
    $sql  = "SELECT c.nombre, c.title, c.description, c.ruta FROM enrollments e JOIN cursos c ON e.id_curso = c.id_curso WHERE e.id_user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $myCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { /* silencioso */ }

$base_path = '../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — OTEC</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>

<?php include '../core/navbar.php'; ?>

<!-- Hero Banner -->
<div class="banner">
    <h1>Hola, <span><?php echo htmlspecialchars($user["name"]); ?></span> 👋</h1>
    <p>Bienvenido a tu plataforma de capacitación. Sigue aprendiendo y certificándote.</p>
    <a href="../courses/courses-hub.php" class="btn">Ver todos los cursos</a>
</div>

<!-- Mis cursos inscritos -->
<section style="padding: 50px 24px; background: var(--black);">
    <div class="container">
        <div class="flex justify-between align-center mb-4" style="flex-wrap:wrap; gap:12px;">
            <h2 style="color:var(--white);">Mis cursos inscritos</h2>
            <a href="../enrollments/my-courses.php" class="btn-outline btn-sm">Ver todos</a>
        </div>

        <?php if (empty($myCourses)): ?>
            <div class="card" style="text-align:center; padding:40px;">
                <p style="margin-bottom:16px;">Aún no estás inscrito en ningún curso.</p>
                <a href="../courses/courses-hub.php" class="btn">Explorar cursos</a>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach (array_slice($myCourses, 0, 3) as $course): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($course['title'] ?: $course['nombre']); ?></h3>
                    <p style="margin:8px 0 16px;"><?php echo htmlspecialchars(substr($course['description'], 0, 80)); ?>…</p>
                    <a href="<?php echo htmlspecialchars($course['ruta']); ?>" class="btn btn-sm">Ir al curso</a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Quiénes somos -->
<section class="section-who" id="who">
    <div class="container">
        <h2>¿Quiénes somos?</h2>
        <p>Somos una Organización Técnica de Capacitación estudiantil comprometida con entregar formación de calidad, accesible y pertinente al mundo laboral actual. Nuestros cursos están diseñados por estudiantes para estudiantes.</p>
    </div>
</section>

<!-- Contacto -->
<section class="section-contact" id="contact">
    <div class="container" style="max-width:600px;">
        <h2>Contáctanos</h2>
        <p style="margin-bottom:28px;">¿Tienes algún problema o consulta? Rellena el formulario y te responderemos.</p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-card" style="max-width:100%;">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Asunto</label>
                    <input type="text" name="form_title" placeholder="¿En qué te podemos ayudar?" required>
                </div>
                <div class="form-group">
                    <label>Mensaje</label>
                    <textarea name="form_msg" placeholder="Describe tu consulta..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Adjuntar imagen (opcional)</label>
                    <input type="file" name="file" accept="image/*">
                </div>
                <button type="submit" class="btn w-full" style="justify-content:center;">
                    Enviar mensaje
                </button>
            </form>
        </div>
    </div>
</section>

<footer style="background:var(--black-2); border-top:1px solid var(--black-5); padding:24px; text-align:center;">
    <p style="color:var(--grey-dark); font-size:0.85rem;">© <?php echo date('Y'); ?> OTEC Platform</p>
</footer>

<script src="../assets/js.js"></script>
</body>
</html>
