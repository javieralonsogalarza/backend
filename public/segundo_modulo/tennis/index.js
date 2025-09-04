(function () {
    let inputFile = document.getElementById("imageUpload"),
        popupCropper = document.querySelector(".popup_cropper"),
        popupCropperCanvas = document.querySelector(".popup_cropper-canvas");
    let image4Photo = document.querySelector('#imageContainer img');
    let cropper;
    
    // Función para mostrar el loading de página completa
    function showFullPageLoading() {
        document.body.classList.add("loading");
        
        // Asegurarse de que exista el elemento de loading
        if (!document.getElementById('fullpage-loader')) {
            const loaderOverlay = document.createElement('div');
            loaderOverlay.id = 'fullpage-loader';
            loaderOverlay.className = 'fullpage-loader';
            
            const spinner = document.createElement('div');
            spinner.className = 'loader-spinner';
            
            loaderOverlay.appendChild(spinner);
            document.body.appendChild(loaderOverlay);
        } else {
            document.getElementById('fullpage-loader').style.display = 'flex';
        }
    }
    
    // Función para ocultar el loading
    function hideFullPageLoading() {
        document.body.classList.remove("loading");
        if (document.getElementById('fullpage-loader')) {
            document.getElementById('fullpage-loader').style.display = 'none';
        }
    }
    
    if (inputFile) {
        inputFile.addEventListener("change", function (e) {
            var file = e.target.files[0];
            var reader = new FileReader();
            image4Photo.src = '';
            document.querySelector('.generate_image').disabled = true;
            
            setTimeout(() => {
                reader.onload = function (event) {
                    // Crear un nuevo elemento img
                    let img = document.createElement("img");
                    img.id = 'croppy';
                    img.src = event.target.result;
                    
                    console.log(img.src);
    
                    // Limpiar cualquier imagen previa en el canvas
                    popupCropperCanvas.innerHTML = "";
                    popupCropperCanvas.appendChild(img);
    
                    // Quitar el loading y mostrar el popup
                    popupCropper.classList.add("active");
                    cropper = new Cropper(img, {
                        aspectRatio: 1.502 / 1.008
                    });
                };
    
                // Leer el archivo como una URL
                reader.readAsDataURL(file);
                
            }, 1000);
            
            // Configurar los botones
            popupCropper.querySelector('#cut').addEventListener('click', cutButtonHandler);
            popupCropper.querySelector('#clean').addEventListener('click', cleanButtonHandler);
        });
        
        // Handler para el botón de cortar - AHORA INCLUYE SUBIDA INMEDIATA
        function cutButtonHandler(e) {
            e.preventDefault();
            const canvas = cropper.getCroppedCanvas();
            const croppedImageData = canvas.toDataURL("image/png");
            
            if (croppedImageData) {
                // Mostrar la imagen recortada en la interfaz
                image4Photo.src = croppedImageData;
                if(!inputFile.parentElement.classList.contains('with_preview')){
                    inputFile.parentElement.classList.add('with_preview');
                }
                inputFile.parentElement.style.background = `url(${croppedImageData})`;
                
                // Inmediatamente enviar la imagen al servidor
                showFullPageLoading();
                
                // Obtener los datos necesarios para el envío
                const torneocategoriaId = datos['torneo_categoria_id'];
                const torneo = datos['torneo'];
                const categoria = datos['categoria'];
                const categoriaId = datos['categoria_id'];
                const partido = datos['partido'];
                const partidoId = datos['partido'];
                const ronda = datos['ronda'];
                const grupo = datos['grupo'];
                
                // Preparar los datos para enviar
                const data = {
                    image: croppedImageData,
                    torneocategoria_id: parseInt(torneocategoriaId),
                    torneo: torneo,
                    categoria: categoria,
                    categoria_id: parseInt(categoriaId),
                    partido: partido,
                    partido_id: parseInt(partidoId),
                    ronda: parseInt(ronda),
                    grupo: grupo
                };
                
                // Enviar la solicitud al servidor
                fetch('/api/upload-image-segunda', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                 
                    if (result.status === 'success') {
                        console.log('Imagen guardada automáticamente:', result.filepath);
                        // Guardar la ruta en localStorage
                        const storageKey = `imagen_partido_${partidoId}`;
                        localStorage.setItem(storageKey, result.filepath);
                        // Opcional: guardar la URL en un campo oculto para uso posterior
                        if (document.getElementById('uploaded_image_url')) {
                            document.getElementById('uploaded_image_url').value = result.filepath;
                        }
                        // Habilitar el botón generate_image después de carga exitosa
                        document.querySelector('.generate_image').disabled = false;
                        hideFullPageLoading();
                    } else {
                        alert('Error al guardar la imagen: ' + result.message);
                    }
                })
                .catch(error => {
                    hideFullPageLoading();
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            } else {
                console.error("Error al generar el DataURL.");
            }
            resetCropper();
        }
        
        // Handler para el botón de limpiar
        function cleanButtonHandler(e) {
            e.preventDefault();
            image4Photo.src = '';
            resetCropper();
        }
        
        // Event listener para el botón de eliminar preview
        document.querySelector(`.delete_preview[data-id="1"]`).addEventListener('click', (e)=>{
            e.preventDefault();
            inputFile.parentElement.style.background = '';
            if(inputFile.parentElement.classList.contains('with_preview')){
                inputFile.parentElement.classList.remove('with_preview');
            }
            image4Photo.src = '';
            setTimeout(() => {
                document.querySelector('.generate_image').disabled = true;
                resetCropper();
            }, 1000);
        });
        
        function resetCropper(){
            if (popupCropper.classList.contains("active")) {
                popupCropper.classList.remove("active");
            }
            document.getElementById("imageUpload").value = '';
            if(document.body.classList.contains('loading')){
            }
            setTimeout(() => {
                popupCropperCanvas.innerHTML = '';
            }, 500);
        }
    }
})();