<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Lista de Jugadores</title>
</head>
<body>

    <div class="loader_fixed">
        <span class="loader"></span>
    </div>

    <?php
    // 1. Define la ruta a la carpeta de fondos
$directorioFondos = 'images/bg/';

// 2. Busca todos los archivos que terminen en .jpeg o .jpg dentro de esa carpeta
$archivosFondos = array_merge(
    glob($directorioFondos . '*.jpeg'),
    glob($directorioFondos . '*.jpg')
);

// 3. Cuenta cuántos archivos se encontraron
$numeroDeFondos = count($archivosFondos);
    // Leer el archivo JSON desde el archivo .txt
    $rutaArchivo = $_GET['json'] ?? './example/players/6.json';
    $jsonData = file_get_contents($rutaArchivo);
    $datos = json_decode($jsonData, true);
    
    $datos = $datos[0];


    $validation = 0;

    function numeroAleatorio() {
        return rand(1, 8);
    }
    
    function cortarNombreApellido($nombreCompleto, $nombrePila, $maxCaracteres = 12, $formatoEspecial = true) {
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
                <h1>Lista de Jugadores</h1>
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
                           <div class="background-selector-container">
    <h4 style="text-align: center; width: 100%; margin-bottom: 15px;">Selecciona un fondo:</h4>
    <div class="background-options">

        <!-- PRIMERA OPCIÓN: Randomizador con ? -->
        <div class="bg-option randomizer active" data-bg="random" title="Fondo Aleatorio">
            <span style="font-size: 24px; font-weight: bold; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">?</span>
        </div>

        <!-- Luego los fondos normales -->
        <?php 
        $bgCounter = 1;
        foreach ($archivosFondos as $archivoFondo): 
            $nombreArchivo = basename($archivoFondo);
            $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
        ?>
            <div 
                class="bg-option" 
                data-bg="bg<?php echo $bgCounter; ?>" 
                data-extension="<?php echo $extension; ?>"
                style="background-image: url('<?php echo $archivoFondo; ?>');">
            </div>
        <?php 
            $bgCounter++;
        endforeach; 
        ?>

    </div>
</div>
                    <button id="generate">Generar</button>
                <?php endif; ?>

            </div>
            <div class="wh-100vh view_hero-image">
                <img src="images/bg_mod3_jugadores.jpg">
            </div>
        </div>
    </div>


<?php
// Función para reordenar jugadores
function reordenarJugadores($jugadores, $columnas) {
    $jugadoresReordenados = [];
    $totalJugadores = count($jugadores);
    
    // Ordenar por posición primero
    usort($jugadores, function($a, $b) {
        return $a['posicion'] - $b['posicion'];
    });
    
    // Calcular cuántos jugadores necesarios para ser múltiplo de 4
    $jugadoresNecesarios = ceil($totalJugadores / 4) * 4;
    $jugadoresAñadir = $jugadoresNecesarios - $totalJugadores;
    
    // Añadir jugadores fantasma
    for ($i = 0; $i < $jugadoresAñadir; $i++) {
        $jugadores[] = [
            'id' => 'fantasma_' . $i,
            'nombre' => 'Jugador Fantasma',
            'posicion' => 1000,
            'puntos' => 1
        ];
    }
    
    $totalJugadores = count($jugadores);
    
    // Calcular cuántos jugadores van en cada columna
    $jugadoresPorColumna = ceil($totalJugadores / $columnas);
    
    for ($fila = 0; $fila < $jugadoresPorColumna; $fila++) {
        for ($columna = 0; $columna < $columnas; $columna++) {
            $indice = $fila + $columna * $jugadoresPorColumna;
            
            if ($indice < $totalJugadores) {
                // Ignorar jugadores fantasma al mostrar
                    $jugadoresReordenados[] = $jugadores[$indice];
                
            }
        }
    }
    
    return $jugadoresReordenados;
}

$jugadoresPorPagina = 64;
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

foreach ($gruposDeJugadores as $indiceGrupo => $grupoJugadores):
    $datosGrupo = $datos;
    $datosGrupo['jugadores'] = $grupoJugadores;
    $datosGrupo['multiple'] = count($gruposDeJugadores) > 1;
    $datosGrupo['paginaActual'] = $indiceGrupo + 1;
    $datosGrupo['totalPaginas'] = count($gruposDeJugadores);

    // Determinar número de columnas
    $totalJugadores = count($grupoJugadores);
    $cols = 2;
    $class = 'separado_all';

    if ($totalJugadores <= 25) {
        $cols = 2;
    }else if($totalJugadores <= 32) {
        $cols = 3;
    }
    
    else {
        $cols = 4;
    }

    // Reordenar jugadores
    $grupoJugadores = reordenarJugadores($grupoJugadores, $cols);

    // Ajustar clase de espaciado
    if ($totalJugadores > 30) {
        $class = 'pegado_all';
    }
    if ($totalJugadores > 56) {
        $class = 'muy_pegado_all';
    }
?>
    <div class="canvas_scroll w-100 <?php echo $datosGrupo['multiple'] ? 'is_multiple' : ''; ?>">
        <div class="canvas canvas-full bg<?php echo $fondoAleatorio; ?>">
            <img src="images/topbar.png" class="canvas_topbar">
                 <img src="<?php echo $datos['imagen_comunidad']; ?>" alt="Logo" class="canvas_flag">                
            <div class="canvas_head">
                <div>
                    <h1>Ranking</h1>
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
                    <?php if ($datosGrupo['multiple']): ?>
                        <h4>P&aacute;gina <?php echo $datosGrupo['paginaActual']; ?> de <?php echo $datosGrupo['totalPaginas']; ?></h4>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($datosGrupo['jugadores'])): ?>
                    <div class="wh-100 relative centrado" data-lines="<?php echo $cols - 1; ?>">
                        <?php for ($i = 0; $i < $cols - 1; $i++) {
                            echo '<div class="line absolute"></div>';
                        } ?>
                        <ul data-qty="<?php echo $totalJugadores; ?>" data-cols="<?php echo $cols; ?>" class="<?php echo $class; ?>">
