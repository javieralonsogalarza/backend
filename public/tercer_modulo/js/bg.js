(function(){

    const diagram = document.querySelector('.canvas'),
        fileInput = document.getElementById('imageUpload'),
        heroViewForBg = document.querySelector('.view_hero-input');

    let defaultImageUrl = 'images/bg/1.jpg';

    fileInput.addEventListener('change', function(event) {
        
        document.body.classList.add('loading');
        
        setTimeout(() => {
            
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageUrl = e.target.result;
    
                    if(diagram.classList.contains('bg1')){
                        diagram.classList.remove('bg1');
                    }
                    if(diagram.classList.contains('bg2')){
                        diagram.classList.remove('bg2');
                    }
                    if(diagram.classList.contains('bg3')){
                        diagram.classList.remove('bg3');
                    }
                    if(diagram.classList.contains('bg4')){
                        diagram.classList.remove('bg4');
                    }
                    if(diagram.classList.contains('bg5')){
                        diagram.classList.remove('bg5');
                    }
                    if(diagram.classList.contains('bg6')){
                        diagram.classList.remove('bg6');
                    }
                    if(diagram.classList.contains('bg7')){
                        diagram.classList.remove('bg7');
                    }
                    if(diagram.classList.contains('bg8')){
                        diagram.classList.remove('bg8');
                    }
    
                    diagram.style.background = `url(${imageUrl})`;
    
                    heroViewForBg.style.background = `url(${imageUrl})`;
                    heroViewForBg.classList.add('with_preview');

                    if(document.body.classList.contains('loading')){
                        document.body.classList.remove('loading')
                    }
                }
                reader.readAsDataURL(file);
            } else {
                resetToDefaultImage();
            }
            
        }, 600);

    });
    
    document.querySelector('.delete_preview').addEventListener('click', function(){
        resetToDefaultImage();
    })

    function obtenerNumeroAleatorio() {
        return Math.floor(Math.random() * 8) + 1;
    }

    function resetToDefaultImage(){
        diagram.style.background = ``;
        heroViewForBg.style.background = ``;

        if(document.body.classList.contains('loading')){
            document.body.classList.remove('loading')
        }

        diagram.classList.add(`bg${obtenerNumeroAleatorio()}`);       
            
        if(heroViewForBg.classList.contains('with_preview')){
            heroViewForBg.classList.remove('with_preview');
        }
    }


}())