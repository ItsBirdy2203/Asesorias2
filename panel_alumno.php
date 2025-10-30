<?php
session_start();
require_once 'db_conexion.php';

// Verificación de sesión de alumno
if (!isset($_SESSION['alumno_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
    header("Location: login.php");
    exit();
}

$alumno_id = $_SESSION['alumno_id'];

// Obtener la información actual del perfil del asesor
$stmt = $conexion->prepare("SELECT * FROM perfiles_asesores WHERE alumno_id = ?");
$stmt->bind_param("i", $alumno_id);
$stmt->execute();
$perfil = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Asesor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="wrapper">
    <nav class="sidebar">
        <div class="sidebar-header"><a class="sidebar-logo" href="index.php">FCQ</a></div>
        <ul class="nav flex-column sidebar-nav">
            <li class="nav-item"><a class="nav-link active" href="#"><i class="fa-solid fa-user-pen"></i> Mi Perfil</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h2>Bienvenido, <?php echo htmlspecialchars(ucwords($perfil['nombre_completo'] ?? 'Asesor')); ?></h2>
                
                <h5 class="mb-0 mt-2">
                    Horas Acumuladas: 
                    <strong style="color: #D32F2F;"><?php echo htmlspecialchars($perfil['horas_acumuladas'] ?? 0); ?></strong>
                </h5>
                </div>
            <div class="card-body">

                <?php if (isset($_GET['exito'])): ?>
                    <div class="alert alert-success">¡Perfil actualizado correctamente!</div>
                <?php elseif (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">Error al actualizar.</div>
                <?php elseif (isset($_GET['exito_pass'])): ?>
                    <div class="alert alert-success">¡Contraseña cambiada exitosamente!</div>
                <?php elseif (isset($_GET['error_pass'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error_pass']); ?></div>
                <?php endif; ?>

                <h3 class="mb-3">Actualizar mi Perfil</h3>
                <form action="alumno_acciones.php" method="POST">
                    <input type="hidden" name="accion" value="actualizar_perfil">

                    <div class="mb-3">
                        <label class="form-label">Nombre Completo (registrado)</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars(ucwords($perfil['nombre_completo'] ?? '')); ?>" disabled>
                        <div class="form-text">Tu nombre se actualiza automáticamente desde las encuestas.</div>
                    </div>

                    <div class="mb-3">
                        <label for="carrera" class="form-label">Carrera Universitaria</label>
                        <select class="form-select" id="carrera" name="carrera" required>
                            <option value="" disabled <?php echo empty($perfil['carrera']) ? 'selected' : ''; ?>>-- Selecciona tu carrera --</option>
                            <option value="LQA" <?php echo ($perfil['carrera'] ?? '') == 'LQA' ? 'selected' : ''; ?>>LQA</option>
                            <option value="LQI" <?php echo ($perfil['carrera'] ?? '') == 'LQI' ? 'selected' : ''; ?>>LQI</option>
                            <option value="IQA" <?php echo ($perfil['carrera'] ?? '') == 'IQA' ? 'selected' : ''; ?>>IQA</option>
                            <option value="IQI" <?php echo ($perfil['carrera'] ?? '') == 'IQI' ? 'selected' : ''; ?>>IQI</option>
                            <option value="Otra" <?php echo ($perfil['carrera'] ?? '') == 'Otra' ? 'selected' : ''; ?>>Otra</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="materias" class="form-label">Materias que imparto</label>
                        <input type="text" class="form-control" id="materias" name="materias" value="<?php echo htmlspecialchars($perfil['materias'] ?? ''); ?>" placeholder="Ej. Cálculo, Química Orgánica...">
                    </div>

                    <div class="mb-3">
                        <label for="contacto" class="form-label">Información de Contacto</label>
                        <input type="text" class="form-control" id="contacto" name="contacto" value="<?php echo htmlspecialchars($perfil['contacto'] ?? ''); ?>" placeholder="Ej. WhatsApp o Correo">
                    </div>

                    <h4 class="mt-4">Mi Disponibilidad</h4>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="horario_lunes" class="form-label">Lunes</label>
                            <input type="text" class="form-control" id="horario_lunes" name="horario_lunes" value="<?php echo htmlspecialchars($perfil['horario_lunes'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="horario_martes" class="form-label">Martes</label>
                            <input type="text" class="form-control" id="horario_martes" name="horario_martes" value="<?php echo htmlspecialchars($perfil['horario_martes'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="horario_miercoles" class="form-label">Miércoles</label>
                            <input type="text" class="form-control" id="horario_miercoles" name="horario_miercoles" value="<?php echo htmlspecialchars($perfil['horario_miercoles'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="horario_jueves" class="form-label">Jueves</label>
                            <input type="text" class="form-control" id="horario_jueves" name="horario_jueves" value="<?php echo htmlspecialchars($perfil['horario_jueves'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="horario_viernes" class="form-label">Viernes</label>
                            <input type="text" class="form-control" id="horario_viernes" name="horario_viernes" value="<?php echo htmlspecialchars($perfil['horario_viernes'] ?? ''); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Actualizar Perfil</button>
                </form>

                <hr class="my-4">

                <h3 class="mb-3">Seguridad</h3>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cambiarPasswordModal">
                    <i class="fa-solid fa-lock"></i> Cambiar mi Contraseña
                </button>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cambiarPasswordModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Cambiar Contraseña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="alumno_acciones.php" method="POST" onsubmit="return confirmarCambioPassword();">
          <div class="modal-body">
            <input type="hidden" name="accion" value="cambiar_password">

            <div class="mb-3">
                <label for="password_actual" class="form-label">Contraseña Actual</label>
                <input type="password" class="form-control" id="password_actual" name="password_actual" required>
            </div>
            <div class="mb-3">
                <label for="password_nueva" class="form-label">Contraseña Nueva</label>
                <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
            </div>
            <div class="mb-3">
                <label for="password_confirmar" class="form-label">Confirmar Contraseña Nueva</label>
                <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Confirmar Cambio</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // NUEVO: Script de confirmación
    function confirmarCambioPassword() {
        var passNueva = document.getElementById('password_nueva').value;
        var passConfirmar = document.getElementById('password_confirmar').value;

        if (passNueva !== passConfirmar) {
            alert("Error: Las contraseñas nuevas no coinciden.");
            return false; // Evita que se envíe el formulario
        }

        // Confirmación final
        return confirm("¿Estás seguro de que deseas cambiar tu contraseña? Esta acción no se puede deshacer.");
    }
</script>
</body>
</html>


