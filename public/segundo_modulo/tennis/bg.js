(function(){

    const diagramForBg = document.querySelector('.general_canvas'), 
        inputBg = document.querySelector('#putBg');

    if (inputBg) {
        inputBg.addEventListener('change', function(event){
            const file = event.target.files[0];
            if(file){
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageUrl = e.target.result;

                    // Quitar todas las clases de fondo predefinidas
                    diagramForBg.className = diagramForBg.className.replace(/bg\d+/g, '').trim();

                    // Aplicar el fondo personalizado
                    diagramForBg.style.background = `url(${imageUrl}) no-repeat`;
                    diagramForBg.style.backgroundSize = 'cover'; // Asegurar que cubra bien

                    // ACTUALIZADO: Desactivar cualquier selector de fondo predefinido
                    document.querySelectorAll('.bg-option').forEach(opt => opt.classList.remove('active'));

                    // preview
                    if(!inputBg.parentElement.classList.contains('with_preview')){
                        inputBg.parentElement.classList.add('with_preview');
                    }
                    inputBg.parentElement.style.background = `url(${imageUrl})`;

                }
                reader.readAsDataURL(file);
            } else {
                resetToDefaultImage();
            }
        });
    }

    const deleteButton = document.querySelector('.delete_preview[data-id="2"]');
    if (deleteButton) {
        deleteButton.addEventListener('click', (e)=>{
            e.preventDefault();

            // preview
            inputBg.parentElement.style.background = ``;
            if(inputBg.parentElement.classList.contains('with_preview')){
                inputBg.parentElement.classList.remove('with_preview');
            }
            
            inputBg.value = ''; // Limpiar el input

            resetToDefaultImage();
        });
    }

    function resetToDefaultImage(){
        // Quitar fondo personalizado en línea
        diagramForBg.style.background = ``;

        // Quitar cualquier clase de fondo existente para evitar duplicados
        diagramForBg.className = diagramForBg.className.replace(/bg\d+/g, '').trim();
        
        // ACTUALIZADO: Volver al fondo por defecto 'bg1' en lugar de uno aleatorio
        diagramForBg.classList.add(`bg1`);

        // ACTUALIZADO: Marcar el selector 'bg1' como activo en la UI
        document.querySelectorAll('.bg-option').forEach(opt => opt.classList.remove('active'));
        const defaultOption = document.querySelector('.bg-option[data-bg="bg1"]');
        if (defaultOption) {
            defaultOption.classList.add('active');
        }
    }

}());