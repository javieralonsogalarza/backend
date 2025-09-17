<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Copa Level Up 2024</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- <script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@latest/dist/face-api.min.js"></script> -->
    <link rel="stylesheet" href="cropper/cropper.css">
    <script src="cropper/cropper.js"></script>
        <?php
        // Leer el archivo JSON desde el archivo especificado o el predeterminado
        $rutaArchivom = $_GET['json'] ?? '../example1.json';
        $jsonDatam = file_get_contents($rutaArchivom);
        $datosm = json_decode($jsonDatam, true);

        // Obtener los IDs de los jugadores o usar valores predeterminados
        $jugador_local_id = $datosm['jugador_local_id'] ?? 1;
        $jugador_rival_id = $datosm['jugador_rival_id'] ?? 2;
    ?>
    <?php

function formatDateInSpanish($date) {
    $months = [
        'January' => 'Ene.', 'February' => 'Feb.', 'March' => 'Mar.',
        'April' => 'Abr.', 'May' => 'May.', 'June' => 'Jun.',
        'July' => 'Jul.', 'August' => 'Ago.', 'September' => 'Sep.',
        'October' => 'Oct.', 'November' => 'Nov.', 'December' => 'Dic.'
    ];

    // Obtener la fecha actual
    $currentDate = date("Y-m-d");
    $inputDate = date("Y-m-d", strtotime($date));

    // Si la fecha proporcionada es superior a la fecha actual, usar la fecha actual
    if ($inputDate > $currentDate) {
        $date = $currentDate;
    }

    // Formatear la fecha al mes y año
    $formattedDate = date("F y", strtotime($date));
    $englishMonth = date("F", strtotime($date)); // Obtener el mes en inglés

    // Reemplazar el mes en inglés por el mes en español
    $spanishMonth = $months[$englishMonth] ?? $englishMonth;

    // Devolver la fecha con el mes en español
    return str_replace($englishMonth, $spanishMonth, $formattedDate);
}

function shortenName($fullName, $condition = null, $maxLength = 14) {
    // Solo proceder si no hay condición o si condition es 5
    if ($condition === null || $condition == 5) {
        // Dividir el nombre en partes
        $nameParts = explode(' ', $fullName);
        
        // Solo acortar si tiene exactamente 3 palabras
        if (count($nameParts) == 3) {
            // Mantener el primer nombre y el último apellido
            $firstName = $nameParts[0];
            $middleInitial = strtoupper(substr($nameParts[1], 0, 1));
            $lastName = $nameParts[2];
            
            // Construir el nombre acortado
            $shortenedName = $firstName . ' ' . $middleInitial . '. ' . $lastName;
            
            return trim($shortenedName);
        }
    }
    
    // Si no cumple las condiciones, devolver el nombre completo
    return $fullName;
}
?>

       <script>
       
        const datos = {
            jugador_local_id: <?php echo json_encode($jugador_local_id); ?>,
            jugador_rival_id: <?php echo json_encode($jugador_rival_id); ?>
        };
    </script>
</head>

