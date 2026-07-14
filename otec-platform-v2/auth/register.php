<?php
session_start();
require_once "../config/database.php";

// Si ya está logueado, redirigir
if (isset($_SESSION["user_id"])) {
    header("Location: ../user/dashboard.php");
    exit();
}

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST["name"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role     = "alumno";

    if (!$name || !$email || !$password) {
        $error = "Por favor completa todos los campos.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        // Verificar si el correo ya existe
        $check = $conn->prepare("SELECT id_user FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "Ese correo ya está registrado.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql  = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt->execute([$name, $email, $hashedPassword, $role])) {
                // Loguear automáticamente al registrarse
                $userId = $conn->lastInsertId();
                $_SESSION["user_id"]   = $userId;
                $_SESSION["user_name"] = $name;
                $_SESSION["user_role"] = $role;
                header("Location: ../user/dashboard.php");
                exit();
            } else {
                $error = "Error al registrar. Intenta nuevamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — OTEC</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>
<div class="auth-page">
    <div style="width:100%; max-width:440px;">
        <div class="auth-logo">OTEC<span> Platform</span></div>

        <div class="form-card">
            <h2>Crear cuenta</h2>
            <p class="subtitle">Únete a la plataforma de capacitación</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Nombre completo</label>
                    <input type="text" id="name" name="name"
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                           placeholder="Tu nombre" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="correo@ejemplo.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password"
                           placeholder="Mínimo 6 caracteres" required>
                </div>
                <button type="submit" class="btn w-full" style="justify-content:center; margin-top:8px;">
                    Registrarse
                </button>
            </form>

            <p style="text-align:center; margin-top:20px; color:var(--grey); font-size:0.9rem;">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
            </p>
        </div>
    </div>
</div>
<script src="../assets/js.js"></script>
</body>
</html>
