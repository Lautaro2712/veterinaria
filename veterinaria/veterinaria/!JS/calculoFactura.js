/**
 * calculoFactura.js
 * ---------------------------------------------------------------------
 * Reemplaza el campo de "monto total" de tipo libre por una calculadora:
 * el empleado elige productos del sistema (uno o más, y puede repetir el
 * mismo producto varias veces) y este script va sumando el monto total
 * de la factura a partir del precio de cada producto agregado.
 *
 * El resultado de la suma se guarda en el <input type="hidden"
 * id="montoTotalFactura">, que es el que realmente se envía al servidor
 * junto con el resto del formulario. El empleado ya no puede escribir un
 * número a mano.
 *
 * REQUISITOS EN EL HTML:
 *   - <select id="selectProductoFactura">, con un <option> por producto,
 *     cada uno con data-nombre y data-precio.
 *   - <button id="btnAgregarProductoFactura">.
 *   - <ul id="listaProductosFactura"> (se completa dinámicamente).
 *   - <span/strong id="totalFacturaTexto"> (se actualiza con el total).
 *   - <input type="hidden" id="montoTotalFactura">.
 */
(function () {

    const selectProducto = document.getElementById('selectProductoFactura');
    const btnAgregar = document.getElementById('btnAgregarProductoFactura');
    const lista = document.getElementById('listaProductosFactura');
    const totalTexto = document.getElementById('totalFacturaTexto');
    const inputMontoTotal = document.getElementById('montoTotalFactura');

    // Si falta cualquiera de estos elementos, la página no tiene la
    // calculadora montada (o está mal armada) y no hay nada que hacer.
    if (!selectProducto || !btnAgregar || !lista || !totalTexto || !inputMontoTotal) {
        return;
    }

    // idProducto (string) -> { nombre, precio, cantidad }
    const itemsAgregados = {};

    function formatearMoneda(valor) {
        return '$' + valor.toLocaleString('es-UY', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function recalcularTotal() {
        let total = 0;

        Object.keys(itemsAgregados).forEach(function (id) {
            const item = itemsAgregados[id];
            total += item.precio * item.cantidad;
        });

        totalTexto.textContent = formatearMoneda(total);
        // Se guarda como número plano (sin formato de moneda) para que
        // el servidor lo reciba tal cual espera un campo numérico.
        inputMontoTotal.value = total.toFixed(2);
    }

    function renderizarLista() {
        lista.innerHTML = '';

        const ids = Object.keys(itemsAgregados);

        if (ids.length === 0) {
            const vacio = document.createElement('li');
            vacio.className = 'list-group-item text-secondary';
            vacio.textContent = 'Todavía no agregaste ningún producto.';
            lista.appendChild(vacio);
            return;
        }

        ids.forEach(function (id) {
            const item = itemsAgregados[id];

            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2';

            const info = document.createElement('span');
            info.textContent = item.nombre + ' x' + item.cantidad +
                ' — ' + formatearMoneda(item.precio * item.cantidad);

            const controles = document.createElement('div');
            controles.className = 'd-flex align-items-center gap-1';

            const btnMenos = document.createElement('button');
            btnMenos.type = 'button';
            btnMenos.className = 'btn btn-sm btn-outline-secondary';
            btnMenos.textContent = '−';
            btnMenos.setAttribute('aria-label', 'Quitar una unidad de ' + item.nombre);
            btnMenos.addEventListener('click', function () {
                item.cantidad -= 1;
                if (item.cantidad <= 0) {
                    delete itemsAgregados[id];
                }
                renderizarLista();
                recalcularTotal();
            });

            const btnMas = document.createElement('button');
            btnMas.type = 'button';
            btnMas.className = 'btn btn-sm btn-outline-secondary';
            btnMas.textContent = '+';
            btnMas.setAttribute('aria-label', 'Agregar una unidad más de ' + item.nombre);
            btnMas.addEventListener('click', function () {
                item.cantidad += 1;
                renderizarLista();
                recalcularTotal();
            });

            const btnQuitar = document.createElement('button');
            btnQuitar.type = 'button';
            btnQuitar.className = 'btn btn-sm btn-outline-danger';
            btnQuitar.textContent = 'Quitar';
            btnQuitar.addEventListener('click', function () {
                delete itemsAgregados[id];
                renderizarLista();
                recalcularTotal();
            });

            controles.appendChild(btnMenos);
            controles.appendChild(btnMas);
            controles.appendChild(btnQuitar);

            li.appendChild(info);
            li.appendChild(controles);
            lista.appendChild(li);
        });
    }

    btnAgregar.addEventListener('click', function () {
        const opcion = selectProducto.options[selectProducto.selectedIndex];

        if (!opcion || !opcion.value) {
            alert('Seleccioná un producto para agregar.');
            return;
        }

        const idProducto = opcion.value;
        const precio = parseFloat(opcion.dataset.precio || '0');
        const nombre = opcion.dataset.nombre || opcion.textContent.trim();

        if (itemsAgregados[idProducto]) {
            itemsAgregados[idProducto].cantidad += 1;
        } else {
            itemsAgregados[idProducto] = {
                nombre: nombre,
                precio: precio,
                cantidad: 1
            };
        }

        renderizarLista();
        recalcularTotal();

        // Vuelve el <select> al placeholder, para que quede claro que hay
        // que elegir de nuevo si se quiere agregar otro producto (o el
        // mismo, para sumar otra unidad).
        selectProducto.selectedIndex = 0;
    });

    // El formulario no debería poder enviarse sin haber agregado ningún
    // producto, ya que ahí el monto total quedaría en $0. La validación
    // real (por si alguien arma el POST a mano) está del lado del
    // servidor; esto es solo para dar el aviso antes de intentar enviar.
    const formulario = inputMontoTotal.closest('form');
    if (formulario) {
        formulario.addEventListener('submit', function (e) {
            if (Object.keys(itemsAgregados).length === 0) {
                e.preventDefault();
                alert('Agregá al menos un producto a la factura antes de continuar.');
            }
        });
    }

    // Estado inicial: lista vacía y total en $0.
    renderizarLista();
    recalcularTotal();

})();