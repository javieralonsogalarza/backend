// Este script se encarga de verificar y mostrar íconos de ganador faltantes
document.addEventListener("DOMContentLoaded", function() {
    // Función para verificar si un jugador es el ganador
    function esGanador(ganadorId, jugadorId) {
        if (!ganadorId || !jugadorId) return false;
        return String(ganadorId) === String(jugadorId);
    }

    // Procesar todas las tablas de partidos
    const tablaPartidos = document.querySelectorAll('.table-game');
    tablaPartidos.forEach(tabla => {
        // Obtener datos del partido
        const partidoId = tabla.getAttribute('data-id');
        if (!partidoId) return;
        
        // Buscar el elemento que muestra el resultado
        const resultado = tabla.nextElementSibling?.querySelector('a');
        if (!resultado) return;
        
        // Si existe un resultado, verificar si debemos mostrar el ícono de ganador
        const filas = tabla.querySelectorAll('tr');
        if (filas.length >= 2) {
            const filaLocal = filas[0];
            const filaRival = filas[1];

            // Verificar si ya tienen íconos de ganador
            const tieneIconoLocal = filaLocal.querySelector('.fa-check');
            const tieneIconoRival = filaRival.querySelector('.fa-check');

            // Si hay un resultado pero no hay íconos de ganador, revisa vía API
            if (resultado.innerText.trim() !== '' && !(tieneIconoLocal || tieneIconoRival)) {
                // Obtener datos del partido vía API
                fetch('/auth/torneo/partido/export/json?id=' + partidoId)
                    .then(response => response.json())
                    .then(data => {
                        // Verificar quién es el ganador
                        if (data && data.jugador_ganador_uno_id) {
                            if (esGanador(data.jugador_ganador_uno_id, data.jugador_local_uno_id)) {
                                // Agregar ícono para local
                                if (!tieneIconoLocal) {
                                    const tdLocal = filaLocal.querySelector('td');
                                    const icono = document.createElement('i');
                                    icono.className = 'fas fa-check text-success';
                                    icono.style.marginLeft = '9px';
                                    tdLocal.appendChild(icono);
                                }
                            } else if (esGanador(data.jugador_ganador_uno_id, data.jugador_rival_uno_id)) {
                                // Agregar ícono para rival
                                if (!tieneIconoRival) {
                                    const tdRival = filaRival.querySelector('td');
                                    const icono = document.createElement('i');
                                    icono.className = 'fas fa-check text-success';
                                    icono.style.marginLeft = '9px';
                                    tdRival.appendChild(icono);
                                }
                            }
                        }
                    })
                    .catch(error => console.error('Error al obtener datos del partido:', error));
            }
        }
    });
});