<body>

    <div class="popup_cropper">
        <div class="popup_cropper-bg"></div>
        <div class="popup_cropper-box">
            <div class="popup_cropper-canvas">
                <!-- cropper -->
            </div>
            <div class="popup_cropper-actions">
                <button style="margin-top: 0px" type="button" id="clean">
                    Limpiar
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M170.5 51.6L151.5 80l145 0-19-28.4c-1.5-2.2-4-3.6-6.7-3.6l-93.7 0c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80 368 80l48 0 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-8 0 0 304c0 44.2-35.8 80-80 80l-224 0c-44.2 0-80-35.8-80-80l0-304-8 0c-13.3 0-24-10.7-24-24S10.7 80 24 80l8 0 48 0 13.8 0 36.7-55.1C140.9 9.4 158.4 0 177.1 0l93.7 0c18.7 0 36.2 9.4 46.6 24.9zM80 128l0 304c0 17.7 14.3 32 32 32l224 0c17.7 0 32-14.3 32-32l0-304L80 128zm80 64l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16z" />
                    </svg>
                </button>
                <button style="margin-top: 0px" type="button" id="cut">
                    Recortar
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M448 109.3l54.6-54.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L402.7 64 160 64l0 64 178.7 0L128 338.7 128 32c0-17.7-14.3-32-32-32S64 14.3 64 32l0 32L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l32 0 0 256c0 35.3 28.7 64 64 64l224 0 0-64-178.7 0L384 173.3 384 480c0 17.7 14.3 32 32 32s32-14.3 32-32l0-32 32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-32 0 0-274.7z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
      <div class="popup_cropper popup_cropper_two">
        <div class="popup_cropper-bg "></div>
        <div class="popup_cropper-box">
            <div class="popup_cropper-canvas popup_cropper-canvas_two">
                <!-- cropper -->
            </div>
            <div class="popup_cropper-actions">
                <button style="margin-top:0px"  type="button" id="cleanTwo">
                    Limpiar
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M170.5 51.6L151.5 80l145 0-19-28.4c-1.5-2.2-4-3.6-6.7-3.6l-93.7 0c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80 368 80l48 0 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-8 0 0 304c0 44.2-35.8 80-80 80l-224 0c-44.2 0-80-35.8-80-80l0-304-8 0c-13.3 0-24-10.7-24-24S10.7 80 24 80l8 0 48 0 13.8 0 36.7-55.1C140.9 9.4 158.4 0 177.1 0l93.7 0c18.7 0 36.2 9.4 46.6 24.9zM80 128l0 304c0 17.7 14.3 32 32 32l224 0c17.7 0 32-14.3 32-32l0-304L80 128zm80 64l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16z" />
                    </svg>
                </button>
                <button style="margin-top:0px" type="button" id="cutTwo">
                    Recortar
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M448 109.3l54.6-54.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L402.7 64 160 64l0 64 178.7 0L128 338.7 128 32c0-17.7-14.3-32-32-32S64 14.3 64 32l0 32L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l32 0 0 256c0 35.3 28.7 64 64 64l224 0 0-64-178.7 0L384 173.3 384 480c0 17.7 14.3 32 32 32s32-14.3 32-32l0-32 32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-32 0 0-274.7z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="loader_fixed"><span class="loader"></span></div>

    <?php
    // Leer el archivo JSON desde el archivo .txt
    $rutaArchivo = $_GET['json'] ?? '../example1.json';
    $jsonData = file_get_contents($rutaArchivo);
    setlocale(LC_TIME, 'es_ES.UTF-8'); // Para sistemas Linux/Mac
    $datos = json_decode($jsonData, true);

    $validation = 0;
    ?>

    <div class="hero_view wh-100vh">
        <div class="hero_view-txt wh-100">
            <h1>Generador <br>de Imágenes</h1>

            <div class="hero_view-test w-100">
                <ul id="testing">
                    <?php if (isset($datos['jugador_local']) && !empty($datos['jugador_local'])): $validation++; ?>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                            </svg>
                            <p>Jugador 1 cargado correctamente.</p>
                        </li>
                    <?php else: ?>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                            </svg>
                            <p>Error al cargar el jugador 1.</p>
                        </li>
                    <?php endif; ?>

                   <?php if (isset($datos['jugador_rival']) && !empty($datos['jugador_rival'])): $validation++; ?>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                            </svg>
                            <p>Jugador 2 cargado correctamente.</p>
                        </li>
                    <?php else: ?>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                            </svg>
                            <p>Error al cargar el jugador 1.</p>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php if ($validation > 1): ?>
             <div class="contenedor-imagenes">
            
                <div class="w-100 hero_view-input" id="local" 
    style="background-image: <?php echo !empty($datos['jugador_local_imagen']) ? 'url('.$datos['jugador_local_imagen'].')' : 'none'; ?>;
           background-size: cover;
           background-position: center;
           background-repeat: no-repeat;">
                    <label for="imageUpload" class="labelFile">
                        <p class="span" id="imageStatus2">
    <?php if (!empty($datos['jugador_local_imagen'])): ?>
      
    <?php else: ?>
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
        
    <?php endif; ?>
