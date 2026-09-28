(function () {
    const menu = document.getElementById('menuContextual');
    const btnModificar = document.getElementById('btnModificar');
    const btnDarBaja = document.getElementById('btnDarBaja');

    // Si la página no tiene el menú contextual montado, no hacemos nada
    // (evita errores en páginas que en el futuro carguen este script sin
    // necesitarlo).
    if (!menu || !btnModificar) {
        return;
    }

    // cambiarcambiarcambiarcambiarcambiarcambiar
    if (btnDarBaja) {
        btnDarBaja.disabled = false;
        btnDarBaja.removeAttribute('title');
    }

    // Mapea el "tipo" lógico de la celda al atributo type de un <input> HTML
    const mapaTipos = {
        fecha: 'date',
        hora: 'time',
        numero: 'number',
        texto: 'text'
    };
// recuerda que con el poder del amor podras superar todas tus dificultades >:(
    let celdaActual = null;

    // Abrir el menú contextual al hacer clic sobre cualquier celda editable
    document.querySelectorAll('.editable-cell').forEach(function (celda) {
        celda.addEventListener('click', function (e) {
            if (celda.classList.contains('celda-edicion')) return;

            e.stopPropagation();
            celdaActual = celda;
            menu.style.left = e.pageX + 'px';
            menu.style.top = e.pageY + 'px';
            menu.style.display = 'block';
        });
    });

    // Cerrar el menú al hacer clic en cualquier otro lado de la página
    document.addEventListener('click', function () {
        menu.style.display = 'none';
    });
    menu.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    // "Modificar" -> pide confirmación (mismo modal que "Dar de baja") y,
    // si se confirma, activa el modo edición sobre la celda seleccionada.
    btnModificar.addEventListener('click', function () {
        menu.style.display = 'none';
        if (celdaActual) {
            const celdaParaEditar = celdaActual;
            mostrarConfirmacion('¿Está seguro de su acción?', function () {
                iniciarEdicion(celdaParaEditar);
            });
        }
    });

    // "Dar de baja" -> pide confirmación (con un modal propio, no confirm()
    // del navegador) y, si se confirma, elimina el registro completo.
    if (btnDarBaja) {
        btnDarBaja.addEventListener('click', function () {
            menu.style.display = 'none';
            if (celdaActual) {
                iniciarBaja(celdaActual);
            }
        });
    }

    function iniciarBaja(celda) {
        const tabla = celda.dataset.tabla;
        const idRegistro = celda.dataset.id;
        // La fila completa a eliminar del DOM (representación que hace el navegador de tu HTML para que JavaScript pueda manipularlo.) tras el borrado exitoso.
        // Todas las celdas de una misma fila comparten el mismo data-id,
        // así que alcanza con encontrar el <ul> que contiene a esta celda.
        const fila = celda.closest('ul.list-group-horizontal');

        mostrarConfirmacion('¿Está seguro de su acción?', function () {
            const cuerpo = new URLSearchParams();
            cuerpo.set('tabla', tabla);
            cuerpo.set('id', idRegistro);
            cuerpo.set('accion', 'eliminar');

           fetch('/!PHPchecks/actualizarbm.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: cuerpo.toString()
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.exito) {
                        if (fila) {
                            fila.remove();
                        }
                    } else {
                        alert(data.error || 'No se pudo dar de baja el registro.');
                    }
                })
                .catch(function () {
                    alert('Error de conexión al dar de baja el registro.');
                });
        });
    }

    function obtenerModalConfirmacion() {
        let modal = document.getElementById('modalConfirmacionBaja');
        if (modal) {
            return modal;
        }

        modal = document.createElement('div');
        modal.id = 'modalConfirmacionBaja';
        modal.className = 'overlay-confirmacion';
        modal.innerHTML =
            '<div class="caja-confirmacion">' +
                '<p class="mensaje-confirmacion"></p>' +
                '<div class="botones-confirmacion">' +
                    '<button type="button" class="btn btn-sm btn-secondary" id="btnCancelarBaja">Volver atrás</button>' +
                    '<button type="button" class="btn btn-sm btn-danger" id="btnConfirmarBaja">Confirmar</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(modal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                ocultarConfirmacion();
            }
        });

        return modal;
    }

    function mostrarConfirmacion(mensaje, alConfirmar) {
        const modal = obtenerModalConfirmacion();
        modal.querySelector('.mensaje-confirmacion').textContent = mensaje;

        modal.querySelector('#btnConfirmarBaja').onclick = function () {
            ocultarConfirmacion();
            alConfirmar();
        };
        modal.querySelector('#btnCancelarBaja').onclick = function () {
            ocultarConfirmacion();
        };

        modal.classList.add('visible');
    }

    function ocultarConfirmacion() {
        const modal = document.getElementById('modalConfirmacionBaja');
        if (modal) {
            modal.classList.remove('visible');
        }
    }

    function iniciarEdicion(celda) {
        const valorOriginal = celda.textContent.trim();
        // Traduce el bool a texto, para no mostrar un 1 o 0.
        const valorCrudo = (celda.dataset.valorCrudo !== undefined)
            ? celda.dataset.valorCrudo
            : valorOriginal;
        const tabla = celda.dataset.tabla;
        const idRegistro = celda.dataset.id;
        const campo = celda.dataset.campo;
        const tipo = celda.dataset.tipo || 'texto';

        celda.classList.add('celda-edicion');
        celda.textContent = '';

        let input;

        if (tipo === 'booleano') {
            // Un <select> también expone .value, igual que un <input>, así que
            // el resto del código (guardarCambio, terminarEdicion, etc.) no
            // necesita saber que esto no es un <input> literal.
            input = document.createElement('select');
            input.className = 'form-select form-select-sm d-inline-block';

            // data-etiquetas opcional: "textoParaCero,textoParaUno".
            // Si no está presente, se usa "No,Sí" por defecto.
            const etiquetas = (celda.dataset.etiquetas || 'No,Sí').split(',');

            const opcionCero = document.createElement('option');
            opcionCero.value = '0';
            opcionCero.textContent = etiquetas[0] || 'No';

            const opcionUno = document.createElement('option');
            opcionUno.value = '1';
            opcionUno.textContent = etiquetas[1] || 'Sí';

            input.appendChild(opcionCero);
            input.appendChild(opcionUno);
            input.value = valorCrudo; // preselecciona la opción actual
        } else if (tipo === 'seleccion') {
            // <select> con opciones dinámicas (N opciones, no solo 0/1),
            // por ejemplo un listado de proveedores. Las opciones vienen
            // del servidor como JSON en data-opciones, ya que el JS no
            // puede consultar la base de datos por su cuenta.
            input = document.createElement('select');
            input.className = 'form-select form-select-sm d-inline-block';

            let opciones = [];
            try {
                opciones = JSON.parse(celda.dataset.opciones || '[]');
            } catch (e) {
                opciones = [];
            }

            const hayCoincidencia = opciones.some(function (op) {
                return String(op.valor) === valorCrudo;
            });
            if (!hayCoincidencia) {
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '-- Seleccionar --';
                input.appendChild(placeholder);
            }

            opciones.forEach(function (opcion) {
                const el = document.createElement('option');
                el.value = String(opcion.valor);
                el.textContent = opcion.texto;
                input.appendChild(el);
            });

            input.value = valorCrudo;
        } else {
            input = document.createElement('input');
            input.type = mapaTipos[tipo] || 'text';
            input.value = valorCrudo;
            input.className = 'form-control form-control-sm d-inline-block';
        }

        const btnGuardar = document.createElement('button');
        btnGuardar.type = 'button';
        btnGuardar.textContent = 'Confirmar';
        btnGuardar.className = 'btn btn-sm btn-success ms-1';
        btnGuardar.title = 'Guardar cambio';

        celda.appendChild(input);
        celda.appendChild(btnGuardar);
        input.focus();

        function terminarEdicion(nuevoTexto) {
            celda.textContent = nuevoTexto;
            celda.classList.remove('celda-edicion');
        }

        function guardarCambio() {
            const nuevoValor = input.value;

            if (!nuevoValor) {
                alert('El valor no puede estar vacío.');
                return;
            }

            input.disabled = true;
            btnGuardar.disabled = true;

            const cuerpo = new URLSearchParams();
            cuerpo.set('tabla', tabla);
            cuerpo.set('id', idRegistro);
            cuerpo.set('campo', campo);
            cuerpo.set('valor', nuevoValor);

            fetch('/!PHPchecks/actualizarbm.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: cuerpo.toString()
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.exito) {
                        // Si el servidor resolvió un valor "para mostrar" (por
                        // ejemplo, el nombre de un proveedor a partir del ID
                        // que se escribió), usamos ese texto en la celda en
                        // vez del valor crudo tal cual se tipeó.
                        const textoFinal = (typeof data.valorMostrado === 'string')
                            ? data.valorMostrado
                            : nuevoValor;
                        // Guardamos el valor crudo recién confirmado, para que
                        // la PRÓXIMA vez que se edite esta celda el input/select
                        // arranque con el valor correcto (no con el texto
                        // traducido que se ve en pantalla).
                        celda.dataset.valorCrudo = nuevoValor;
                        terminarEdicion(textoFinal);
                    } else {
                        alert(data.error || 'No se pudo guardar el cambio.');
                        terminarEdicion(valorOriginal);
                    }
                })
                .catch(function () {
                    alert('Error de conexión al guardar el cambio.');
                    terminarEdicion(valorOriginal);
                });
        }

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                guardarCambio();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                terminarEdicion(valorOriginal);
            }
        });

        btnGuardar.addEventListener('click', function (e) {
            e.stopPropagation();
            guardarCambio();
        });

        // Evita que un clic dentro del input/botón cierre el menú sin querer
        input.addEventListener('click', function (e) { e.stopPropagation(); });
    }
})();