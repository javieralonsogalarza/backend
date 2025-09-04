<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title><?php 
        // Cambiar título basado en la variable 'all'
        if (isset($datos['all']) && $datos['all'] === "true") {
            echo "Jugadores";
        } else {
            echo "Top 10 Jugadores";
        }
    ?></title>
    <style>

/* Estilo mejorado para resaltar el jugador filtrado */
.top-ten-player.highlighted {
    /* Gradiente más sutil que combina con el azul del sistema */
    background: linear-gradient(135deg, rgba(0, 185, 242, 0.3), rgba(0, 185, 242, 0.6));
    border: 3px solid #00b9f2;
    box-shadow: 0 0 25px rgba(0, 185, 242, 0.4);
    transform: scale(1.02);
    position: relative;
    /* Animación sutil para llamar la atención */
    animation: subtleGlow 2s ease-in-out infinite alternate;
}

.top-ten-player.highlighted::before {
    content: "★";
    position: absolute;
    top: -12px;
    right: -12px;
    background: #00b9f2;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: bold;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0, 185, 242, 0.3);
}

.top-ten-player.highlighted .player-name {
    font-weight: bold;
    color: #ffffff;
    text-shadow: 2px 2px 4px rgba(0, 185, 242, 0.5);
}

.top-ten-player.highlighted .player-points {
    font-weight: bold;
    color: #ffffff;
    opacity: 0.9;
    text-shadow: 1px 1px 3px rgba(0, 185, 242, 0.4);
}

.top-ten-player.highlighted .player-position {
    color: #ffffff;
    text-shadow: 3px 4px 6px rgba(0, 185, 242, 0.6);
}

.top-ten-player.highlighted .player-photo img {
    border: 8px solid #00b9f2;
    box-shadow: 0 0 25px rgba(0, 185, 242, 0.4);
}

/* Animación sutil para el efecto de brillo */
@keyframes subtleGlow {
    0% {
        box-shadow: 0 0 25px rgba(0, 185, 242, 0.4);
    }
    100% {
        box-shadow: 0 0 35px rgba(0, 185, 242, 0.6);
    }
}

/* Variante alternativa más elegante con tonos más oscuros */
.top-ten-player.highlighted.dark-theme {
    background: linear-gradient(135deg, rgba(30, 30, 30, 0.8), rgba(60, 60, 60, 0.9));
    border: 3px solid #666;
    box-shadow: 0 0 25px rgba(255, 255, 255, 0.2);
}

.top-ten-player.highlighted.dark-theme::before {
    background: #666;
    box-shadow: 0 2px 8px rgba(255, 255, 255, 0.2);
}

.top-ten-player.highlighted.dark-theme .player-name,
.top-ten-player.highlighted.dark-theme .player-points,
.top-ten-player.highlighted.dark-theme .player-position {
    color: #ffffff;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.top-ten-player.highlighted.dark-theme .player-photo img {
    border: 8px solid #666;
    box-shadow: 0 0 25px rgba(255, 255, 255, 0.2);
}

/* Variante con acento verde más sutil */
.top-ten-player.highlighted.green-theme {
    background: linear-gradient(135deg, rgba(46, 204, 113, 0.2), rgba(46, 204, 113, 0.4));
    border: 3px solid #2ecc71;
    box-shadow: 0 0 25px rgba(46, 204, 113, 0.3);
}

.top-ten-player.highlighted.green-theme::before {
    background: #2ecc71;
    box-shadow: 0 2px 8px rgba(46, 204, 113, 0.3);
}

.top-ten-player.highlighted.green-theme .player-name,
.top-ten-player.highlighted.green-theme .player-points {
    color: #ffffff;
    text-shadow: 2px 2px 4px rgba(46, 204, 113, 0.4);
}

.top-ten-player.highlighted.green-theme .player-position {
    color: #ffffff;
    text-shadow: 3px 4px 6px rgba(46, 204, 113, 0.5);
}