</p>
                        
                    </label>
                    <input class="input" type="file" id="imageUpload" accept=".jpg, .jpeg, .png">
                    <?php if (empty($datos['jugador_local_imagen'])): ?>
                    <span class="texto-abajo">No tiene imagen precargada en el sistema: <?php echo $datos['jugador_local']; ?>.</span>  <?php endif; ?>
                    <?php if (!empty($datos['jugador_local_imagen'])): ?>
                    <span class="texto-abajo">Ya tiene imagen precargada en el sistema: <?php echo $datos['jugador_local']; ?>.</span>  <?php endif; ?>
                   
                </div>
                
                
                
                <div class="w-100 hero_view-input" id="rival"
    style="background-image: <?php echo !empty($datos['jugador_rival_imagen']) ? 'url('.$datos['jugador_rival_imagen'].')' : 'none'; ?>;
           background-size: cover;
           background-position: center;
           background-repeat: no-repeat;">
                    <label for="imageUploadTwo" class="labelFile">
                        
                    <p class="span" id="imageStatus">
    <?php if (!empty($datos['jugador_rival_imagen'])): ?>
       
    <?php else: ?>
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
       
    <?php endif; ?>
</p>
                    </label>
                    <input class="input" type="file" id="imageUploadTwo" accept=".jpg, .jpeg, .png">
                    <?php if (empty($datos['jugador_rival_imagen'])): ?>
                    <span class="texto-abajo">No tiene imagen precargada en el sistema: <?php echo $datos['jugador_rival']; ?>.</span>  <?php endif; ?>
                    <?php if (!empty($datos['jugador_rival_imagen'])): ?>
                    <span class="texto-abajo">Ya tiene imagen precargada en el sistema: <?php echo $datos['jugador_rival']; ?>.</span>  <?php endif; ?>
                    
                </div>

                <!-- // ------------------------------------------- // -->
</div>
            <?php endif; ?>




            <canvas id="canvas" style="display:none;"></canvas>
            <button type="button" class="generate_image_two generate_image">
                Generar
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l370.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128z" />
                </svg>
            </button>

            <br><br><br><br><br><br><br>
        </div>
        <div class="hero_view-img wh-100">
            <img src="images/bg_mod2.jpg">
        </div>
    </div>

    <?php if (!empty($datos)) : ?>
       
       <?php if (!empty($datos)) : ?>
    <div class="canvas_scroll w-100">
        <div class="canvas general_canvas bg<?php echo rand(1, 14); ?>">

           <div class="container">
                       <img src="images/topbar.png" class="canvas_topbar">
   <img src="<?php echo $datos['imagen_comunidad']; ?>" alt="Logo" class="canvas_flag">                <div class="canvas_head for_group">
                    <div>
                        <h1>HEAD TO HEAD</h1>
                    </div>
                    <?php if (isset($datos['torneo']) && !empty($datos['torneo'])): ?>
                        <div>
                            <h2><?php echo $datos['torneo']; ?></h2>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($datos['nombre_categoria'])): ?>
    <div class="categoria-title">
                <h3 class="categoria-especial">Categoria: <?php echo $datos['nombre_categoria']; ?></h3>

    </div>
