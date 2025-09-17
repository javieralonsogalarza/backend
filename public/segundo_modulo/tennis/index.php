<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // AHORA (Reemplaza la línea anterior con esto)

// 1. Define la ruta a la carpeta de fondos
$directorioFondos = 'images/bg/';

// 2. Busca todos los archivos que terminen en .jpeg dentro de esa carpeta
$archivosFondos = glob($directorioFondos . '*.jpeg');

// 3. Cuenta cuántos archivos se encontraron
$numeroDeFondos = count($archivosFondos);
    // Leer el archivo JSON desde el archivo .txt
    $rutaArchivo = $_GET['json'] ?? '../example1.json';
    $jsonData = file_get_contents($rutaArchivo);
    $datos = json_decode($jsonData, true);

    $validation = 0;
    ?>
    <title>Copa Level Up 2024</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- <script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@latest/dist/face-api.min.js"></script> -->
    <link rel="stylesheet" href="cropper/cropper.css">
    <script src="cropper/cropper.js"></script>
    
   <script>
        // Convertir los datos PHP a un objeto JavaScript
        const datos = <?php echo json_encode([
            'titulo' => $datos['titulo'],
            'torneo' => $datos['torneo'],
            'formato' => $datos['formato'],
            'grupo' => $datos['grupo'],
            'ronda' => $datos['ronda'],
            'categoria' => $datos['categoria'],
            'jugador_ganador_uno' => $datos['jugador_ganador_uno'],
            'jugador_ganador_dos' => $datos['jugador_ganador_dos'],
            'jugador_rival_uno' => $datos['jugador_rival_uno'],
            'jugador_rival_dos' => $datos['jugador_rival_dos'],
            'resultado_ganador' => $datos['resultado_ganador'],
            'resultado_rival' => $datos['resultado_rival'],
            'partido' => $datos['partido'],
            'torneo_categoria_id' => $datos['torneo_categoria_id'],
            'categoria_id' => $datos['categoria_id']
        ]); ?>;
        
        // Agregar validación de datos
        if (!datos) {
            console.error('Error: No se pudieron cargar los datos del partido');
        }
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
                <button type="button" id="clean">
                    Limpiar
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M170.5 51.6L151.5 80l145 0-19-28.4c-1.5-2.2-4-3.6-6.7-3.6l-93.7 0c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80 368 80l48 0 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-8 0 0 304c0 44.2-35.8 80-80 80l-224 0c-44.2 0-80-35.8-80-80l0-304-8 0c-13.3 0-24-10.7-24-24S10.7 80 24 80l8 0 48 0 13.8 0 36.7-55.1C140.9 9.4 158.4 0 177.1 0l93.7 0c18.7 0 36.2 9.4 46.6 24.9zM80 128l0 304c0 17.7 14.3 32 32 32l224 0c17.7 0 32-14.3 32-32l0-304L80 128zm80 64l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16z" />
                    </svg>
                </button>
                <button type="button" id="cut">
                    Recortar
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M448 109.3l54.6-54.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L402.7 64 160 64l0 64 178.7 0L128 338.7 128 32c0-17.7-14.3-32-32-32S64 14.3 64 32l0 32L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l32 0 0 256c0 35.3 28.7 64 64 64l224 0 0-64-178.7 0L384 173.3 384 480c0 17.7 14.3 32 32 32s32-14.3 32-32l0-32 32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-32 0 0-274.7z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="loader_fixed"><span class="loader"></span></div>

 

    <div class="hero_view wh-100vh">
        <div class="hero_view-txt wh-100">
            <h1>Generador <br>de Imágenes</h1>

            <div class="hero_view-test w-100">
                <ul id="testing">
                    <?php if (isset($datos['titulo']) && !empty($datos['titulo'])): $validation++; ?>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                            </svg>
                            <p>Título cargado correctamente.</p>
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
                            <p>Categor&iacute;a cargado correctamente.</p>
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
                            <p>Grupo cargado correctamente.</p>
                        </li>
                    <?php else: ?>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                            </svg>
                            <p>No se encontró el grupo, por lo que se tomará <b>Fase de Grupos</b> por defecto.</p>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($datos['ronda']) && !empty($datos['ronda'])): $validation++; ?>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                            </svg>
                            <p>Ronda cargada correctamente.</p>
                        </li>
                    <?php else: ?>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                            </svg>
                            <p>Error al cargar la ronda.</p>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($datos['jugador_ganador_uno']) && !empty($datos['jugador_ganador_uno']) && isset($datos['jugador_rival_uno']) && !empty($datos['jugador_rival_uno'])): $validation++; ?>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                            </svg>
                            <p>Ambos jugadores cargados correctamente.</p>
                        </li>
                    <?php else: ?>
                        <?php if (!isset($datos['jugador_ganador_uno']) || empty($datos['jugador_ganador_uno'])): $validation++; ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                </svg>
                                <p>No encontramos el jugador 1 cargado.</p>
                            </li>
                        <?php endif; ?>
                        <?php if (!isset($datos['jugador_rival_uno']) || empty($datos['jugador_rival_uno'])): $validation++; ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                </svg>
                                <p>No encontramos el jugador 2 cargado.</p>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <?php if ($validation > 2): ?>
      <div class="w-100 hero_view-input">
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
        <p><?php if(isset($datos['imagen_path']) && !empty($datos['imagen_path'])): ?>Arrastre y suelte su foto.<?php else: ?>Arrastre y suelte su foto.<?php endif; ?></p>
    </label>
    <input class="input" type="file" id="imageUpload" accept=".jpg, .jpeg, .png" <?php if(isset($datos['imagen_path']) && !empty($datos['imagen_path'])): ?>data-json-image="<?php echo htmlspecialchars($datos['imagen_path']); ?>"<?php endif; ?>>
    <button class="delete_preview" data-id="1" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
        </svg>
    </button>
