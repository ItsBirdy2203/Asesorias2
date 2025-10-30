<?php

<?php
function normalizarNombre($nombre) {
    // 1. Quitar acentos (Método moderno y robusto con 'intl')
    // Convierte "José García" a "Jose Garcia"
    $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower();', Transliterator::FORWARD);
    $nombre = $transliterator->transliterate($nombre);

    // 2. Convertir a minúsculas (el transliterador ya lo hace, pero re-aseguramos)
    $nombre = strtolower($nombre);

    // 3. Quitar espacios extra
    $nombre = preg_replace('/\s+/', ' ', $nombre); // Reemplaza múltiples espacios por uno solo
    $nombre = trim($nombre); // Quita espacios al inicio/final

    return $nombre;
}


// Archivo: procesar_encuesta.php
// (Versión corregida con normalización de nombres y cálculo de horas por duración)
require_once 'db_conexion.php';

// --- PASO 1: LEER DATOS Y VALIDAR ENTRADA ---
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['tipo_encuesta'], $data['asesor_nombre'], $data['fecha_asesoria'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos esenciales.']);
    exit();
}

// --- PASO 2: ASIGNAR VARIABLES Y OBTENER ID DEL ASESOR (CON NORMALIZACIÓN DE ACENTOS) ---
$tipo_encuesta  = $data['tipo_encuesta'];
$asesor_nombre_input = $data['asesor_nombre']; 
$fecha_asesoria = $data['fecha_asesoria'];
$asesor_id = null;

// 1. NORMALIZACIÓN TOTAL (acentos, espacios, mayúsculas)
$nombre_normalizado = normalizarNombre($asesor_nombre_input);

// 2. Verificación de seguridad: Si el nombre está vacío, detenemos el script.
if (empty($nombre_normalizado)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'El nombre del asesor no puede estar vacío.']);
    exit();
}

// 3. Hacemos la búsqueda MÁS SIMPLE Y RÁPIDA
//    Buscamos una coincidencia exacta con el nombre 100% normalizado
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

    // Generamos un nombre de usuario (ej. "jgarcia")
    $partes_nombre = explode(' ', $nombre_normalizado); // usamos el nombre ya limpio
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

    // Guardamos el nombre 100% normalizado
    $stmt_insert_perfil->bind_param("is", $nuevo_alumno_id, $nombre_normalizado);
    $stmt_insert_perfil->execute();
    $stmt_insert_perfil->close();

    $asesor_id = $nuevo_alumno_id; // Usamos el ID recién creado
}
$stmt_find->close();

// --- PASO 3: GESTIONAR VALIDACIÓN (LÓGICA CORREGIDA) ---
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
        
        // --- LÓGICA NUEVA: Guardar duración ---
        $duracion = $data['duracion_asesoria'] ?? null;
        $stmt = $conexion->prepare("UPDATE validaciones_asesorias SET encuesta_asesor_completada = TRUE, duracion_reportada = ? WHERE id = ?");
        $stmt->bind_param("si", $duracion, $id_registro);
        // --- FIN LÓGICA NUEVA ---
        
        $stmt->execute();
        $stmt->close();
        if ($registro['encuesta_asesorado_completada']) { $validar_hora_final = true; }

    } elseif ($tipo_encuesta == 'asesorado' && !$registro['encuesta_asesorado_completada']) {
        $stmt = $conexion->prepare("UPDATE validaciones_asesorias SET encuesta_asesorado_completada = TRUE WHERE id = ?");
        $stmt->bind_param("i", $id_registro);
        $stmt->execute();
        $stmt->close();
        if ($registro['encuesta_asesor_completada']) { $validar_hora_final = true; }
    }
} else {
    // Si no existe, creamos el nuevo registro
    if ($tipo_encuesta == 'asesor') {
        
        // --- LÓGICA NUEVA: Guardar duración ---
        $duracion = $data['duracion_asesoria'] ?? null;
        $stmt = $conexion->prepare("INSERT INTO validaciones_asesorias (asesor_id, fecha_asesoria, encuesta_asesor_completada, duracion_reportada) VALUES (?, ?, TRUE, ?)");
        $stmt->bind_param("iss", $asesor_id, $fecha_asesoria, $duracion);
        // --- FIN LÓGICA NUEVA ---

    } else { // tipo_encuesta == 'asesorado'
        $stmt = $conexion->prepare("INSERT INTO validaciones_asesorias (asesor_id, fecha_asesoria, encuesta_asesorado_completada) VALUES (?, ?, TRUE)");
        $stmt->bind_param("is", $asesor_id, $fecha_asesoria);
    }
    $stmt->execute();
    $id_registro = $stmt->insert_id;
    $stmt->close();
}

// --- PASO 4: ACCIÓN FINAL (CÁLCULO DE HORAS POR DURACIÓN) ---
if ($validar_hora_final) {
    // Para el cálculo, volvemos a obtener el registro actualizado
    $stmt_get_duracion = $conexion->prepare("SELECT duracion_reportada FROM validaciones_asesorias WHERE id = ?");
    $stmt_get_duracion->bind_param("i", $id_registro);
    $stmt_get_duracion->execute();
    $registro_final = $stmt_get_duracion->get_result()->fetch_assoc();
    $stmt_get_duracion->close();

    $horas_a_sumar = 0; // Valor por defecto
    if ($registro_final && !empty($registro_final['duracion_reportada'])) {
        
        // Convertimos la respuesta de texto a un número
        switch ($registro_final['duracion_reportada']) {
            case '30 min':
                $horas_a_sumar = 0.5;
                break;
            case '1 hora':
                $horas_a_sumar = 1;
                break;
            case '2 horas':
                $horas_a_sumar = 2;
                break;
            case '3 horas':
                $horas_a_sumar = 3;
                break;
            case '4 horas':
                $horas_a_sumar = 4;
                break;
            default:
                $horas_a_sumar = 0; // No se suma nada si la respuesta no coincide
        }
    }

    // Marcamos como completada y sumamos las horas
    $stmt_validar = $conexion->prepare("UPDATE validaciones_asesorias SET estado = 'completada' WHERE id = ?");
    $stmt_validar->bind_param("i", $id_registro);
    $stmt_validar->execute();
    $stmt_validar->close();
    
    // Solo sumamos si es mayor que cero
    if ($horas_a_sumar > 0) {
        $stmt_sumar_hora = $conexion->prepare("UPDATE perfiles_asesores SET horas_acumuladas = horas_acumuladas + ? WHERE alumno_id = ?");
        
        // ¡¡IMPORTANTE!! Cambiamos "ii" (integer, integer) a "di" (double, integer)
        $stmt_sumar_hora->bind_param("di", $horas_a_sumar, $asesor_id);
        
        $stmt_sumar_hora->execute();
        $stmt_sumar_hora->close();
        
        $mensaje_final = '¡Validación completada! Se han registrado ' . $horas_a_sumar . ' hora(s) para ' . $asesor_nombre_original . '.';
    } else {
        $mensaje_final = '¡Validación completada! No se registraron horas (duración no válida).';
    }

} else {
    $mensaje_final = 'Encuesta guardada. La hora quedará pendiente de validación.';
}

http_response_code(200);
echo json_encode(['status' => 'success', 'message' => $mensaje_final]);
$conexion->close();
?>





