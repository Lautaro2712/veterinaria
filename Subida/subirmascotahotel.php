<?php
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);
?>
<!DOCTYPE html>
<html lang="en"></html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/../!CSS/basecss.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://unpkg.com/scrollreveal"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="icon" type="image/png" href="/../!Imagenes/favicon.png">
    <title>Veterinaria del Mar</title>
</head>

<body>
    <!-- DETAIL: HIDE OVERFLOW-X -->
    <div style="overflow-x: hidden;">

    <!-- TOP BAR -->
    <?php include '../topbar.php'; ?>

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- REGISTER FORM -->
    <div class="container-fluid margins"><main class="form-signin m-5"> 
        <br><form>  
            <h1 class="h3 mb-3 fw-normal text-secondary">Subir mascota al hotel</h1> 
            <div class="form-floating"> 
                <input type="text" class="form-control" id="nombreMascota" name="nombreMascota" maxlength="20" placeholder="Whiskas" required> 
                <label for="nombreMascota">Nombre de la mascota</label> 
            </div><br>
            <div class="form-floating"> 
                <select name="tipoProducto" id="tipoProducto" class="form-select" aria-label="Default select example" required>
                    <option value="1">Comida</option>
                    <option value="2">Higiene</option>
                    <option value="3">Medicación</option>
                    <option value="4">Juguete</option>
                    <option value="5">Insumo clinico</option>
                    <option value="5">Otro</option>
                </select>
                <label for="tipoProducto">Tipo de producto</label>
            </div><br>
            <div class="form-floating"> 
                <input type="number" class="form-control" id="precioProducto" name="precioProducto" placeholder="500" required> 
                <label for="precioProducto">Precio del producto en pesos</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="number" class="form-control" id="stockProducto" name="stockProducto" minlength="1" maxlength="4" placeholder="12345678" required> 
                <label for="stockProducto">Stock del producto</label> 
            </div><br>
            <button class="btn bg-info-subtle me-2 border border-info-subtle rounded-5 w-100" type="submit">Subir al sistema</button><br><br>
        </form> 
    </main></div><br>
    <!-- END: REGISTER FORM -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- BOTTOM BAR -->
    <header class="p-3"> 
        <div class="container"> 
            <div> 
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0"> 
                    <li><a href="/Informacion/tos.html" class="nav-link px-2 text-secondary ahover">Terminos y Condiciones</a></li>
                    <li><a href="/Informacion/politicaprivacidad.html" class="nav-link px-2 text-secondary ahover">Politica de Privacidad</a></li> 
                </ul>
            </div> 
        </div>
    </header>
    <!-- END: BOTTOM BAR -->


    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>