<?php
// Archivo: db_conexion.php (Versión para Render + TiDB)

// Cargar variables de entorno desde Render
$servidor = getenv('DB_HOST');
$usuario_db = getenv('DB_USER');
$contrasena_db = getenv('DB_PASS');
$nombre_db = getenv('DB_NAME');
// El puerto es diferente para TiDB, no es el 3306
$puerto = getenv('DB_PORT'); 

// Crear la conexión
$conexion = mysqli_init();

// --- CONFIGURACIÓN SSL OBLIGATORIA PARA TiDB ---
// Le decimos a PHP dónde encontrar los certificados de seguridad en Render
mysqli_ssl_set($conexion, NULL, NULL, "/etc/ssl/certs/ca-certificates.crt", NULL, NULL);

// Establecer la conexión segura
if (!mysqli_real_connect($conexion, $servidor, $usuario_db, $contrasena_db, $nombre_db, (int)$puerto, NULL, MYSQLI_CLIENT_SSL)) {
    // Si la conexión falla, muestra el error
    die("Conexión fallida: " . mysqli_connect_error());
}

// Establecer el charset para evitar problemas con acentos
mysqli_set_charset($conexion, 'utf8mb4');
?>
