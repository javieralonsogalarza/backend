document.addEventListener('DOMContentLoaded', (event) => {
    const button = document.querySelector('#generate');
    const canvasScrolls = document.querySelectorAll(".canvas_scroll");

    // Función para descargar imagen individual
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

    // Función para generar PDF con todas las páginas
    async function generatePDF() {
        // Crear un nuevo documento jsPDF en formato cuadrado
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: [200, 200] // Formato cuadrado 200x200mm
        });

        // Mostrar loader/mensaje de progreso
        const pdfButton = document.querySelector('#pdf-generate');
        const originalPdfText = pdfButton.textContent;
        pdfButton.textContent = 'Generando PDF...';
        pdfButton.disabled = true;

        try {
            for (let i = 0; i < canvasScrolls.length; i++) {
                const canvasScroll = canvasScrolls[i];
                const canvasFull = canvasScroll.querySelector(".canvas-full");

                // Generar canvas de la página actual
                const canvas = await html2canvas(canvasFull, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true
                });

                // Crear canvas con tamaño específico para PDF
                const newCanvas = document.createElement('canvas');
                newCanvas.width = 1440;
                newCanvas.height = 1440;
                const ctx = newCanvas.getContext('2d');
                
                // Dibujar imagen escalada
                ctx.drawImage(canvas, 0, 0, canvas.width, canvas.height, 0, 0, 1440, 1440);

                // Convertir a imagen para el PDF
                const imgData = newCanvas.toDataURL('image/png');

                // Si no es la primera página, añadir nueva página
                if (i > 0) {
                    pdf.addPage();
                }

                // Añadir imagen al PDF (ajustada al tamaño de página)
                pdf.addImage(imgData, 'PNG', 0, 0, 200, 200);

                // Actualizar texto del botón con progreso
                pdfButton.textContent = `Generando PDF... (${i + 1}/${canvasScrolls.length})`;
            }

            // Generar nombre de archivo para el PDF
            const filename = `Ranking_Completo_${sanitize(datos.categoria_name)}.pdf`;
            
            // Descargar PDF
            pdf.save(filename);

        } catch (error) {
            console.error('Error generando PDF:', error);
            alert('Error al generar el PDF. Por favor, inténtelo de nuevo.');
        } finally {
            // Restaurar botón
            pdfButton.textContent = originalPdfText;
            pdfButton.disabled = false;
        }
    }

    // Función para sanitizar nombres de archivo
    function sanitize(text) {
        return text ? text.replace(/[^a-z0-9]/gi, '_').toLowerCase() : 'sin_categoria';
    }

    // Conservar el texto original del botón
    const originalButtonText = button.textContent || 'Generar';
    
    // Crear controles de descarga
    function createDownloadControls() {
        const buttonParent = button.parentNode;
        
        if (canvasScrolls.length > 1) {
            // Múltiples páginas: crear dropdown para PNG y botón separado para PDF
            const controlsHTML = `
                <div class="download-controls">
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
                    <button id="pdf-generate" class="btn btn-primary btn-xs">📄 Generar PDF</button>
                </div>
            `;
            
            // Reemplazar el botón original
            button.outerHTML = controlsHTML;
            
            // Configurar eventos para dropdown de páginas
            const generateButton = document.querySelector('#generate');
            generateButton.addEventListener('click', (e) => {
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
                    downloadImage(pageIndex);
                    e.target.closest('.dropdown-menu').classList.remove('show');
                });
            });
            
            // Configurar evento para el botón PDF
            const pdfButton = document.querySelector('#pdf-generate');
            pdfButton.addEventListener('click', (e) => {
                e.preventDefault();
                generatePDF();
            });
            
        } else {
            // Una sola página: botones simples
            const controlsHTML = `
                <div class="download-controls">
                    <button id="generate" class="btn btn-primary btn-xs">${originalButtonText}</button>
                    <button id="pdf-generate" class="btn btn-primary btn-xs">📄 Generar PDF</button>
                </div>
            `;
            
            // Reemplazar el botón original
            button.outerHTML = controlsHTML;
            
            // Configurar eventos para botones simples
            document.querySelector('#generate').addEventListener('click', (e) => {
                e.preventDefault();
                downloadImage(0);
            });
            
            document.querySelector('#pdf-generate').addEventListener('click', (e) => {
                e.preventDefault();
                generatePDF();
            });
        }
        
        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', (e) => {
            const dropdowns = document.querySelectorAll('.dropdown-menu.show');
            dropdowns.forEach(dropdown => {
                if (!dropdown.closest('.dropdown').contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        });
    }

    // Crear los controles de descarga
    createDownloadControls();

    // Estilos CSS manteniendo el formato original
    const style = document.createElement('style');
    style.textContent = `
        .download-controls {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
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
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
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
            cursor: pointer;
        }
        
        .dropdown-item:hover, .dropdown-item:focus {
            color: #16181b;
            text-decoration: none;
            background-color: #f8f9fa;
        }
        
        /* Posicionamiento del dropdown */
        #generate-dropdown .dropdown-menu {
            left: 0;
            top: 100%;
        }
        
        /* Estilos para mantener el formato original de botones */
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            background-image: none;
            border: 1px solid transparent;
            white-space: nowrap;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        
        .btn:hover {
            text-decoration: none;
        }
        
        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
        
        .btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .btn-primary:hover:not(:disabled) {
            color: #fff;
            background-color: #0069d9;
            border-color: #0062cc;
        }
        
        /* Estilos específicos para el botón PDF */
        #pdf-generate {
            width: 160px;
            text-transform: uppercase;
            font-size: 17px;
            transition: all 0.3s ease;
            letter-spacing: 1px;
            height: 48px;
            cursor: pointer;
            background: #000;
            border: 2px solid #000;
            color: white;
        }
        
        #pdf-generate:hover:not(:disabled) {
            background-color: #333;
            border-color: #333;
            color: white;
        }
        

    `;
    document.head.appendChild(style);

    // Cargar jsPDF si no está disponible
    if (typeof window.jspdf === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        script.onload = () => {
            console.log('jsPDF cargado correctamente');
        };
        script.onerror = () => {
            console.error('Error cargando jsPDF');
        };
        document.head.appendChild(script);
    }
});