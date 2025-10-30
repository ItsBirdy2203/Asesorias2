<?php
require_once 'db_conexion.php';

// Consulta actualizada para obtener el horario detallado por días
$sql = "SELECT nombre_completo, materias, horario_lunes, horario_martes, horario_miercoles, horario_jueves, horario_viernes, contacto 
        FROM perfiles_asesores 
        WHERE nombre_completo IS NOT NULL AND nombre_completo != ''";
$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asesorías Estudiantiles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="wrapper">
    <nav class="sidebar">
        <div class="sidebar-header"><a class="sidebar-logo" href="index.php">FCQ</a></div>
        <ul class="nav flex-column sidebar-nav">
            <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
            <li class="nav-item"><a class="nav-link" href="login.php"><i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión</a></li>
            <li class="nav-item"><a class="nav-link" href="difusion.php"><i class="fa-solid fa-bullhorn"></i> Difusión</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="card">
            <div class="card-header"><h2>Asesores Disponibles</h2></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre del Alumno</th>
                                <th>Materias</th>
                                <th>Lunes</th>
                                <th>Martes</th>
                                <th>Miércoles</th>
                                <th>Jueves</th>
                                <th>Viernes</th>
                                <th>Contacto</th>
                            </tr>
                        </thead>
                       <tbody>
                            <?php
                            if ($resultado && $resultado->num_rows > 0) {
                                while($fila = $resultado->fetch_assoc()) {
                                    echo "<tr>";
                                    // Añadimos '?? ""' a cada variable
                                    echo "<td>" . htmlspecialchars(ucwords($fila['nombre_completo'] ?? '')) . "</td>";
                                    echo "<td>" . htmlspecialchars($fila['materias'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($fila['horario_lunes'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($fila['horario_martes'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($fila['horario_miercoles'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($fila['horario_jueves'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($fila['horario_viernes'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($fila['contacto'] ?? '') . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center text-muted p-5'>No hay asesores disponibles.</td></tr>";
                            }
                            $conexion->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>


