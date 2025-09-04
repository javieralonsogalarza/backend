<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Document</title>
</head>

<body>

    <div class="loader_fixed">
        <span class="loader"></span>
    </div>

    <?php
    // Leer el archivo JSON desde el archivo .txt
    $rutaArchivo = $_GET['json'] ?? './example/groups/4.json';
    $jsonData = file_get_contents($rutaArchivo);
    $datos = json_decode($jsonData, true);

    $validation = 0;

    function numeroAleatorio() {
        return rand(1, 8);
    }
    
    function cortarNombreApellido($nombreCompleto, $nombrePila, $maxCaracteres = 16, $formatoEspecial = true) {
    // 1. Extraer el paréntesis con el número
    preg_match('/\s*\(\d+\)/', $nombreCompleto, $parentesisMatch);
    $parentesis = isset($parentesisMatch[0]) ? $parentesisMatch[0] : '';
    
    // 2. Quitar el paréntesis del nombre completo
    $nombreSinParentesis = trim(str_replace($parentesis, '', $nombreCompleto));
    
    // 3. Verificar si el nombre sin paréntesis ya está dentro del límite
    $limiteAjustado = empty($parentesis) ? $maxCaracteres + 3 : $maxCaracteres;
    if (mb_strlen($nombreSinParentesis, 'UTF-8') <= $limiteAjustado) {
        return $nombreCompleto;
    }
    
    // 4. Si se solicita el formato especial (F. Perez de V.)
    if ($formatoEspecial) {
        // Extraer nombre(s) y apellido(s)
        $nombrePilaPartes = explode(' ', $nombrePila);
        
        // Encontrar dónde termina el nombre y comienzan los apellidos
        $posicionApellidos = mb_strpos($nombreSinParentesis, trim(str_replace($nombrePila, '', $nombreSinParentesis)));
        $apellidos = trim(mb_substr($nombreSinParentesis, $posicionApellidos));
        
        // Dividir los apellidos en partes
        $apellidosPartes = explode(' ', $apellidos);
        
        // CAMBIO: Abreviar todos los nombres, no solo el primero
        $nombresAbreviados = [];
        foreach ($nombrePilaPartes as $parte) {
            $nombresAbreviados[] = mb_substr($parte, 0, 1, 'UTF-8') . '.';
        }
        $nombreAbreviado = implode(' ', $nombresAbreviados);
        
        // Identificar preposiciones
        $preposicionesCortas = ['de', 'la', 'del', 'el', 'da', 'di', 'du', 'y', 'e', 'i'];
        
        // Encontrar el primer apellido y posibles preposiciones
        $primerApellido = $apellidosPartes[0];
        $indiceActual = 1;
        
        // Agregar preposiciones si existen
        while ($indiceActual < count($apellidosPartes) && 
               in_array(mb_strtolower($apellidosPartes[$indiceActual], 'UTF-8'), $preposicionesCortas)) {
            $primerApellido .= ' ' . $apellidosPartes[$indiceActual];
            $indiceActual++;
        }
        
        // Abreviar los apellidos restantes
        $apellidosRestantes = [];
        for ($i = $indiceActual; $i < count($apellidosPartes); $i++) {
            $apellidosRestantes[] = mb_substr($apellidosPartes[$i], 0, 1, 'UTF-8') . '.';
        }
        
        // Construir el nombre formateado
        $nombreFormateado = $nombreAbreviado . ' ' . $primerApellido;
        if (!empty($apellidosRestantes)) {
            $nombreFormateado .= ' ' . implode(' ', $apellidosRestantes);
        }
        
        // Verificar si está dentro del límite
        if (mb_strlen($nombreFormateado, 'UTF-8') <= $limiteAjustado) {
            return $nombreFormateado . ($parentesis ? ' ' . $parentesis : '');
        }
        
        // Si excede el límite, recortar el primer apellido
        $espacioRestante = $limiteAjustado - mb_strlen($nombreAbreviado . ' ', 'UTF-8');
        if (!empty($apellidosRestantes)) {
            $espacioRestante -= mb_strlen(' ' . implode(' ', $apellidosRestantes), 'UTF-8');
        }
        
        if ($espacioRestante > 0) {
            $primerApellidoRecortado = mb_substr($primerApellido, 0, $espacioRestante, 'UTF-8');
            $nombreFormateado = $nombreAbreviado . ' ' . $primerApellidoRecortado;
            if (!empty($apellidosRestantes)) {
                $nombreFormateado .= ' ' . implode(' ', $apellidosRestantes);
            }
            return $nombreFormateado . ($parentesis ? ' ' . $parentesis : '');
        }
    }
    
    // 5. Si no se usa el formato especial o si falló, continuar con el método anterior
    // Dividir el nombre en partes
    $partes = explode(' ', $nombreSinParentesis);
    $totalPartes = count($partes);
    
    // Identificar preposiciones y artículos cortos (2-3 caracteres)
    $preposicionesCortas = ['de', 'la', 'del', 'el', 'da', 'di', 'du', 'y', 'e', 'i'];
    
    // Obtener el apellido final (última parte)
    $apellidoFinal = $partes[$totalPartes - 1];
    
    // Procesar las partes anteriores
    $procesadas = [];
    for ($i = 0; $i < $totalPartes - 1; $i++) {
        // Mantener preposiciones cortas tal cual
        if (in_array(mb_strtolower($partes[$i], 'UTF-8'), $preposicionesCortas) && mb_strlen($partes[$i], 'UTF-8') <= 3) {
            $procesadas[] = $partes[$i];
        } else {
            // Convertir a inicial las que no son preposiciones cortas
            $procesadas[] = mb_substr($partes[$i], 0, 1, 'UTF-8') . '.';
        }
    }
    
    $procesadasStr = implode(' ', $procesadas);
    $nombreFormateado = $procesadasStr . ' ' . $apellidoFinal;
    
    // Verificar si el formato actual está dentro del límite ajustado
    if (mb_strlen($nombreFormateado, 'UTF-8') <= $limiteAjustado) {
        return $nombreFormateado . ($parentesis ? ' ' . $parentesis : '');
    }
    
    // Si aún excede, convertir todas las preposiciones a iniciales también
    $iniciales = [];
    for ($i = 0; $i < $totalPartes - 1; $i++) {
        $iniciales[] = mb_substr($partes[$i], 0, 1, 'UTF-8') . '.';
    }
    
    $inicialesStr = implode(' ', $iniciales);
    $nombreFormateado = $inicialesStr . ' ' . $apellidoFinal;
    
    // Verificar de nuevo con el límite ajustado
    if (mb_strlen($nombreFormateado, 'UTF-8') <= $limiteAjustado) {
        return $nombreFormateado . ($parentesis ? ' ' . $parentesis : '');
    }
    
    // Si sigue excediendo, recortar el apellido final
    $espacioDisponible = $limiteAjustado - mb_strlen($inicialesStr . ' ', 'UTF-8');
    if ($espacioDisponible > 0) {
        $apellidoRecortado = mb_substr($apellidoFinal, 0, $espacioDisponible, 'UTF-8');
        return $inicialesStr . ' ' . $apellidoRecortado . ($parentesis ? ' ' . $parentesis : '');
    } else {
        // En caso extremo donde no hay espacio ni para una letra del apellido
        $iniciales = [];
        // Reducir a iniciales sin espacios entre ellas
        for ($i = 0; $i < $totalPartes - 1; $i++) {
            $iniciales[] = mb_substr($partes[$i], 0, 1, 'UTF-8');
        }
        $inicialesSinEspacio = implode('', $iniciales) . '.';
        
        $espacioDisponible = $limiteAjustado - mb_strlen($inicialesSinEspacio . ' ', 'UTF-8');
        $apellidoRecortado = mb_substr($apellidoFinal, 0, $espacioDisponible, 'UTF-8');
        return $inicialesSinEspacio . ' ' . $apellidoRecortado . ($parentesis ? ' ' . $parentesis : '');
    }
}
    ?>

    <div class="view_hero">
        <div class="view_hero-grid">
            <div class="wh-100vh view_hero-info">
                <h1>Tabla de Partidos</h1>
                <div class="view_hero-test w-100">
                    <ul>
                        <?php if (isset($datos['titulo']) && !empty($datos['titulo'])): $validation++; ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                </svg>
                                <p>Título: <?php echo $datos['titulo']; ?>.</p>
                            </li>
                        <?php else: ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                </svg>
                                <p>Error al cargar el título.</p>
                            </li>
                        <?php endif; ?>

                        <?php if (isset($datos['categoria']) && !empty($datos['categoria'])): $validation++; ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                </svg>
                                <p>Se cargó categor&iacute;a: <?php echo $datos['categoria']; ?>.</p>
                            </li>
                        <?php else: ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                </svg>
                                <p>Error al cargar la categor&iacute;a.</p>
                            </li>
                        <?php endif; ?>

                        <?php if (isset($datos['grupo']) && !empty($datos['grupo'])): $validation++; ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                </svg>
                                <p>Se cargo el grupo</p>
                            </li>
                        <?php else: ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                </svg>
                                <p>No existen datos en el JSON.</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <?php if ($validation == 3): ?>
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
                <img src="images/bg_mod3_grupos.jpg">
            </div>
        </div>
    </div>

