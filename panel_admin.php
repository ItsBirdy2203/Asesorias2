<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }
require_once 'db_conexion.php';

// Lógica PHP actualizada para obtener los datos correctos
$sql_ranking = "SELECT nombre_completo, horas_acumuladas FROM perfiles_asesores ORDER BY horas_acumuladas DESC";
$ranking_horas = $conexion->query($sql_ranking);
$sql_asesores = "SELECT a.id, a.usuario, p.nombre_completo FROM alumnos a JOIN perfiles_asesores p ON a.id = p.alumno_id WHERE a.rol = 2";
$lista_asesores = $conexion->query($sql_asesores);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

<div class="wrapper">
    <nav class="sidebar">
        <div class="sidebar-header"><a class="sidebar-logo" href="#">FCQ</a></div>
        <ul class="nav flex-column sidebar-nav">
            <li class="nav-item"><a class="nav-link active" href="#"><i class="fa-solid fa-house"></i> Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-bullhorn"></i> Difusión</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <header class="main-header">
            <div>
                <h2>Panel de Administración</h2>
                <p class="text-muted">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_usuario']); ?>.</p>
            </div>
            <a href="logout.php" class="btn btn-danger btn-logout">Cerrar Sesión</a>
        </header>
        
        <?php if(isset($_GET['exito'])) echo "<div class='alert alert-success'>¡Acción completada exitosamente!</div>"; ?>
        <?php if(isset($_GET['error'])) echo "<div class='alert alert-danger'>Ocurrió un error.</div>"; ?>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">Ranking de Asesores</div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead><tr><th>#</th><th>Nombre</th><th>Horas Acumuladas</th></tr></thead>
                            <tbody>
                            <?php
                            $posicion = 1;
                            if ($ranking_horas && $ranking_horas->num_rows > 0) {
                                while($fila = $ranking_horas->fetch_assoc()) {
                                    echo "<tr><td><strong>" . $posicion++ . "</strong></td><td>" . htmlspecialchars($fila['nombre_completo']) . "</td><td>" . htmlspecialchars($fila['horas_acumuladas']) . "</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>No hay datos.</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">Gestionar Cuentas de Asesores</div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead><tr><th>Usuario</th><th>Nombre</th><th>Acción</th></tr></thead>
                            <tbody>
                            <?php
                            if ($lista_asesores && $lista_asesores->num_rows > 0) {
                                while($fila = $lista_asesores->fetch_assoc()) {
                                    echo "<tr><td>" . htmlspecialchars($fila['usuario']) . "</td><td>" . htmlspecialchars($fila['nombre_completo']) . "</td><td><button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#cambiarContrasenaModal' data-id='" . $fila['id'] . "' data-nombre='" . htmlspecialchars($fila['nombre_completo']) . "'>Cambiar Contraseña</button></td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>No hay asesores.</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cambiarContrasenaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cambiar Contraseña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="admin_acciones.php" method="post">
          <div class="modal-body">
                <p>Estás cambiando la contraseña para: <strong id="nombreAsesorModal"></strong></p>
                <input type="hidden" name="accion" value="cambiar_contrasena">
                <input type="hidden" name="alumno_id" id="alumnoIdModal">
                <div class="mb-3">
                    <label for="nueva_contrasena" class="form-label">Nueva Contraseña:</label>
                    <input type="password" name="nueva_contrasena" class="form-control" required>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
var cambiarContrasenaModal = document.getElementById('cambiarContrasenaModal');
cambiarContrasenaModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var alumnoId = button.getAttribute('data-id');
  var alumnoNombre = button.getAttribute('data-nombre');
  var modalBodyInputId = cambiarContrasenaModal.querySelector('#alumnoIdModal');
  var modalBodyNombre = cambiarContrasenaModal.querySelector('#nombreAsesorModal');
  modalBodyInputId.value = alumnoId;
  modalBodyNombre.textContent = alumnoNombre;
});
</script>
</body>
</html>