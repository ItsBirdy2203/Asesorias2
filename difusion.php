<?php
// (No se necesita PHP aquí, pero mantenemos la estructura)
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
            <div class="card-body">

                <div class="btn-group" role="group" aria-label="Selector de imágenes">
                    <button type="button" class="btn btn-primary" onclick="mostrarImagen('img-1')">Infografía 1</button>
                    <button type="button" class="btn btn-success" onclick="mostrarImagen('img-2')">Infografía 2</button>
                </div>
                
                <hr>

                <div class="imagen-difusion-container">
                    
                    <img id="img-1" class="img-difusion active" src="Imagen2.png" alt="Infografía 1">
                    
                    <img id="img-2" class="img-difusion" src="Imagen1.png" alt="Infografía 2">

                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function mostrarImagen(idDeImagen) {
        // 1. Oculta todas las imágenes
        var imagenes = document.querySelectorAll('.img-difusion');
        imagenes.forEach(function(img) {
            img.classList.remove('active');
        });
        
        // 2. Muestra solo la imagen seleccionada
        var imagenSeleccionada = document.getElementById(idDeImagen);
        if (imagenSeleccionada) {
            imagenSeleccionada.classList.add('active');
        }
    }

    // Asegurarnos de que la primera imagen se muestre al cargar
    document.addEventListener("DOMContentLoaded", function() {
        mostrarImagen('img-1');
    });
</script>

</body>
</html>

