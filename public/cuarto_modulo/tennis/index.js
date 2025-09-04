(function () {
    // Helper function to create image handler
    function createImageHandler(config) {
        const {
            inputFile,
            popupCropper,
            popupCropperCanvas,
            imageContainer,
            generateButton,
            cutButton,
            cleanButton,
            deleteButton,
            playerId,
            type
        } = config;

        let cropper;
        let currentAbortController = null;

        // Crear el elemento del loader
        const loader = document.createElement('div');
        loader.className = 'upload-loader';
        loader.innerHTML = '<div class="spinner"></div><p>Subiendo imagen...</p>';
        loader.style.display = 'none';
        document.body.appendChild(loader);

        // Estilos para el loader
        const style = document.createElement('style');
        style.textContent = `
            .upload-loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                color: white;
            }
            .spinner {
                width: 50px;
                height: 50px;
                border: 5px solid rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                border-top-color: white;
                animation: spin 1s ease-in-out infinite;
                margin-bottom: 20px;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

        if (!inputFile) return;

        inputFile.addEventListener("change", function (e) {
            document.body.classList.add("loading");

            var file = e.target.files[0];
            var reader = new FileReader();

            imageContainer.src = '';
            generateButton.disabled = true;

            setTimeout(() => {
                reader.onload = function (event) {
                    let img = document.createElement("img");
                    img.src = event.target.result;

                    popupCropperCanvas.innerHTML = "";
                    popupCropperCanvas.appendChild(img);

                    document.body.classList.remove("loading");
                    popupCropper.classList.add("active");

                    cropper = new Cropper(img, {
                        aspectRatio: 1
                    });
                };

                reader.readAsDataURL(file);
            }, 1000);
        });

        function resetCropper() {
            if (popupCropper.classList.contains("active")) {
                popupCropper.classList.remove("active");
            }

            inputFile.value = '';

            if (document.body.classList.contains('loading')) {
                document.body.classList.remove('loading');
            }

            setTimeout(() => {
                popupCropperCanvas.innerHTML = '';
            }, 500);
        }

        function updateStatusText(isLocal) {
            const containerId = isLocal ? 'local' : 'rival';
            const container = document.getElementById(containerId);
            if (!container) return;

            const statusSpan = container.querySelector('.texto-abajo');
            if (!statusSpan) return;

            const playerNameElement = isLocal ? 
                document.querySelector('.nombre-jugador') : 
                document.querySelectorAll('.nombre-jugador')[1];
            const playerName = playerNameElement?.textContent || (isLocal ? 'Jugador Local' : 'Jugador Rival');
            
            statusSpan.textContent = `Ya tiene imagen precargada en el sistema: ${playerName}.`;
        }

        function handleImageUpload(croppedImage) {
            // Mostrar el loader antes de iniciar la carga
            loader.style.display = 'flex';
            
            // Cancel any previous request if it exists
            if (currentAbortController) {
                currentAbortController.abort();
            }

            // Create new controller for this request
            currentAbortController = new AbortController();

            return fetch('/api/upload-image', {
                method: 'POST',
                body: JSON.stringify({
                    image: croppedImage,
                    jugador_id: playerId
                }),
                headers: {
                    'Content-Type': 'application/json',
                },
                signal: currentAbortController.signal
            })
            .then(response => response.json())
            .then(data => {
                currentAbortController = null; // Clear controller after completion
                
                // Ocultar el loader después de completar la carga
                loader.style.display = 'none';
                
                if (data.status === 'success') {
                    // Add timestamp to prevent caching
                    const timestamp = new Date().getTime();
                    const playerImage = `/storage/uploads/img/jugador_${playerId}.png?t=${timestamp}`;
                    
                    if(type == "local") {
                        localStorage.setItem(`playerLocalImage_${playerId}`, playerImage);
                        updateStatusText(true);
                        
                        // Force image refresh in all containers
                        const localImg = document.querySelector('#imageContainer img');
                        if(localImg) {
                            localImg.src = playerImage;
                        }
                    } else {
                        localStorage.setItem(`playerRivalImage_${playerId}`, playerImage);
                        updateStatusText(false);
                        
                        // Force image refresh in all containers
                        const rivalImg = document.querySelector('#imageContainerTwo img');
                        if(rivalImg) {
                            rivalImg.src = playerImage;
                        }
                    }
                    
                    alert('Imagen guardada exitosamente');
                } else {
                    console.error('Error al guardar la imagen:', data.message);
                    alert('Error al guardar la imagen: ' + data.message);
                }
            })
            .catch(error => {
                // Ocultar el loader en caso de error
                loader.style.display = 'none';
                
                if (error.name === 'AbortError') {
                    console.log('Petición cancelada');
                } else {
                    console.error('Error al subir la imagen:', error);
                    alert('Error al subir la imagen. Por favor, intente nuevamente.');
                }
            });
        }

        function refreshAllImages() {
            const timestamp = new Date().getTime();
            const localPlayerId = datos.jugador_local_id;
            const rivalPlayerId = datos.jugador_rival_id;
            
            // Refresh local player image
            const localStoredImage = localStorage.getItem(`playerLocalImage_${localPlayerId}`);
            if(localStoredImage) {
                const refreshedUrl = localStoredImage.split('?')[0] + '?t=' + timestamp;
                localStorage.setItem(`playerLocalImage_${localPlayerId}`, refreshedUrl);
                
                const localImg = document.querySelector('#imageContainer img');
                if(localImg) {
                    localImg.src = refreshedUrl;
                }
                
                const localHeroView = document.getElementById('local');
                if(localHeroView) {
                    localHeroView.style.backgroundImage = `url(${refreshedUrl})`;
                }
            }
            
            // Refresh rival player image
            const rivalStoredImage = localStorage.getItem(`playerRivalImage_${rivalPlayerId}`);
            if(rivalStoredImage) {
                const refreshedUrl = rivalStoredImage.split('?')[0] + '?t=' + timestamp;
                localStorage.setItem(`playerRivalImage_${rivalPlayerId}`, refreshedUrl);
                
                const rivalImg = document.querySelector('#imageContainerTwo img');
                if(rivalImg) {
                    rivalImg.src = refreshedUrl;
                }
                
                const rivalHeroView = document.getElementById('rival');
                if(rivalHeroView) {
                    rivalHeroView.style.backgroundImage = `url(${refreshedUrl})`;
                }
            }
        }

        cutButton.addEventListener('click', (e) => {
            e.preventDefault();

            const canvas = cropper.getCroppedCanvas();
            var croppedImage = canvas.toDataURL("image/png");

            if (croppedImage) {
                imageContainer.src = croppedImage;

                if (!inputFile.parentElement.classList.contains('with_preview')) {
                    inputFile.parentElement.classList.add('with_preview');
                }
                inputFile.parentElement.style.background = `url(${croppedImage})`;
                generateButton.disabled = false;

                handleImageUpload(croppedImage)
                    .then(() => {
                        refreshAllImages();
                    });
            } else {
                console.error("Error al generar el DataURL.");
            }

            resetCropper();
        });

        cleanButton.addEventListener('click', (e) => {
            e.preventDefault();
            // Cancelar cualquier petición en curso
            if (currentAbortController) {
                currentAbortController.abort();
                currentAbortController = null;
                // Asegurarse de que el loader se oculte si se cancela
                loader.style.display = 'none';
            }
            imageContainer.src = '';
            resetCropper();
        });
    }

    // Initialize first image handler
    createImageHandler({
        inputFile: document.getElementById("imageUpload"),
        popupCropper: document.querySelector(".popup_cropper"),
        popupCropperCanvas: document.querySelector(".popup_cropper-canvas"),
        imageContainer: document.querySelector('#imageContainer img'),
        generateButton: document.querySelector('.generate_image'),
        cutButton: document.querySelector('.popup_cropper #cut'),
        cleanButton: document.querySelector('.popup_cropper #clean'),
        deleteButton: document.querySelector('.delete_preview[data-id="1"]'),
        playerId: datos['jugador_local_id'],
        type: "local"
    });

    // Initialize second image handler
    createImageHandler({
        inputFile: document.getElementById("imageUploadTwo"),
        popupCropper: document.querySelector(".popup_cropper_two"),
        popupCropperCanvas: document.querySelector(".popup_cropper-canvas_two"),
        imageContainer: document.querySelector('#imageContainerTwo img'),
        generateButton: document.querySelector('.generate_image_two'),
        cutButton: document.querySelector('.popup_cropper_two #cutTwo'),
        cleanButton: document.querySelector('.popup_cropper_two #cleanTwo'),
        deleteButton: document.querySelector('.delete_preview[data-id="2"]'),
        playerId: datos['jugador_rival_id'],
        type: "rival"
    });
})();