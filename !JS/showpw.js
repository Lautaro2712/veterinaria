(function () {

    // Íconos en SVG (basados en el set Feather Icons, MIT license), en
    // vez de emojis: así el trazo hereda el color del texto (currentColor)
    // y se ve consistente en cualquier navegador o sistema operativo.
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

    // Si el campo ya está envuelto (por si el script se carga dos veces
    // por error), no se procesa de nuevo.
    function yaProcesado(input) {
        return input.dataset.mostrarPasswordListo === "1";
    }

    function envolverCampo(input) {
        if (yaProcesado(input)) return;
        input.dataset.mostrarPasswordListo = "1";

        // No se envuelve el input en un <div> nuevo: eso rompía selectores
        // CSS como ".form-floating > .form-control" (que exigen que el
        // input sea hijo DIRECTO de su contenedor original), y el input
        // perdía el alto/estilos que le da Bootstrap. En cambio, el botón
        // se agrega como un hijo más del contenedor que YA tenía el input,
        // y se posiciona con position:absolute sobre él.
        const padre = input.parentNode;

        // Si el contenedor no tiene ya una posición explícita, se le pone
        // "relative" para que el botón absoluto se ubique respecto a él
        // (y no respecto a otro ancestro más arriba en la página).
        if (getComputedStyle(padre).position === "static") {
            padre.style.position = "relative";
        }

        // Deja lugar a la derecha del campo para el botón, sin pisar el
        // texto que el usuario esté escribiendo.
        input.style.paddingRight = "2.75rem";
        input.style.boxSizing = "border-box";

        // --- Botón de mostrar/ocultar ---
        const boton = document.createElement("button");
        boton.type = "button";
        boton.className = "mostrar-password-boton";
        boton.setAttribute("aria-label", "Mostrar contraseña");
        boton.setAttribute("aria-pressed", "false");
        boton.innerHTML = SVG_OJO_ABIERTO;

        boton.style.position = "absolute";
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

        // Se agrega al final del contenedor (después del <label> de
        // Bootstrap, si lo hay) para no interponerse entre el input y su
        // label, que es el orden que Bootstrap necesita para el efecto
        // "floating label".
        padre.appendChild(boton);

        // No se usa "top: 50%" porque ese porcentaje se calcularía sobre
        // la altura de TODO el contenedor (input + su <label>, texto de
        // ayuda, etc.), no sobre la altura del input en sí — eso hacía
        // que el botón quedara desplazado hacia arriba, encimado con el
        // borde del campo. En cambio, se calcula la posición exacta del
        // input dentro del contenedor (offsetTop + la mitad de su alto)
        // y se la asigna directamente en píxeles.
        function reposicionarBoton() {
            const centroVertical = input.offsetTop + (input.offsetHeight / 2);
            boton.style.top = centroVertical + "px";
        }

        reposicionarBoton();

        // Por si el <label> pasa a ocupar dos líneas (pantallas angostas)
        // u otro cambio de layout mueve al input dentro de su contenedor.
        window.addEventListener("resize", reposicionarBoton);

        if (typeof ResizeObserver !== "undefined") {
            const observadorTamaño = new ResizeObserver(reposicionarBoton);
            observadorTamaño.observe(padre);
        }

        boton.addEventListener("click", function () {
            const visible = input.type === "text";

            input.type = visible ? "password" : "text";
            boton.innerHTML = visible ? SVG_OJO_ABIERTO : SVG_OJO_TACHADO;
            boton.setAttribute("aria-label", visible ? "Mostrar contraseña" : "Ocultar contraseña");
            boton.setAttribute("aria-pressed", visible ? "false" : "true");

            // Devuelve el foco al campo para no cortar la escritura del usuario.
            input.focus();
        });
    }

    function procesarPagina() {
        document
            .querySelectorAll('input[type="password"]')
            .forEach(envolverCampo);
    }

    // Campos ya presentes al cargar la página.
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", procesarPagina);
    } else {
        procesarPagina();
    }

    // Por si algún campo de contraseña se agrega dinámicamente después
    // (por ejemplo, un formulario que aparece en un modal).
    const observador = new MutationObserver(procesarPagina);
    observador.observe(document.body, { childList: true, subtree: true });

})();