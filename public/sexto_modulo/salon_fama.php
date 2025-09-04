<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>SALÓN DE LA FAMA</title>
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


    $validation = 0;

    function numeroAleatorio() {
        return rand(1, 8);
    }
    ?>

    <div class="view_hero">
        <div class="view_hero-grid">
            <div class="wh-100vh view_hero-info">
                <h1>SALÓN DE LA FAMA</h1>
                <div class="view_hero-test w-100">
                    <ul>

                        <?php if (isset($datos['jugadores']) && !empty($datos['jugadores'])): $validation++; ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                </svg>
                                <p>Se cargaron <?php echo count($datos['jugadores']); ?> jugadores</p>
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
    <div class="canvas_scroll w-100 <?php if(isset($datos['multiple']) && $datos['multiple'] == true){echo 'is_multiple';} ?>">
        <div class="canvas canvas-full bg<?php echo numeroAleatorio(); ?>">
            <img src="images/topbar.png" class="canvas_topbar">
            <img src="images/flag.png" class="canvas_flag">
            <div class="canvas_head">
                <div>
                    <h1>SALÓN DE LA FAMA</h1>
                </div>
                <div>
                    <h1>Ranking</h1>
                </div>
            </div>
            <div class="canvas_body">
                <div class="wh-100 canvas_body-title">
                    <h3>Categor&iacute;a: <?php echo $datos['categoria_name']; ?></h3>
                </div>
                
                <?php if (!empty($datos['jugadores'])): ?>
                    <?php
                    // Determinar número de columnas
                    $totalJugadores = count($datos['jugadores']);
                    $cols = 2;
                    $class = 'separado';
                    
                    if ($totalJugadores < 10) {
                        $cols = 2;
                    } elseif ($totalJugadores < 37) {
                        $cols = 3;
                    } else {
                        $cols = 4;
                    }

                    // Ajustar clase de espaciado
                    if ($totalJugadores > 30) {
                        $class = 'pegado';
                    }
                    if ($totalJugadores > 56) {
                        $class = 'muy_pegado';
                    }

                    // Calcular jugadores por columna
                    $jugadoresPorColumna = ceil($totalJugadores / $cols);
                    ?>
                    <div class="top-ten-container">
                        <?php 
                        // Iterar sobre las columnas
                        for ($columna = 0; $columna < $cols; $columna++): 
                        ?>
                            <div class="top-ten-column">
                                <?php 
                                // Calcular rango de jugadores para esta columna
                                $inicio = $columna * $jugadoresPorColumna;
                                $fin = min($inicio + $jugadoresPorColumna, $totalJugadores);

                                // Iterar sobre los jugadores de esta columna
                                for ($i = $inicio; $i < $fin; $i++): 
                                    $jugador = $datos['jugadores'][$i];
                                    $foto = !empty($jugador['foto']) && $jugador['foto'] != '/storage/uploads/img/default_player.png' 
                                        ? $jugador['foto'] 
                                        : 'images/incognito.png';
                                ?>
                                    <div class="top-ten-player">
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
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    .top-ten-container {
        display: flex;
        justify-content: space-between;
    }
    .top-ten-column {
        width: calc(100% / <?php echo $cols; ?> - 10px);
    }
</style>

    <script src="js/html2canvas.min.js"></script>
    <script src="js/bg.js"></script>
    <script src="js/main.js"></script>
<script>
    const datos = <?php echo json_encode($datos); ?>;
</script>

</body>

</html>