<?php endif; ?>

            
                <div class="header">
                  
                    
                    <div class="score"><?php echo $datos['victorias_local_vs'];?></div>
                    
                </div>
                 <div class="header_score">
                  
                    
                    <div class="score"><?php echo $datos['victorias_rival_vs']; ?></div>
                    
                </div>
            
                <div class="players">
                    <div class="player">
                        <div class="canvas_image" id="imageContainer">
                                <img src="" 
                                 title="Foto" 
                                 alt="Foto">
                        </div>      
                            <h2 class="nombre-jugador"><?php echo $datos['jugador_local']; ?></h2>
                    </div>
                    <!-- Contenedor para los datos en tres columnas -->
                    <div class="player-stats">
                        <div class="column">
                            <p><?php echo $datos['ranking_local'] ?? '-'; ?></p>
                            <p><?php echo $datos['jugador_local_mano_habil'] ?? '-'; ?></p>
                            <p><?php echo $datos['jugador_local_edad'] ? $datos['jugador_local_edad'] . ' años' : '-'; ?></p>
                            <p><?php echo $datos['jugador_local_tamano'] ? $datos['jugador_local_tamano'] . ' m' : '-'; ?></p>
                            <p><?php echo $datos['jugador_local_peso'] ? $datos['jugador_local_peso'] . ' kg' : '-'; ?></p>
                        </div>
                        <div class="column">
                            <p class="ranking"><strong>Ranking</strong></p>
                            <p><strong>Mano Hábil</strong></p>
                            <p><strong>Edad</strong></p>
                            <p><strong>Estatura</strong></p>
                            <p><strong>Peso</strong></p>
                        </div>
                        <div class="column">
                            <p><?php echo $datos['ranking_rival'] ?? '-'; ?></p>
                            <p><?php echo $datos['jugador_rival_mano_habil'] ?? '-'; ?></p>
                            <p><?php echo $datos['jugador_rival_edad'] ? $datos['jugador_rival_edad'] . ' años' : '-'; ?></p>
                            <p><?php echo $datos['jugador_rival_tamano'] ? $datos['jugador_rival_tamano'] . ' m' : '-'; ?></p>
                            <p><?php echo $datos['jugador_rival_peso'] ? $datos['jugador_rival_peso'] . ' kg' : '-'; ?></p>
                        </div>
                    </div>
                    <div class="player">
                        <div class="canvas_image" id="imageContainerTwo">
                                <img src="" 
                                 title="Foto" 
                                 alt="Foto">                        </div>
                        <h2 class="nombre-jugador"><?php echo $datos['jugador_rival']; ?></h2>
                    </div>
                </div>
                <!-- Lista de Enfrentamientos Directos -->
                <div class="vs-results-container">
                    <div class="vs-results">
                        <h3 class="list-title-first">Enfrentamientos Directos</h3>
                        <?php foreach ($datos['partido_vs'] as $partido) : ?>
                            <div class="match">
                               <div class="date-container">
                                <div class="date"><?php echo formatDateInSpanish($partido['fecha_final']); ?></div>
                            </div>
                                <div class="opponent-container">
                                    
                                   <div class="opponent1_especial">
                                      <div class="names">
                                        <div class="espacio"><?php echo $partido['jugador_local']; ?></div>
                                        <div><?php echo $partido['jugador_rival']; ?></div>
                                      </div>
                                      <div>
                                                                                <div class="torneo-name"><?php echo $partido['torneo']; ?></div>
                                      <div><?php echo $partido['fase']; ?></div>

                                      </div>
                                     <div class="score-especial">
    <?php
        // Divide el texto del resultado por el símbolo "/"
        $resultados = explode('/', $partido['resultado']);

        // Separamos los sets en columnas
        $sets_ganados = [];
        $sets_perdidos = [];

        // Procesamos cada resultado para separar los puntos ganados y perdidos
        foreach ($resultados as $resultado) {
            list($ganado, $perdido) = explode('-', $resultado);
            $sets_ganados[] = $ganado;
            $sets_perdidos[] = $perdido;
        }
    ?>

    <div class="score-row">
        <?php foreach ($sets_ganados as $set) : ?>
            <div class="score-set"><?php echo $set; ?></div>
        <?php endforeach; ?>
    </div>
    <div class="score-row">
        <?php foreach ($sets_perdidos as $set) : ?>
            <div class="score-set"><?php echo $set; ?></div>
        <?php endforeach; ?>
    </div>
</div>


                                    </div>
                                </div>
                               
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Resultados recientes de cada jugador -->
                <div class="match-results-container">
    <!-- Lista de partidos del jugador local -->
    <div class="match-results">
<?php
$nombreCompleto = $datos['jugador_local'];

if (strlen($nombreCompleto) > 14) {
    $partes = explode(' ', $nombreCompleto);
    if (count($partes) > 1) {
        $nombreReducido = $partes[0] . ' ' . substr($partes[1], 0, 1) . '.';
        if (strlen($nombreReducido) > 14) {
            $nombreReducido = substr($partes[0], 0, 14) . '.';
        }
    } else {
        $nombreReducido = substr($nombreCompleto, 0, 14) . '.';
    }
} else {
    $nombreReducido = $nombreCompleto;
}
?>

