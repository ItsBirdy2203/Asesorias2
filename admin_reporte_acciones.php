<?php
session_start();
require_once 'db_conexion.php';

// Proteger el archivo para que solo el admin pueda usarlo
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reporte_id']) && isset($_POST['accion'])) {
    
    $reporte_id = $_POST['reporte_id'];
    $accion = $_POST['accion'];
    $nuevo_estado = '';

    // Determinar el nuevo estado basado en la acción
    if ($accion == 'aprobar') {
        $nuevo_estado = 'aprobado';
    } elseif ($accion == 'rechazar') {
        $nuevo_estado = 'rechazado';
    }

    // Si la acción es válida, actualizar la base de datos
    if (!empty($nuevo_estado)) {
        $stmt = $conexion->prepare("UPDATE reportes_asesorias SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_estado, $reporte_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirigir de vuelta al panel de administración
    header("Location: panel_admin.php");
    exit();

} else {
    // Si se accede al archivo incorrectamente, redirigir
    header("Location: panel_admin.php");
    exit();
}
?>
