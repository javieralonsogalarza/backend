document.addEventListener('DOMContentLoaded', (event) => {
    
    let buttonInstagramLeft = document.querySelector('#instagramLeft'),
        buttonInstagramRight = document.querySelector('#instagramRight'),
        buttonFacebook = document.querySelector('#facebook'),
        diagramIg = document.querySelector(".canvas.ig"),
        diagramFb = document.querySelector(".canvas.fb");

    function downloadImage(data, filename) {
        let a = document.createElement('a');
        a.href = data;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
   function getFase(datos) {
    console.log(datos.llaves);

    // Validar las fases en orden (de más a menos importancia)
    if (datos.llaves.final && !isEmpty(datos.llaves.final)) {
        return 'Final';
    } else if (datos.llaves.semifinal && !isEmpty(datos.llaves.semifinal)) {
        return 'Semifinal';
    } else if (datos.llaves.cuartos && !isEmpty(datos.llaves.cuartos)) {
        return 'Cuartos de final';
    } else if (datos.llaves.octavos && !isEmpty(datos.llaves.octavos)) {
        return 'Octavos de final';
    } else if (datos.llaves.ronda32 && !isEmpty(datos.llaves.ronda32)) {
        return '1/16 de final';
    }
    return 'Fase desconocida';
}
function isEmpty(obj) {
    if (!obj) return true;
    if (Array.isArray(obj)) {
        return obj.length === 0;
    }
    if (typeof obj === 'object') {
        return Object.keys(obj).length === 0;
    }
    return true;
}
    function createCanvasAndDownload(canvas, startX, width, height, targetWidth, targetHeight, filename) {
        let newCanvas = document.createElement('canvas');
        newCanvas.width = targetWidth;
        newCanvas.height = targetHeight;
        let ctx = newCanvas.getContext('2d');
        ctx.drawImage(canvas, startX, 0, width, height, 0, 0, targetWidth, targetHeight);
        let dataURL = newCanvas.toDataURL('image/png');
        const baseFilename ="Modulo_1";
        const categoria = datos.categoria;
        const fase = getFase(datos);

        filename = `${baseFilename} - ${categoria} - ${fase}.png`; // Incluye categoría y fase

        downloadImage(dataURL, filename);
    }

    if (diagramIg) {
        if (buttonInstagramLeft) {
            buttonInstagramLeft.addEventListener('click', (e) => {
                e.preventDefault();
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
            });
        }
        if (buttonInstagramRight) {
            buttonInstagramRight.addEventListener('click', (e) => {
                e.preventDefault();
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
            });
        }
    }

    if (diagramFb) {
        if (buttonFacebook) {
            buttonFacebook.addEventListener('click', (e) => {
                e.preventDefault();
                setTimeout(() => {
                    html2canvas(diagramFb, {
                        scale: 1
                    }).then(canvas => {
                        let width = canvas.width;
                        let height = canvas.height;
                        let targetWidth = 3000;
                        let targetHeight = 1575;
                        createCanvasAndDownload(canvas, 0, width, height, targetWidth, targetHeight, 'facebooksss.png');
                    });
                }, 100);
            });
        }
    }
});