/**
 * pantallaCompletaManejo.js
 * ---------------------------------------------------------------------
 * Agrega la función de "pantalla completa" a las páginas de "manejar X":
 * al hacer clic en el botón, la caja de la lista se expande hasta cubrir
 * toda la ventana (tapando también la barra superior del sitio), y al
 * volver a hacer clic -o al apretar Escape- vuelve a su tamaño normal.
 *
 * REQUISITOS PARA USARLO EN OTRA PÁGINA "manejar X" (sin tocar este
 * archivo ni escribir CSS aparte):
 *
 *   1. Agregar la clase "caja-manejo" al <div> que contiene la lista
 *      (el mismo <div class="h-100 p-5 bg-light border rounded-3 ...">
 *      que ya usan todas estas páginas).
 *
 *   2. Poner DENTRO de ese <div> un botón con la clase
 *      "btn-pantalla-completa-manejo", que contenga un
 *      <span class="texto-pantalla-completa">Pantalla completa</span>.
 *      El script solo cambia el texto de ese <span>, así que se le puede
 *      agregar un ícono (u otros elementos) al botón sin que se pierdan.
 *
 *   3. Incluir este archivo con un <script src="..."></script>.
 *
 * Los estilos se inyectan desde acá, para que todo lo necesario viaje en
 * este único archivo. Si más adelante se prefiere, se pueden mover tal
 * cual a basecss.css.
 */
(function () {

    // ---------------------------------------------------------------
    // ESTILOS
    // ---------------------------------------------------------------
    // z-index muy altos a propósito: la caja tiene que quedar por encima
    // de la barra superior, y el menú contextual y el modal de
    // confirmación de edicionInline.js (que viven FUERA de la caja) por
    // encima de la caja, para que "Modificar" y "Dar de baja" sigan
    // funcionando a pantalla completa.
    function inyectarEstilos() {
        if (document.getElementById('estilos-pantalla-completa-manejo')) return;

        const estilos = document.createElement('style');
        estilos.id = 'estilos-pantalla-completa-manejo';
        estilos.textContent = [
            '.caja-manejo { position: relative; }',

            '.btn-pantalla-completa-manejo {',
            '    position: absolute;',
            '    top: 1rem;',
            '    right: 1rem;',
            '    z-index: 2;',
            '}',

            '.caja-manejo.pantalla-completa {',
            '    position: fixed !important;',
            '    top: 0;',
            '    left: 0;',
            '    right: 0;',
            '    bottom: 0;',
            '    width: auto;',
            '    height: auto !important;',
            '    margin: 0 !important;',
            '    z-index: 10000;',
            '    overflow: auto;',
            '    border: 0 !important;',
            '    border-radius: 0 !important;',
            '}',

            'body.manejo-pantalla-completa { overflow: hidden; }',
            'body.manejo-pantalla-completa #menuContextual { z-index: 10010 !important; }',
            'body.manejo-pantalla-completa .overlay-confirmacion { z-index: 10020 !important; }'
        ].join('\n');

        document.head.appendChild(estilos);
    }

    inyectarEstilos();

    // ---------------------------------------------------------------
    // COMPORTAMIENTO
    // ---------------------------------------------------------------
    document.querySelectorAll('.btn-pantalla-completa-manejo').forEach(function (boton) {

        const caja = boton.closest('.caja-manejo');
        if (!caja) return;

        const textoBoton = boton.querySelector('.texto-pantalla-completa');

        const TEXTO_ENTRAR = '⛶';
        const TEXTO_SALIR = '⛶';

        function estaActiva() {
            return caja.classList.contains('pantalla-completa');
        }

        function actualizarBoton(activa) {
            boton.setAttribute('aria-pressed', activa ? 'true' : 'false');
            boton.title = activa ? TEXTO_SALIR : 'Poner en pantalla completa';

            if (textoBoton) {
                textoBoton.textContent = activa ? TEXTO_SALIR : TEXTO_ENTRAR;
            }
        }

        function activar() {
            caja.classList.add('pantalla-completa');
            document.body.classList.add('manejo-pantalla-completa');
            actualizarBoton(true);
        }

        function desactivar() {
            caja.classList.remove('pantalla-completa');
            document.body.classList.remove('manejo-pantalla-completa');
            actualizarBoton(false);
        }

        boton.addEventListener('click', function () {
            if (estaActiva()) {
                desactivar();
            } else {
                activar();
            }
        });

        // Escape también sale de pantalla completa, salvo que se esté
        // editando una celda (ahí Escape ya significa "cancelar la
        // edición", según edicionInline.js) o haya un modal de
        // confirmación abierto: en esos casos no debe cerrar las dos
        // cosas a la vez.
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape' || !estaActiva()) return;

            if (e.target.closest && e.target.closest('input, select, textarea')) return;

            const modalConfirmacion = document.getElementById('modalConfirmacionBaja');
            if (modalConfirmacion && modalConfirmacion.classList.contains('visible')) return;

            desactivar();
        });
    });

})();