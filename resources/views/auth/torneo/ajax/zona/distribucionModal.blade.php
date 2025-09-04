<!-- Modal para mostrar la distribución de jugadores por zonas -->
<div class="modal fade" id="zonasDistribucionModal" tabindex="-1" role="dialog" aria-labelledby="zonasDistribucionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="zonasDistribucionModalLabel">Distribución de Jugadores por Zonas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tablaDistribucionZonas" class="table table-bordered table-striped">
                        <thead id="headerZonas">
                            <!-- Las cabeceras de zona se cargarán aquí dinámicamente -->
                            <tr>
                                <th class="text-center">Cargando zonas...</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDistribucionZonas">
                            <!-- Los jugadores se cargarán aquí dinámicamente -->
                            <tr>
                                <td class="text-center">Cargando datos...</td>
                            </tr>
                        </tbody>
                        <tfoot id="footerZonas">
                            <!-- Los totales se cargarán aquí -->
                            <tr>
                                <th class="text-center">Cargando totales...</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para controlar cómo se muestran los nombres largos */
#tablaDistribucionZonas td {
    white-space: nowrap;       /* Evita que el texto se divida en múltiples líneas */
    overflow: hidden;          /* Oculta el texto que exceda el ancho de la celda */
    text-overflow: ellipsis;   /* Muestra puntos suspensivos para texto truncado */
    max-width: 150px;          /* Ancho máximo para cada celda */
    height: 30px;              /* Altura fija para las celdas */
}

#tablaDistribucionZonas tbody td:hover {
    overflow: visible;         /* Al pasar el mouse, muestra el texto completo */
    white-space: normal;       /* Al pasar el mouse, permite que el texto ocupe varias líneas si es necesario */
    position: relative;        /* Para posicionar el texto sobre otras celdas si es necesario */
    z-index: 1;               /* Asegura que el texto aparezca sobre otros elementos */
    background-color: #f8f9fa; /* Fondo para el texto visible al hacer hover */
}

/* Clase personalizada para un gris más oscuro y notorio */
.bg-gray-darker {
    background-color: #d9d9d9 !important; /* Gris más oscuro y notorio */
}

#tablaDistribucionZonas thead th {
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

#tablaDistribucionZonas tfoot th {
    text-align: center;
    white-space: nowrap;
}
</style>

