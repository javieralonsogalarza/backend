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
    
    // Extraer los datos de la nueva estructura
    $datos =  $datos['Data'];
    $partidos = [];
    $categorias = [];
    $validation = 0;
    
    // Procesar finales si existen
    if (isset($datos['finales']) && is_array($datos['finales'])) {
        $partidos = $datos['finales'];
        
        // Extraer todas las categorías únicas
        foreach ($datos['finales'] as $final) {
            if (isset($final['categoria'])) {
                $categoriaId = $final['categoria']['id'];
                if (!isset($categorias[$categoriaId])) {
                    $categorias[$categoriaId] = $final['categoria'];
                }
            }
        }
    }
    


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

                        <?php if (!empty($categorias)): $validation++; ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                </svg>
                                <p>Se cargaron <?php echo count($categorias); ?> categoría(s): 
                                    <?php 
                                    $nombresCategorias = array_map(function($cat) { return $cat['nombre']; }, $categorias);
                                    echo implode(', ', $nombresCategorias); 
                                    ?>
                                </p>
                            </li>
                        <?php else: ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                </svg>
                                <p>Error al cargar las categor&iacute;as.</p>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($partidos)): $validation++; ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                </svg>
                                <p>Se cargaron <?php echo count($partidos); ?> partidos</p>
                            </li>
                        <?php else: ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                </svg>
                                <p>No existen partidos en el JSON.</p>
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
                    <div style="margin-top: 25px; width: 100%;">
                        <label for="customText" style="color: #000; font-weight: 300; display: block; margin-bottom: 12px; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
                            Texto personalizado:
                        </label>
                        <input type="text" id="customText" placeholder="Ingrese el texto que desea mostrar..." style="width: 100%; padding: 12px 16px; border: 2px solid #000; border-radius: 0; background: white; font-size: 16px; font-family: 'Anton', sans-serif; font-weight: 300; letter-spacing: 0.5px; transition: all 0.3s ease; box-sizing: border-box;" onfocus="this.style.borderColor='#000'; this.style.background='rgba(255,255,255,0.95)'" onblur="this.style.borderColor='#000'; this.style.background='white'">
                    </div>
                    
                    <div style="margin-top: 25px; width: 100%;">
                        <label style="color: #000; font-weight: 300; display: block; margin-bottom: 12px; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
                            Configuración de Categorías y Horarios:
                        </label>
                        <div id="categoriesContainer" style="background: #f5f5f5; padding: 20px; border: 2px solid #000; border-radius: 0;">
                            <div class="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; align-items: start;">
                                <?php foreach ($categorias as $categoria): ?>
                                <div class="category-item" style="padding: 15px; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                                    <label style="display: flex; align-items: center; margin-bottom: 12px; font-size: 15px; font-weight: 600; color: #333; cursor: pointer;">
                                        <input type="checkbox" class="category-checkbox" data-category-id="<?php echo $categoria['id']; ?>" data-category-name="<?php echo htmlspecialchars($categoria['nombre']); ?>" style="margin-right: 12px; transform: scale(1.3); accent-color: #00b9f2; cursor: pointer;">
                                        <span class="selection-order" style="display: none; background: #00b9f2; color: white; border-radius: 50%; width: 24px; height: 24px; font-size: 12px; font-weight: bold; margin-right: 8px; align-items: center; justify-content: center;"></span>
                                        <?php echo $categoria['multiple'] ? 'DOBLES' : 'SINGLES'; ?> <?php echo strtoupper(htmlspecialchars($categoria['nombre'])); ?>
                                    </label>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <label style="font-size: 13px; color: #666; min-width: 50px;">Hora:</label>
                                        <input type="time" class="category-time" data-category-id="<?php echo $categoria['id']; ?>" style="width: 120px; padding: 8px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; font-weight: 500; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='#00b9f2'" onblur="this.style.borderColor='#ddd'">
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <button id="generate">Generar</button>
                <?php endif; ?>

            </div>
            <div class="wh-100vh view_hero-image">
                <img src="images/bg_mod3_grupos.jpg">
            </div>
        </div>
    </div>

