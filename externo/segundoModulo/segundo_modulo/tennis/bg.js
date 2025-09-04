(function(){

    const diagramForBg = document.querySelector('.general_canvas'), 
        inputBg = document.querySelector('#putBg');

    inputBg.addEventListener('change', function(event){
        const file = event.target.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageUrl = e.target.result;
                console.log(imageUrl)

                if(diagramForBg.classList.contains('bg1')){
                    diagramForBg.classList.remove('bg1');
                }
                if(diagramForBg.classList.contains('bg2')){
                    diagramForBg.classList.remove('bg2');
                }
                if(diagramForBg.classList.contains('bg3')){
                    diagramForBg.classList.remove('bg3');
                }
                if(diagramForBg.classList.contains('bg4')){
                    diagramForBg.classList.remove('bg4');
                }
                if(diagramForBg.classList.contains('bg5')){
                    diagramForBg.classList.remove('bg5');
                }
                if(diagramForBg.classList.contains('bg6')){
                    diagramForBg.classList.remove('bg6');
                }

                diagramForBg.style.background = `url(${imageUrl}) no-repeat`;

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

    })

    document.querySelector(`.delete_preview[data-id="2"]`).addEventListener('click', (e)=>{
        e.preventDefault();

        // preview
        inputBg.parentElement.style.background = ``;
        if(inputBg.parentElement.classList.contains('with_preview')){
            inputBg.parentElement.classList.remove('with_preview');
        }

        resetToDefaultImage();
    })

    function obtenerNumeroAleatorio() {
        return Math.floor(Math.random() * 6) + 1;
    }

    function resetToDefaultImage(){
        diagramForBg.style.background = ``;
        diagramForBg.classList.add(`bg${obtenerNumeroAleatorio()}`);
    }


}());