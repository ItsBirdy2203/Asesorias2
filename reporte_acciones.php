<?php
session_start();
require_once 'db_conexion.php';

if (!isset($_SESSION['alumno_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $asesor_id = $_SESSION['alumno_id'];
    $nombre_asesorado = $_POST['nombre_asesorado'];
    $fecha_asesoria = $_POST['fecha_asesoria'];
    $hora_asesoria = $_POST['hora_asesoria'];
    $duracion_horas = $_POST['duracion_horas'];

    $upload_dir = 'uploads/';
    $foto_url_1 = null;
    $foto_url_2 = null;

    // Función para manejar la subida de un archivo
    function subir_archivo($file_input_name, $upload_dir) {
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
            $file = $_FILES[$file_input_name];
            // Validar tipo y tamaño
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowed_types)) {
                return ['error' => 'Tipo de archivo no permitido.'];
            }
            if ($file['size'] > 5000000) { // 5MB
                return ['error' => 'El archivo es demasiado grande.'];
            }

            // Crear un nombre de archivo único
            $filename = uniqid() . '-' . basename($file['name']);
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                return ['success' => $target_path];
            } else {
                return ['error' => 'Error al mover el archivo.'];
            }
        }
        return ['success' => null]; // No hay archivo o hubo un error no crítico
    }

    // Procesar foto 1
    $resultado1 = subir_archivo('foto1', $upload_dir);
    if (isset($resultado1['error'])) {
        header("Location: panel_alumno.php?reporte_error=" . urlencode($resultado1['error']));
        exit();
    }
    $foto_url_1 = $resultado1['success'];

    // Procesar foto 2
    $resultado2 = subir_archivo('foto2', $upload_dir);
    if (isset($resultado2['error'])) {
        header("Location: panel_alumno.php?reporte_error=" . urlencode($resultado2['error']));
        exit();
    }
    $foto_url_2 = $resultado2['success'];

    // Insertar en la base de datos
    $stmt = $conexion->prepare("INSERT INTO reportes_asesorias (asesor_id, nombre_asesorado, fecha_asesoria, hora_asesoria, duracion_horas, foto_url_1, foto_url_2) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssiss", $asesor_id, $nombre_asesorado, $fecha_asesoria, $hora_asesoria, $duracion_horas, $foto_url_1, $foto_url_2);

    if ($stmt->execute()) {
        header("Location: panel_alumno.php?reporte_exito=1");
    } else {
        header("Location: panel_alumno.php?reporte_error=" . urlencode('Error en la base de datos.'));
    }
    $stmt->close();
    $conexion->close();

} else {
    header("Location: panel_alumno.php");
    exit();
}
?>
