<!DOCTYPE html>
<html lang="en"></html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/!CSS/basecss.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://unpkg.com/scrollreveal"></script>
    <link rel="icon" type="image/png" href="/!Imagenes/favicon.png">
    <title>Veterinaria del Mar</title>
</head>

<body>

     <!-- TOP BAR -->
    <?php include 'topbar.php'; ?>
    
    <!-- DETAIL: HIDE OVERFLOW-X -->
    <div style="overflow-x: hidden;">

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- PRESENTACION EN CARUSEL -->
    <div id="myCarousel" class="carousel slide mb-5 scrollreveal_fadeup shadow-lg" data-bs-ride="carousel"> 
        <div class="carousel-indicators"> 
            <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="0" class="active" aria-label="Slide 1" aria-current="true"></button> 
            <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="1" aria-label="Slide 2" class=""></button> 
            <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="2" aria-label="Slide 3" class=""></button> 
        </div> 
        <div class="carousel-inner"> 
            <div class="carousel-item active"> 
                <img src="!Imagenes/imagen1.png" class="carousel-image imageresize" draggable="false" alt="">
                <div class="container">
                    <div class="carousel-caption text-start text-white txtbgdarkening ps-5 pe-5"> 
                        <h1>¿Tus mascotas se están comportando de forma rara?</h1>
                        <p>Debido a los cambios de temperatura bruscos que estan habiendo recientemente, algunos animales experimentan alteraciones metabolicas que les llevan a tener cambios de comportamiento.</p> 
                    </div>
                </div> 
            </div> 
            <div class="carousel-item"> 
                <img src="!Imagenes/imagen2.png" class="carousel-image imageresize" draggable="false" alt="">
                <div class="container"> 
                    <div class="carousel-caption text-start text-white txtbgdarkening ps-5 pe-5"> 
                        <h1>La vida de nuestros animales</h1> 
                        <p>A veces el cuidado será tratar. A veces será adaptar. A veces será acompañar. Y, eventualmente, será sobre saber cuándo dejar ir.</p> 
                    </div> 
                </div> 
            </div> 
            <div class="carousel-item"> 
                <img src="!Imagenes/imagen3.png" class="carousel-image imageresize" draggable="false" alt=""> 
                <div class="container"> 
                    <div class="carousel-caption text-start text-white txtbgdarkening ps-5 pe-5"> 
                        <h1>La importancia de la vacunación</h1> 
                        <p>Ante el aumento de casos de enfermedades infecciosas que afectan especialmente a perros jóvenes y animales con esquemas de vacunación incompletos, es fundamental revisar que las vacunas estén al día.</p>
                    </div> 
                </div> 
            </div> 
        </div> 
        <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev"> 
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button> 
        <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next"> 
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button> 
    </div>

    <!-- END: PRESENTACION EN CARUSEL -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- INFORMACION PAGINA -->

    <div class="container marketing">  
        <div class="row featurette scrollreveal_faderight"> 
            <div class="col-md-7"> 
                <h2 class="featurette-heading fw-normal lh-1">¿Quienes somos?</h2> 
                <p class="lead">Somos una veterinaria ubicada en el centro de Maldonado, la cual se dedica a brindar tratamiento seguro y efectivo a sus clientes. Nos especializamos en vacunación, diagnostico y medicación, cirugia y venta de productos varios.</p> 
            </div> 
            <div class="col-md-5"> 
                <img src="!Imagenes/imagen4.png" class="featurette_image img-fluid shadow imagenpresentacion" draggable="false" alt="">
            </div> 
        </div> 
        <hr class="featurette-divider"> 
        <div class="row featurette scrollreveal_fadeleft"> 
            <div class="col-md-7 order-md-2"> 
                <h2 class="featurette-heading fw-normal lh-1">¿En donde nos ubicamos?</h2> 
                <p class="lead">Dr. Roman Bergalli 970, 20000 Maldonado, Departamento de Maldonado. A la izquierda, o en su defecto abajo, podrás ver un mapa interactuable detallado de la zona.</p> 
            </div> 
            <div class="col-md-5 order-md-1"> 
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3550.4088409468254!2d-54.96424039455721!3d-34.90453307087088!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9575107f4b262481%3A0x32e03e877d5d60ea!2sVeterinaria%20del%20mar!5e1!3m2!1ses-419!2suy!4v1788879039021!5m2!1ses-419!2suy" width="500" height="500" style="border:0;" class="shadow featurette_image shadow imagenpresentacion" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </div> 
        </div> 
        <hr class="featurette-divider"> 
        <div class="row featurette scrollreveal_faderight"> 
            <div class="col-md-7"> 
                <h2 class="featurette-heading fw-normal lh-1">¿Que horarios tenemos?</h2> 
                <p class="lead">Trabajamos de lunes a sabado, de <a class="text-dark">9 a 13 y de 15 a 20</a>. Los domingos el local se encontrará cerrado, así que le pedimos que solicite su cita dentro del plazo disponible.</p> 
            </div> 
            <div class="col-md-5"> 
                <img src="!Imagenes/imagen5.png" class="featurette_image img-fluid shadow imagenpresentacion" draggable="false" alt="">
            </div> 
        </div>
        <hr class="featurette-divider">
        <div class="row featurette scrollreveal_fadeleft"> 
            <div class="col-md-7 order-md-2"> 
                <h2 class="featurette-heading fw-normal lh-1">¿Como nos contactamos?</h2> 
                <p class="lead">Puede contactarse con nosotros vía <a href="https://api.whatsapp.com/send?phone=598094191775">Whatsapp</a> al número 094 191 775. En su defecto, puede contactarse con nosotros vía <a href="https://www.instagram.com/veterinaria_del_mar/?hl=es">Instagram</a> o <a href="https://www.facebook.com/VeterinariadelMar">Facebook</a></p> 
            </div> 
            <div class="col-md-5 order-md-1"> 
                <img src="!Imagenes/imagen6.png" class="featurette_image img-fluid shadow imagenpresentacion" draggable="false" alt="">
            </div>
        </div> 
        <hr class="featurette-divider">
    </div>

    <!-- END: INFORMACION PAGINA -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- BOTTOM BAR -->
    <footer class="p-3">
        <div class="container"> 
            <div> 
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0"> 
                    <li><a href="/Informacion/terminoscondiciones.pdf" class="nav-link px-2 text-secondary ahover">Terminos y Condiciones</a></li>
                     <li><a href="/Informacion/terminoscondiciones.pdf" class="nav-link px-2 text-secondary ahover">-</a></li>
                    <li><a href="/Informacion/politicaprivacidad.pdf" class="nav-link px-2 text-secondary ahover">Politica de Privacidad</a></li> 
                </ul>
            </div> 
        </div>
    </footer><br>
    <!-- END: BOTTOM BAR -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- SCRIPTS -->

    <!-- SROLLREVEAL -->
    <script>
		window.sr = ScrollReveal();
        sr.reveal('.scrollreveal_fadeup', {
            duration: 500,
        });
        sr.reveal('.scrollreveal_fadeleft', {
            duration: 1000,
            origin:'left',
            distance: '5%'
        });
        sr.reveal('.scrollreveal_faderight', {
            duration: 1000,
            origin:'right',
            distance: '5%'
        });
	</script>

    <!-- END: SCRIPTS -->

    <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★ -->

    <!-- JS MANAGERS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="!JS/slideshow.js"></script>
</div></body>
</html>