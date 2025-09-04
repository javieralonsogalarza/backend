document.addEventListener('DOMContentLoaded', (event) => {
    
    let diagramIg = document.querySelector(".canvas.ig"),
        diagramFb = document.querySelector(".canvas.fb");

    function downloadImage(data, filename) {
        let a = document.createElement('a');
        a.href = data;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function createCanvasAndDownload(canvas, startX, width, height, targetWidth, targetHeight, filename) {
        let newCanvas = document.createElement('canvas');
        newCanvas.width = targetWidth;
        newCanvas.height = targetHeight;
        let ctx = newCanvas.getContext('2d');
        ctx.drawImage(canvas, startX, 0, width, height, 0, 0, targetWidth, targetHeight);
        let dataURL = newCanvas.toDataURL('image/png');
        filename = `Modulo_5_${sanitize(datos.categoria)}_Reporte_1_4.png`;

        downloadImage(dataURL, filename);
    }
function sanitize(text) {
    if (!text) return 'SinCategoria';
    return text.replace(/[^A-Za-z0-9]/g, '');
}

    let sourceSelectable = document.querySelector('#source'),
        size4Recomendation = document.querySelector('.hero_view-selectable p');

    let generateButton = document.querySelector('#generate_button'),
        btnFileBox = document.querySelector('.toggle');

    sourceSelectable.addEventListener('change', (e)=>{
        
        e.preventDefault();
        size4Recomendation.innerHTML = '';

        let typeImage = e.target.value.trim();

        if(typeImage != ''){
            if(!btnFileBox.classList.contains('active')){
                btnFileBox.classList.add('active');
            }
        }else{
            if(btnFileBox.classList.contains('active')){
                btnFileBox.classList.remove('active');
            }
        }

        if(typeImage == 'igl' || typeImage == 'igr'){
            size4Recomendation.innerHTML = `El tamaño recomendado para la imagen de fondo es <b>5176x3235px</b>`;
        }else if(typeImage == 'fb'){
            size4Recomendation.innerHTML = `El tamaño recomendado para la imagen de fondo es <b>5000x2625px</b>`;
        }

    })

    //-----------------------------------------------------------

    const fileInput = document.getElementById('file'),
        heroViewForBg = document.querySelector('.hero_view-for_bg'),
        defaultImageUrl = 'images/fondo.jpeg';

    fileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageUrl = e.target.result;
                diagramIg.style.backgroundImage = `url(${imageUrl})`;
                diagramFb.style.backgroundImage = `url(${imageUrl})`;
                heroViewForBg.classList.add('filled');
            }
            reader.readAsDataURL(file);
        } else {
            resetToDefaultImage();
        }
    });

    document.querySelector('#deleteBg').addEventListener('click', (e)=>{
        e.preventDefault();
        resetToDefaultImage();
    });

    function resetToDefaultImage() {
        diagramIg.style.backgroundImage = `url(${defaultImageUrl})`;
        diagramFb.style.backgroundImage = `url(${defaultImageUrl})`;
        heroViewForBg.classList.remove('filled');
    }

    // Verifica si el input file tiene una imagen cargada al inicio
    if (!fileInput.files.length) {
        resetToDefaultImage();
    }

    //-----------------------------------------------------------

    if(generateButton){
        generateButton.addEventListener('click', (e)=>{
            e.preventDefault();

            let typeImage = sourceSelectable.value.trim();

            if(typeImage == ''){
                alert('Asegurate de haber seleccionado un formato para descargar la imagen');
                return;
            }

            if(typeImage == 'fb'){
                setTimeout(() => {
                    html2canvas(diagramFb, {
                        scale: 1
                    }).then(canvas => {
                        let width = canvas.width;
                        let height = canvas.height;
                        let targetWidth = 3000;
                        let targetHeight = 1575;
                        createCanvasAndDownload(canvas, 0, width, height, targetWidth, targetHeight, 'facebook.png');
                    });
                }, 100);
            }
            if(typeImage == 'igl'){
                setTimeout(() => {
                    html2canvas(diagramIg, {
                        scale: 1
                    }).then(canvas => {
                        let width = canvas.width;
                        let height = canvas.height;
                        let halfWidth = width / 2;
                        createCanvasAndDownload(canvas, 0, halfWidth, height, 2700, 3375, 'instagram_left.png');
                    });
                }, 100);
            }
            if(typeImage == 'igr'){
                setTimeout(() => {
                    html2canvas(diagramIg, {
                        scale: 1
                    }).then(canvas => {
                        let width = canvas.width;
                        let height = canvas.height;
                        let halfWidth = width / 2;
                        createCanvasAndDownload(canvas, halfWidth, halfWidth, height, 2700, 3375, 'instagram_right.png');
                    });
                }, 100);
            }

        })
    }

});