<h3 class="list-title">Últimos partidos de <?php echo htmlspecialchars($nombreReducido); ?></h3>

        <?php foreach ($datos['partidos_local'] as $partido_local) : ?>
            <div class="match">
                <div class="date-container">
                    <div class="date"><?php echo formatDateInSpanish($partido_local['fecha_final']); ?></div>
                </div>
                <div class="opponent-container">
                    <div class="opponent1">
                        <div class="names">
                            <div><?php echo shortenName($partido_local['jugador_local'],count($datos['partidos_local'])); ?></div>
                            <div><?php echo shortenName($partido_local['jugador_rival'],count($datos['partidos_local'])); ?></div>
                        </div>
                        <div class="score-especial">
    <?php
        // Divide el texto del resultado por el símbolo "/"
        $resultados = explode('/', $partido_local['resultado']);

        // Inicializa arrays para puntos ganados y perdidos
        $sets_ganados = [];
        $sets_perdidos = [];

        // Procesa cada resultado y separa los puntos ganados y perdidos
        foreach ($resultados as $resultado) {
            list($ganado, $perdido) = explode('-', $resultado);
            $sets_ganados[] = $ganado;
            $sets_perdidos[] = $perdido;
        }
    ?>

    <div class="score-row">
        <?php foreach ($sets_ganados as $set) : ?>
            <div class="score-set"><?php echo $set; ?></div>
        <?php endforeach; ?>
    </div>
    <div class="score-row">
        <?php foreach ($sets_perdidos as $set) : ?>
            <div class="score-set"><?php echo $set; ?></div>
        <?php endforeach; ?>
    </div>
</div>

                    </div>
                    <div class="status <?php echo strtolower($partido_local['gano']) == 'ganó' ? 'win' : 'loss'; ?>">
                        <?php echo strtoupper($partido_local['gano'][0]); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Lista de partidos del jugador rival -->
     <div class="match-results">
<?php
$nombreCompleto = $datos['jugador_rival'];

if (strlen($nombreCompleto) > 14) {
    $partes = explode(' ', $nombreCompleto); // Dividir el nombre en palabras
    if (count($partes) > 1) {
        $nombreReducido = $partes[0] . ' ' . substr($partes[1], 0, 1) . '.'; // Primer nombre + Inicial del segundo nombre
        if (strlen($nombreReducido) > 14) {
            $nombreReducido = substr($partes[0], 0, 14) . '.'; // Recortar aún más si sigue largo
        }
    } else {
        $nombreReducido = substr($nombreCompleto, 0, 14) . '.'; // Si solo tiene un nombre, recortar
    }
} else {
    $nombreReducido = $nombreCompleto;
}
?>

<h3 class="list-title">Últimos partidos de <?php echo htmlspecialchars($nombreReducido); ?></h3>

        <?php foreach ($datos['partidos_rival'] as $partido_rival) : ?>
            <div class="match">
                <div class="date-container">
                    <div class="date"><?php echo formatDateInSpanish($partido_rival['fecha_final']); ?></div>
                </div>
                <div class="opponent-container">
                    <div class="opponent1">
                        <div class="names">
                            <div><?php echo shortenName($partido_rival['jugador_local'],count($datos['partidos_rival'])); ?></div>
                            <div><?php echo shortenName($partido_rival['jugador_rival'],count($datos['partidos_rival'])); ?></div>
                        </div>
                       <div class="score-especial">
    <?php
        // Divide el texto del resultado por el símbolo "/"
        $resultados = explode('/', $partido_rival['resultado']);

        // Inicializa arrays para puntos ganados y perdidos
        $sets_ganados = [];
        $sets_perdidos = [];

        // Procesa cada resultado y separa los puntos ganados y perdidos
        foreach ($resultados as $resultado) {
            list($ganado, $perdido) = explode('-', $resultado);
            $sets_ganados[] = $ganado;
            $sets_perdidos[] = $perdido;
        }
    ?>

    <div class="score-row">
        <?php foreach ($sets_ganados as $set) : ?>
            <div class="score-set"><?php echo $set; ?></div>
        <?php endforeach; ?>
    </div>
    <div class="score-row">
        <?php foreach ($sets_perdidos as $set) : ?>
            <div class="score-set"><?php echo $set; ?></div>
        <?php endforeach; ?>
    </div>
</div>

                    </div>
                    <div class="status <?php echo strtolower($partido_rival['gano']) == 'ganó' ? 'win' : 'loss'; ?>">
                        <?php echo strtoupper($partido_rival['gano'][0]); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

        </div>
    </div>
