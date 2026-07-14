<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";
$msgType = "";

// CORRECCIÓN: la columna es id_user, no id
try {
    $sql  = "SELECT name, email FROM users WHERE id_user = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) die("Usuario no encontrado.");
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST["name"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!$name || !$email) {
        $message = "Nombre y correo son obligatorios.";
        $msgType = "error";
    } else {
        try {
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $message = "La contraseña debe tener al menos 6 caracteres.";
                    $msgType = "error";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    // CORRECCIÓN: columna id_user
                    $sql  = "UPDATE users SET name = :name, email = :email, password = :password WHERE id_user = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":password", $hashed);
                    $stmt->bindParam(":name",  $name);
                    $stmt->bindParam(":email", $email);
                    $stmt->bindParam(":id",    $user_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $message = "Perfil actualizado correctamente.";
                    $msgType = "success";
                    $_SESSION["user_name"] = $name;
                    $user["name"]  = $name;
                    $user["email"] = $email;
                }
            } else {
                // CORRECCIÓN: columna id_user
                $sql  = "UPDATE users SET name = :name, email = :email WHERE id_user = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":name",  $name);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":id",    $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $message = "Perfil actualizado correctamente.";
                $msgType = "success";
                $_SESSION["user_name"] = $name;
                $user["name"]  = $name;
                $user["email"] = $email;
            }
        } catch (PDOException $e) {
            $message = "Error al actualizar: " . $e->getMessage();
            $msgType = "error";
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
    <title>Editar Perfil — OTEC</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>

<?php include '../core/navbar.php'; ?>

<div class="page-title">
    <div class="container">
        <div>
            <h1>Editar Perfil</h1>
            <p>Actualiza tu información personal</p>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msgType; ?>" style="max-width:460px; margin:0 auto 20px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Nueva contraseña <span style="color:var(--grey-dark)">(dejar vacío para no cambiar)</span></label>
                <input type="password" name="password" placeholder="Mínimo 6 caracteres">
            </div>
            <button type="submit" class="btn w-full" style="justify-content:center; margin-top:8px;">
                Guardar cambios
            </button>
        </form>
        <div style="text-align:center; margin-top:16px;">
            <a href="profile.php" class="text-grey text-sm">← Volver al perfil</a>
        </div>
    </div>
</div>

<script src="../assets/js.js"></script>
</body>
</html>
