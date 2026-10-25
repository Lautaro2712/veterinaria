/**
 * facturaServicio.js
 * ---------------------------------------------------------------------
 * Maneja el tipo de factura en subirfactura.php:
 *
 *   - "Facturar una compra"   -> se muestra el menú de productos de siempre
 *                                (lo maneja calculoFactura.js, que no se toca).
 *   - "Facturar un servicio"  -> se muestra el menú de servicio: se elige el
 *                                servicio y, si ese servicio depende del
 *                                animal, aparece una segunda lista con los
 *                                tipos de animal y su precio para ESE servicio.
 *
 * Los datos NO están escritos acá: llegan desde la base de datos en el
 * atributo data-servicios de #seccionServicio, con esta forma:
 *
 *   [ { id, nombre, precioFijo, animales: [ { id, nombre, precio }, ... ] } ]
 *
 *   - precioFijo distinto de null  -> el servicio cuesta siempre lo mismo y
 *                                     no pide tipo de animal (ej.: consulta).
 *   - precioFijo null              -> el precio sale del animal elegido.
 *
 * Por eso agregar servicios, animales o precios en la base alcanza: este
 * archivo no necesita cambios.
 *
 * El total que se muestra es solo informativo. El servidor vuelve a leer el
 * precio de la base de datos al registrar la factura.
 */
(function () {

    const selectTipoFactura = document.getElementById('tipoFactura');
    const seccionCompra = document.getElementById('seccionCompra');
    const seccionServicio = document.getElementById('seccionServicio');
    const selectServicio = document.getElementById('idTipoServicio');
    const bloqueAnimal = document.getElementById('bloqueTipoAnimal');
    const selectAnimal = document.getElementById('idTipoAnimal');
    const totalTexto = document.getElementById('totalServicioTexto');

    // Si falta algo, la página no tiene el menú de servicio montado.
    if (!selectTipoFactura || !seccionCompra || !seccionServicio || !selectServicio ||
        !bloqueAnimal || !selectAnimal || !totalTexto) {
        return;
    }

    let servicios = [];
    try {
        servicios = JSON.parse(seccionServicio.dataset.servicios || '[]');
    } catch (e) {
        servicios = [];
    }

    function formatearMoneda(valor) {
        return '$' + valor.toLocaleString('es-UY', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function buscarServicio(id) {
        return servicios.find(function (s) {
            return String(s.id) === String(id);
        }) || null;
    }

    // Precio según lo elegido hasta ahora, o null si todavía no alcanza.
    function precioActual() {
        const servicio = buscarServicio(selectServicio.value);
        if (!servicio) return null;

        if (servicio.precioFijo !== null) {
            return servicio.precioFijo;
        }

        const animal = servicio.animales.find(function (a) {
            return String(a.id) === String(selectAnimal.value);
        });

        return animal ? animal.precio : null;
    }

    function actualizarTotal() {
        const precio = precioActual();
        totalTexto.textContent = formatearMoneda(precio === null ? 0 : precio);
    }

    // Reconstruye la lista de animales según el servicio elegido.
    function actualizarAnimales() {
        const servicio = buscarServicio(selectServicio.value);

        selectAnimal.innerHTML = '';

        // Sin servicio, o servicio de precio fijo: no se pide animal.
        if (!servicio || servicio.precioFijo !== null) {
            bloqueAnimal.classList.add('d-none');
            actualizarTotal();
            return;
        }

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.disabled = true;
        placeholder.selected = true;
        placeholder.textContent = servicio.animales.length > 0
            ? 'Seleccione un tipo de animal'
            : 'No hay precios cargados para este servicio';
        selectAnimal.appendChild(placeholder);

        servicio.animales.forEach(function (animal) {
            const opcion = document.createElement('option');
            opcion.value = String(animal.id);
            opcion.textContent = animal.nombre + ' — ' + formatearMoneda(animal.precio);
            selectAnimal.appendChild(opcion);
        });

        bloqueAnimal.classList.remove('d-none');
        actualizarTotal();
    }

    function actualizarModo() {
        const esServicio = selectTipoFactura.value === 'servicio';

        seccionCompra.classList.toggle('d-none', esServicio);
        seccionServicio.classList.toggle('d-none', !esServicio);
    }

    selectTipoFactura.addEventListener('change', actualizarModo);
    selectServicio.addEventListener('change', actualizarAnimales);
    selectAnimal.addEventListener('change', actualizarTotal);

    // Validación al enviar, SOLO en modo servicio.
    //
    // Se engancha en la fase de captura del document a propósito: así corre
    // ANTES que el listener de submit de calculoFactura.js, que exige haber
    // agregado productos y bloquearía el envío de una factura de servicio
    // (donde no hay productos). Con stopPropagation() ese listener no llega
    // a ejecutarse, pero el envío normal del formulario sigue su curso.
    // En modo compra no se hace nada: lo sigue manejando calculoFactura.js.
    //
    // La validación que realmente cuenta es la del servidor; esta solo avisa
    // antes de enviar.
    document.addEventListener('submit', function (e) {
        if (e.target !== selectTipoFactura.form) return;
        if (selectTipoFactura.value !== 'servicio') return;

        e.stopPropagation();

        const servicio = buscarServicio(selectServicio.value);

        if (!servicio) {
            e.preventDefault();
            alert('Seleccioná el servicio que querés facturar.');
            return;
        }

        if (precioActual() === null) {
            e.preventDefault();
            alert('Seleccioná el tipo de animal que recibió el servicio.');
        }
    }, true);

    // Estado inicial (por ejemplo, si la página se recargó con un error en
    // modo servicio).
    actualizarModo();
    actualizarAnimales();

})();