<?php if (isset($datos) && !empty($datos)): ?>
    <div class="canvas_scroll w-100">
        <div class="canvas canvas-full bg<?php echo numeroAleatorio(); ?>">
            <img src="images/topbar.png" class="canvas_topbar">
            <img src="images/flag.png" class="canvas_flag">
            <div class="canvas_head for_group">
                <div>
                    <h1><?php echo $datos['grupo']['nombre']; ?></h1>
                </div>
                <?php if (isset($datos['torneo']) && !empty($datos['torneo'])): ?>
                    <div>
                        <h2><?php echo $datos['torneo']; ?></h2>
                    </div>
                <?php endif; ?>
            </div>
            <div class="canvas_body for_group">
                <?php if (isset($datos['categoria']) && !empty($datos['categoria'])): ?>
                    <div class="wh-100 canvas_body-title">
                        <h3><?php echo 'Categoría: ' . $datos['categoria']; ?></h3>
                        <?php if (isset($datos['zonas']) && !empty($datos['zonas'])): ?>
                            <div style="clear: both; display: block; margin-top: 10px;">
                                <h6 style="font-weight: 200; font-size: 4.1em; text-shadow: 3px 4px 4px rgba(0, 0, 0, 0.8); color: white;">
                                    <?php echo 'Distritos: ' . $datos['zonas']; ?>
                                </h6>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                 
                <?php if (isset($datos['grupo']['partidos']) && !empty($datos['grupo']['partidos'])): ?>
                    <div class="w-100 relative canvas_body-grid fixture-container">
                        <div class="group">
                            <div class="group_list">
                                <?php foreach ($datos['grupo']['partidos'] as $partido): ?>
                                    <div class="match-card">
                                        <div class="match-footer">
                                            <div class="match-details">
                                                <div class="match-date">
                                                    <?php 
                                                    $meses = [
                                                        'Jan' => 'enero',
                                                        'Feb' => 'febrero',
                                                        'Mar' => 'marzo',
                                                        'Apr' => 'abril',
                                                        'May' => 'mayo',
                                                        'Jun' => 'junio',
                                                        'Jul' => 'julio',
                                                        'Aug' => 'agosto',
                                                        'Sep' => 'septiembre',
                                                        'Oct' => 'octubre',
                                                        'Nov' => 'noviembre',
                                                        'Dec' => 'diciembre'
                                                    ];

                                                    $fechaInicio = date('d', strtotime($partido['fecha_inicio']));
                                                    $mesInicio = $meses[date('M', strtotime($partido['fecha_inicio']))];
                                                    $fechaFin = date('d', strtotime($partido['fecha_final']));
                                                    $mesFin = $meses[date('M', strtotime($partido['fecha_final']))];
                                                    echo "Semana del $fechaInicio de $mesInicio al $fechaFin de $mesFin";
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="match-body">
                                            <div class="match-team match-team-local">
                                                <div class="team-name-container">
                                                    <div class="team-logo">
                                                        <img src="<?php echo $partido['jugadores']['local']['v1']['imagen']; ?>" alt="Local 1">
                                                        <?php if (isset($datos['multiple']) && $datos['multiple'] == 1): ?>
                                                            <img src="<?php echo $partido['jugadores']['local']['v2']['imagen']; ?>" alt="Local 2" class="second-player-img">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="team-name">
                                                        <?php 
                                                        // Check if multiple is 1 to display both players
                                                        if (isset($datos['multiple']) && $datos['multiple'] == 1) {
                                                            $localNombre1 = $partido['jugadores']['local']['v1']['nombre_completo_temporal'];
                                                            $localNombres1 = $partido['jugadores']['local']['v1']['nombres'];
                                                            // Format first player name
                                                            $localNombreFormateado1 = cortarNombreApellido($localNombre1, $localNombres1, 16, true);
                                                            
                                                            $localNombre2 = $partido['jugadores']['local']['v2']['nombre_completo_temporal'];
                                                            $localNombres2 = $partido['jugadores']['local']['v2']['nombres'];
                                                            // Format second player name
                                                            $localNombreFormateado2 = cortarNombreApellido($localNombre2, $localNombres2, 16, true);
                                                            
                                                            echo htmlspecialchars($localNombreFormateado1) . '<br>' . htmlspecialchars($localNombreFormateado2);
                                                        } else {
                                                            $localNombre = $partido['jugadores']['local']['v1']['nombre_completo_temporal'];
                                                            $localNombres = $partido['jugadores']['local']['v1']['nombres'];
                                                            // Format player name
                                                            $localNombreFormateado = cortarNombreApellido($localNombre, $localNombres, 16, true);
                                                            echo htmlspecialchars($localNombreFormateado);
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="match-versus">
                                                <span class="versus-text">VS</span>
                                            </div>
                                            
                                            <div class="match-team match-team-away">
                                                <div class="team-name-container">
                                                    <div class="team-logo">
                                                        <img src="<?php echo $partido['jugadores']['rival']['v1']['imagen']; ?>" alt="Rival 1">
                                                        <?php if (isset($datos['multiple']) && $datos['multiple'] == 1): ?>
                                                            <img src="<?php echo $partido['jugadores']['rival']['v2']['imagen']; ?>" alt="Rival 2" class="second-player-img">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="team-name">
                                                        <?php 
                                                        // Check if multiple is 1 to display both players
                                                        if (isset($datos['multiple']) && $datos['multiple'] == 1) {
                                                            $rivalNombre1 = $partido['jugadores']['rival']['v1']['nombre_completo_temporal'];
                                                            $rivalNombres1 = $partido['jugadores']['rival']['v1']['nombres'];
                                                            // Format first player name
                                                            $rivalNombreFormateado1 = cortarNombreApellido($rivalNombre1, $rivalNombres1, 16, true);
                                                            
                                                            $rivalNombre2 = $partido['jugadores']['rival']['v2']['nombre_completo_temporal'];
                                                            $rivalNombres2 = $partido['jugadores']['rival']['v2']['nombres'];
                                                            // Format second player name
                                                            $rivalNombreFormateado2 = cortarNombreApellido($rivalNombre2, $rivalNombres2, 16, true);
                                                            
                                                            echo htmlspecialchars($rivalNombreFormateado1) . '<br>' . htmlspecialchars($rivalNombreFormateado2);
                                                        } else {
                                                            $rivalNombre = $partido['jugadores']['rival']['v1']['nombre_completo_temporal'];
                                                            $rivalNombres = $partido['jugadores']['rival']['v1']['nombres'];
                                                            // Format player name
                                                            $rivalNombreFormateado = cortarNombreApellido($rivalNombre, $rivalNombres, 16, true);
                                                            echo htmlspecialchars($rivalNombreFormateado);
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
    
  <style>
.fixture-container {
    padding: 0px 40px 0px 40px;
}

.match-body {
    display: flex
;
    align-items: center;
    justify-content: space-between;
    position: relative;
   
}

    .match-team {
        width: 45%;
    }

    .team-name-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }

    .match-team-local .team-name-container {
        flex-direction: row;
    }

    .match-team-away .team-name-container {
        flex-direction: row-reverse;
    }

.team-name {
    font-size: 4.5em;
    /* font-weight: bold; */
    color: white;
    flex-grow: 1;
    text-align: center;
    font-family: "Anton";
    letter-spacing: 0.5px;
    font-style: italic;
    text-shadow: 3px 4px 4px rgba(0, 0, 0, 0.8);
    height: 200px;
    background: #00b9f224;
    display: flex
;
    align-items: center;
    justify-content: center;
    margin-left: 20px;
    margin-right: 20px;
    border-radius: 50px;
}



.match-versus {
    width: 10%;
    display: flex
;
    justify-content: center;
    align-items: center;
    height: 100%;
    background:  #00b9f2;
    height: 200px;
}

.versus-text {
    font-size: 6.6em;
    /* font-weight: bold; */
    color: white;
    padding: 0 10px;
    border-radius: 20px;
    letter-spacing: 0.5px;
    font-family: "Anton";
}

    .match-footer {
        padding: 15px 20px;
    }

    .match-details {
         font-size: 4.5em;
        display: flex;
        justify-content: center;
        align-items: center;
    }

.match-date {
  
    color: white;
    letter-spacing: 0.5px;
    /* font-style: italic; */
    font-family: "Anton";
    margin-right: 28px;
}

    .match-result {
        display: flex;
        align-items: center;
    }

    .result-label {
        margin-right: 10px;
        color: white;
    }

    .result-value {
        color: white;
    }
.team-logo {
    display: flex;
    flex-direction: row;
    align-items: center;
}

.team-logo img {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    object-fit: cover;
}

.team-logo .second-player-img {
    margin-left: 5px;
}

</style>
    
    
<script>
    const datos = <?php echo json_encode($datos); ?>;
</script>

    <script src="js/html2canvas.min.js"></script>
    <script src="js/bg.js"></script>
    <script src="js/main.js"></script>

</body>

</html>