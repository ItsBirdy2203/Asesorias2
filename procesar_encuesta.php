<?php
// --- INICIO DE FUNCIÓN DE DEBUG ---
// Esta función escribirá en un archivo llamado debug_log.txt
function log_debug($mensaje) {
    $log_file = 'debug_log.txt';
    $log_message = date('Y-m-d H:i:s') . " - " . $mensaje . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}
log_debug("--- SCRIPT INICIADO ---");
// --- FIN DE FUNCIÓN DE DEBUG ---

// --- INICIO DE FUNCIÓN DE NORMALIZACIÓN ---
function normalizarNombre($nombre) {
    log_debug("Normalizando nombre: " . $nombre);
    // 1. Quitar acentos y caracteres especiales
    $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýýþÿ';
    $reemplazos = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuuyyby';
    $nombre = utf8_decode($nombre);
    $nombre = strtr($nombre, utf8_decode($originales), $reemplazos);
    $nombre = utf8_encode($nombre);
    
    // 2. Convertir a minúsculas
    $nombre = strtolower($nombre);
    
    // 3. Quitar espacios extra
    $nombre = preg_replace('/\s+/', ' ', $nombre); // Reemplaza múltiples espacios por uno solo
    $nombre = trim($nombre); // Quita espacios al inicio/final
    
    log_debug("Nombre normalizado: " . $nombre);
    return $nombre;
}
// --- FIN DE FUNCIÓN DE NORMALIZACIÓN ---

require_once 'db_conexion.php';
log_debug("Conexión a BD requerida.");

// --- PASO 1: LEER DATOS Y VALIDAR ENTRADA ---
$json_data = file_get_contents('php://input');
log_debug("Datos JSON recibidos: " . $json_data);
$data = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    log_debug("¡ERROR DE JSON! " . json_last_error_msg());
}

if (!$data || !isset($data['tipo_encuesta'], $data['asesor_nombre'], $data['fecha_asesoria'])) {
    http_response_code(400);
    $error_msg = 'Faltan datos esenciales.';
    log_debug("ERROR 400: " . $error_msg);
    echo json_encode(['status' => 'error', 'message' => $error_msg]);
    exit();
}
log_debug("Paso 1 completado. Datos esenciales recibidos.");

// --- PASO 2: ASIGNAR VARIABLES Y OBTENER ID DEL ASESOR ---
$tipo_encuesta  = $data['tipo_encuesta'];
$asesor_nombre_input = $data['asesor_nombre']; 
$fecha_asesoria = $data['fecha_asesoria'];
$asesor_id = null;
log_debug("Tipo: " . $tipo_encuesta . ", Nombre: " . $asesor_nombre_input . ", Fecha: " . $fecha_asesoria);

// 1. NORMALIZACIÓN TOTAL
$nombre_normalizado = normalizarNombre($asesor_nombre_input);

// 2. Verificación de seguridad
if (empty($nombre_normalizado)) {
    http_response_code(400);
    $error_msg = 'El nombre del asesor no puede estar vacío.';
    log_debug("ERROR 400: " . $error_msg);
    echo json_encode(['status' => 'error', 'message' => $error_msg]);
    exit();
}

// 3. Hacemos la búsqueda
log_debug("Buscando asesor: " . $nombre_normalizado);
$stmt_find = $conexion->prepare("SELECT alumno_id FROM perfiles_asesores WHERE nombre_completo = ?");
$stmt_find->bind_param("s", $nombre_normalizado);
$stmt_find->execute();
$resultado = $stmt_find->get_result();
log_debug("Búsqueda ejecutada. Filas encontradas: " . $resultado->num_rows);

