<?php
session_start();
if (!isset($_SESSION['alumno_id'])) { header("Location: login.php"); exit(); }
require_once 'db_conexion.php';
$alumno_id = $_SESSION['alumno_id'];

// Consulta actualizada para obtener el horario por días
$stmt = $conexion->prepare("SELECT nombre_completo, materias, horario_lunes, horario_martes, horario_miercoles, horario_jueves, horario_viernes, contacto, horas_acumuladas FROM perfiles_asesores WHERE alumno_id = ?");
$stmt->bind_param("i", $alumno_id);
$stmt->execute();
$perfil = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Asesor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="wrapper">
    <nav class="sidebar">
        <div class="sidebar-header"><a class="sidebar-logo" href="#">FCQ</a></div>
        <ul class="nav flex-column sidebar-nav">
            <li class="nav-item"><a class="nav-link active" href="#"><i class="fa-solid fa-house"></i> Mi Panel</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-bullhorn"></i> Difusión</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <header class="main-header">
            <div>
                <h2>Panel de Asesor</h2>
                <p class="text-muted">Bienvenido, <?php echo htmlspecialchars($perfil['nombre_completo'] ?? $_SESSION['alumno_usuario']); ?>.</p>
            </div>
            <a href="logout.php" class="btn btn-danger btn-logout">Cerrar Sesión</a>
        </header>

        <?php if(isset($_GET['exito'])) echo "<div class='alert alert-success'>¡Perfil actualizado correctamente!</div>"; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Editar Mi Perfil y Horario</div>
                    <div class="card-body">
                        <form action="alumno_acciones.php" method="post">
                            <input type="hidden" name="accion" value="actualizar_perfil">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre Completo:</label>
                                    <input type="text" name="nombre_completo" class="form-control" value="<?= htmlspecialchars($perfil['nombre_completo'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Materias que imparto:</label>
                                    <input type="text" name="materias" class="form-control" value="<?= htmlspecialchars($perfil['materias'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3"><label>Lunes:</label><input type="text" name="horario_lunes" class="form-control" value="<?= htmlspecialchars($perfil['horario_lunes'] ?? '') ?>"></div>
                                <div class="col-md-4 mb-3"><label>Martes:</label><input type="text" name="horario_martes" class="form-control" value="<?= htmlspecialchars($perfil['horario_martes'] ?? '') ?>"></div>
                                <div class="col-md-4 mb-3"><label>Miércoles:</label><input type="text" name="horario_miercoles" class="form-control" value="<?= htmlspecialchars($perfil['horario_miercoles'] ?? '') ?>"></div>
                                <div class="col-md-4 mb-3"><label>Jueves:</label><input type="text" name="horario_jueves" class="form-control" value="<?= htmlspecialchars($perfil['horario_jueves'] ?? '') ?>"></div>
                                <div class="col-md-4 mb-3"><label>Viernes:</label><input type="text" name="horario_viernes" class="form-control" value="<?= htmlspecialchars($perfil['horario_viernes'] ?? '') ?>"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contacto (Email, Teléfono, etc.):</label>
                                <input type="text" name="contacto" class="form-control" value="<?= htmlspecialchars($perfil['contacto'] ?? '') ?>">
                            </div>
                            <button type="submit" class="btn btn-primary w-100" style="background-color: var(--color-principal-rojo); border-color: var(--color-principal-rojo);">Guardar Cambios</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card text-center">
                    <div class="card-header">Mis Horas Acumuladas</div>
                    <div class="card-body">
                        <h1 class="display-1 fw-bold" style="color: var(--color-principal-rojo);"><?= $perfil['horas_acumuladas'] ?? 0 ?></h1>
                        <p class="card-text text-muted">Horas validadas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>