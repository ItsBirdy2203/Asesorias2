<?php
session_start();
require_once 'db_conexion.php'; // Asegúrate de que este archivo usa las variables de entorno

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    if (empty($usuario) || empty($contrasena)) {
        header("Location: login.php?error=1");
        exit();
    }

    // Preparamos la consulta para obtener el usuario
    $stmt = $conexion->prepare("SELECT id, usuario, password, rol FROM alumnos WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();

        // --- ¡CAMBIO CLAVE! ---
        // Verificamos la contraseña hasheada
        if (password_verify($contrasena, $fila['password'])) {
            // Contraseña correcta: Iniciar sesión
            $_SESSION['alumno_id'] = $fila['id'];
            $_SESSION['usuario'] = $fila['usuario'];
            $_SESSION['rol'] = $fila['rol'];

            // Redirigir según el rol
            if ($fila['rol'] == 1) {
                header("Location: panel_admin.php");
            } elseif ($fila['rol'] == 2) {
                header("Location: panel_alumno.php");
            } else {
                // Rol desconocido, por si acaso
                header("Location: index.php");
            }
            exit();

        } else {
            // Contraseña incorrecta
            header("Location: login.php?error=1");
            exit();
        }
    } else {
        // Usuario no encontrado
        header("Location: login.php?error=1");
        exit();
    }
    $stmt->close();
} else {
    header("Location: login.php");
    exit();
}
$conexion->close();
?>
