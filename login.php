<?php
session_start();

// Redirigir si ya hay una sesión iniciada
if (isset($_SESSION['admin_id'])) {
    header("Location: panel_admin.php");
    exit();
}
if (isset($_SESSION['alumno_id'])) {
    header("Location: panel_alumno.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Enlace a tu hoja de estilos de login -->
    <link rel="stylesheet" href="login_style.css">
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            <a class="login-logo" href="index.php">FCQ</a>
            <h1>Iniciar Sesión</h1>

            <?php
            if (isset($_GET['error'])) {
                echo "<div class='alert alert-danger'>Usuario o contraseña incorrectos.</div>";
            }
            ?>

            <form action="auth.php" method="post">
                <div class="mb-3">
                    <input type="text" id="usuario" name="usuario" class="form-control" placeholder="Usuario" required>
                </div>
                <div class="mb-4">
                    <input type="password" id="contrasena" name="contrasena" class="form-control" placeholder="Contraseña" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-login">Entrar</button>
            </form>
            
            <a href="index.php" class="back-link">Volver a la página principal</a>
        </div>
    </div>

</body>
</html>