<script>
function cargarDistribucionZonas(categoriaId) {
    // Mostrar estado de carga
    $("#headerZonas").html('<tr><th class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando zonas...</th></tr>');
    $("#tbodyDistribucionZonas").html('<tr><td class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando datos...</td></tr>');
    $("#footerZonas").html('<tr><th class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando totales...</th></tr>');
    
    // Realizar solicitud AJAX para obtener la distribución de jugadores por zonas
    $.ajax({
        url: `/auth/torneo/zonas-distribucion/${categoriaId}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log("Respuesta recibida:", response);
            
            if ((response.Success || response.success) && (response.JugadoresPorZona || response.data)) {
                // Determinar la fuente de datos correcta
                let jugadoresPorZona = response.JugadoresPorZona || response.data;
                // Obtener la configuración max_players del torneo, por defecto 4 si no está definido
                let maxPlayersGroup = response.max_players || 4;
                
                // Verificar si hay datos
                if (!jugadoresPorZona || jugadoresPorZona.length === 0) {
                    $("#headerZonas").html('<tr><th class="text-center">No hay zonas disponibles</th></tr>');
                    $("#tbodyDistribucionZonas").html('<tr><td class="text-center">No hay datos disponibles</td></tr>');
                    $("#footerZonas").html('<tr><th class="text-center">Total: 0</th></tr>');
                    return;
                }
                
                // Paso 1: Crear un mapa de zonas y ordenar los jugadores alfabéticamente
                let zonasMap = {};
                let maxJugadores = 0;
                
                jugadoresPorZona.forEach(function(zonaData) {
                    if (zonaData.zona && zonaData.jugadores) {
                        const zonaId = zonaData.zona.id;
                        const zonaNombre = zonaData.zona.nombre;
                        const jugadores = zonaData.jugadores;
                        
                        // Ordenar jugadores alfabéticamente
                        jugadores.sort(function(a, b) {
                            const nombreA = a.jugador_simple ? (a.jugador_simple.nombres + ' ' + a.jugador_simple.apellidos) : '';
                            const nombreB = b.jugador_simple ? (b.jugador_simple.nombres + ' ' + b.jugador_simple.apellidos) : '';
                            return nombreA.localeCompare(nombreB);
                        });
                        
                        zonasMap[zonaId] = {
                            nombre: zonaNombre,
                            jugadores: jugadores
                        };
                        
                        // Actualizar el número máximo de jugadores
                        if (jugadores.length > maxJugadores) {
                            maxJugadores = jugadores.length;
                        }
                    }
                });
                
                // Paso 2: Crear las cabeceras de la tabla
                let headerHTML = '<tr>';
                Object.values(zonasMap).forEach(function(zona) {
                    headerHTML += `<th class="text-center" title="${zona.nombre}">${zona.nombre}</th>`;
                });
                headerHTML += '</tr>';
                $("#headerZonas").html(headerHTML);                  // Paso 3: Crear las filas de jugadores
                let tbodyHTML = '';
                for (let i = 0; i < maxJugadores; i++) {                // Determinar el grupo de colores actual (cada maxPlayersGroup filas)
                    const groupIndex = Math.floor(i / maxPlayersGroup);
                    // Determinar si este grupo es par o impar para aplicar el estilo correspondiente
                    const isGrayGroup = groupIndex % 2 === 0;
                    // Clase CSS para filas grises o blancas según el grupo
                    const rowClass = isGrayGroup ? 'bg-gray-darker' : 'bg-white'; // bg-gray-darker para gris más oscuro, bg-white para blanco
                    
                    tbodyHTML += `<tr class="${rowClass}">`;
                    
                    Object.values(zonasMap).forEach(function(zona) {
                        if (i < zona.jugadores.length) {
                            const jugador = zona.jugadores[i];
                            const nombreJugador = jugador.jugador_simple ? 
                                (jugador.jugador_simple.nombres + ' ' + jugador.jugador_simple.apellidos) : 
                                'Jugador sin nombre';
                            // Añadido el atributo title para mostrar el nombre completo en tooltip
                            tbodyHTML += `<td class="text-center" title="${nombreJugador}">${nombreJugador}</td>`;
                        } else {
                            tbodyHTML += '<td></td>'; // Celda vacía si no hay jugador
                        }
                    });
                    
                    tbodyHTML += '</tr>';
                }
                $("#tbodyDistribucionZonas").html(tbodyHTML);
                
                // Paso 4: Crear la fila de totales
                let footerHTML = '<tr>';
                Object.values(zonasMap).forEach(function(zona) {
                    footerHTML += `<th class="text-center">Total: ${zona.jugadores.length}</th>`;
                });
                footerHTML += '</tr>';
                $("#footerZonas").html(footerHTML);
                
            } else {
                $("#headerZonas").html('<tr><th class="text-center">Error</th></tr>');
                $("#tbodyDistribucionZonas").html('<tr><td class="text-center">No se pudieron cargar los datos</td></tr>');
                $("#footerZonas").html('<tr><th class="text-center">Total: 0</th></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error AJAX:", error, xhr.responseText);
            $("#headerZonas").html('<tr><th class="text-center">Error</th></tr>');
            $("#tbodyDistribucionZonas").html('<tr><td class="text-center text-danger">Error al cargar los datos: ' + error + '</td></tr>');
            $("#footerZonas").html('<tr><th class="text-center">Total: 0</th></tr>');
        }
    });
}
</script>