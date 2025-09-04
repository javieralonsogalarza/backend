document.addEventListener('DOMContentLoaded', (event) => {
    const button = document.querySelector('#generate');
    const canvasScrolls = document.querySelectorAll(".canvas_scroll");

    // Función para descargar imagen
    function downloadImage(pageIndex) {
        const canvasScroll = canvasScrolls[pageIndex];
        const canvasFull = canvasScroll.querySelector(".canvas-full");

        html2canvas(canvasFull, {
            scale: 2,
            useCORS: true,
            allowTaint: true
        }).then(canvas => {
            // Crear canvas con tamaño específico
            const newCanvas = document.createElement('canvas');
            newCanvas.width = 1440;
            newCanvas.height = 1440;
            const ctx = newCanvas.getContext('2d');
            
            // Dibujar imagen escalada
            ctx.drawImage(canvas, 0, 0, canvas.width, canvas.height, 0, 0, 1440, 1440);

            // Generar nombre de archivo
            const filename = `Ranking_${sanitize(datos.categoria_name)}_Pagina${pageIndex + 1}_EntryList.png`;

            // Descargar
            const link = document.createElement('a');
            link.download = filename;
            link.href = newCanvas.toDataURL('image/png');
            link.click();
        });
    }

    // Función para sanitizar nombres de archivo
    function sanitize(text) {
        return text ? text.replace(/[^a-z0-9]/gi, '_').toLowerCase() : 'sin_categoria';
    }

    // Conservar el texto original del botón
    const originalButtonText = button.textContent || 'Generar';
    
    // Si hay más de una página, convertir el botón en un dropdown
    if (canvasScrolls.length > 1) {
        // Cambiar el botón existente por un dropdown
        const buttonParent = button.parentNode;
        const buttonPosition = Array.from(buttonParent.children).indexOf(button);
        
        // Crear el HTML del dropdown
        const dropdownHTML = `
            <div class="dropdown" id="generate-dropdown">
                <button id="generate" class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    ${originalButtonText}
                </button>
                <ul class="dropdown-menu" aria-labelledby="generate">
                    ${Array.from(canvasScrolls).map((canvas, index) => 
                        `<li><a class="dropdown-item page-option" href="#" data-page="${index}">Página ${index + 1}</a></li>`
                    ).join('')}
                </ul>
            </div>
        `;
        
        // Reemplazar el botón por el dropdown
        button.outerHTML = dropdownHTML;
        
        // Obtener la referencia al nuevo botón
        const newButton = document.querySelector('#generate');
        
        // Configurar el evento de toggle para el dropdown
        newButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const dropdownMenu = e.currentTarget.nextElementSibling;
            dropdownMenu.classList.toggle('show');
        });
        
        // Configurar eventos para las opciones del dropdown
        const pageOptions = document.querySelectorAll('.page-option');
        pageOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault();
                const pageIndex = parseInt(e.target.getAttribute('data-page'));
                // Descargar la imagen de la página seleccionada
                downloadImage(pageIndex);
                // Ocultar el dropdown
                e.target.closest('.dropdown-menu').classList.remove('show');
            });
        });
        
        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', (e) => {
            const dropdowns = document.querySelectorAll('.dropdown-menu.show');
            dropdowns.forEach(dropdown => {
                if (!dropdown.closest('.dropdown').contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        });
    } else {
        // Si solo hay una página, mantener el comportamiento original
        button.addEventListener('click', (e) => {
            e.preventDefault();
            downloadImage(0);
        });
    }

    // Estilos CSS para el dropdown
    const style = document.createElement('style');
    style.textContent = `
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            z-index: 1000;
            min-width: 10rem;
            padding: 0.5rem 0;
            margin: 0;
            font-size: 1rem;
            color: #212529;
            text-align: left;
            list-style: none;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: 0.25rem;
        }
        .dropdown-menu.show {
            display: block;
        }
        .dropdown-item {
            display: block;
            width: 100%;
            padding: 0.25rem 1.5rem;
            clear: both;
            font-weight: 400;
            color: #212529;
            text-align: inherit;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
            text-decoration: none;
        }
        .dropdown-item:hover, .dropdown-item:focus {
            color: #16181b;
            text-decoration: none;
            background-color: #f8f9fa;
        }
        /* Asegurar que el dropdown se muestre hacia abajo y no se corte */
        #generate-dropdown .dropdown-menu {
            left: 0;
            top: 100%;
        }
    `;
    document.head.appendChild(style);
});