</div>

                <!-- // ------------------------------------------- // -->

                <div class="w-100 hero_view-input" style="margin-top:0">
                    <label for="putBg" class="labelFile">
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
                        <p>Arrastre y suelte su imagen de fondo.</p>
                    </label>
                    <input class="input" type="file" id="putBg" accept=".jpg, .jpeg, .png">
                    <button class="delete_preview" data-id="2" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                        </svg>
                    </button>
                </div>
            <?php endif; ?>


            <!-- <div class="hero_view-bg">
                <input type="file" id="putBg">
                <button>
                    <svg aria-hidden="true" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-width="2" stroke="#fffffff" d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H11M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125" stroke-linejoin="round" stroke-linecap="round"></path>
                        <path stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="#fffffff" d="M17 15V18M17 21V18M17 18H14M17 18H20"></path>
                    </svg>
                    Añadir Fondo
                </button>
            </div> -->

            <canvas id="canvas" style="display:none;"></canvas>
<div class="background-selector-container">
    <h4 style="text-align: center; width: 100%; margin-bottom: 15px;">Selecciona un fondo:</h4>
    <div class="background-options">

        <!-- PRIMERA OPCIÓN: Randomizador con ? -->
        <div class="bg-option randomizer active" data-bg="random" title="Fondo Aleatorio">
            <span style="font-size: 24px; font-weight: bold; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">?</span>
        </div>

        <!-- Luego los fondos normales -->
        <?php for ($i = 1; $i <= $numeroDeFondos; $i++): ?>
            <div 
                class="bg-option" 
                data-bg="bg<?php echo $i; ?>" 
                style="background-image: url('images/bg/bg<?php echo $i; ?>.jpeg');">
            </div>
        <?php endfor; ?>

    </div>
