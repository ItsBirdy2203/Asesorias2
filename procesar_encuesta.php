<?php
// --- INICIO DE FUNCIÓN DE NORMALIZACIÓN (VERSIÓN MODERNA) ---
function normalizarNombre($nombre) {
    // 1. Quitar acentos (método moderno y seguro)
    $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);
    $nombre = $transliterator->transliterate($nombre);
    
    // 2. Convertir a minúsculas
    $nombre = strtolower($nombre);
    
    // 3. Quitar espacios extra
    $nombre = preg_replace('/\s+/', ' ', $nombre); // Reemplaza múltiples espacios por uno solo
    $nombre = trim($nombre); // Quita espacios al inicio/final
    
    return $nombre;
}
// --- FIN DE FUNCIÓN DE NORMALIZACIÓN ---

require_once 'db_conexion.php';

// --- PASO 1: LEER DATOS Y VALIDAR ENTRADA ---
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['tipo_encuesta'], $data['asesor_nombre'], $data['fecha_asesoria'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos esenciales.']);
    exit();
}

// --- PASO 2: ASIGNAR VARIABLES Y OBTENER ID DEL ASESOR ---
$tipo_encuesta  = $data['tipo_encuesta'];
$asesor_nombre_input = $data['asesor_nombre']; 
$fecha_asesoria = $data['fecha_asesoria'];
$asesor_id = null;

// 1. NORMALIZACIÓN TOTAL (acentos, espacios, mayúsculas)
$nombre_normalizado = normalizarNombre($asesor_nombre_input);

// 2. Verificación de seguridad
if (empty($nombre_normalizado)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'El nombre del asesor no puede estar vacío.']);
    exit();
}

// 3. Hacemos la búsqueda MÁS SIMPLE Y RÁPIDA
$stmt_find = $conexion->prepare("SELECT alumno_id FROM perfiles_asesores WHERE nombre_completo = ?");
$stmt_find->bind_param("s", $nombre_normalizado);
$stmt_find->execute();
$resultado = $stmt_find->get_result();

if ($resultado->num_rows > 0) {
    // Si lo encuentra, usa el ID existente
    $fila = $resultado->fetch_assoc();
    $asesor_id = $fila['alumno_id'];
} else {
    // Si NO lo encuentra, auto-registra al asesor
    $partes_nombre = explode(' ', $nombre_normalizado);
    $nombre_usuario = $partes_nombre[0][0] . end($partes_nombre); 
    $usuario_base = $nombre_usuario;
    $contador = 1;

    // Bucle para asegurar que el nombre de usuario sea único
    while(true) {
        $stmt_check_user = $conexion->prepare("SELECT id FROM alumnos WHERE usuario = ?");
        $stmt_check_user->bind_param("s", $nombre_usuario);
        $stmt_check_user->execute();
        if ($stmt_check_user->get_result()->num_rows === 0) { $stmt_check_user->close(); break; }
        $stmt_check_user->close();
        $nombre_usuario = $usuario_base . $contador++;
    }
    
    // Insertamos el nuevo ALUMNO
    $contrasena_default = "asesor123";
    $stmt_insert_alumno = $conexion->prepare("INSERT INTO alumnos (usuario, password, rol) VALUES (?, ?, 2)");
    $stmt_insert_alumno->bind_param("ss", $nombre_usuario, $contrasena_default);
    $stmt_insert_alumno->execute();
    $nuevo_alumno_id = $conexion->insert_id;
    $stmt_insert_alumno->close();
    
    // Insertamos el nuevo PERFIL DE ASESOR
    $stmt_insert_perfil = $conexion->prepare("INSERT INTO perfiles_asesores (alumno_id, nombre_completo) VALUES (?, ?)");
    $stmt_insert_perfil->bind_param("is", $nuevo_alumno_id, $nombre_normalizado);
    $stmt_insert_perfil->execute();
    $stmt_insert_perfil->close();
    
    $asesor_id = $nuevo_alumno_id;
}
$stmt_find->close();

// --- PASO 3: GESTIONAR VALIDACIÓN ---
$stmt_check = $conexion->prepare("SELECT * FROM validaciones_asesorias WHERE asesor_id = ? AND fecha_asesoria = ?");
$stmt_check->bind_param("is", $asesor_id, $fecha_asesoria);
$stmt_check->execute();
$registro = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

$validar_hora_final = false;
$id_registro = null;

if ($registro) {
    // Si ya existe un registro, lo actualizamos
    $id_registro = $registro['id'];
    if ($tipo_encuesta == 'asesor' && !$registro['encuesta_asesor_completada']) {
        
        // --- LÓGICA A PRUEBA DE BALAS para duración ---
        $duracion_input = $data['duracion_asesoria'] ?? null;
        $duracion = null;
        if (is_string($duracion_input)) {
            $duracion = strtolower(trim($duracion_input));
        } elseif (is_array($duracion_input) && !empty($duracion_input)) {
            $duracion = strtolower(trim($duracion_input[0]));
        }
        // --- FIN LÓGICA A PRUEBA DE BALAS ---

        $stmt = $conexion->prepare("UPDATE validaciones_asesorias SET encuesta_asesor_completada = TRUE, duracion_reportada = ? WHERE id = ?");
        $stmt->bind_param("si", $duracion, $id_registro);
        $stmt->execute();
        $stmt->close();
        if ($registro['encuesta_asesorado_completada']) { $validar_hora_final = true; }

    } elseif ($tipo_encuesta == 'asesorado' && !$registro['encuesta_asesorado_completada']) {
        $stmt = $conexion->prepare("UPDATE validaciones_asesorias SET encuesta_asesorado_completada = TRUE WHERE id = ?");
        $stmt->bind_param("i", $id_registro);
        $stmt->execute();
        $stmt->close();