<?php endif; ?>


        <?php
        function to_make_image_name($cat = '')
        {
            // Si $cat no está vacía, eliminar espacios y caracteres especiales
            if (!empty($cat)) {
                $cat = preg_replace('/[^A-Za-z0-9]/', '', $cat);
            } else {
                $cat = 'SinCategoria';
            }
        
            // Construir el nombre del archivo
            $imageName = "Modulo_4_" . $cat . "_ReporteH2H";
        
            // Retornar el nombre generado
            echo $imageName;
        }
        ?>

        <script src="index.js"></script>
        <script src="bg.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', (event) => {
            const datos = {
                    jugador_local_id: <?php echo json_encode($datos['jugador_local_id']); ?>,
                    jugador_rival_id: <?php echo json_encode($datos['jugador_rival_id']); ?>
                };
                let generateButton = document.querySelector('.generate_image'),
                    generateButtonTwo = document.querySelector('.generate_image_two'),
                    inputImage = document.querySelector('#imageUpload'),
                    inputImageTwo = document.querySelector('#imageUploadTwo'),
                    generalDiagram = document.querySelector('.general_canvas');

                function downloadImage(data, filename) {
                    let a = document.createElement('a');
                    a.href = data;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }

                function applyClipPathToCanvas(canvas, clipPaths) {
                    let ctx = canvas.getContext('2d');
                    ctx.save();

                    clipPaths.forEach(({
                        x,
                        y,
                        path
                    }) => {
                        ctx.beginPath();
                        path.forEach(([moveX, moveY], index) => {
                            if (index === 0) {
                                ctx.moveTo(x + moveX, y + moveY);
                            } else {
                                ctx.lineTo(x + moveX, y + moveY);
                            }
                        });
                        ctx.closePath();
                        ctx.clip();
                    });

                    ctx.restore();
                }

                function createCanvasAndDownload(canvas, startX, width, height, targetWidth, targetHeight, filename) {
                    let newCanvas = document.createElement('canvas');
                    newCanvas.width = targetWidth;
                    newCanvas.height = targetHeight;
                    let ctx = newCanvas.getContext('2d');
                    ctx.drawImage(canvas, startX, 0, width, height, 0, 0, targetWidth, targetHeight);

                    // Aplicar manualmente el clip-path después de dibujar la imagen
                    let clipPaths = [{
                            x: 0,
                            y: 0,
                            path: [
                                [0, 0],
                                [targetWidth * 0.99, 0],
                                [targetWidth * 0.87, targetHeight],
                                [0, targetHeight]
                            ]
                        },
                        {
                            x: targetWidth * 0.5,
                            y: 0,
                            path: [
                                [targetWidth * 0.13, 0],
                                [targetWidth, 0],
                                [targetWidth, targetHeight],
                                [targetWidth * 0.01, targetHeight]
                            ]
                        }
                    ];
                    applyClipPathToCanvas(newCanvas, clipPaths);

                    let dataURL = newCanvas.toDataURL('image/png');

                    if (document.body.classList.contains("loading")) {
                        document.body.classList.remove("loading")
                    }

                    downloadImage(dataURL, filename);
                }
                
           function refreshAllImages() {
    const timestamp = new Date().getTime();
    const localPlayerId = datos.jugador_local_id;
    const rivalPlayerId = datos.jugador_rival_id;
    
    // Refresh local player image
    const localStoredImage = localStorage.getItem(`playerLocalImage_${localPlayerId}`);
    if(localStoredImage) {
        const refreshedUrl = localStoredImage.split('?')[0] + '?t=' + timestamp;
        localStorage.setItem(`playerLocalImage_${localPlayerId}`, refreshedUrl);
        
        const localImg = document.querySelector('#imageContainer img');
        if(localImg) {
            localImg.src = refreshedUrl;
        }
        
        const localHeroView = document.getElementById('local');
        if(localHeroView) {
            localHeroView.style.backgroundImage = `url(${refreshedUrl})`;
        }
    }
    
    // Refresh rival player image
    const rivalStoredImage = localStorage.getItem(`playerRivalImage_${rivalPlayerId}`);
    if(rivalStoredImage) {
        const refreshedUrl = rivalStoredImage.split('?')[0] + '?t=' + timestamp;
        localStorage.setItem(`playerRivalImage_${rivalPlayerId}`, refreshedUrl);
        
        const rivalImg = document.querySelector('#imageContainerTwo img');
        if(rivalImg) {
            rivalImg.src = refreshedUrl;
        }
        
        const rivalHeroView = document.getElementById('rival');
        if(rivalHeroView) {
            rivalHeroView.style.backgroundImage = `url(${refreshedUrl})`;
        }
    }
}     

                
generateButton.addEventListener('click', (e) => {
    e.preventDefault();

   
    
    if (!document.body.classList.contains("loading")) {
        document.body.classList.add("loading")
    }
    
    // Force refresh images before generating canvas
    const localImg = document.querySelector('#imageContainer img');
    const rivalImg = document.querySelector('#imageContainerTwo img');
    
    if(localImg && localImg.src) {
        const timestamp = new Date().getTime();
        localImg.src = localImg.src.split('?')[0] + '?t=' + timestamp;
    }
    
    if(rivalImg && rivalImg.src) {
        const timestamp = new Date().getTime();
        rivalImg.src = rivalImg.src.split('?')[0] + '?t=' + timestamp;
    }

    // Short delay to ensure images are refreshed
    setTimeout(() => {
        html2canvas(generalDiagram, {
            scale: 1,
            useCORS: true,  // Add this to handle cross-origin images
            allowTaint: true // Allow processing of tainted canvas
        }).then(canvas => {
            let width = canvas.width;
            let height = canvas.height;
            createCanvasAndDownload(canvas, 0, width, height, 1080, 1080, '<?php to_make_image_name($datos['nombre_categoria']); ?>.jpg');
        });
    }, 500);  // Increased timeout to ensure images load
});
                
            });
        </script>
        <script>
