<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    die("Acceso denegado.");
}

$accion  = $_GET["accion"] ?? "listar";
$success = "";

// CORRECCIÓN: eliminar usa id_user, no id
if ($accion == "eliminar" && isset($_GET["id"])) {
    $id = (int) $_GET["id"];
    // No eliminar el propio admin logueado
    if ($id !== (int) $_SESSION["user_id"]) {
        $conn->prepare("DELETE FROM enrollments WHERE id_user = ?")->execute([$id]);
        $conn->prepare("DELETE FROM users WHERE id_user = ?")->execute([$id]);
    }
    header("Location: manage-users.php");
    exit();
}

if ($accion == "crear" && $_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST["name"]);
    $email    = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role     = $_POST["role"];

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $role]);
    header("Location: manage-users.php");
    exit();
}

if ($accion == "editar" && $_SERVER["REQUEST_METHOD"] == "POST") {
    $id    = (int) $_POST["id"];
    $name  = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $role  = $_POST["role"];

    // CORRECCIÓN: usar id_user
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id_user=?");
    $stmt->execute([$name, $email, $role, $id]);
    header("Location: manage-users.php");
    exit();
}

$stmt    = $conn->query("SELECT id_user, name, email, role, created_at FROM users ORDER BY id_user DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usuarioEditar = null;
if ($accion == "editar" && isset($_GET["id"])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->execute([(int) $_GET["id"]]);
    $usuarioEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios — Admin</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2>OTEC Admin</h2>
            <p><?php echo htmlspecialchars($_SESSION["user_name"]); ?></p>
        </div>
        <nav>
            <a href="admin-dashboard.php">📊 Dashboard</a>
            <a href="manage-users.php" class="active">👥 Usuarios</a>
            <a href="../courses/courses-hub.php">📚 Cursos</a>
            <a href="reports.php">📋 Reportes</a>
            <a href="../user/profile.php">👤 Mi perfil</a>
        </nav>
        <div class="sidebar-logout">
            <a href="../user/dashboard.php">← Ir al sitio</a><br><br>
            <a href="../auth/logout.php">🚪 Cerrar sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <h1>Gestionar Usuarios</h1>
            <p>Crea, edita y elimina usuarios de la plataforma</p>
        </div>

        <?php if ($accion == "crear" || $accion == "editar"): ?>
        <div class="form-card" style="max-width:500px;">
            <h3 style="margin-bottom:20px; color:var(--yellow);">
                <?php echo ($accion == "editar") ? "Editar Usuario" : "Nuevo Usuario"; ?>
            </h3>
            <form method="POST">
                <?php if ($accion == "editar"): ?>
                    <input type="hidden" name="id" value="<?php echo $usuarioEditar["id_user"]; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="name" placeholder="Nombre completo"
                           value="<?php echo htmlspecialchars($usuarioEditar["name"] ?? ""); ?>" required>
                </div>
                <div class="form-group">
                    <label>Correo</label>
                    <input type="email" name="email" placeholder="correo@ejemplo.com"
                           value="<?php echo htmlspecialchars($usuarioEditar["email"] ?? ""); ?>" required>
                </div>
                <?php if ($accion == "crear"): ?>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Rol</label>
                    <select name="role">
                        <option value="alumno"  <?php if (($usuarioEditar["role"] ?? "") == "alumno")  echo "selected"; ?>>Alumno</option>
                        <option value="docente" <?php if (($usuarioEditar["role"] ?? "") == "docente") echo "selected"; ?>>Docente</option>
                        <option value="admin"   <?php if (($usuarioEditar["role"] ?? "") == "admin")   echo "selected"; ?>>Admin</option>
                    </select>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn">Guardar</button>
                    <a href="manage-users.php" class="btn-outline">Cancelar</a>
                </div>
            </form>
        </div>

        <?php else: ?>

        <div style="display:flex; justify-content:flex-end; margin-bottom:20px;">
            <a href="manage-users.php?accion=crear" class="btn">+ Nuevo usuario</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Registrado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?php echo $u["id_user"]; ?></td>
                        <td><?php echo htmlspecialchars($u["name"]); ?></td>
                        <td><?php echo htmlspecialchars($u["email"]); ?></td>
                        <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u["role"]); ?></span></td>
                        <td class="text-sm text-grey"><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="manage-users.php?accion=editar&id=<?php echo $u["id_user"]; ?>" class="btn-outline btn-sm">Editar</a>
                                <?php if ($u["id_user"] != $_SESSION["user_id"]): ?>
                                <a href="manage-users.php?accion=eliminar&id=<?php echo $u["id_user"]; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Eliminar usuario <?php echo htmlspecialchars(addslashes($u['name'])); ?>?')">
                                   Eliminar
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>
    </main>
</div>

<script src="../assets/js.js"></script>
</body>
</html>
