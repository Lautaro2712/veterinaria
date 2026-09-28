/**
 * ordenInline.js
 * ---------------------------------------------------------------------
 * Le da funcionalidad al botón de "reordenar" de las páginas de
 * "manejar X": alterna entre el orden con el que llegan las filas desde
 * el servidor (el que ya trae la consulta SQL) y un orden alfabético
 * calculado en el navegador, sin recargar la página.
 *
 * REQUISITOS PARA USARLO EN OTRA PÁGINA "manejar X" (reutilizable, sin
 * tocar este archivo):
 *
 *   1. El contenedor de la lista (el mismo <div class="... bg-light ...">
 *      que ya usan todas estas páginas) necesita el atributo
 *      data-orden-nombre (con cualquier valor; alcanza con que esté
 *      presente) para que el script sepa que esta página tiene botón de
 *      reordenar.
 *
 *   2. La celda por la que se quiere ordenar alfabéticamente necesita el
 *      atributo data-celda-orden (sin valor), sin importar si esa celda
 *      es editable o no. Esto es importante en columnas que muestran el
 *      nombre de una clave foránea (por ejemplo, el cliente de una cita,
 *      calculado con un JOIN): ese texto casi nunca coincide con un
 *      data-campo real de la tabla que se está editando -a veces ni
 *      siquiera hay data-campo en esa celda-, así que no alcanza con
 *      buscar por data-campo.
 *
 *      (Compatibilidad: si ninguna celda de la fila tiene
 *      data-celda-orden, el script busca como antes una celda cuyo
 *      data-campo coincida con el valor de data-orden-nombre. Esto es
 *      solo para no romper páginas ya armadas con ese método viejo;
 *      en páginas nuevas, usar directamente data-celda-orden.)
 *
 *   3. El botón de reordenar necesita la clase "btn-orden-manejo".
 *
 * Con esos datos alcanza: este script encuentra solo el encabezado y las
 * filas de datos (misma lógica que ya usa busquedainline.js), guarda el
 * orden original al cargar la página, y alterna hacia/desde el orden
 * alfabético cada vez que se hace clic en el botón.
 */
(function () {

    document.querySelectorAll('[data-orden-nombre]').forEach(function (contenedor) {

        const campoOrden = contenedor.dataset.ordenNombre;

        const boton = contenedor.querySelector('.btn-orden-manejo');
        if (!boton) return;

        const encabezado = contenedor.querySelector('ul.list-group.list-group-horizontal.pb-1');
        if (!encabezado) return;

        // Filas de datos: <ul> hijos directos del contenedor, sin "pb-1"
        // (misma detección que usa busquedainline.js).
        const filas = Array.prototype.filter.call(contenedor.children, function (el) {
            return el.tagName === 'UL' &&
                el.classList.contains('list-group-horizontal') &&
                !el.classList.contains('pb-1');
        });

        if (filas.length === 0) return;

        // Orden con el que llegaron del servidor: es el orden "por
        // defecto" al que se vuelve al desactivar el modo alfabético.
        const ordenOriginal = filas.slice();

        let modoAlfabetico = false;

        function obtenerTextoOrden(fila) {
            // Prioridad 1: el marcador explícito data-celda-orden, que
            // sirve tanto para celdas editables como para celdas de solo
            // lectura (como un nombre calculado con JOIN).
            let celda = fila.querySelector('[data-celda-orden]');

            // Prioridad 2 (compatibilidad hacia atrás): una celda editable
            // cuyo data-campo coincide con data-orden-nombre.
            if (!celda && campoOrden) {
                celda = fila.querySelector('[data-campo="' + campoOrden + '"]');
            }

            return celda ? celda.textContent.trim().toLowerCase() : '';
        }

        function aplicarOrden(listaFilas) {
            // Se insertan todas justo después del encabezado, recorriendo
            // la lista al revés: así, cada inserción empuja a la anterior
            // hacia abajo y el resultado final queda en el orden deseado,
            // sin tener que tocar ningún otro elemento del contenedor
            // (como la barra de búsqueda o el mensaje de "sin resultados",
            // que agrega busquedainline.js).
            for (let i = listaFilas.length - 1; i >= 0; i--) {
                encabezado.insertAdjacentElement('afterend', listaFilas[i]);
            }
        }

        boton.addEventListener('click', function () {
            modoAlfabetico = !modoAlfabetico;

            if (modoAlfabetico) {

                const filasOrdenadas = ordenOriginal.slice().sort(function (a, b) {
                    return obtenerTextoOrden(a).localeCompare(obtenerTextoOrden(b), 'es');
                });

                aplicarOrden(filasOrdenadas);

                boton.setAttribute('aria-pressed', 'true');
                boton.title = 'Volver al orden original';

            } else {

                aplicarOrden(ordenOriginal);

                boton.setAttribute('aria-pressed', 'false');
                boton.title = 'Ordenar alfabéticamente';
            }
        });
    });

})();