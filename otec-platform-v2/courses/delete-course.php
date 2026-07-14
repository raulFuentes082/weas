<?php
require_once "../core/auth-check.php";
require_once "../config/database.php";

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: courses-hub.php");
    exit();
}

$nombre = $_GET["nombre"] ?? "";
if (!$nombre) {
    header("Location: courses-hub.php");
    exit();
}

$nombre = preg_replace('/[^a-zA-Z0-9_]/', '_', $nombre);

try {
    // Eliminar enrollments relacionados
    $stmtE = $conn->prepare("DELETE FROM enrollments WHERE id_curso = (SELECT id_curso FROM cursos WHERE nombre = ?)");
    $stmtE->execute([$nombre]);

    // Eliminar curso
    $stmt = $conn->prepare("UPDATE cursos SET estado = 'inactivo' WHERE nombre = ?");
    $stmt->execute([$nombre]);

} catch (PDOException $e) {
    // silencioso en producción
}

header("Location: courses-hub.php");
exit();