<?php if (!empty($partidos)): ?>
    <div class="canvas_scroll w-100">
        <div class="canvas canvas-full bg<?php echo numeroAleatorio(); ?>">
            <img src="images/topbar.png" class="canvas_topbar">
            <img src="images/flag.png" class="canvas_flag">
            <div class="canvas_head for_group">
                <div>
                    <h1><?php echo isset($datos['grupo']['nombre']) ? $datos['grupo']['nombre'] : 'Finales'; ?></h1>
                </div>
                <?php if (isset($datos['titulo']) && !empty($datos['titulo'])): ?>
                    <div>
                        <h2><?php echo $datos['titulo']; ?></h2>
                    </div>
                <?php endif; ?>
            </div>
            <div class="canvas_body for_group">
                <div class="wh-100 canvas_body-title">
                    <div style="clear: both; display: block; margin-top: 20px; margin-bottom: 20px;">
                        <h6 style="font-weight: 400; font-size: 7em; text-shadow: 3px 4px 6px rgba(0, 0, 0, 0.9); color: white; text-align: start; font-family: 'Anton', sans-serif; letter-spacing: 2px; line-height: 1.1; margin: 0; padding: 10px 20px; background: transparent;" id="displayCustomText">
                            Texto personalizado aparecerá aquí
                        </h6>
                    </div>
                </div>
                
                <div class="w-100 relative canvas_body-grid fixture-container">
                    <div class="group">
                        <div class="group_list">
                            <?php foreach ($partidos as $partido): ?>
                                <div class="match-card" data-category-id="<?php echo $partido['categoria']['id']; ?>" style="display: none;">
                                    
                                    <div class="match-body">
                                        <div class="match-category">
                                            <h4 id="category-<?php echo $partido['categoria']['id']; ?>">
                                                <span class="category-order" style="display: none; background: #ffff00; color: #000; border-radius: 50%; width: 0px; height: 0px; font-size: 0px; align-items: center; justify-content: center;"></span><?php echo $partido['categoria']['multiple'] ? '' : 'SINGLES'; ?> <?php echo strtoupper(htmlspecialchars($partido['categoria']['nombre'])); ?> - <span class="category-time-display"></span>
                                            </h4>
                                        </div>
                                        <div class="match-teams-container">
                                            <div class="match-team match-team-local <?php echo ($partido['ganador'] == 'equipo1') ? '' : ''; ?>">
                                                <div class="team-container">
                                                    <div class="team-logo">
                                                        <?php 
                                                        $equipo1 = $partido['jugadores']['equipo1'];
                                                        $isMultiple = count($equipo1) > 1;
                                                        
                                                        foreach ($equipo1 as $index => $jugador): 
                                                        ?>
                                                            <img src="<?php echo htmlspecialchars($jugador['imagen']); ?>" 
                                                                 alt="Equipo 1 - Jugador <?php echo $index + 1; ?>" 
                                                                 <?php echo $index > 0 ? 'class="second-player-img"' : ''; ?>>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="team-name">
                                                        <?php 
                                                        if ($isMultiple) {
                                                            // Para dobles, mostrar nombres con formato "Nombre + Nombre"
                                                            $nombres = [];
                                                            foreach ($equipo1 as $jugador) {
                                                                $nombreFormateado = cortarNombreApellido($jugador['nombre'], $jugador['nombre'], 16, true);
                                                                $nombres[] = htmlspecialchars($nombreFormateado);
                                                            }
                                                            echo implode(' + ', $nombres);
                                                        } else {
                                                            // Para singles, mostrar nombre tal cual
                                                            echo htmlspecialchars($equipo1[0]['nombre']);
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="match-versus">
                                                <span class="versus-text">VS</span>
                                            </div>
                                            
                                            <div class="match-team match-team-away <?php echo ($partido['ganador'] == 'equipo2') ? '' : ''; ?>">
                                                <div class="team-container">
                                                    <div class="team-logo">
                                                        <?php 
                                                        $equipo2 = $partido['jugadores']['equipo2'];
                                                        $isMultiple2 = count($equipo2) > 1;
                                                        
                                                        foreach ($equipo2 as $index => $jugador): 
                                                        ?>
                                                            <img src="<?php echo htmlspecialchars($jugador['imagen']); ?>" 
                                                                 alt="Equipo 2 - Jugador <?php echo $index + 1; ?>" 
                                                                 <?php echo $index > 0 ? 'class="second-player-img"' : ''; ?>>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="team-name">
                                                        <?php 
                                                        if ($isMultiple2) {
                                                            // Para dobles, mostrar nombres con formato "Nombre + Nombre"
                                                            $nombres = [];
                                                            foreach ($equipo2 as $jugador) {
                                                                $nombreFormateado = cortarNombreApellido($jugador['nombre'], $jugador['nombre'], 16, true);
                                                                $nombres[] = htmlspecialchars($nombreFormateado);
                                                            }
                                                            echo implode(' + ', $nombres);
                                                        } else {
                                                            // Para singles, mostrar nombre tal cual
                                                            echo htmlspecialchars($equipo2[0]['nombre']);
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
    
  <style>
.fixture-container {
    padding: 0px 40px 0px 40px;
}



.match-header {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

.match-result {
    display: flex;
    align-items: center;
    gap: 10px;
}

.result-label {
    color: white;
    font-size: 2.8em;
    font-family: "Anton";
    text-shadow: 2px 3px 3px rgba(0, 0, 0, 0.8);
}

.result-value {
    color: #00ff00;
    font-size: 3.2em;
    font-family: "Anton";
    font-weight: bold;
    text-shadow: 2px 3px 3px rgba(0, 0, 0, 0.8);
}

.match-body {
    display: flex;
    flex-direction: column;
    position: relative;
}

.match-category {
    width: 100%;
    text-align: center;
    display: flex;
    justify-content: center;
}

.match-category h4 {
    color: white;
    font-size: 4.9em;
    font-family: "Anton";
    margin: 0;
    text-shadow: 2px 3px 3px rgba(0, 0, 0, 0.8);
    background: #00b9f2;
    padding: 12px 25px;
    border-radius: 20px;
    letter-spacing: 1px;
    display: inline-block;
    width: auto;
    font-weight: 500;
    font-style: italic;
}

.match-teams-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.match-team {
    width: 45%;
    transition: all 0.3s ease;
}

.match-team.winner {
    transform: scale(1.05);
}

.match-team.winner .team-name {
    background: linear-gradient(135deg, #00ff00, #00cc00);
    box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
}

.team-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.team-name {
    font-size: 3.5em;
    color: white;
    text-align: center;
    font-family: "Anton";
    letter-spacing: 0.5px;
    font-style: italic;
    text-shadow: 3px 4px 4px rgba(0, 0, 0, 0.8);
    min-height: 70px;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 10px;
    padding: 15px 20px;
    border-radius: 25px;
    transition: all 0.3s ease;
    width: 100%;
    box-sizing: border-box;
}

.match-versus {
    width: 10%;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    background: #00b9f2;
    height: 180px;
    border-radius: 20px;
}

.versus-text {
    font-size: 5.5em;
    color: white;
    padding: 0 10px;
    border-radius: 20px;
    letter-spacing: 0.5px;
    font-family: "Anton";
    text-shadow: 3px 4px 4px rgba(0, 0, 0, 0.8);
}

.match-footer {
    padding: 15px 20px;
    text-align: center;
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
    font-family: "Anton";
    margin-right: 28px;
    text-shadow: 2px 3px 3px rgba(0, 0, 0, 0.8);
}

.team-logo {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
}

.team-logo img {
    width: 280px;
    height: 280px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.team-logo img:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
}

.team-logo .second-player-img {
    margin-left: 20px;
    z-index: 1;
}

.team-logo img:first-child {
    z-index: 2;
}



/* Category Configuration Styles */
#categoriesContainer {
    position: relative;
}

#categoriesContainer::-webkit-scrollbar {
    width: 8px;
}

#categoriesContainer::-webkit-scrollbar-track {
    background: #f5f5f5;
    border-radius: 4px;
}

#categoriesContainer::-webkit-scrollbar-thumb {
    background: #00b9f2;
    border-radius: 4px;
    transition: background 0.3s ease;
}