if ($resultado->num_rows > 0) {
    // Si lo encuentra, usa el ID existente
    $fila = $resultado->fetch_assoc();
    $asesor_id = $fila['alumno_id'];
    log_debug("Asesor ENCONTRADO. ID: " . $asesor_id);
} else {
    // Si NO lo encuentra, auto-registra al asesor
    log_debug("Asesor NO encontrado. Iniciando auto-registro...");
    
    $partes_nombre = explode(' ', $nombre_normalizado);
    $nombre_usuario = $partes_nombre[0][0] . end($partes_nombre); 
    $usuario_base = $nombre_usuario;
    $contador = 1;

    // Bucle para asegurar que el nombre de usuario sea único
    log_debug("Buscando nombre de usuario único para: " . $nombre_usuario);
    while(true) {
        $stmt_check_user = $conexion->prepare("SELECT id FROM alumnos WHERE usuario = ?");
        $stmt_check_user->bind_param("s", $nombre_usuario);
        $stmt_check_user->execute();
        if ($stmt_check_user->get_result()->num_rows === 0) { $stmt_check_user->close(); break; }
        $stmt_check_user->close();
        $nombre_usuario = $usuario_base . $contador++;
    }
    log_debug("Nombre de usuario único encontrado: " . $nombre_usuario);
    
    // Insertamos el nuevo ALUMNO
    $contrasena_default = "asesor123";
    $stmt_insert_alumno = $conexion->prepare("INSERT INTO alumnos (usuario, password, rol) VALUES (?, ?, 2)");
    $stmt_insert_alumno->bind_param("ss", $nombre_usuario, $contrasena_default);
    $stmt_insert_alumno->execute();
    $nuevo_alumno_id = $conexion->insert_id;
    $stmt_insert_alumno->close();
    log_debug("Nuevo alumno creado. ID: " . $nuevo_alumno_id);
    
    // Insertamos el nuevo PERFIL DE ASESOR
    $stmt_insert_perfil = $conexion->prepare("INSERT INTO perfiles_asesores (alumno_id, nombre_completo) VALUES (?, ?)");
    $stmt_insert_perfil->bind_param("is", $nuevo_alumno_id, $nombre_normalizado);
    $stmt_insert_perfil->execute();
    $stmt_insert_perfil->close();
    log_debug("Nuevo perfil de asesor creado.");
    
    $asesor_id = $nuevo_alumno_id;
}
$stmt_find->close();
log_debug("Paso 2 completado. Asesor ID final: " . $asesor_id);

// --- PASO 3: GESTIONAR VALIDACIÓN ---
log_debug("Iniciando Paso 3: Gestión de Validación...");
$stmt_check = $conexion->prepare("SELECT * FROM validaciones_asesorias WHERE asesor_id = ? AND fecha_asesoria = ?");
$stmt_check->bind_param("is", $asesor_id, $fecha_asesoria);
$stmt_check->execute();
$registro = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

$validar_hora_final = false;
$id_registro = null;

