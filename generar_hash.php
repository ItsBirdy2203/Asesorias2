<?php
// Archivo: generar_hash.php
// Este script es solo para generar un hash de contraseña seguro.
// Úsalo una vez y luego puedes borrarlo.

$contrasenaPlana = 'admin123';

// Generar el hash usando el algoritmo BCRYPT, que es el estándar actual.
$hash = password_hash($contrasenaPlana, PASSWORD_DEFAULT);

echo "<h1>Hash de Contraseña Generado</h1>";
echo "<p>Copia el siguiente texto y pégalo en la columna 'contrasena' de tu tabla 'administradores' para el usuario 'admin'.</p>";
echo "<hr>";
echo "<strong>Hash a copiar:</strong>";
echo "<br>";
echo "<textarea rows='4' cols='80' readonly>" . htmlspecialchars($hash) . "</textarea>";
echo "<br><br>";
echo "<a href='login.php'>Volver al Login</a>";

?>
