<?php
require_once __DIR__ . "/!PHPchecks/auth.php";

$rol = rolActual();
?>

<header class="p-2 shadow bg-body"> 
    <div class="container-fluid"> 
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start"> 

            <a href="/index.php"><img 
                src="/!Imagenes/veticon.png" 
                class="me-3" 
                style="max-width: 50px; max-height: 50px;" 
                draggable="false" 
                alt=""
            ></a>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0"> 

                <li>
                    <a 
                        href="/index.php" 
                        class="nav-link px-2 text-secondary ahover"
                    >
                        Indice
                    </a>
                </li>

                <?php if ($rol == ROL_CLIENTE): ?>

                    <li>
                        <a 
                            href="/Subida/solicitarcita.php" 
                            class="nav-link px-2 text-secondary ahover"
                        >
                            Solicitar cita
                        </a>
                    </li>

                <?php endif; ?>

                <?php if ($rol >= ROL_EMPLEADO): ?>

                    <li class="nav-item dropdown">

                        <a 
                            class="nav-link dropdown-toggle px-2 text-secondary ahover" 
                            href="#" 
                            role="button" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false"
                        >
                            Menu de gestión
                        </a>

                        <ul class="dropdown-menu animate__animated animate__zoomIn">

                            <li>
                                <a 
                                    href="/Subida/manejarcitas.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Citas
                                </a>
                            </li> 

                            <li>
                                <a 
                                    href="/Subida/manejarfichas.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Fichas
                                </a>
                            </li>

                            <li>
                                <a 
                                    href="/Subida/manejarclientes.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Clientes
                                </a>
                            </li>


                            <?php if ($rol >= ROL_ADMINISTRADOR): ?>

                                <li>
                                    <a 
                                        href="/Subida/manejarempleados.php?v=2" 
                                        class="nav-link px-2 text-secondary ahover"
                                    >
                                        Empleados
                                    </a>
                                </li>

                            <?php endif; ?>


                            <li>
                                <a 
                                    href="/Subida/manejarmascotas.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Mascotas
                                </a>
                            </li>

                            <li>
                                <a 
                                    href="/Subida/manejarmascotashotel.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Mascotas en el hotel
                                </a>
                            </li> 

                            <li>
                                <a 
                                    href="/Subida/manejarproveedores.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Proveedores
                                </a>
                            </li> 

                            <li>
                                <a 
                                    href="/Subida/manejarproductos.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Productos
                                </a>
                            </li>

                            <li>
                                <a 
                                    href="/Subida/manejarfacturas.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Facturas
                                </a>
                            </li>

                        </ul>

                    </li>


                    <li class="nav-item dropdown">

                        <a 
                            class="nav-link dropdown-toggle px-2 text-secondary ahover" 
                            href="#" 
                            role="button" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false"
                        >
                            Registrar
                        </a>

                        <ul class="dropdown-menu animate__animated animate__zoomIn">

                            <li>
                                <a 
                                    href="/Subida/subircita.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Citas
                                </a>
                            </li>

                            <li>
                                <a 
                                    href="/Subida/subirficha.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Fichas
                                </a>
                            </li>
                        
                            <li>
                                <a 
                                    href="/Subida/subircliente.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Clientes
                                </a>
                            </li> 

                            <?php if ($rol >= ROL_ADMINISTRADOR): ?>

                                <li>
                                    <a 
                                        href="/Subida/subirempleado.php" 
                                        class="nav-link px-2 text-secondary ahover"
                                    >
                                        Empleados
                                    </a>
                                </li>

                            <?php endif; ?>


                            <li>
                                <a 
                                    href="/Subida/subirmascota.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Mascotas
                                </a>
                            </li>

                            <li>
                                <a 
                                    href="/Subida/subirmascotahotel.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Mascotas al hotel
                                </a>
                            </li>

                            <li>
                                <a 
                                    href="/Subida/subirproveedor.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Proveedores
                                </a>
                            </li>

                            <li>
                                <a 
                                    href="/Subida/subirproducto.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Productos
                                </a>
                            </li> 

                            <li>
                                <a 
                                    href="/Subida/subirfactura.php" 
                                    class="nav-link px-2 text-secondary ahover"
                                >
                                    Facturas
                                </a>
                            </li>

                        </ul>

                    </li>

                <?php endif; ?>

            </ul>


            <div class="text-end">

                <?php if (estaLogueado()): ?>

                    <span class="text-secondary me-3">

                        Hola, 
                        <?php 
                            echo htmlspecialchars(
                                $_SESSION["nombreCliente"], 
                                ENT_QUOTES, 
                                'UTF-8'
                            ); 
                        ?>

                    </span>


                    <!-- BOTÓN PERFIL -->
                   <a href="/perfil.php" class="text-secondary nohighlight">
    <button 
        type="button" 
        class="btn bg-info-subtle me-2 border border-info-subtle rounded-4 buttonhover"
    >
        Mi perfil
    </button>
</a>

                    <!-- CERRAR SESIÓN -->
                    <a 
                        href="/!PHPchecks/logout.php" 
                        class="text-secondary nohighlight nohighlight"
                    >
                        <button 
                            type="button" 
                            class="btn bg-info-subtle border border-info-subtle rounded-4"
                        >
                            Cerrar sesión
                        </button>
                    </a>


                <?php else: ?>


                    <a 
                        href="/Subida/login.php" 
                        class="text-secondary nohighlight"
                    >
                        <button 
                            type="button" 
                            class="btn bg-info-subtle me-2 border border-info-subtle rounded-4 buttonhover"
                        >
                            Iniciar sesión
                        </button>
                    </a> 


                    <a 
                        href="/Subida/registro.php" 
                        class="text-secondary nohighlight"
                    >
                        <button 
                            type="button" 
                            class="btn bg-info-subtle border border-info-subtle rounded-4"
                        >
                            Registrarse
                        </button>
                    </a>

                <?php endif; ?>

            </div> 

        </div> 
    </div>
</header>