if ($registro) {
    log_debug("Registro de validación ENCONTRADO. ID: " . $registro['id']);
    $id_registro = $registro['id'];
    if ($tipo_encuesta == 'asesor' && !$registro['encuesta_asesor_completada']) {
        log_debug("Actualizando registro para ASESOR.");
        $duracion = $data['duracion_asesoria'] ?? null;
        $stmt = $conexion->prepare("UPDATE validaciones_asesorias SET encuesta_asesor_completada = TRUE, duracion_reportada = ? WHERE id = ?");
        $stmt->bind_param("si", $duracion, $id_registro);
        $stmt->execute();
        $stmt->close();
        if ($registro['encuesta_asesorado_completada']) { $validar_hora_final = true; }
    } elseif ($tipo_encuesta == 'asesorado' && !$registro['encuesta_asesorado_completada']) {
        log_debug("Actualizando registro para ASESORADO.");
        $stmt = $conexion->prepare("UPDATE validaciones_asesorias SET encuesta_asesorado_completada = TRUE WHERE id = ?");
        $stmt->bind_param("i", $id_registro);
        $stmt->execute();
        $stmt->close();
        if ($registro['encuesta_asesor_completada']) { $validar_hora_final = true; }
    }
} else {
    log_debug("Registro de validación NO encontrado. Creando uno nuevo...");
    if ($tipo_encuesta == 'asesor') {
        log_debug("Creando registro para ASESOR.");
        $duracion = $data['duracion_asesoria'] ?? null;
        $stmt = $conexion->prepare("INSERT INTO validaciones_asesorias (asesor_id, fecha_asesoria, encuesta_asesor_completada, duracion_reportada) VALUES (?, ?, TRUE, ?)");
        $stmt->bind_param("iss", $asesor_id, $fecha_asesoria, $duracion);
    } else { // tipo_encuesta == 'asesorado'
        log_debug("Creando registro para ASESORADO.");
        $stmt = $conexion->prepare("INSERT INTO validaciones_asesorias (asesor_id, fecha_asesoria, encuesta_asesorado_completada) VALUES (?, ?, TRUE)");
        $stmt->bind_param("is", $asesor_id, $fecha_asesoria);
    }
    $stmt->execute();
    $id_registro = $stmt->insert_id;
    $stmt->close();
    log_debug("Nuevo registro de validación CREADO. ID: " . $id_registro);
}
log_debug("Paso 3 completado. Validar al final: " . ($validar_hora_final ? 'SI' : 'NO'));

// --- PASO 4: ACCIÓN FINAL (CÁLCULO DE HORAS) ---
if ($validar_hora_final) {
    log_debug("Iniciando Paso 4: Cálculo de horas...");
    $stmt_get_duracion = $conexion->prepare("SELECT duracion_reportada FROM validaciones_asesorias WHERE id = ?");
    $stmt_get_duracion->bind_param("i", $id_registro);
    $stmt_get_duracion->execute();
    $registro_final = $stmt_get_duracion->get_result()->fetch_assoc();
    $stmt_get_duracion->close();

    $horas_a_sumar = 0;
    if ($registro_final && !empty($registro_final['duracion_reportada'])) {
        log_debug("Duración reportada: " . $registro_final['duracion_reportada']);
        switch ($registro_final['duracion_reportada']) {
            case '30 min': $horas_a_sumar = 0.5; break;
            case '1 hora': $horas_a_sumar = 1; break;
            case '2 horas': $horas_a_sumar = 2; break;
            case '3 horas': $horas_a_sumar = 3; break;
            case '4 horas': $horas_a_sumar = 4; break;
            default: $horas_a_sumar = 0;
        }
    }
    log_debug("Horas a sumar: " . $horas_a_sumar);

    $stmt_validar = $conexion->prepare("UPDATE validaciones_asesorias SET estado = 'completada' WHERE id = ?");
    $stmt_validar->bind_param("i", $id_registro);
    $stmt_validar->execute();
    $stmt_validar->close();
    
    if ($horas_a_sumar > 0) {
        log_debug("Sumando " . $horas_a_sumar . " horas al Asesor ID: " . $asesor_id);
        $stmt_sumar_hora = $conexion->prepare("UPDATE perfiles_asesores SET horas_acumuladas = horas_acumuladas + ? WHERE alumno_id = ?");
        $stmt_sumar_hora->bind_param("di", $horas_a_sumar, $asesor_id);
        $stmt_sumar_hora->execute();
        $stmt_sumar_hora->close();
        $mensaje_final = '¡Validación completada! Se han registrado ' . $horas_a_sumar . ' hora(s).';
    } else {
        $mensaje_final = '¡Validación completada! No se registraron horas.';
    }
    log_debug("Paso 4 completado.");

} else {
    $mensaje_final = 'Encuesta guardada. La hora quedará pendiente de validación.';
}

http_response_code(200);
echo json_encode(['status' => 'success', 'message' => $mensaje_final]);
log_debug("--- SCRIPT FINALIZADO CON ÉXITO ---");
$conexion->close();
?>
