(function () {
    const encabezados = document.querySelectorAll('ul.list-group.list-group-horizontal.pb-1');

    encabezados.forEach(function (encabezado) {
        const contenedor = encabezado.parentElement;
        if (!contenedor) return;

        // Filas de datos: <ul> hermanos directos del encabezado, sin "pb-1"
        const filas = Array.prototype.filter.call(contenedor.children, function (el) {
            return el.tagName === 'UL' &&
                el.classList.contains('list-group-horizontal') &&
                !el.classList.contains('pb-1');
        });

        // Si no hay ninguna fila (lista vacía, o estructura distinta a la
        // esperada), no tiene sentido mostrar una barra de búsqueda.
        if (filas.length === 0) return;

        // --- Barra de búsqueda ---
        const wrapper = document.createElement('div');
        wrapper.className = 'input-group mb-3 buscador-inline';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control';
        input.placeholder = 'Buscar...';
        input.setAttribute('aria-label', 'Buscar en la lista');

        const btnLimpiar = document.createElement('button');
        btnLimpiar.type = 'button';
        btnLimpiar.className = 'btn btn-outline-secondary';
        btnLimpiar.textContent = '×';
        btnLimpiar.title = 'Limpiar búsqueda';

        wrapper.appendChild(input);
        wrapper.appendChild(btnLimpiar);

        // Mensaje de "sin resultados"
        const sinResultados = document.createElement('p');
        sinResultados.className = 'text-secondary mt-3 buscador-sin-resultados';
        sinResultados.textContent = 'No se encontraron resultados.';
        sinResultados.style.display = 'none';

        contenedor.insertBefore(wrapper, encabezado);
        // Se inserta justo después de la última fila de datos
        const ultimaFila = filas[filas.length - 1];
        contenedor.insertBefore(sinResultados, ultimaFila.nextSibling);

        function filtrar() {
            const consulta = input.value.trim().toLowerCase();
            let algunaVisible = false;

            filas.forEach(function (fila) {
                const coincide = consulta === '' || fila.textContent.toLowerCase().includes(consulta);
                // '' (no 'block'/'flex') para volver al display por defecto
                // definido por la clase de Bootstrap, en vez de forzar uno.
                fila.style.display = coincide ? '' : 'none';
                if (coincide) algunaVisible = true;
            });

            sinResultados.style.display = algunaVisible ? 'none' : '';
        }

        input.addEventListener('input', filtrar);
        btnLimpiar.addEventListener('click', function () {
            input.value = '';
            filtrar();
            input.focus();
        });
    });
})();