<?php foreach ($grupoJugadores as $jugador): 
      $nombreMostrar = cortarNombreApellido($jugador['nombre'], $jugador['nombres']);

?>
    <li class="<?php echo (isset($jugador['id']) && strpos($jugador['id'], 'fantasma') !== false) ? 'fantasma' : ''; ?>">
        <span class="player-position_all"><?php echo $jugador['posicion']; ?></span>
        <span class="player-name_all"><?php echo $nombreMostrar; ?></span>
        <span class="player-points_all"><?php echo $jugador['puntos']; ?> pts</span>
    </li>
<?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<style>
.fantasma .player-name_all,
.fantasma .player-points_all,
.fantasma .player-position_all{
    visibility: hidden;
    height: 0;
    margin: 0;
    padding: 0;
}
.canvas_body ol, .canvas_body ul {
  width: 100%;
  padding-inline: 1.1%;
  position: relative;
  list-style: none;
  display: grid;
  -moz-column-gap: 100px;
       column-gap: 100px;
}
.canvas_body ul.separado_all li,
.canvas_body ul.pegado_all li,
.canvas_body ul.muy_pegado_all li {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 18px 0;
    font-family: "Anton";
}
.player-position_all {
    color: white;
    margin-right: 10px;
    text-align: center;
    font-family: "Anton";
}
.player-name_all {
    flex-grow: 1;
    font-family: "Anton";
}
.player-points_all {
    color: rgba(255,255,255,0.7);
    min-width: 150px;
    text-align: right;
    font-family: "Anton";
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bgOptionsContainer = document.querySelector('.background-options');
    const allCanvases = document.querySelectorAll('.canvas');
    
    // Número total de fondos disponibles (sin contar el randomizador)
    const totalBackgrounds = <?php echo $numeroDeFondos; ?>;
    
    // Array con las rutas exactas de los fondos para sincronización
    const backgroundPaths = [
        <?php 
        foreach ($archivosFondos as $archivoFondo): 
            echo "'" . $archivoFondo . "'";
            if ($archivoFondo !== end($archivosFondos)) echo ", ";
        endforeach; 
        ?>
    ];
    
    // Colores aleatorios para el modo random
    const randomColors = [
        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
        'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)',
        'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
        'linear-gradient(135deg, #ff8a80 0%, #ea80fc 100%)',
        'linear-gradient(135deg, #82b1ff 0%, #b388ff 100%)',
        'linear-gradient(135deg, #84ffff 0%, #18ffff 100%)',
        'linear-gradient(135deg, #b9f6ca 0%, #69f0ae 100%)',
        'linear-gradient(135deg, #fff59d 0%, #ffeb3b 100%)',
        'linear-gradient(135deg, #ffcc80 0%, #ff9800 100%)',
        'linear-gradient(135deg, #ffab91 0%, #ff5722 100%)'
    ];

    if (bgOptionsContainer && allCanvases.length > 0) {
        bgOptionsContainer.addEventListener('click', function(e) {
            const selectedOption = e.target.closest('.bg-option');
            if (!selectedOption) return;

            const bgClass = selectedOption.dataset.bg;

            if (bgClass === 'random') {
                handleRandomBackground();
            } else {
                // Para fondos específicos, usar directamente la imagen del selector
                applySpecificBackgroundByImage(selectedOption);
            }
        });
    }

    function handleRandomBackground() {
        // Decidir aleatoriamente si usar una imagen de fondo o un color
        const useImageBackground = Math.random() > 0.3; // 70% probabilidad de usar imagen
        
        if (useImageBackground && totalBackgrounds > 0) {
            // Usar una imagen de fondo aleatoria
            const randomIndex = Math.floor(Math.random() * backgroundPaths.length);
            const randomImagePath = backgroundPaths[randomIndex];
            
            allCanvases.forEach(canvas => {
                // Limpiar estilos y clases
                canvas.style.background = '';
                canvas.style.backgroundImage = '';
                canvas.className = canvas.className.replace(/bg\d+/g, '').trim();
                
                // Aplicar imagen aleatoria directamente
                canvas.style.backgroundImage = `url('${randomImagePath}')`;
                canvas.style.backgroundSize = 'cover';
                canvas.style.backgroundPosition = 'center';
                canvas.style.backgroundRepeat = 'no-repeat';
            });
            
        } else {
            // Usar un color/gradiente aleatorio
            const randomColor = randomColors[Math.floor(Math.random() * randomColors.length)];
            
            allCanvases.forEach(canvas => {
                // Quitar todas las clases de fondo existentes
                canvas.className = canvas.className.replace(/bg\d+/g, '').trim();
                canvas.style.backgroundImage = '';
                
                // Aplicar el color/gradiente aleatorio
                canvas.style.background = randomColor;
            });
        }

        // Actualizar estado visual del selector
        document.querySelectorAll('.bg-option').forEach(opt => opt.classList.remove('active'));
        document.querySelector('[data-bg="random"]').classList.add('active');
    }

    function applySpecificBackgroundByImage(selectedOption) {
        // Obtener la imagen de fondo del selector mismo
        const computedStyle = window.getComputedStyle(selectedOption);
        const backgroundImage = computedStyle.backgroundImage;
        
        if (backgroundImage && backgroundImage !== 'none') {
            allCanvases.forEach(canvas => {
                // Limpiar estilos y clases previas
                canvas.style.background = '';
                canvas.className = canvas.className.replace(/bg\d+/g, '').trim();
                
                // Aplicar la misma imagen que se ve en el selector
                canvas.style.backgroundImage = backgroundImage;
                canvas.style.backgroundSize = 'cover';
                canvas.style.backgroundPosition = 'center';
                canvas.style.backgroundRepeat = 'no-repeat';
            });
        }

        // Actualizar estado visual del selector
        document.querySelectorAll('.bg-option').forEach(opt => opt.classList.remove('active'));
        selectedOption.classList.add('active');
    }

    // Manejar carga de fondo personalizado
    const imageUpload = document.getElementById('imageUpload');
    const heroViewInput = document.querySelector('.view_hero-input');
    
    if (imageUpload && heroViewInput) {
        imageUpload.addEventListener('change', function(event) {
            const files = event.target.files;
            
            if (files && files[0]) {
                const newBgFile = files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    const imageUrl = e.target.result;

                    allCanvases.forEach(canvas => {
                        // Quitar clases de fondo y limpiar estilos
                        canvas.className = canvas.className.replace(/bg\d+/g, '').trim();
                        canvas.style.background = '';
                        
                        // Aplicar fondo personalizado
                        canvas.style.backgroundImage = `url(${imageUrl})`;
                        canvas.style.backgroundSize = 'cover';
                        canvas.style.backgroundPosition = 'center';
                        canvas.style.backgroundRepeat = 'no-repeat';
                    });

                    // Mostrar previsualización
                    heroViewInput.style.background = `url(${imageUrl}) no-repeat center center`;
                    heroViewInput.style.backgroundSize = 'cover';
                    
                    if (!heroViewInput.classList.contains('with_preview')) {
                        heroViewInput.classList.add('with_preview');
                    }

                    // Desactivar todas las opciones del selector
                    document.querySelectorAll('.bg-option').forEach(opt => opt.classList.remove('active'));
                };

                reader.readAsDataURL(newBgFile);
            }
        });
    }

    // Manejar el botón de eliminar preview
    const deleteButton = document.querySelector('.delete_preview');
    if (deleteButton && heroViewInput) {
        deleteButton.addEventListener('click', function() {
            if (imageUpload) {
                imageUpload.value = '';
            }
            
            heroViewInput.style.background = '';
            heroViewInput.classList.remove('with_preview');
            
            handleRandomBackground();
        });
    }

    // Aplicar un fondo aleatorio al cargar la página
    handleRandomBackground();
});
</script>
    <script src="js/html2canvas.min.js"></script>
    <script src="js/bg.js"></script>
    <script src="js/main.js"></script>
<script>
    const datos = <?php echo json_encode($datos); ?>;
</script>

</body>

</html>