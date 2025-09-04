document.addEventListener('DOMContentLoaded', (event) => {
    
    let button = document.querySelector('#generate'),
        canvasFull = document.querySelector(".canvas-full");

    function downloadImage(data, filename) {
        let a = document.createElement('a');
        a.href = data;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        if(document.body.classList.contains('loading')){
            document.body.classList.remove('loading')
        }
    }
function sanitize(text) {
    if (!text) return 'SinCategoria';
    return text.replace(/[^A-Za-z0-9]/g, '');
}
    function createCanvasAndDownload(canvas, startX, width, height, targetWidth, targetHeight, filename) {
        let newCanvas = document.createElement('canvas');
        newCanvas.width = targetWidth;
        newCanvas.height = targetHeight;
        let ctx = newCanvas.getContext('2d');
        ctx.drawImage(canvas, startX, 0, width, height, 0, 0, targetWidth, targetHeight);
        let dataURL = newCanvas.toDataURL('image/png');
           filename = "Modulo_3_";
    if (datos.jugadores && Array.isArray(datos.jugadores) && datos.jugadores.length > 0) {
        filename += `${sanitize(datos.categoria)}_EntryList.png`;
    } else if (datos.grupos && Array.isArray(datos.grupos) && datos.grupos.length > 0) {
        filename += `${sanitize(datos.categoria)}_Grupos.png`;
    } else {
        filename += `${sanitize(datos.categoria)}_Desconocido.png`;
    }
        downloadImage(dataURL, filename);
    }

    if (canvasFull) {
        if (button) {
            button.addEventListener('click', (e) => {
                e.preventDefault();

                document.body.classList.add('loading');

                setTimeout(() => {
                    html2canvas(canvasFull, {
                        scale: 1
                    }).then(canvas => {
                        let width = canvas.width;
                        let height = canvas.height;
                        let targetWidth = 1440;
                        let targetHeight = 1440;
                        createCanvasAndDownload(canvas, 0, width, height, targetWidth, targetHeight, 'imagen.png');
                    });
                }, 600);

            });
        }
    }
});