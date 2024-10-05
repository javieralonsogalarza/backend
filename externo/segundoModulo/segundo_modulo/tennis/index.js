(function () {
	
    let inputFile = document.getElementById("imageUpload"),
		popupCropper = document.querySelector(".popup_cropper"),
		popupCropperCanvas = document.querySelector(".popup_cropper-canvas");

    let image4Photo = document.querySelector('#imageContainer img');

    let cropper;

	if (inputFile) {

		inputFile.addEventListener("change", function (e) {
			document.body.classList.add("loading");

			var file = e.target.files[0];
			var reader = new FileReader();

            image4Photo.src = '';
            document.querySelector('.generate_image').disabled = true;

            setTimeout(() => {

                reader.onload = function (event) {
                    // Crear un nuevo elemento img
                    let img = document.createElement("img");
                    img.id = 'croppy';
                    img.src = event.target.result; // Usar la URL de la imagen cargada
    
                    // Limpiar cualquier imagen previa en el canvas
                    popupCropperCanvas.innerHTML = "";
                    popupCropperCanvas.appendChild(img);
    
                    // Quitar el loading y mostrar el popup
                    document.body.classList.remove("loading");

                    popupCropper.classList.add("active");

                    cropper = new Cropper(img, {
                        aspectRatio: 1.502 / 1.008
                    });

                };
    
                // Leer el archivo como una URL
                reader.readAsDataURL(file);
                
            }, 1000);

            popupCropper.querySelector('#cut').addEventListener('click', (e) => {
                e.preventDefault();

                const canvas = cropper.getCroppedCanvas();
                // console.log("Canvas generado:", canvas);

                var croppedImage = canvas.toDataURL("image/png");
                // console.log("DataURL generado:", croppedImage);

                if (croppedImage) {
                    image4Photo.src = croppedImage;

                    // preview
                    if(!inputFile.parentElement.classList.contains('with_preview')){
                        inputFile.parentElement.classList.add('with_preview');
                    }
                    inputFile.parentElement.style.background = `url(${croppedImage})`;

                    document.querySelector('.generate_image').disabled = false;
                } else {
                    console.error("Error al generar el DataURL.");
                }

                resetCropper();
            });

            popupCropper.querySelector('#clean').addEventListener('click', (e) => {
                e.preventDefault();
                image4Photo.src = '';
                resetCropper();
            });

		});

        document.querySelector(`.delete_preview[data-id="1"]`).addEventListener('click', (e)=>{
            e.preventDefault();
            document.body.classList.add('loading');

            inputFile.parentElement.style.background = '';
            if(inputFile.parentElement.classList.contains('with_preview')){
                inputFile.parentElement.classList.remove('with_preview');
            }

            image4Photo.src = '';

            setTimeout(() => {
                document.querySelector('.generate_image').disabled = true;
                resetCropper();
            }, 1000);
        })

        function resetCropper(){
            if (popupCropper.classList.contains("active")) {
                popupCropper.classList.remove("active");
            }

            document.getElementById("imageUpload").value = '';

            if(document.body.classList.contains('loading')){
                document.body.classList.remove('loading');
            }

            setTimeout(() => {
                popupCropperCanvas.innerHTML = '';
            }, 500);
        }

	}

})();