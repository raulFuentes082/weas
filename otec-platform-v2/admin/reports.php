<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    die("Acceso denegado.");
}

if (isset($_GET["eliminar"])) {
    $stmt = $conn->prepare("DELETE FROM reports WHERE id_report = ?");
    $stmt->execute([(int) $_GET["eliminar"]]);
    header("Location: reports.php");
    exit();
}

$stmt    = $conn->query("SELECT * FROM reports ORDER BY datet DESC");
$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes — Admin</title>
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
            <a href="manage-users.php">👥 Usuarios</a>
            <a href="../courses/courses-hub.php">📚 Cursos</a>
            <a href="reports.php" class="active">📋 Reportes</a>
            <a href="../user/profile.php">👤 Mi perfil</a>
        </nav>
        <div class="sidebar-logout">
            <a href="../user/dashboard.php">← Ir al sitio</a><br><br>
            <a href="../auth/logout.php">🚪 Cerrar sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <h1>Reportes / Contactos</h1>
            <p>Mensajes recibidos de los usuarios</p>
        </div>

        <?php if (empty($reportes)): ?>
            <div class="card" style="text-align:center; padding:50px;">
                <p>No hay reportes aún.</p>
            </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Asunto</th>
                        <th>Mensaje</th>
                        <th>Fecha</th>
                        <th>Archivo</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportes as $r): ?>
                    <tr>
                        <td class="text-grey"><?php echo $r["id_report"]; ?></td>
                        <td>
                            <strong style="color:var(--white);"><?php echo htmlspecialchars($r["name"]); ?></strong><br>
                            <span class="text-sm text-grey"><?php echo htmlspecialchars($r["email"]); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($r["title"]); ?></td>
                        <td style="max-width:220px;">
                            <span title="<?php echo htmlspecialchars($r['msg']); ?>" style="overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
                                <?php echo htmlspecialchars($r["msg"]); ?>
                            </span>
                        </td>
                        <td class="text-sm text-grey"><?php echo date('d/m/Y H:i', strtotime($r["datet"])); ?></td>
                        <td>
                            <?php if ($r["file"]): ?>
                                <a href="../user/uploads/<?php echo htmlspecialchars($r["file"]); ?>" target="_blank" class="text-sm">Ver archivo</a>
                            <?php else: ?>
                                <span class="text-grey text-sm">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="reports.php?eliminar=<?php echo $r["id_report"]; ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Eliminar este reporte?')">
                               Eliminar
                            </a>
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
