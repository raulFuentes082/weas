<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../config/database.php";

// Si ya está logueado, redirigir
if (isset($_SESSION["user_id"])) {
    header("Location: ../user/dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email && $password) {
        $sql  = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"]   = $user["id_user"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_role"] = $user["role"];

            header("Location: ../user/dashboard.php");
            exit();
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — OTEC</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>
<div class="auth-page">
    <div style="width:100%; max-width:440px;">
        <div class="auth-logo">OTEC<span> Platform</span></div>

        <div class="form-card">
            <h2>Iniciar sesión</h2>
            <p class="subtitle">Ingresa tus credenciales para continuar</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="correo@ejemplo.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn w-full" style="justify-content:center; margin-top:8px;">
                    Entrar
                </button>
            </form>

            <p style="text-align:center; margin-top:20px; color:var(--grey); font-size:0.9rem;">
                ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
            </p>
        </div>
    </div>
</div>
<script src="../assets/js.js"></script>
</body>
</html>