// Función para actualizar la imagen del jugador local
function updateLocalPlayerImage(imageUrl, playerId) {
    // Actualizar localStorage
    localStorage.setItem('playerLocalImage_' + playerId, imageUrl);
    
    // Actualizar imagen en el contenedor principal
    const localContainer = document.getElementById('imageContainer');
    if (localContainer) {
        const localImg = localContainer.querySelector('img');
        if (localImg) {
            localImg.src = imageUrl;
        }
    }
    
    // Actualizar background en la vista hero
    const localHeroView = document.getElementById('local');
    if (localHeroView) {
        localHeroView.style.backgroundImage = `url(${imageUrl})`;
    }
        updateStatusMessage(playerId, document.querySelector('.nombre-jugador')?.textContent || 'Jugador Local', true);

}

// Función para actualizar la imagen del jugador rival
function updateRivalPlayerImage(imageUrl, playerId) {
    // Actualizar localStorage
    localStorage.setItem('playerRivalImage_' + playerId, imageUrl);
    
    // Actualizar imagen en el contenedor principal
    const rivalContainer = document.getElementById('imageContainerTwo');
    if (rivalContainer) {
        const rivalImg = rivalContainer.querySelector('img');
        if (rivalImg) {
            rivalImg.src = imageUrl;
        }
    }
    
    // Actualizar background en la vista hero
    const rivalHeroView = document.getElementById('rival');
    if (rivalHeroView) {
        rivalHeroView.style.backgroundImage = `url(${imageUrl})`;
    }
    
        updateStatusMessage(playerId, document.querySelectorAll('.nombre-jugador')[1]?.textContent || 'Jugador Rival', false);

}

// Función para limpiar el localStorage al cargar la página
function clearStorageOnLoad() {
   
}

// Función para establecer las imágenes iniciales
function setInitialPlayerImages() {
    const localPlayerImage = '<?php echo $datos["jugador_local_imagen"]; ?>';
    const localPlayerGender = '<?php echo $datos["jugador_local_sexo"]; ?>';
    const localPlayerId = <?php echo $datos["jugador_local_id"]; ?>;
    
    const rivalPlayerImage = '<?php echo $datos["jugador_rival_imagen"]; ?>';
    const rivalPlayerGender = '<?php echo $datos["jugador_rival_sexo"]; ?>';
    const rivalPlayerId = <?php echo $datos["jugador_rival_id"]; ?>;

    const storedLocalImage = localStorage.getItem('playerLocalImage_' + localPlayerId);
    console.log(storedLocalImage)
    const storedRivalImage = localStorage.getItem('playerRivalImage_' + rivalPlayerId);
     console.log(storedRivalImage)
     
 
    if (localPlayerImage) {
        updateLocalPlayerImage(localPlayerImage, localPlayerId);
    }else{
        if(storedLocalImage){
         updateLocalPlayerImage(storedLocalImage, localPlayerId);
        }
    }

    // Establecer imagen del jugador rival
    if (rivalPlayerImage) {
        updateRivalPlayerImage(rivalPlayerImage, rivalPlayerId);
    }else{
         if(storedRivalImage){
         updateRivalPlayerImage(storedRivalImage, localPlayerId);
         }
    }

    initializeStatusMessages();

}