.top-ten-player.highlighted.green-theme .player-photo img {
    border: 8px solid #2ecc71;
    box-shadow: 0 0 25px rgba(46, 204, 113, 0.3);
}
    </style>
</head>
<body>

    <div class="loader_fixed">
        <span class="loader"></span>
    </div>

    <?php
    // Leer el archivo JSON desde el archivo .txt
    $rutaArchivo = $_GET['json'] ?? './example/players/6.json';
    $jsonData = file_get_contents($rutaArchivo);
    $datos = json_decode($jsonData, true);
    
    $datos = $datos[0];

    // Obtener el ID del jugador a filtrar desde los datos JSON
    $filterJugadorId = isset($datos['filter_jugador']) ? $datos['filter_jugador'] : null;

    $validation = 0;

    function numeroAleatorio() {
        return rand(1, 8);
    }

    // Función para encontrar la página donde está el jugador
    function encontrarPaginaJugador($jugadores, $jugadorId, $jugadoresPorPagina = 10) {
        foreach ($jugadores as $index => $jugador) {
            if ($jugador['id'] == $jugadorId) {
                return floor($index / $jugadoresPorPagina);
            }
        }
        return -1; // No encontrado
    }
    ?>

    <div class="view_hero">
        <div class="view_hero-grid">
            <div class="wh-100vh view_hero-info">
                <h1><?php 
                    // Cambiar título basado en la variable 'all'
                    if (isset($datos['all']) && $datos['all'] === "true") {
                        echo "Jugadores";
                    } else {
                        echo "Top 10 Jugadores";
                    }
                ?></h1>
                <div class="view_hero-test w-100">
                    <ul>

                        <?php if (isset($datos['jugadores']) && !empty($datos['jugadores'])): $validation++; ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                </svg>

<p>Se cargaron <?php 
    if ($filterJugadorId) {
        echo "1 jugador (filtrado)";
    } else {
        // Si es torneo de maestros, filtrar jugadores con 0 puntos
        if (isset($datos['maestros']) && $datos['maestros'] === "true") {
            $jugadoresConPuntos = array_filter($datos['jugadores'], function($jugador) {
                return $jugador['puntos'] > 0;
            });
            echo count($jugadoresConPuntos) . " jugadores";
        } else {
            echo count($datos['jugadores']) . " jugadores";
        }
    }
?></p>
                            </li>
                        <?php else: ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                </svg>
                                <p>No existen jugadores en el JSON.</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <?php if ($validation == 1): ?>
                    <div class="w-100 view_hero-input">
                        <label for="imageUpload" class="labelFile">
                            <span>
                                <svg xml:space="preserve" viewBox="0 0 184.69 184.69" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Capa_1" version="1.1" width="60px" height="60px">
                                    <g>
                                        <g>
                                            <g>
                                                <path d="M149.968,50.186c-8.017-14.308-23.796-22.515-40.717-19.813
				C102.609,16.43,88.713,7.576,73.087,7.576c-22.117,0-40.112,17.994-40.112,40.115c0,0.913,0.036,1.854,0.118,2.834
				C14.004,54.875,0,72.11,0,91.959c0,23.456,19.082,42.535,42.538,42.535h33.623v-7.025H42.538
				c-19.583,0-35.509-15.929-35.509-35.509c0-17.526,13.084-32.621,30.442-35.105c0.931-0.132,1.768-0.633,2.326-1.392
				c0.555-0.755,0.795-1.704,0.644-2.63c-0.297-1.904-0.447-3.582-0.447-5.139c0-18.249,14.852-33.094,33.094-33.094
				c13.703,0,25.789,8.26,30.803,21.04c0.63,1.621,2.351,2.534,4.058,2.14c15.425-3.568,29.919,3.883,36.604,17.168
				c0.508,1.027,1.503,1.736,2.641,1.897c17.368,2.473,30.481,17.569,30.481,35.112c0,19.58-15.937,35.509-35.52,35.509H97.391
				v7.025h44.761c23.459,0,42.538-19.079,42.538-42.535C184.69,71.545,169.884,53.901,149.968,50.186z" style="fill:#010002;"></path>
                                            </g>
                                            <g>
                                                <path d="M108.586,90.201c1.406-1.403,1.406-3.672,0-5.075L88.541,65.078
				c-0.701-0.698-1.614-1.045-2.534-1.045l-0.064,0.011c-0.018,0-0.036-0.011-0.054-0.011c-0.931,0-1.85,0.361-2.534,1.045
				L63.31,85.127c-1.403,1.403-1.403,3.672,0,5.075c1.403,1.406,3.672,1.406,5.075,0L82.296,76.29v97.227
				c0,1.99,1.603,3.597,3.593,3.597c1.979,0,3.59-1.607,3.59-3.597V76.165l14.033,14.036
				C104.91,91.608,107.183,91.608,108.586,90.201z" style="fill:#010002;"></path>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                            </span>
                            <p>Arrastre y suelte su imagen de fondo <small style="display:block">(min. 1900x1900)</small></p>
                        </label>
                        <input class="input" type="file" id="imageUpload" accept=".jpg, .jpeg, .png">
                        <button class="delete_preview" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368"></path>
                            </svg>
                        </button>
                    </div>
                    <button id="generate">Generar</button>
                <?php endif; ?>

            </div>
            <div class="wh-100vh view_hero-image">
                <img src="images/bg_mod3_jugadores.jpg">
            </div>
        </div>
    </div>

