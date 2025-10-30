<?php
// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "--- INICIO DE PRUEBA ---<br>";

if (function_exists('utf8_decode')) {
    echo "¡ÉXITO! La extensión XML (con utf8_decode) está instalada y funcionando.<br>";
} else {
    echo "¡ERROR! La extensión XML (utf8_decode) NO ESTÁ INSTALADA.<br>";
}

echo "--- FIN DE PRUEBA ---";
?>