// Escuchar eventos de carga y actualización
document.addEventListener('DOMContentLoaded', () => {
    clearStorageOnLoad();
    setInitialPlayerImages();
});

// Actualizar indicadores visuales
function updateImageStatus() {
    const jugadorLocalId = <?php echo json_encode($datos['jugador_local_id']); ?>;
    const jugadorRivalId = <?php echo json_encode($datos['jugador_rival_id']); ?>;
    
    const localStatus = document.getElementById('imageStatus2');
    const rivalStatus = document.getElementById('imageStatus');
    
    if (localStorage.getItem(`playerLocalImage_${jugadorLocalId}`)) {
        if (localStatus) localStatus.textContent = "";
    }
    
    if (localStorage.getItem(`playerRivalImage_${jugadorRivalId}`)) {
        if (rivalStatus) rivalStatus.textContent = "";
    }
}

// Llamar a updateImageStatus después de cualquier cambio en las imágenes
document.addEventListener('DOMContentLoaded', updateImageStatus);
        </script>
        <script>
    const jugadorRivalId = <?php echo json_encode($datos['jugador_rival_id']); ?>;
    const storedImage = localStorage.getItem(`playerRivalImage_${jugadorRivalId}`);

    if (storedImage) {
        document.getElementById('imageStatus').textContent = "";
    }
</script>
 <script>
    const jugadorLocalId = <?php echo json_encode($datos['jugador_local_id']); ?>;
    const storedImage2 = localStorage.getItem(`playerLocalImage_${jugadorLocalId}`);

    if (storedImage2) {
        document.getElementById('imageStatus2').textContent = "";
    }
</script>
 <script src="js/html2canvas.min.js"></script>
 <script>
// Function to update status message for a player
function updateStatusMessage(playerId, playerName, isLocal = true) {
    const containerId = isLocal ? 'local' : 'rival';
    const container = document.getElementById(containerId);
    
    if (!container) return;

    const statusSpan = container.querySelector('.texto-abajo');
    if (!statusSpan) return;

    const hasStoredImage = localStorage.getItem(`player${isLocal ? 'Local' : 'Rival'}Image_${playerId}`);
    const hasBackgroundImage = container.style.backgroundImage && container.style.backgroundImage !== 'none';

    if (hasStoredImage || hasBackgroundImage) {
        statusSpan.textContent = `Ya tiene imagen precargada en el sistema: ${playerName}.`;
    } else {
        statusSpan.textContent = `No tiene imagen precargada en el sistema: ${playerName}.`;
    }
}

function initializeStatusMessages() {
    const localPlayerId = datos.jugador_local_id;
    const rivalPlayerId = datos.jugador_rival_id;
    const localPlayerName = document.querySelector('.nombre-jugador')?.textContent || 'Jugador Local';
    const rivalPlayerName = document.querySelectorAll('.nombre-jugador')[1]?.textContent || 'Jugador Rival';

    updateStatusMessage(localPlayerId, localPlayerName, true);
    updateStatusMessage(rivalPlayerId, rivalPlayerName, false);
}

document.addEventListener('DOMContentLoaded', () => {
    const imageUpload = document.getElementById('imageUpload');
    const imageUploadTwo = document.getElementById('imageUploadTwo');
    
    if (imageUpload) {
        imageUpload.addEventListener('change', () => {
            const localPlayerName = document.querySelector('.nombre-jugador')?.textContent || 'Jugador Local';
            updateStatusMessage(datos.jugador_local_id, localPlayerName, true);
        });
    }
    
    if (imageUploadTwo) {
        imageUploadTwo.addEventListener('change', () => {
            const rivalPlayerName = document.querySelectorAll('.nombre-jugador')[1]?.textContent || 'Jugador Rival';
            updateStatusMessage(datos.jugador_rival_id, rivalPlayerName, false);
        });
    }

    initializeStatusMessages();
});

window.addEventListener('storage', () => {
    initializeStatusMessages();
});
</script>
    <?php endif; ?>

</body>

</html>