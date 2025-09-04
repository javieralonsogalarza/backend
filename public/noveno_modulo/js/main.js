document.addEventListener('DOMContentLoaded', (event) => {
    
    let button = document.querySelector('#generate'),
        canvasFull = document.querySelector(".canvas-full");

    // Variables globales para rastrear el orden de selección - DECLARADAS UNA SOLA VEZ
    if (typeof window.selectionOrderTracker === 'undefined') {
        window.selectionOrderTracker = [];
    }
    if (typeof window.nextOrderNumber === 'undefined') {
        window.nextOrderNumber = 1;
    }
    
    // Referencias locales a las variables globales
    // let selectionOrderTracker = window.selectionOrderTracker;  // REMOVIDO - usar window.selectionOrderTracker directamente
    // let nextOrderNumber = window.nextOrderNumber;  // REMOVIDO - usar window.nextOrderNumber directamente
    
    // Variable para prevenir múltiples ejecuciones
    let isProcessing = false;

    // Función para limpiar el estado anterior
    function clearPreviousState() {
        // Ocultar todas las categorías
        document.querySelectorAll('.match-card').forEach(card => {
            card.style.display = 'none';
        });
        
        // Ocultar todos los números de orden
        document.querySelectorAll('.selection-order').forEach(order => {
            order.style.display = 'none';
        });
        
        document.querySelectorAll('.category-order').forEach(order => {
            order.style.display = 'none';
        });
        
        // Resetear las horas mostradas
        document.querySelectorAll('.category-time-display').forEach(display => {
            display.textContent = '';
        });
    }

    function downloadImage(data, filename, removeLoading = true) {
        let a = document.createElement('a');
        a.href = data;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        if(removeLoading && document.body.classList.contains('loading')){
            document.body.classList.remove('loading')
        }
    }
function sanitize(text) {
    if (!text) return 'SinCategoria';
    return text.replace(/[^A-Za-z0-9]/g, '');
}
    function createCanvasAndDownload(canvas, startX, width, height, targetWidth, targetHeight, filename, removeLoading = true) {
        let newCanvas = document.createElement('canvas');
        newCanvas.width = targetWidth;
        newCanvas.height = targetHeight;
        let ctx = newCanvas.getContext('2d');
        ctx.drawImage(canvas, startX, 0, width, height, 0, 0, targetWidth, targetHeight);
        let dataURL = newCanvas.toDataURL('image/png');
        
        // Si no se proporciona un filename específico, usar el formato anterior
        if (!filename || filename === 'imagen.png') {
            filename = "Modulo_3_";
            if (datos.jugadores && Array.isArray(datos.jugadores) && datos.jugadores.length > 0) {
                filename += `${sanitize(datos.categoria)}_EntryList.png`;
            } else if (datos.grupos && Array.isArray(datos.grupos) && datos.grupos.length > 0) {
                filename += `${sanitize(datos.categoria)}_Grupos.png`;
            } else {
                filename += `${sanitize(datos.categoria)}_Desconocido.png`;
            }
        }
        
        downloadImage(dataURL, filename, removeLoading);
    }

    if (canvasFull) {
        if (button) {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Si hay un select dentro del botón, no hacer nada (el select maneja su propio evento)
                if (button.querySelector('select')) {
                    return;
                }
                
                // Prevenir múltiples ejecuciones
                if (isProcessing) {
                    console.log('Ya se está procesando una solicitud');
                    return;
                }
                
                console.log('Botón clickeado'); // Debug

                // Limpiar estado anterior antes de verificar categorías seleccionadas
                clearPreviousState();

                // Obtener categorías seleccionadas
                const selectedCategories = Array.from(document.querySelectorAll('.category-checkbox:checked'));
                
                console.log('Categorías seleccionadas:', selectedCategories.length); // Debug
                
                if (selectedCategories.length === 0) {
                    console.log('No hay categorías seleccionadas'); // Debug
                    document.body.classList.remove('loading');
                    return;
                }
                
                // Marcar como procesando
                isProcessing = true;
                
                // Para botón normal (4 o menos categorías), generar imagen única
                console.log('4 o menos categorías, generando imagen única'); // Debug
                generateSingleImage();
            });
        } else {
            console.log('Botón no encontrado'); // Debug
        }
    } else {
        console.log('Canvas no encontrado'); // Debug
    }

    function updateCategoryDisplay() {
        console.log('updateCategoryDisplay called'); // Debug
        const checkboxes = document.querySelectorAll('.category-checkbox');
        
        // Actualizar orden de selección
        updateSelectionOrder();
        
        // Obtener categorías seleccionadas y ordenarlas por orden de selección
        const selectedCategories = Array.from(checkboxes).filter(checkbox => checkbox.checked);
        const sortedSelectedCategories = selectedCategories.sort((a, b) => {
            const aOrder = window.selectionOrderTracker.find(item => item.categoryId === a.getAttribute('data-category-id'))?.order || 999;
            const bOrder = window.selectionOrderTracker.find(item => item.categoryId === b.getAttribute('data-category-id'))?.order || 999;
            return aOrder - bOrder;
        });
        
        console.log('Selection order:', window.selectionOrderTracker); // Debug
        console.log('Sorted categories:', sortedSelectedCategories.map(cb => cb.getAttribute('data-category-id'))); // Debug
        
        // Reorganizar las tarjetas de partido en el DOM según el orden de selección
        const container = document.querySelector('.group_list');
        if (container) {
            const allCards = Array.from(container.querySelectorAll('.match-card'));
            
            // Crear un array de tarjetas ordenadas según el orden de selección
            const orderedCards = [];
            
            // Primero agregar las tarjetas de categorías seleccionadas en orden
            sortedSelectedCategories.forEach(checkbox => {
                const categoryId = checkbox.getAttribute('data-category-id');
                const cards = allCards.filter(card => card.getAttribute('data-category-id') === categoryId);
                orderedCards.push(...cards);
            });
            
            // Luego agregar las tarjetas no seleccionadas al final
            allCards.forEach(card => {
                if (!orderedCards.includes(card)) {
                    orderedCards.push(card);
                }
            });
            
            // Reorganizar el DOM - mover elementos según el nuevo orden
            orderedCards.forEach(card => {
                container.appendChild(card);
            });
            
            console.log('DOM reorganized, new order:', orderedCards.map(card => card.getAttribute('data-category-id'))); // Debug
        }
        
        // Después de reorganizar, mostrar/ocultar según selección
        checkboxes.forEach((checkbox) => {
            const categoryId = checkbox.getAttribute('data-category-id');
            const timeInput = document.querySelector(`.category-time[data-category-id="${categoryId}"]`);
            const categoryDisplay = document.querySelector(`#category-${categoryId} .category-time-display`);
            const matchCards = document.querySelectorAll(`.match-card[data-category-id="${categoryId}"]`);
            
            if (checkbox.checked) {
                // Mostrar las tarjetas de partido para esta categoría
                matchCards.forEach(card => {
                    card.style.display = 'block';
                });
                
                // Actualizar la hora mostrada con formato 12h
                if (categoryDisplay) {
                    if (timeInput && timeInput.value) {
                        categoryDisplay.textContent = formatTime12Hour(timeInput.value);
                    } else {
                        categoryDisplay.textContent = '';
                    }
                }
            } else {
                // Ocultar las tarjetas de partido para esta categoría
                matchCards.forEach(card => {
                    card.style.display = 'none';
                });
                
                if (categoryDisplay) {
                    categoryDisplay.textContent = '';
                }
            }
        });
    }

    // Hacer updateCategoryDisplay globalmente accesible
    window.updateCategoryDisplay = updateCategoryDisplay;

    // Función para actualizar el orden de selección - GLOBAL
    function updateSelectionOrder() {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        
        checkboxes.forEach((checkbox) => {
            const categoryId = checkbox.getAttribute('data-category-id');
            const selectionOrder = checkbox.parentElement.querySelector('.selection-order');
            const categoryOrder = document.querySelector(`#category-${categoryId} .category-order`);
            
            if (checkbox.checked) {
                // Buscar el orden asignado a esta categoría
                const orderInfo = window.selectionOrderTracker.find(item => item.categoryId === categoryId);
                if (orderInfo) {
                    // Mostrar número de orden en la configuración
                    if (selectionOrder) {
                        selectionOrder.style.display = 'inline-flex';
                        selectionOrder.textContent = orderInfo.order;
                    }
                    
                    // Mostrar número de orden en la imagen
                    if (categoryOrder) {
                        categoryOrder.style.display = 'inline-flex';
                        categoryOrder.textContent = orderInfo.order;
                    }
                }
            } else {
                // Ocultar número de orden
                if (selectionOrder) {
                    selectionOrder.style.display = 'none';
                }
                if (categoryOrder) {
                    categoryOrder.style.display = 'none';
                }
            }
        });
    }

    // Función para manejar el cambio de estado de checkbox - GLOBAL
    function handleCheckboxChange(checkbox) {
        const categoryId = checkbox.getAttribute('data-category-id');
        console.log('handleCheckboxChange called for category:', categoryId, 'checked:', checkbox.checked); // Debug
        
        if (checkbox.checked) {
            // Si se selecciona, agregar al tracker con el siguiente número de orden
            const existingIndex = window.selectionOrderTracker.findIndex(item => item.categoryId === categoryId);
            if (existingIndex === -1) {
                window.selectionOrderTracker.push({
                    categoryId: categoryId,
                    order: window.nextOrderNumber
                });
                window.nextOrderNumber++;
                console.log('Category added to tracker:', categoryId, 'order:', window.nextOrderNumber - 1); // Debug
            }
        } else {
            // Si se deselecciona, remover del tracker y reorganizar los números
            const existingIndex = window.selectionOrderTracker.findIndex(item => item.categoryId === categoryId);
            if (existingIndex !== -1) {
                const removedOrder = window.selectionOrderTracker[existingIndex].order;
                window.selectionOrderTracker.splice(existingIndex, 1);
                console.log('Category removed from tracker:', categoryId, 'was order:', removedOrder); // Debug
                
                // Reorganizar los números de orden
                window.selectionOrderTracker.forEach(item => {
                    if (item.order > removedOrder) {
                        item.order--;
                    }
                });
                
                // Ajustar el siguiente número
                window.nextOrderNumber = window.selectionOrderTracker.length + 1;
            }
        }
        
        // Limpiar estado anterior antes de actualizar
        clearPreviousState();
        updateCategoryDisplay();
        
        // Verificar si necesitamos mostrar dropdown o botón normal
        checkButtonState();
    }

    // Hacer las funciones globalmente accesibles
    window.updateSelectionOrder = updateSelectionOrder;
    window.handleCheckboxChange = handleCheckboxChange;

    // Función para formatear hora de 24h a 12h con AM/PM (copia de la función en grupos.php)
    function formatTime12Hour(time24) {
        if (!time24) return '';
        
        const [hours, minutes] = time24.split(':');
        const hour = parseInt(hours, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12; // Convierte 0 a 12 para medianoche
        
        return `${hour12}:${minutes} ${ampm}`;
    }

    // Función para verificar el estado del botón según las categorías seleccionadas
    function checkButtonState() {
        const selectedCategories = Array.from(document.querySelectorAll('.category-checkbox:checked'));
        
        if (selectedCategories.length > 4) {
            // Convertir a dropdown si hay más de 4 categorías seleccionadas
            if (!button.querySelector('select')) {
                convertButtonToDropdown(selectedCategories);
            } else {
                // Actualizar las opciones del dropdown existente
                updateDropdownOptions(selectedCategories);
            }
        } else {
            // Restaurar botón normal si hay 4 o menos categorías
            if (button.querySelector('select')) {
                restoreOriginalButton();
            }
        }
    }

    // Función para actualizar las opciones del dropdown existente
    function updateDropdownOptions(selectedCategories) {
        const select = button.querySelector('select');
        if (!select) return;
        
        const totalPages = Math.ceil(selectedCategories.length / 4);
        
        // Remover event listeners anteriores clonando el elemento
        const newSelect = select.cloneNode(false);
        
        // Copiar estilos del select original y mantener el texto visible
        newSelect.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            color: transparent;
            border: none;
            font-family: 'Anton', sans-serif;
            font-size: 17px;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            outline: none;
            z-index: 1;
            pointer-events: all;
        `;
        
        // Agregar opción placeholder
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = '';
        placeholderOption.selected = true;
        placeholderOption.disabled = true;
        newSelect.appendChild(placeholderOption);
        
        // Agregar opciones para cada página
        for (let i = 1; i <= totalPages; i++) {
            const option = document.createElement('option');
            option.value = i.toString();
            option.textContent = `Página ${i}`;
            newSelect.appendChild(option);
        }
        
        // Agregar el nuevo event listener
        newSelect.addEventListener('change', createDropdownChangeHandler(selectedCategories));
        
        // Reemplazar el select anterior
        button.replaceChild(newSelect, select);
        

    }

    function generateSingleImage() {
        document.body.classList.add('loading');

        // Limpiar estado anterior y actualizar visualización
        clearPreviousState();
        updateCategoryDisplay();

        setTimeout(() => {
            html2canvas(canvasFull, {
                scale: 1
            }).then(canvas => {
                let width = canvas.width;
                let height = canvas.height;
                let targetWidth = 1440;
                let targetHeight = 1440;
                createCanvasAndDownload(canvas, 0, width, height, targetWidth, targetHeight, 'imagen.png');
                
                // Resetear el estado de procesamiento
                isProcessing = false;
            }).catch(error => {
                console.error('Error al generar imagen:', error);
                isProcessing = false;
                document.body.classList.remove('loading');
            });
        }, 600);
    }

    function convertButtonToDropdown(selectedCategories) {
        console.log('Ejecutando convertButtonToDropdown'); // Debug
        const totalPages = Math.ceil(selectedCategories.length / 4);
        console.log('Total de páginas:', totalPages); // Debug
        
        // Si ya hay un select, no crear otro
        if (button.querySelector('select')) {
            return;
        }
        
        // Crear el select elemento
        const select = document.createElement('select');
        select.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            color: transparent;
            border: none;
            font-family: 'Anton', sans-serif;
            font-size: 17px;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            outline: none;
            z-index: 1;
            pointer-events: all;
        `;
        
        // Agregar opción placeholder que no hace nada (para mantener el texto visible)
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = '';
        placeholderOption.selected = true;
        placeholderOption.disabled = true;
        select.appendChild(placeholderOption);
        
        // Agregar opciones para cada página
        for (let i = 1; i <= totalPages; i++) {
            const option = document.createElement('option');
            option.value = i.toString();
            option.textContent = `Página ${i}`;
            select.appendChild(option);
        }
        
        // Event listener para cuando se selecciona una opción
        select.addEventListener('change', createDropdownChangeHandler(selectedCategories));
        
        // Hacer el botón relativo para posicionar el select y mantener el texto visible
        button.style.position = 'relative';
        button.style.zIndex = '1';
        
        // Asegurar que el texto del botón siga siendo visible
        const buttonText = button.childNodes[0];
        if (buttonText && buttonText.nodeType === Node.TEXT_NODE) {
            // Crear un span para el texto si no existe
            const textSpan = document.createElement('span');
            textSpan.textContent = buttonText.textContent;
            textSpan.style.cssText = `
                position: relative;
                z-index: 2;
                pointer-events: none;
                color: white;
                font-family: 'Anton', sans-serif;
                font-size: 17px;
                text-transform: uppercase;
                letter-spacing: 1px;
            `;
            button.replaceChild(textSpan, buttonText);
        }
        
        // Agregar el select al botón
        button.appendChild(select);
        

    }

    // Función para crear el handler del cambio en el dropdown
    function createDropdownChangeHandler(selectedCategories) {
        return function() {
            const selectedValue = this.value;
            
            if (selectedValue) {
                generateSpecificPage(selectedCategories, parseInt(selectedValue));
            }
            
            // Resetear la selección para mantener el placeholder
            this.selectedIndex = 0;
        };
    }

    function restoreOriginalButton() {
        // Remover el select si existe
        const select = button.querySelector('select');
        if (select) {
            button.removeChild(select);
        }
        
        // Restaurar el span de texto a texto normal si existe
        const textSpan = button.querySelector('span');
        if (textSpan) {
            const textNode = document.createTextNode(textSpan.textContent);
            button.replaceChild(textNode, textSpan);
        }
        
        // Resetear el estilo del botón
        button.style.position = '';
        button.style.zIndex = '';
        
        // Resetear el estado de procesamiento
        isProcessing = false;
    }

    function generateSpecificPage(selectedCategories, pageNumber) {
        document.body.classList.add('loading');
        
        // Guardar el estado original
        const originalState = Array.from(document.querySelectorAll('.category-checkbox')).map(checkbox => ({
            element: checkbox,
            checked: checkbox.checked
        }));
        const originalOrderTracker = [...window.selectionOrderTracker];
        const originalNextOrderNumber = window.nextOrderNumber;
        
        // Ordenar las categorías seleccionadas según su orden de selección
        const sortedCategories = selectedCategories.sort((a, b) => {
            const aOrder = window.selectionOrderTracker.find(item => item.categoryId === a.getAttribute('data-category-id'))?.order || 999;
            const bOrder = window.selectionOrderTracker.find(item => item.categoryId === b.getAttribute('data-category-id'))?.order || 999;
            return aOrder - bOrder;
        });
        
        // Obtener las categorías para la página específica
        const startIndex = (pageNumber - 1) * 4;
        const endIndex = startIndex + 4;
        const pageCategories = sortedCategories.slice(startIndex, endIndex);
        
        // Desmarcar todas las categorías
        document.querySelectorAll('.category-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Resetear el tracker de orden
        window.selectionOrderTracker = [];
        window.nextOrderNumber = 1;
        
        // Marcar solo las categorías de la página específica y mantener el orden original
        pageCategories.forEach((category) => {
            category.checked = true;
            const categoryId = category.getAttribute('data-category-id');
            // Buscar el orden original de esta categoría
            const originalOrder = originalOrderTracker.find(item => item.categoryId === categoryId);
            if (originalOrder) {
                window.selectionOrderTracker.push({
                    categoryId: categoryId,
                    order: originalOrder.order
                });
            }
        });
        window.nextOrderNumber = Math.max(...window.selectionOrderTracker.map(item => item.order)) + 1;
        
        // Actualizar la visualización
        if (typeof updateCategoryTimes === 'function') {
            updateCategoryTimes();
        } else {
            updateCategoryDisplay();
        }
        
        // Generar la imagen después de un momento
        setTimeout(() => {
            html2canvas(canvasFull, {
                scale: 1
            }).then(canvas => {
                let width = canvas.width;
                let height = canvas.height;
                let targetWidth = 1440;
                let targetHeight = 1440;
                
                // Crear nombre de archivo
                const categoryNames = pageCategories.map(cat => 
                    sanitize(cat.getAttribute('data-category-name'))
                ).join('_');
                
                const filename = `Partidos_Pagina_${pageNumber}_${categoryNames}.png`;
                
                createCanvasAndDownload(canvas, 0, width, height, targetWidth, targetHeight, filename);
                
                // Restaurar el estado original
                originalState.forEach(state => {
                    state.element.checked = state.checked;
                });
                window.selectionOrderTracker = originalOrderTracker;
                window.nextOrderNumber = originalNextOrderNumber;
                
                // Actualizar la visualización
                if (typeof updateCategoryTimes === 'function') {
                    updateCategoryTimes();
                } else {
                    updateCategoryDisplay();
                }
                
                // Verificar el estado del botón después de restaurar el estado original
                checkButtonState();
                
                // Resetear el estado de procesamiento
                isProcessing = false;
            }).catch(error => {
                console.error('Error al generar imagen específica:', error);
                
                // Restaurar el estado original en caso de error
                originalState.forEach(state => {
                    state.element.checked = state.checked;
                });
                window.selectionOrderTracker = originalOrderTracker;
                window.nextOrderNumber = originalNextOrderNumber;
                
                if (typeof updateCategoryTimes === 'function') {
                    updateCategoryTimes();
                } else {
                    updateCategoryDisplay();
                }
                
                // Verificar el estado del botón después de restaurar el estado original
                checkButtonState();
                
                isProcessing = false;
                document.body.classList.remove('loading');
            });
        }, 600);
    }

    // Funcionalidad para el texto personalizado
    const customTextInput = document.getElementById('customText');
    const displayCustomText = document.getElementById('displayCustomText');

    console.log('customTextInput:', customTextInput);
    console.log('displayCustomText:', displayCustomText);

    if (customTextInput && displayCustomText) {
        console.log('Ambos elementos encontrados, configurando event listeners');
        
        // Actualizar el texto en tiempo real
        customTextInput.addEventListener('input', function() {
            const text = this.value.trim();
            console.log('Texto ingresado:', text);
            if (text) {
                displayCustomText.textContent = text;
            } else {
                displayCustomText.textContent = 'Texto personalizado aparecerá aquí';
            }
        });

        // Establecer texto inicial si hay algún valor
        if (customTextInput.value.trim()) {
            displayCustomText.textContent = customTextInput.value.trim();
        }
    } else {
        console.log('No se encontraron los elementos necesarios');
        if (!customTextInput) console.log('No se encontró customTextInput');
        if (!displayCustomText) console.log('No se encontró displayCustomText');
    }
    
    // Verificación inicial del estado del botón
    checkButtonState();
});