<?php if (isset($datos) && !empty($datos)): ?>
    <?php
    // Determinar si es modo "all" para paginación
    $esAll = isset($datos['all']) && $datos['all'] === "true";
    
    if ($esAll) {
        // Modo paginación - 10 jugadores por página
        $jugadoresPorPagina = 10;
        $jugadores = $datos['jugadores'];
        
        // Filtrar jugadores con 0 puntos si es torneo de maestros
        if (isset($datos['maestros']) && $datos['maestros'] === "true") {
            $jugadores = array_filter($jugadores, function($jugador) {
                return $jugador['puntos'] > 0;
            });
        }
        
        $totalJugadores = count($jugadores);
        $gruposDeJugadores = array_chunk($jugadores, $jugadoresPorPagina);
        $fondoAleatorio = numeroAleatorio();
        
        // Si hay un filtro de jugador, encontrar solo la página correspondiente
        if ($filterJugadorId) {
            $paginaJugador = encontrarPaginaJugador($jugadores, $filterJugadorId, $jugadoresPorPagina);
            if ($paginaJugador >= 0 && isset($gruposDeJugadores[$paginaJugador])) {
                // Solo mostrar la página donde está el jugador
                $gruposDeJugadores = [$gruposDeJugadores[$paginaJugador]];
                $indiceGrupoReal = $paginaJugador;
            } else {
                // Jugador no encontrado, mostrar mensaje de error
                echo "<div class='error-message'>Jugador con ID {$filterJugadorId} no encontrado.</div>";
                $gruposDeJugadores = [];
            }
        }
        
        foreach ($gruposDeJugadores as $indiceGrupo => $grupoJugadores):
            $datosGrupo = $datos;
            $datosGrupo['jugadores'] = $grupoJugadores;
            $datosGrupo['multiple'] = count($gruposDeJugadores) > 1;
            
            // Si hay filtro, usar el índice real de la página
            if ($filterJugadorId && isset($indiceGrupoReal)) {
                $datosGrupo['paginaActual'] = $indiceGrupoReal + 1;
                $datosGrupo['totalPaginas'] = count(array_chunk($jugadores, $jugadoresPorPagina));
            } else {
                $datosGrupo['paginaActual'] = $indiceGrupo + 1;
                $datosGrupo['totalPaginas'] = count($gruposDeJugadores);
            }
    ?>
        <div class="canvas_scroll w-100 <?php echo $datosGrupo['multiple'] ? 'is_multiple' : ''; ?>">
            <div class="canvas canvas-full bg<?php echo $fondoAleatorio; ?>">
                <img src="images/topbar.png" class="canvas_topbar">
                <img src="images/flag.png" class="canvas_flag">
                <div class="canvas_head">
                    <div>
                        <h1>Jugadores</h1>
                    </div>
                    <div>
                        <h2>
                            <?php if (isset($datosGrupo['maestros']) && $datosGrupo['maestros'] === "true"): ?>
                                Carrera hacia el torneo de maestros
                            <?php else: ?>
                                <?php echo $datosGrupo['tournament_name']; ?>
                            <?php endif; ?>
                        </h2>
                    </div>
                </div>
                <div class="canvas_body">
                    <div class="wh-100 canvas_body-title">
                        <h3>Categor&iacute;a: <?php echo $datosGrupo['categoria_name']; ?></h3>
                        <?php if ($datosGrupo['multiple'] || $filterJugadorId): ?>
                            <h4>P&aacute;gina <?php echo $datosGrupo['paginaActual']; ?> de <?php echo $datosGrupo['totalPaginas']; ?></h4>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($datosGrupo['jugadores'])): ?>
                        <div class="top-ten-container">
                            <div class="top-ten-column">
                                <?php 
                                // Primeros 5 jugadores
                                for ($i = 0; $i < min(5, count($grupoJugadores)); $i++): 
                                    $jugador = $grupoJugadores[$i];
                                    $foto = !empty($jugador['foto']) && $jugador['foto'] != '/storage/uploads/img/default_player.png' 
                                        ? $jugador['foto'] 
                                        : 'images/incognito.png';
                                    
                                    // Verificar si este jugador debe ser resaltado
                                    $esJugadorResaltado = $filterJugadorId && $jugador['id'] == $filterJugadorId;
                                    $claseResaltado = $esJugadorResaltado ? 'highlighted' : '';
                                ?>
                                    <div class="top-ten-player <?php echo $claseResaltado; ?>">
                                        <div class="player-position"><i><?php echo $jugador['posicion']; ?></i></div>
                                        <div class="player-details">
                                            <div class="player-name"><?php echo $jugador['nombre']; ?></div>
                                            <div class="player-points"><?php echo $jugador['puntos']; ?> pts</div>
                                        </div>
                                        <div class="player-photo">
                                            <img src="<?php echo $foto; ?>" alt="Foto de <?php echo $jugador['nombre']; ?>">
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <div class="top-ten-column">
                                <?php 
                                // Siguientes 5 jugadores
                                for ($i = 5; $i < min(10, count($grupoJugadores)); $i++): 
                                    $jugador = $grupoJugadores[$i];
                                    $foto = !empty($jugador['foto']) && $jugador['foto'] != '/storage/uploads/img/default_player.png' 
                                        ? $jugador['foto'] 
                                        : 'images/incognito.png';
                                    
                                    // Verificar si este jugador debe ser resaltado
                                    $esJugadorResaltado = $filterJugadorId && $jugador['id'] == $filterJugadorId;
                                    $claseResaltado = $esJugadorResaltado ? 'highlighted' : '';
                                ?>
                                    <div class="top-ten-player <?php echo $claseResaltado; ?>">
                                        <div class="player-position"><i><?php echo $jugador['posicion']; ?></i></div>
                                        <div class="player-details">
                                            <div class="player-name"><?php echo $jugador['nombre']; ?></div>
                                            <div class="player-points"><?php echo $jugador['puntos']; ?> pts</div>
                                        </div>
                                        <div class="player-photo">
                                            <img src="<?php echo $foto; ?>" alt="Foto de <?php echo $jugador['nombre']; ?>">
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php 
        endforeach;
    } else {
        // Modo original - Top 10
    ?>
        <div class="canvas_scroll w-100 <?php if(isset($datos['multiple']) && $datos['multiple'] == true){echo 'is_multiple';} ?>">
            <div class="canvas canvas-full bg<?php echo numeroAleatorio(); ?>">
                <img src="images/topbar.png" class="canvas_topbar">
                <img src="images/flag.png" class="canvas_flag">
                <div class="canvas_head">
                    <div>
                        <h1>TOP TEN</h1>
                    </div>
                    <div>
                        <h2>
                            <?php if (isset($datos['maestros']) && $datos['maestros'] === "true"): ?>
                                Carrera hacia el torneo de maestros
                            <?php else: ?>
                                <?php echo $datos['tournament_name']; ?>
                            <?php endif; ?>
                        </h2>
                    </div>
                </div>
                <div class="canvas_body">
                    <div class="wh-100 canvas_body-title">
                        <h3>Categor&iacute;a: <?php echo $datos['categoria_name']; ?></h3>
                    </div>
                    
                    <?php if (!empty($datos['jugadores'])): ?>
                        <div class="top-ten-container">
                            <div class="top-ten-column">
                                <?php 
                                // Determinar si es modo carrera
                                $esCarrera = isset($datos['maestros']) && $datos['maestros'] === "true";
                                
                                // Preparar jugadores
                                $jugadores = $datos['jugadores'];
                                
                                // Primeros 5 jugadores
                                for ($i = 0; $i < min(5, count($jugadores)); $i++): 
                                    $jugador = $jugadores[$i];
                                    $foto = !empty($jugador['foto']) && $jugador['foto'] != '/storage/uploads/img/default_player.png' 
                                        ? $jugador['foto'] 
                                        : 'images/incognito.png';
                                    
                                    // Verificar si este jugador debe ser resaltado
                                    $esJugadorResaltado = $filterJugadorId && $jugador['id'] == $filterJugadorId;
                                    $claseResaltado = $esJugadorResaltado ? 'highlighted' : '';
                                ?>
                                    <div class="top-ten-player <?php echo $claseResaltado; ?>">
                                        <div class="player-position"><i><?php echo $jugador['posicion']; ?></i></div>
                                        <div class="player-details">
                                            <div class="player-name"><?php echo $jugador['nombre']; ?></div>
                                            <div class="player-points"><?php echo $jugador['puntos']; ?> pts</div>
                                        </div>
                                        <div class="player-photo">
                                            <img src="<?php echo $foto; ?>" alt="Foto de <?php echo $jugador['nombre']; ?>">
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <div class="top-ten-column">
                                <?php 
                                // Siguientes 5 jugadores
                                for ($i = 5; $i < min(10, count($jugadores)); $i++): 
                                    $jugador = $jugadores[$i];
                                    $foto = !empty($jugador['foto']) && $jugador['foto'] != '/storage/uploads/img/default_player.png' 
                                        ? $jugador['foto'] 
                                        : 'images/incognito.png';
                                    
                                    // Verificar si este jugador debe ser resaltado
                                    $esJugadorResaltado = $filterJugadorId && $jugador['id'] == $filterJugadorId;
                                    $claseResaltado = $esJugadorResaltado ? 'highlighted' : '';
                                ?>
                                    <div class="top-ten-player <?php echo $claseResaltado; ?>">
                                        <div class="player-position"><i><?php echo $jugador['posicion']; ?></i></div>
                                        <div class="player-details">
                                            <div class="player-name"><?php echo $jugador['nombre']; ?></div>
                                            <div class="player-points"><?php echo $jugador['puntos']; ?> pts</div>
                                        </div>
                                        <div class="player-photo">
                                            <img src="<?php echo $foto; ?>" alt="Foto de <?php echo $jugador['nombre']; ?>">
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php } ?>
<?php endif; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="js/html2canvas.min.js"></script>
    <script src="js/bg.js"></script>
    <script src="js/main.js"></script>

<script>
    const datos = <?php echo json_encode($datos); ?>;
    const filterJugadorId = <?php echo json_encode($filterJugadorId); ?>;
</script>

</body>

</html>