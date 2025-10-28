<?php
// Archivo: logout.php
session_start();

// Destruir todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir a la página principal
header("Location: index.php");
exit();
?>
