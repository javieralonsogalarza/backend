(function(){

    const diagramForBg = document.querySelector('.general_canvas'), 
        inputBg = document.querySelector('#putBg');





    function obtenerNumeroAleatorio() {
        return Math.floor(Math.random() * 6) + 1;
    }

    function resetToDefaultImage(){
        diagramForBg.style.background = ``;
        diagramForBg.classList.add(`bg${obtenerNumeroAleatorio()}`);
    }


}());