</div>

            <div class="buttons-container" style="display: flex; flex-direction: row; gap: 10px; flex-wrap: wrap; justify-content: center;">
                
                
                <button type="button" class="report_social_media app-button" id="reportSocialMedia">
                    Publicar
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                          <path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l370.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128z" />
                    </svg>
                </button>
                
                <button type="button" class="generate_image app-button" disabled>
                    Generar
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l370.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128z" />
                    </svg>
                </button>
            </div>
            <!-- Contenedor dedicado para mensajes de publicación -->
            <div id="publicationMessages" style="width: 100%; text-align: center; margin-top: 10px; clear: both; display: block; min-height: 30px;"></div>

            <br><br><br><br><br><br><br>
        </div>
        <div class="hero_view-img wh-100">
            <img src="images/bg_mod2.jpg">
        </div>
    </div>

    <?php if (!empty($datos)) : ?>
        <div class="canvas_scroll w-100">
            <div class="canvas general_canvas bg<?php echo rand(1, 14); ?>">
                <div class="canvas_top">

                    <p class="canvas_format">
                        <?php echo 'Categoría <br>'.htmlspecialchars($datos['categoria'] ?? ''); ?>

                        <?php if (!empty($datos['grupo']) && !is_null($datos['grupo'])): ?>
                            <small><?php echo $datos['grupo']; ?></small>
                        <?php endif; ?>
                    </p>
                    <p class="canvas_rounds">
	    		<?php 
				if( is_null($datos['grupo']) || empty($datos['grupo']) ){
					if(!is_null($datos['ronda']) && !empty($datos['ronda']) ){
						if($datos['ronda'] == 16){
							echo 'Ronda de 32';
						}
						if($datos['ronda'] == 8){
							echo 'Octavos de final';
						}
						if($datos['ronda'] == 4){
							echo 'Cuartos de final';
						}
						if($datos['ronda'] == 2){
							echo 'Semifinal';
						}
						if($datos['ronda'] == 1){
							echo 'Final';
						}
						if($datos['ronda'] == "Fase de grupos"){
							echo 'Fase de Grupos';
						}
					}else{
						echo 'Fase de Grupos';
					}
				}else{
					echo 'Fase de Grupos';
				}
			?>
                    	</p>

                   <img src="<?php echo $datos['imagen_comunidad']; ?>" alt="Logo" class="canvas_flag">

                    <div class="canvas_top-title">
                        <p><?php echo htmlspecialchars($datos['torneo'] ?? ''); ?></p>
                        <h1>Resultado del partido</h1>
                    </div>
                    <div class="canvas_image" id="imageContainer">
                        <img src="" title="Foto" alt="Foto">
                    </div>
                </div>
                <div class="canvas_scores">
                    <div class="canvas_scores-top canvas_scores-points">
                        <?php if (isset($datos['jugador_ganador_dos']) && !empty($datos['jugador_ganador_dos'])): ?>
                            <div class="masked">
                                <p class="less"><?php echo $datos['jugador_ganador_uno'] . ' + ' . $datos['jugador_ganador_dos']; ?></p>
                            </div>
                            <div class="canvas_scores-grid less">
                                <?php
                                if (isset($datos['resultado_ganador']) && !empty($datos['resultado_ganador'])) {
                                    $resultado_ganador = $datos['resultado_ganador'];
                                    $resultado_ganador_array = explode('/', $resultado_ganador);

                                    // Rellena el array hasta tener 3 elementos, si es necesario.
                                    while (count($resultado_ganador_array) < 3) {
                                        $resultado_ganador_array[] = ''; // O algún valor por defecto si prefieres
                                    }

                                    // Genera los elementos <p> para cada valor en el array.
                                    foreach ($resultado_ganador_array as $resultado) {
                                        if (!empty($resultado) || $resultado == 0) {
                                            echo '<p class="span">' . htmlspecialchars($resultado) . '</p>';
                                        } else {
                                            echo '<p class="span you_dont_see_me">0</p>';
                                        }
                                    }
                                } else {
                                    // Rellena el array hasta tener 3 elementos si no hay datos.
                                    for ($i = 0; $i < 3; $i++) {
                                        echo '<p class="span you_dont_see_me">0</p>'; // O algún valor por defecto si prefieres
                                    }
                                }
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="masked">
                                <p><?php echo $datos['jugador_ganador_uno'] ?? ''; ?></p>
                            </div>
                            <div class="canvas_scores-grid">
                                <?php
                                if (isset($datos['resultado_ganador']) && !empty($datos['resultado_ganador'])) {
                                    $resultado_ganador = $datos['resultado_ganador'];
                                    $resultado_ganador_array = explode('/', $resultado_ganador);

                                    // Rellena el array hasta tener 3 elementos, si es necesario.
                                    while (count($resultado_ganador_array) < 3) {
                                        $resultado_ganador_array[] = ''; // O algún valor por defecto si prefieres
                                    }

                                    // Genera los elementos <p> para cada valor en el array.
                                    foreach ($resultado_ganador_array as $resultado) {
                                        if (!empty($resultado) || $resultado == 0) {
                                            echo '<p class="span">' . htmlspecialchars($resultado) . '</p>';
                                        } else {
                                            echo '<p class="span you_dont_see_me">0</p>';
                                        }
                                    }
                                } else {
                                    // Rellena el array hasta tener 3 elementos si no hay datos.
                                    for ($i = 0; $i < 3; $i++) {
                                        echo '<p class="span you_dont_see_me">0</p>'; // O algún valor por defecto si prefieres
                                    }
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="canvas_scores-bottom canvas_scores-points">
                        <?php if (isset($datos['jugador_ganador_dos']) && !empty($datos['jugador_ganador_dos'])): ?>
                            <div class="masked">
                                <p class="less"><?php echo $datos['jugador_rival_uno'] . ' + ' . $datos['jugador_rival_dos']; ?></p>
                            </div>
                            <div class="canvas_scores-grid less">
                                <?php
                                if (isset($datos['resultado_rival']) && !empty($datos['resultado_rival'])) {
                                    $resultado_rival = $datos['resultado_rival'];
                                    $resultado_rival_array = explode('/', $resultado_rival);

                                    // Rellena el array hasta tener 3 elementos, si es necesario.
                                    while (count($resultado_rival_array) < 3) {
                                        $resultado_rival_array[] = ''; // O algún valor por defecto si prefieres
                                    }

                                    // Genera los elementos <p> para cada valor en el array.
                                    foreach ($resultado_rival_array as $resultado) {
                                        if (!empty($resultado) || $resultado == 0) {
                                            echo '<p class="span">' . htmlspecialchars($resultado) . '</p>';
                                        } else {
                                            echo '<p class="span you_dont_see_me">0</p>';
                                        }
                                    }
                                } else {
                                    // Rellena el array hasta tener 3 elementos si no hay datos.
                                    for ($i = 0; $i < 3; $i++) {
                                        echo '<p class="span you_dont_see_me">0</p>'; // O algún valor por defecto si prefieres
                                    }
                                }
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="masked">
                                <p><?php echo $datos['jugador_rival_uno'] ?? ''; ?></p>
                            </div>
                            <div class="canvas_scores-grid">
                                <?php
                                if (isset($datos['resultado_rival']) && !empty($datos['resultado_rival'])) {
                                    $resultado_rival = $datos['resultado_rival'];
                                    $resultado_rival_array = explode('/', $resultado_rival);

                                    // Rellena el array hasta tener 3 elementos, si es necesario.
                                    while (count($resultado_rival_array) < 3) {
                                        $resultado_rival_array[] = ''; // O algún valor por defecto si prefieres
                                    }

                                    // Genera los elementos <p> para cada valor en el array.
                                    foreach ($resultado_rival_array as $resultado) {
                                        if (!empty($resultado) || $resultado == 0) {
                                            echo '<p class="span">' . htmlspecialchars($resultado) . '</p>';
                                        } else {
                                            echo '<p class="span you_dont_see_me">0</p>';
                                        }
                                    }
                                } else {
                                    // Rellena el array hasta tener 3 elementos si no hay datos.
                                    for ($i = 0; $i < 3; $i++) {
                                        echo '<p class="span you_dont_see_me"></p>'; // O algún valor por defecto si prefieres
                                    }
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        function to_make_image_name($cat = '', $group = '', $round = '')
        {
             // Si $cat no está vacía, eliminar espacios y caracteres especiales
            if (!empty($cat)) {
                $cat = preg_replace('/[^A-Za-z0-9]/', '', $cat);
            }
        
            // Si $group no está vacía, eliminar espacios y caracteres especiales
            if (!empty($group)) {
                $group = preg_replace('/[^A-Za-z0-9]/', '', $group);
            }
        
            // Si $round no está vacía, eliminar espacios y caracteres especiales
            if (!empty($round)) {
                $round = preg_replace('/[^A-Za-z0-9]/', '', $round);
            }


            echo "Modulo_2_" . $cat . "_" . $group . "_" . $round;
        }
        ?>

        <script src="js/html2canvas.min.js"></script>
        <script src="index.js"></script>
        <script src="bg.js"></script>
        <script>
           document.addEventListener('DOMContentLoaded', (event) => {

    let generateButton = document.querySelector('.generate_image'),
        inputImage = document.querySelector('#imageUpload'),
        generalDiagram = document.querySelector('.general_canvas');

    function downloadImage(data, filename) {
        let a = document.createElement('a');
        a.href = data;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        // Copiar la imagen al portapapeles
        fetch(data)
            .then(res => res.blob())
            .then(blob => {
                const item = new ClipboardItem({ 'image/png': blob });
                navigator.clipboard.write([item]);
            })
            .catch(err => console.error('Error al copiar la imagen al portapapeles:', err));
    }

    function applyClipPathToCanvas(canvas, clipPaths) {
        let ctx = canvas.getContext('2d');
        ctx.save();

        clipPaths.forEach(({ x, y, path }) => {
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
        let clipPaths = [
            {
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
            document.body.classList.remove("loading");
        }

        downloadImage(dataURL, filename);
    }

    if (generateButton) {
        generateButton.addEventListener('click', (e) => {
            e.preventDefault();

            if (!document.body.classList.contains("loading")) {
                document.body.classList.add("loading");
            }

            setTimeout(() => {
                html2canvas(generalDiagram, {
                    scale: 1
                }).then(canvas => {
                    let width = canvas.width;
                    let height = canvas.height;
                    createCanvasAndDownload(canvas, 0, width, height, 1080, 1080, '<?php to_make_image_name($datos['categoria'], $datos['grupo'],$datos['ronda']); ?>.jpg');
                });
            }, 100);
        });
    }
});


        </script>
    <?php endif; ?>
    
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const imageUpload = document.getElementById('imageUpload');
    const heroViewInput = document.querySelector('.hero_view-input');
    
    // Verificar si existe imagen en el contenedor
    if (!document.querySelector('#imageContainer img')) {
        // Crear el elemento img si no existe
        const img = document.createElement('img');
        img.id = 'imageContainer';
        heroViewInput.insertBefore(img, imageUpload);
    }
    
    // Obtener la referencia a la imagen
    const image4Photo = document.querySelector('#imageContainer img');

    // Función para cargar y mostrar la imagen
    function loadAndDisplayImage(imagePath) {
        // Añadir un parámetro de timestamp para evitar el caché
        const cacheBuster = imagePath.includes('?') ? '&t=' + Date.now() : '?t=' + Date.now();
        const pathWithCacheBuster = imagePath + cacheBuster;
        
        image4Photo.src = pathWithCacheBuster;
        
        // Agregar clase with_preview
        if (!heroViewInput.classList.contains('with_preview')) {
            heroViewInput.classList.add('with_preview');
        }
        
        // Establecer el background
        heroViewInput.style.background = `url(${pathWithCacheBuster})`;
        
        // Habilitar el botón de generar
        const generateButton = document.querySelector('.generate_image');
        if (generateButton) {
            generateButton.disabled = false;
        }

        // Verificar que la imagen se cargó correctamente
        image4Photo.onload = function() {
            if (document.querySelector('.generate_image')) {
                document.querySelector('.generate_image').disabled = false;
            }
        };
    }

    // Primero intentar cargar desde localStorage
    const partidoId = datos['partido']; // Asegúrate de que 'datos' esté disponible
    const storedImagePath = localStorage.getItem(`imagen_partido_${partidoId}`);
    
    if (storedImagePath) {
        // Si hay una imagen en localStorage, usarla
        loadAndDisplayImage(storedImagePath);
    } 
    // Si no hay imagen en localStorage, intentar cargar desde JSON
    else if (imageUpload && imageUpload.hasAttribute('data-json-image')) {
        const jsonImagePath = imageUpload.getAttribute('data-json-image');
        if (jsonImagePath) {
            loadAndDisplayImage(jsonImagePath);
            
            // Opcionalmente, guardar también en localStorage
            localStorage.setItem(`imagen_partido_${partidoId}`, jsonImagePath);
        }
    }

    // Opcional: Verificar si la imagen en localStorage es accesible
    if (storedImagePath) {
        // Añadir parámetro para evitar caché si no existe ya
        const cacheBusterPath = storedImagePath.includes('?') ? 
            storedImagePath : 
            storedImagePath + '?t=' + Date.now();
            
        fetch(cacheBusterPath)
            .then(response => {
                if (!response.ok) {
                    // Si la imagen en localStorage no es accesible, intentar con JSON
                    if (imageUpload && imageUpload.hasAttribute('data-json-image')) {
                        const jsonImagePath = imageUpload.getAttribute('data-json-image');
                        if (jsonImagePath) {
                            loadAndDisplayImage(jsonImagePath);
                            localStorage.setItem(`imagen_partido_${partidoId}`, jsonImagePath);
                        }
                    } else {
                        // Si no hay imagen en JSON tampoco, limpiar localStorage
                        localStorage.removeItem(`imagen_partido_${partidoId}`);
                    }
                }
            })
            .catch(() => {
                // Manejar error de fetch
                console.log('Error al verificar la imagen en localStorage');
            });
    }
    
    // nuevo
    const reportButton = document.getElementById('reportSocialMedia');
    
    if (reportButton) {
        // Get the partido ID and other necessary data
        const partidoId = datos['partido'];
        const categoriaId = datos['categoria_id'];
        const torneoCategoriaId = datos['torneo_categoria_id'];
        
        // Check if this partido has already been reported as published in localStorage
        const isReported = localStorage.getItem(`reported_partido_${partidoId}`);
        
        // Usar el contenedor de mensajes existente
        const messageContainer = document.getElementById('publicationMessages');
        
        if (isReported === 'true') {
            // If found in localStorage, mark as already published
            reportButton.classList.add('reported');
            reportButton.innerHTML = 'Despublicar <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>';
        } else {
            // If not found in localStorage, check with the API
            checkPublicationStatus(partidoId, categoriaId, torneoCategoriaId);
        }
        
        reportButton.addEventListener('click', function() {
            // Limpiar mensajes anteriores
            const messageContainer = document.getElementById('publicationMessages');
            if (messageContainer) {
                messageContainer.innerHTML = '';
            }
            
            const isCurrentlyReported = reportButton.classList.contains('reported');
            
            // Guardamos el contenido original para restaurarlo en caso de error
            const originalButtonContent = reportButton.innerHTML;
            
            // Mostrar estado de carga con texto diferente según la operación
            if (isCurrentlyReported) {
                reportButton.innerHTML = 'Despublicando... <svg class="spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z"/></svg>';
            } else {
                reportButton.innerHTML = 'Publicando... <svg class="spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z"/></svg>';
            }
            reportButton.disabled = true;
            
            // Preparar los datos para enviar con un indicador de la acción
            const reportData = {
                partido_id: partidoId,
                categoria_id: categoriaId,
                torneo_categoria_id: torneoCategoriaId,
                action: reportButton.classList.contains('reported') ? 'unpublish' : 'publish'
            };
            
            // Determinar la URL del endpoint basado en la acción
            const endpoint = reportButton.classList.contains('reported') 
                ? '/api/despublicarReporteJsonGenerado' 
                : '/api/actualizarReporteJsonGenerado';
            
            // Enviar la solicitud al servidor
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(reportData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al enviar la información');
                }
                return response.json();
            })
            .then(data => {
                const isUnpublishing = reportButton.classList.contains('reported');
                const messageContainer = document.getElementById('publicationMessages');
                
                if (isUnpublishing) {
                    // Si estamos despublicando
                    reportButton.classList.remove('reported', 'report_success');
                    reportButton.classList.add('unreported', 'report_unpublish');
                    reportButton.innerHTML = 'Publicar <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/></svg>';
                    reportButton.disabled = false;
                    
                    // Eliminar de localStorage para que no se recuerde como publicado
                    localStorage.removeItem(`reported_partido_${partidoId}`);
                    
                    // Mostrar mensaje de despublicación
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'unpublish-message';
                    messageDiv.textContent = 'Imagen despublicada correctamente';
                    messageDiv.style.color = '#28a745';
                    messageDiv.style.fontWeight = 'bold';
                    messageDiv.style.margin = '10px 0';
                    messageContainer.appendChild(messageDiv);
                } else {
                    // Si estamos publicando
                    reportButton.classList.remove('unreported', 'report_unpublish');
                    reportButton.classList.add('reported', 'report_success');
                    reportButton.innerHTML = 'Despublicar <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>';
                    reportButton.disabled = false;
                    
                    // Guardar en localStorage para recordar que este partido fue reportado
                    localStorage.setItem(`reported_partido_${partidoId}`, 'true');
                    
                    // Mostrar mensaje de éxito
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'success-message';
                    messageDiv.textContent = 'Imagen marcada como publicada correctamente';
                    messageDiv.style.color = '#28a745';
                    messageDiv.style.fontWeight = 'bold';
                    messageDiv.style.margin = '10px 0';
                    messageContainer.appendChild(messageDiv);
                }
                
                // Eliminar el mensaje después de unos segundos
                setTimeout(() => {
                    if (messageContainer) {
                        messageContainer.innerHTML = '';
                    }
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Restablecer el estado del botón en caso de error
                reportButton.innerHTML = originalButtonContent;
                reportButton.disabled = false;
                
                // Mostrar mensaje de error
                const messageContainer = document.getElementById('publicationMessages');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                
                if (reportButton.classList.contains('reported')) {
                    errorDiv.textContent = 'Ocurrió un error al despublicar la imagen';
                } else {
                    errorDiv.textContent = 'Ocurrió un error al publicar la imagen';
                }
                
                errorDiv.style.color = '#dc3545';
                errorDiv.style.fontWeight = 'bold';
                errorDiv.style.margin = '10px 0';
                messageContainer.appendChild(errorDiv);
                
                // Eliminar el mensaje después de unos segundos
                setTimeout(() => {
                    if (messageContainer) {
                        messageContainer.innerHTML = '';
                    }
                }, 3000);
            });
        });
    }
    
    // Function to check publication status with the API
    function checkPublicationStatus(partidoId, categoriaId, torneoCategoriaId) {
        // Show loading state on the button
        if (reportButton) {
            reportButton.innerHTML = 'Verificando... <span class="loader"></span>';
            reportButton.disabled = true;
        }
        
        // Prepare the data to send
        const checkData = {
            partido_id: partidoId,
            categoria_id: categoriaId,
            torneo_categoria_id: torneoCategoriaId
        };
        
        // Send the request to check with the API
        fetch('/api/verificarReporteJsonGenerado', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(checkData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al verificar estado de publicación');
            }
            return response.json();
        })
        .then(data => {
            if (data.reporteGenerado) {
                // Si ya está publicado según la API, actualizar el estado del botón
                reportButton.classList.add('reported');
                reportButton.innerHTML = 'Despublicar <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>';
                
                // Guardar en localStorage para futuras referencias
                localStorage.setItem(`reported_partido_${partidoId}`, 'true');
            } else {
                // If not published, restore the original button state
                reportButton.innerHTML = 'Publicar <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/></svg>';
                reportButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error verificando estado:', error);
            // On error, restore the button to a usable state
            if (reportButton) {
                reportButton.innerHTML = 'Publicar <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/></svg>';
                reportButton.disabled = false;
            }
        });
    }
});
</script>

<script>
// Reemplaza tu JavaScript actual de selección de fondos con esto:
document.addEventListener('DOMContentLoaded', function() {
    const bgOptionsContainer = document.querySelector('.background-options');
    const mainCanvas = document.querySelector('.general_canvas');
    
    // Número total de fondos disponibles (sin contar el randomizador)
    const totalBackgrounds = <?php echo $numeroDeFondos; ?>;
    
    // Colores aleatorios para cuando no hay imagen de fondo
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

    if (bgOptionsContainer && mainCanvas) {
        bgOptionsContainer.addEventListener('click', function(e) {
            const selectedOption = e.target.closest('.bg-option');
            if (!selectedOption) return;

            // Obtiene la clase de fondo del atributo data
            const bgClass = selectedOption.dataset.bg;

            if (bgClass === 'random') {
                // Si se seleccionó el randomizador
                handleRandomBackground();
            } else {
                // Si se seleccionó un fondo específico
                applySpecificBackground(bgClass, selectedOption);
            }
        });
    }

    function handleRandomBackground() {
        // Decidir aleatoriamente si usar una imagen de fondo o un color
        const useImageBackground = Math.random() > 0.3; // 70% probabilidad de usar imagen
        
        if (useImageBackground && totalBackgrounds > 0) {
            // Usar una imagen de fondo aleatoria
            const randomNumber = Math.floor(Math.random() * totalBackgrounds) + 1;
            const randomBgClass = `bg${randomNumber}`;
            
            // 1. Quita todas las clases de fondo existentes
            mainCanvas.className = mainCanvas.className.replace(/bg\d+/g, '').trim();
            
            // 2. Quita cualquier fondo personalizado que se haya subido
            mainCanvas.style.background = '';
            
            // 3. Añade la nueva clase de fondo
            mainCanvas.classList.add(randomBgClass);
            
        } else {
            // Usar un color/gradiente aleatorio
            const randomColor = randomColors[Math.floor(Math.random() * randomColors.length)];
            
            // 1. Quita todas las clases de fondo existentes
            mainCanvas.className = mainCanvas.className.replace(/bg\d+/g, '').trim();
            
            // 2. Aplica el color/gradiente aleatorio
            mainCanvas.style.background = randomColor;
        }

        // 4. Actualizar el estado visual del selector - mantener el randomizador como activo
        document.querySelectorAll('.bg-option').forEach(opt => opt.classList.remove('active'));
        document.querySelector('[data-bg="random"]').classList.add('active');
    }

    function applySpecificBackground(bgClass, selectedOption) {
        // 1. Quita todas las clases de fondo existentes
        mainCanvas.className = mainCanvas.className.replace(/bg\d+/g, '').trim();
        
        // 2. Quita cualquier fondo personalizado que se haya subido
        mainCanvas.style.background = '';
        
        // 3. Añade la nueva clase de fondo
        mainCanvas.classList.add(bgClass);

        // 4. Actualiza el estado visual del selector
        document.querySelectorAll('.bg-option').forEach(opt => opt.classList.remove('active'));
        selectedOption.classList.add('active');
    }

    // Aplicar un fondo aleatorio al cargar la página ya que el randomizador está activo por defecto
    handleRandomBackground();
});
</script>

</body>

</html>