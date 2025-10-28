<?php
session_start();
require_once 'db_conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    switch ($accion) {
        case 'cambiar_contrasena':
            if (isset($_POST['alumno_id'], $_POST['nueva_contrasena'])) {
                $alumno_id = $_POST['alumno_id'];
                $nueva_contrasena = $_POST['nueva_contrasena']; // La contraseña va en texto plano

                // --- CAMBIO IMPORTANTE ---
                // Se quita el password_hash()
                $stmt = $conexion->prepare("UPDATE alumnos SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $nueva_contrasena, $alumno_id);
                
                if ($stmt->execute()) {
                    header("Location: panel_admin.php?exito=1");
                } else {
                    header("Location: panel_admin.php?error=1");
                }
                $stmt->close();
            } else {
                 header("Location: panel_admin.php?error=1");
            }
            break;
    }
}

$conexion->close();
?>