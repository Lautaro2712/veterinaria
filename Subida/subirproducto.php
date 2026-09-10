<?php
// Carga e interpreta el código de conexion.php
require_once __DIR__ . "/../!PHPchecks/conexion.php";
require_once __DIR__ . "/../!PHPchecks/auth.php";
requireRole(ROL_EMPLEADO);

// Variables para controlar la visualización de mensajes en el HTML
$exito = false;
$error = null;
$nombreProducto  = "";
$tipoProducto  = "";
$precioProducto = '';
$stockProducto  = '';

$sql = "SELECT idProveedor, nombreProveedor FROM proveedor ORDER BY nombreProveedor ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$proveedores = $stmt->fetchAll();

// Evalúa la variable global $_SERVER para verificar si el usuario envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. RECEPCIÓN Y VALIDACIÓN DE DATOS
    $nombreProducto = $_POST["nombreProducto"] ?? "";
    $tipoProducto  = $_POST["tipoProducto"]  ?? "";
    $precioProducto = $_POST['precioProducto'] ?? '';
    $stockProducto = $_POST['stockProducto']  ?? '';
    $idProveedor = $_POST['idProveedor']  ?? '';

    // Comprobamos que los campos requeridos no estén vacíos
    if (empty($nombreProducto) || empty($tipoProducto)|| empty($precioProducto) || empty($stockProducto) || empty($idProveedor)) {
        $error = "Por favor, complete todos los campos solicitados.";
    } else {
       // -----------------------------------------------------------------------------------------
    // 2. VERIFICACIÓN DE DISPONIBILIDAD E INSERCIÓN
    // -----------------------------------------------------------------------------------------

    // Paso A: Comprobar si ya existe ese producto
    $sqlCheck = "SELECT idProducto FROM producto WHERE nombreProducto = ? AND tipoProducto = ? AND precioProducto = ? AND stockProducto = ? AND idProveedor = ?";
    try {
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->execute([$nombreProducto, $tipoProducto, $precioProducto, $stockProducto, $idProveedor]);

        // Si la consulta devuelve 1 o más filas, el producto existe
        if ($stmtCheck->fetchColumn() !== false) {
            $error = "Ese producto ya existe.";
        } else {
            // Paso B: Si no existe, proceder con la inserción
            $sqlInsert = "INSERT INTO producto (nombreProducto, tipoProducto, precioProducto, stockProducto, idProveedor) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->execute([$nombreProducto, $tipoProducto, $precioProducto, $stockProducto, $idProveedor]);
            $exito = true;
        }
    } catch (PDOException $e) {
        $error = "Error al subir el producto al sistema: " . $e->getMessage();
    }
    }
}
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
    <link rel="icon" type="image/png" href="/!Imagenes/favicon.png">
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
        <?php if (!empty($exito)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✔ ¡Producto subido con exito al sistema!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ✘ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <br><form method="POST">  
            <h1 class="h3 mb-3 fw-normal text-secondary">Subir producto</h1> 
            <div class="form-floating"> 
                <input type="text" class="form-control" id="nombreProducto" name="nombreProducto" maxlength="20" placeholder="Whiskas" required> 
                <label for="nombreProducto">Nombre del producto</label> 
            </div><br>
            <div class="form-floating"> 
                <select name="tipoProducto" id="tipoProducto" class="form-select" aria-label="Default select example" required>
                    <option value="Comida">Comida</option>
                    <option value="Higiene">Higiene</option>
                    <option value="Medicación">Medicación</option>
                    <option value="Juguete">Juguete</option>
                    <option value="Insumo clinico">Insumo clinico</option>
                    <option value="Otro">Otro</option>
                </select>
                <label for="tipoProducto">Tipo de producto</label>
            </div><br>
            <div class="form-floating"> 
                <input type="number" class="form-control" id="precioProducto" name="precioProducto" min="0" placeholder="500" required> 
                <label for="precioProducto">Precio del producto en pesos</label> 
            </div><br>
            <div class="form-floating"> 
                <input type="number" class="form-control" id="stockProducto" name="stockProducto" min="0" max="9999" placeholder="12345678" required> 
                <label for="stockProducto">Stock del producto</label> 
            </div><br>
            <div class="form-floating"> 
                <select name="idProveedor" id="idProveedor" class="form-select" required>
                    <option value="" disabled selected>Seleccione un proveedor</option>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo htmlspecialchars($proveedor['idProveedor']); ?>">
                            <?php echo htmlspecialchars($proveedor['nombreProveedor']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="idProveedor">Proveedor</label> 
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