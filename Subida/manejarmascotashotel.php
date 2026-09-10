<?php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
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

    <!-- MANEJAR EMPLEADO (READ ONLY) -->
    
    <!-- LISTA -->
    <div class="container mt-5 pt-5 col-md-6"><div class="h-100 p-5 bg-light border rounded-3">  
        <h1 class="h3 mb-3 fw-normal text-secondary">Datos de mascotas</h1><br>
        <ul class="list-group list-group-horizontal pb-1">
            <li class="list-group-item list-group-item-action list-group-item-light">Id</li>
            <li class="list-group-item list-group-item-action list-group-item-light">CI Dueño</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Nombre</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Especie</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Raza</li>
            <li class="list-group-item list-group-item-action list-group-item-light">Sexo</li>
            <li class="list-group-item list-group-item-action list-group-item-light">FdN</li>
        </ul>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
        </ul>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
        </ul>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
        </ul>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
        </ul>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
        </ul>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
        </ul>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
        </ul>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
        </ul>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
        </ul>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
            <li class="list-group-item list-group-item-action list-group-item-light">HOLA</li>
        </ul>
    </div></div></div>

    <!-- PAGINADOR -->
    <div class="container mt-2 col-md-1">
        <nav>
            <ul class="pagination">
                <li class="page-item">
                    <a class="page-link text-secondary" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <li class="page-item"><a class="page-link text-secondary" href="#">1</a></li>
                <li class="page-item"><a class="page-link text-secondary" href="#">2</a></li>
                <li class="page-item"><a class="page-link text-secondary" href="#">3</a></li>
                <li class="page-item ">
                    <a class="page-link text-secondary" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- END: MANEJAR EMPLEADO (READ ONLY) -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->


    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="!JS/slideshow.js"></script>
</body>
</html>