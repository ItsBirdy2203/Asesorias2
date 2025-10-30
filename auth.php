<?php
session_start();
require_once 'db_conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['usuario'], $_POST['contrasena'])) {
    
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $stmt = $conexion->prepare("SELECT id, password, rol FROM alumnos WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        
        // Comparamos la contraseña del formulario con la de la base de datos
        if ($contrasena === $fila['password']) {
            // La contraseña es correcta, ahora vemos el rol
            if ($fila['rol'] == 1) { // Es Admin
                $_SESSION['admin_id'] = $fila['id'];
                $_SESSION['admin_usuario'] = $usuario;
                header("Location: panel_admin.php");
                exit();
            } else { // Es Alumno/Asesor (rol 2)
                $_SESSION['alumno_id'] = $fila['id'];
                $_SESSION['alumno_usuario'] = $usuario;
                $_SESSION['rol'] = $fila['rol'];
                header("Location: panel_alumno.php");
                exit();
            }
        }
    }
    
    // Si el usuario no existe o la contraseña es incorrecta, redirigimos con error
    header("Location: login.php?error=1");
    exit();
} else {
    // Si no se envían los datos, redirigimos al login
    header("Location: login.php");
    exit();
}
?>

