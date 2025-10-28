<?php
// Este archivo no necesita conexión a la BD, pero lo dejamos por si acaso
// require_once 'db_conexion.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Difusión del Programa - Asesorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css"> 
</head>
<body>
<div class="wrapper">
    <nav class="sidebar">
        <div class="sidebar-header"><a class="sidebar-logo" href="index.php">FCQ</a></div>
        <ul class="nav flex-column sidebar-nav">
            <li class="nav-item"><a class="nav-link" href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
            <li class="nav-item"><a class="nav-link" href="login.php"><i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión</a></li>
            <li class="nav-item"><a class="nav-link active" href="difusion.php"><i class="fa-solid fa-bullhorn"></i> Difusión</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="card">
            <div class="card-header"><h2>Material de Difusión</h2></div>
            <div class="card-body">
                
                <p>Selecciona el material que deseas visualizar:</p>
                <div class="btn-group" role="group" aria-label="Selector de imágenes">
                    <button type="button" class="btn btn-primary" onclick="mostrarImagen('img-1')">Infografía 1</button>
                    <button type="button" class="btn btn-success" onclick="mostrarImagen('img-2')">Infografía 2</button>
                    <button type="button" class="btn btn-info" onclick="mostrarImagen('img-3')">Horarios</button>
                </div>
                
                <hr>

                <div class="imagen-difusion-container">
                    
                    <img id="img-1" class="img-difusion active" src="ruta/a/tu/imagen1.jpg" alt="Infografía 1">
                    
                    <img id="img-2" class="img-difusion" src="ruta/a/tu/imagen2.jpg" alt="Infografía 2">
                    
                    <img id="img-3" class="img-difusion" src="ruta/a/tu/imagen3.jpg" alt="Horarios">

                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function mostrarImagen(idDeImagen) {
        // 1. Primero, oculta todas las imágenes
        var imagenes = document.querySelectorAll('.img-difusion');
        imagenes.forEach(function(img) {
            img.classList.remove('active');
        });
        
        // 2. Luego, muestra solo la imagen seleccionada
        var imagenSeleccionada = document.getElementById(idDeImagen);
        if (imagenSeleccionada) {
            imagenSeleccionada.classList.add('active');
        }
    }

    // Opcional: Asegurarnos de que la primera imagen se muestre al cargar
    document.addEventListener("DOMContentLoaded", function() {
        mostrarImagen('img-1');
    });
</script>

</body>
</html>
