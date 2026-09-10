(function () {

    // Íconos en SVG
    const SVG_OJO_ABIERTO =
        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" ' +
        'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>' +
        '<circle cx="12" cy="12" r="3"></circle>' +
        '</svg>';

    const SVG_OJO_TACHADO =
        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" ' +
        'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.8 21.8 0 0 1 5.06-6.06"></path>' +
        '<path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.8 21.8 0 0 1-2.16 3.19"></path>' +
        '<path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path>' +
        '<line x1="1" y1="1" x2="23" y2="23"></line>' +
        '</svg>';

    // Si ya esta cargado el script, no se ejecuta de nuevo
    function yaProcesado(input) {
        return input.dataset.mostrarPasswordListo === "1";
    }

    function envolverCampo(input) {
        if (yaProcesado(input)) return;
        input.dataset.mostrarPasswordListo = "1";

        // Se agrega como un botón hijo al input
        const padre = input.parentNode;

        // Se ubica de forma relativa al contenedor padre
        if (getComputedStyle(padre).position === "static") {
            padre.style.position = "relative";
        }

        // Modifica el tamaño de la caja input
        input.style.paddingRight = "2.75rem";
        input.style.boxSizing = "border-box";

        // Botón
        const boton = document.createElement("button");
        boton.type = "button";
        boton.className = "mostrar-password-boton";
        boton.setAttribute("aria-label", "Mostrar contraseña");
        boton.setAttribute("aria-pressed", "false");
        boton.innerHTML = SVG_OJO_ABIERTO;

        boton.style.position = "absolute";
        boton.style.top = "50%";
        boton.style.right = "0.5rem";
        boton.style.transform = "translateY(-50%)";
        boton.style.border = "none";
        boton.style.background = "transparent";
        boton.style.cursor = "pointer";
        boton.style.padding = "0.25rem";
        boton.style.display = "flex";
        boton.style.alignItems = "center";
        boton.style.color = "inherit";
        boton.style.opacity = "0.7";

        // Se agrega al final del contenedor para que no se coma el floating label de Bootstrap
        padre.appendChild(boton);

        boton.addEventListener("click", function () {
            const visible = input.type === "text";

            input.type = visible ? "password" : "text";
            boton.innerHTML = visible ? SVG_OJO_ABIERTO : SVG_OJO_TACHADO;
            boton.setAttribute("aria-label", visible ? "Mostrar contraseña" : "Ocultar contraseña");
            boton.setAttribute("aria-pressed", visible ? "false" : "true");

            // El darle a mostrar contraseña no cambia el foco
            input.focus();
        });
    }

    function procesarPagina() {
        document
            .querySelectorAll('input[type="password"]')
            .forEach(envolverCampo);
    }

    // Empieza a cargar todos los campos de contraseñas
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", procesarPagina);
    } else {
        procesarPagina();
    }
})();