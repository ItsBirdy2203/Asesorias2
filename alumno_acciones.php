<?php
session_start();
require_once 'db_conexion.php';

if (!isset($_SESSION['alumno_id'])) {
    header("Location: login.php");
    exit();
}

$alumno_id = $_SESSION['alumno_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {

    // --- ACCIÓN MODIFICADA: Actualizar Perfil (con Carrera) ---
    if ($_POST['accion'] == 'actualizar_perfil') {

        // Obtenemos los datos (añadimos 'carrera')
        $carrera = $_POST['carrera'];
        $materias = $_POST['materias'];
        $contacto = $_POST['contacto'];

        $horario_lunes = $_POST['horario_lunes'];
        $horario_martes = $_POST['horario_martes'];
        $horario_miercoles = $_POST['horario_miercoles'];
        $horario_jueves = $_POST['horario_jueves'];
        $horario_viernes = $_POST['horario_viernes'];

        // Preparamos la consulta SQL (añadimos 'carrera')
        $stmt = $conexion->prepare("UPDATE perfiles_asesores SET 
                                        carrera = ?, 
                                        materias = ?, 
                                        horario_lunes = ?, 
                                        horario_martes = ?, 
                                        horario_miercoles = ?, 
                                        horario_jueves = ?, 
                                        horario_viernes = ?, 
                                        contacto = ? 
                                    WHERE alumno_id = ?");

        // Actualizamos el bind_param (de "ssssssssi" a "sssssssssi")
        $stmt->bind_param("ssssssssi", $carrera, $materias, $horario_lunes, $horario_martes, $horario_miercoles, $horario_jueves, $horario_viernes, $contacto, $alumno_id);

        if ($stmt->execute()) {
            header("Location: panel_alumno.php?exito=1");
        } else {
            header("Location: panel_alumno.php?error=1");
        }
        $stmt->close();

    // --- ACCIÓN NUEVA: Cambiar Contraseña ---
    } elseif ($_POST['accion'] == 'cambiar_password') {

        $password_actual = $_POST['password_actual'];
        $password_nueva = $_POST['password_nueva'];
        $password_confirmar = $_POST['password_confirmar'];

        // 1. Validar que las nuevas contraseñas coincidan
        if ($password_nueva !== $password_confirmar) {
            header("Location: panel_alumno.php?error_pass=" . urlencode("Las contraseñas nuevas no coinciden."));
            exit();
        }

        // 2. Obtener la contraseña actual (hash) de la BD
        $stmt_get = $conexion->prepare("SELECT password FROM alumnos WHERE id = ?");
        $stmt_get->bind_param("i", $alumno_id);
        $stmt_get->execute();
        $resultado = $stmt_get->get_result();

        if ($resultado->num_rows === 0) {
            header("Location: panel_alumno.php?error_pass=" . urlencode("Error: Usuario no encontrado."));
            exit();
        }

        $usuario = $resultado->fetch_assoc();
        $hash_actual = $usuario['password'];
        $stmt_get->close();

        // 3. Verificar si la contraseña actual es correcta
        // Usamos password_verify (tu auth.php usa esto)
        if (password_verify($password_actual, $hash_actual)) {

            // 4. Si es correcta, crear el hash de la nueva contraseña
            $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);

            // 5. Actualizar la contraseña en la BD
            $stmt_update = $conexion->prepare("UPDATE alumnos SET password = ? WHERE id = ?");
            $stmt_update->bind_param("si", $nuevo_hash, $alumno_id);
            $stmt_update->execute();
            $stmt_update->close();

            header("Location: panel_alumno.php?exito_pass=1");

        } else {
            // Si la contraseña actual es incorrecta
            header("Location: panel_alumno.php?error_pass=" . urlencode("La contraseña actual es incorrecta."));
        }
    }

    $conexion->close();
    exit();
}
?>