#categoriesContainer::-webkit-scrollbar-thumb:hover {
    background: #0095cc;
}

.category-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
}

.categories-grid {
    min-height: fit-content;
}

/* Estilos para el dropdown de páginas */
#page-dropdown {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border: 2px solid #00b9f2;
    border-radius: 10px;
    padding: 20px;
    z-index: 10000;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    font-family: 'Anton', sans-serif;
    min-width: 300px;
    text-align: center;
    display: none;
}

#page-dropdown h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 18px;
}

#page-dropdown select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    font-family: Arial, sans-serif;
}

#page-dropdown button {
    background: #00b9f2;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-family: 'Anton', sans-serif;
    font-size: 14px;
    margin: 0 5px;
    transition: background 0.3s ease;
}

#page-dropdown button:hover {
    background: #0095cc;
}

#page-dropdown #cancel-dropdown {
    background: #666;
}

#page-dropdown #cancel-dropdown:hover {
    background: #555;
}

.dropdown-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
}


</style>
    
    
<script>
    const datos = <?php echo json_encode($datos); ?>;
    const partidos = <?php echo json_encode($partidos); ?>;
    
    // Función para convertir hora de 24h a 12h con AM/PM
    function formatTime12Hour(time24) {
        if (!time24) return '';
        
        const [hours, minutes] = time24.split(':');
        const hour = parseInt(hours, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12; // Convierte 0 a 12 para medianoche
        
        return `${hour12}:${minutes} ${ampm}`;
    }
    
    // Función para actualizar las horas mostradas en las categorías
    function updateCategoryTimes() {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        const timeInputs = document.querySelectorAll('.category-time');
        
        // Llamar a updateCategoryDisplay de main.js si está disponible
        if (typeof updateCategoryDisplay === 'function') {
            updateCategoryDisplay();
            return;
        }
        
        // Primero ocultar todas las categorías
        document.querySelectorAll('.match-card').forEach(card => {
            card.style.display = 'none';
        });
        
        // Obtener categorías seleccionadas y ordenarlas por orden de selección
        const selectedCategories = Array.from(checkboxes).filter(checkbox => checkbox.checked);
        
        // Solo reorganizar si hay un tracker de orden disponible
        if (typeof window !== 'undefined' && window.selectionOrderTracker) {
            const sortedSelectedCategories = selectedCategories.sort((a, b) => {
                const aOrder = window.selectionOrderTracker.find(item => item.categoryId === a.getAttribute('data-category-id'))?.order || 999;
                const bOrder = window.selectionOrderTracker.find(item => item.categoryId === b.getAttribute('data-category-id'))?.order || 999;
                return aOrder - bOrder;
            });
            
            // Reorganizar las tarjetas de partido en el DOM según el orden de selección
            const container = document.querySelector('.group_list');
            if (container) {
                const allCards = Array.from(container.querySelectorAll('.match-card'));
                
                // Crear un array de tarjetas ordenadas según el orden de selección
                const orderedCards = [];
                
                // Primero agregar las tarjetas de categorías seleccionadas en orden
                sortedSelectedCategories.forEach(checkbox => {
                    const categoryId = checkbox.getAttribute('data-category-id');
                    const cards = allCards.filter(card => card.getAttribute('data-category-id') === categoryId);
                    orderedCards.push(...cards);
                });
                
                // Luego agregar las tarjetas no seleccionadas al final
                allCards.forEach(card => {
                    if (!orderedCards.includes(card)) {
                        orderedCards.push(card);
                    }
                });
                
                // Reorganizar el DOM
                orderedCards.forEach(card => {
                    container.appendChild(card);
                });
            }
        }
        
        checkboxes.forEach((checkbox, index) => {
            const categoryId = checkbox.getAttribute('data-category-id');
            const timeInput = document.querySelector(`.category-time[data-category-id="${categoryId}"]`);
            const categoryDisplay = document.querySelector(`#category-${categoryId} .category-time-display`);
            const matchCards = document.querySelectorAll(`.match-card[data-category-id="${categoryId}"]`);
            
            if (checkbox.checked) {
                // Mostrar las tarjetas de partido para esta categoría
                matchCards.forEach(card => {
                    card.style.display = 'block';
                });
                
                // Actualizar la hora mostrada con formato 12h
                if (categoryDisplay) {
                    if (timeInput && timeInput.value) {
                        categoryDisplay.textContent = formatTime12Hour(timeInput.value);
                    } else {
                        categoryDisplay.textContent = '';
                    }
                }
            } else {
                // Ocultar las tarjetas de partido para esta categoría
                matchCards.forEach(card => {
                    card.style.display = 'none';
                });
                
                if (categoryDisplay) {
                    categoryDisplay.textContent = '';
                }
            }
        });
    }
    
    // Event listeners para checkboxes y inputs de tiempo
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        const timeInputs = document.querySelectorAll('.category-time');
        const customTextInput = document.getElementById('customText');
        const displayCustomText = document.getElementById('displayCustomText');
        
        // Usar handleCheckboxChange de main.js si está disponible
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (typeof window.handleCheckboxChange === 'function') {
                    window.handleCheckboxChange(this);
                } else {
                    // Fallback básico si main.js no está cargado
                    updateCategoryTimes();
                }
            });
        });
        
        timeInputs.forEach(timeInput => {
            timeInput.addEventListener('change', function() {
                if (typeof window.updateCategoryDisplay === 'function') {
                    window.updateCategoryDisplay();
                } else {
                    updateCategoryTimes();
                }
            });
            timeInput.addEventListener('input', function() {
                if (typeof window.updateCategoryDisplay === 'function') {
                    window.updateCategoryDisplay();
                } else {
                    updateCategoryTimes();
                }
            });
        });
        
        // Actualizar texto personalizado en tiempo real
        if (customTextInput && displayCustomText) {
            customTextInput.addEventListener('input', function() {
                displayCustomText.textContent = this.value || 'Texto personalizado aparecerá aquí';
            });
        }
        
        // Inicializar sin nada seleccionado
        updateCategoryTimes();
        
        // Ajustar grid de categorías responsivamente
        function adjustCategoriesGrid() {
            const container = document.querySelector('.categories-grid');
            if (container) {
                const containerWidth = container.offsetWidth;
                
                // Calcular número de columnas basado en el ancho disponible
                let columns;
                if (containerWidth >= 900) {
                    columns = 3;
                } else if (containerWidth >= 600) {
                    columns = 2;
                } else {
                    columns = 1;
                }
                
                container.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
            }
        }
        
        // Ajustar al cargar y redimensionar
        adjustCategoriesGrid();
        window.addEventListener('resize', adjustCategoriesGrid);
    });
</script>

    <script src="js/html2canvas.min.js"></script>
    <script src="js/bg.js"></script>
    <script src="js/main.js"></script>

</body>

</html>