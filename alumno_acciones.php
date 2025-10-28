<?php
session_start();
require_once 'db_conexion.php';

if (!isset($_SESSION['alumno_id'])) {
    header("Location: login.php");
    exit();
}

$alumno_id = $_SESSION['alumno_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    
    if ($_POST['accion'] == 'actualizar_perfil') {
        
        $nombre_completo = $_POST['nombre_completo'];
        $materias = $_POST['materias'];
        $contacto = $_POST['contacto'];
        
        // Obtenemos los horarios de cada día
        $horario_lunes = $_POST['horario_lunes'];
        $horario_martes = $_POST['horario_martes'];
        $horario_miercoles = $_POST['horario_miercoles'];
        $horario_jueves = $_POST['horario_jueves'];
        $horario_viernes = $_POST['horario_viernes'];

        // Preparamos la consulta SQL para actualizar todos los campos
        $stmt = $conexion->prepare("UPDATE perfiles_asesores SET 
                                        nombre_completo = ?, 
                                        materias = ?, 
                                        horario_lunes = ?, 
                                        horario_martes = ?, 
                                        horario_miercoles = ?, 
                                        horario_jueves = ?, 
                                        horario_viernes = ?, 
                                        contacto = ? 
                                    WHERE alumno_id = ?");
        
        $stmt->bind_param("ssssssssi", $nombre_completo, $materias, $horario_lunes, $horario_martes, $horario_miercoles, $horario_jueves, $horario_viernes, $contacto, $alumno_id);
        
        if ($stmt->execute()) {
            header("Location: panel_alumno.php?exito=1");
        } else {
            header("Location: panel_alumno.php?error=1");
        }
        
        $stmt->close();
        $conexion->close();
        exit();
    }
}
?>