/**
 * calculoFactura.js
 * ---------------------------------------------------------------------
 * Calculadora de factura: el empleado elige productos del sistema (uno o
 * más, repitiendo el mismo si hace falta) y el script suma el total.
 *
 * Ahora además controla el STOCK en el navegador: no deja agregar más
 * unidades de las que hay disponibles. (Es solo una ayuda visual; la
 * validación real y la baja de stock se hacen en el servidor.)
 *
 * Al enviar el formulario viaja un JSON con [{idProducto, cantidad}] en
 * <input type="hidden" id="itemsFactura" name="itemsFactura">. El servidor
 * recalcula el monto con los precios de la base de datos, por lo que el
 * total que se ve acá es solo informativo.
 *
 * REQUISITOS EN EL HTML:
 *   - <select id="selectProductoFactura">, con un <option> por producto,
 *     cada uno con data-nombre, data-precio y data-stock.
 *   - <button id="btnAgregarProductoFactura">.
 *   - <ul id="listaProductosFactura"> (se completa dinámicamente).
 *   - <span/strong id="totalFacturaTexto"> (se actualiza con el total).
 *   - <input type="hidden" id="itemsFactura" name="itemsFactura">.
 */
(function () {

    const selectProducto = document.getElementById('selectProductoFactura');
    const btnAgregar = document.getElementById('btnAgregarProductoFactura');
    const lista = document.getElementById('listaProductosFactura');
    const totalTexto = document.getElementById('totalFacturaTexto');
    const inputItems = document.getElementById('itemsFactura');

    if (!selectProducto || !btnAgregar || !lista || !totalTexto || !inputItems) {
        return;
    }

    // idProducto (string) -> { nombre, precio, stock, cantidad }
    const itemsAgregados = {};

    function formatearMoneda(valor) {
        return '$' + valor.toLocaleString('es-UY', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function recalcularTotal() {
        let total = 0;
        const payload = [];

        Object.keys(itemsAgregados).forEach(function (id) {
            const item = itemsAgregados[id];
            total += item.precio * item.cantidad;
            payload.push({ idProducto: parseInt(id, 10), cantidad: item.cantidad });
        });

        totalTexto.textContent = formatearMoneda(total);
        inputItems.value = JSON.stringify(payload);
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
                ' — ' + formatearMoneda(item.precio * item.cantidad) +
                ' (stock: ' + item.stock + ')';

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
            if (item.cantidad >= item.stock) {
                btnMas.disabled = true;
                btnMas.title = 'No hay más stock disponible';
            }
            btnMas.addEventListener('click', function () {
                if (item.cantidad >= item.stock) {
                    alert('No hay stock suficiente de "' + item.nombre + '". Disponible: ' + item.stock + '.');
                    return;
                }
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
        const stock = parseInt(opcion.dataset.stock || '0', 10);
        const nombre = opcion.dataset.nombre || opcion.textContent.trim();

        const cantidadActual = itemsAgregados[idProducto] ? itemsAgregados[idProducto].cantidad : 0;

        if (cantidadActual + 1 > stock) {
            alert(stock <= 0
                ? '"' + nombre + '" no tiene stock disponible.'
                : 'No hay stock suficiente de "' + nombre + '". Disponible: ' + stock + '.');
            selectProducto.selectedIndex = 0;
            return;
        }

        if (itemsAgregados[idProducto]) {
            itemsAgregados[idProducto].cantidad += 1;
        } else {
            itemsAgregados[idProducto] = {
                nombre: nombre,
                precio: precio,
                stock: stock,
                cantidad: 1
            };
        }

        renderizarLista();
        recalcularTotal();

        selectProducto.selectedIndex = 0;
    });

    // Aviso previo al envío; la validación real está en el servidor.
    const formulario = inputItems.closest('form');
    if (formulario) {
        formulario.addEventListener('submit', function (e) {
            if (Object.keys(itemsAgregados).length === 0) {
                e.preventDefault();
                alert('Agregá al menos un producto a la factura antes de continuar.');
            }
        });
    }

    renderizarLista();
    recalcularTotal();

})();