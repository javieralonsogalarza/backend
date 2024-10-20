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

    <?php
    // Leer el archivo JSON desde el archivo .txt
    $rutaArchivo = $_GET['json'] ?? '../example1.json';
    $jsonData = file_get_contents($rutaArchivo);
    $datos = json_decode($jsonData, true);

    $validation = 0;
    ?>

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
                        <p>Arrastre y suelte su foto.</p>
                    </label>
                    <input class="input" type="file" id="imageUpload" accept=".jpg, .jpeg, .png">
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
            <button type="button" class="generate_image" disabled>
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

                    <img src="images/logo.png" alt="Logo">
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
        function to_make_image_name($cat = '', $group = '')
        {
            // Si $cat no está vacía
            if (!empty($cat)) {
                // Eliminar espacios y caracteres especiales
                $cat = preg_replace('/[^A-Za-z0-9]/', '', $cat);
            }

            // Si $group no está vacía
            if (!empty($group)) {
                // Eliminar espacios y caracteres especiales
                $group = preg_replace('/[^A-Za-z0-9]/', '', $group);
            }

            echo $cat . $group;
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

                if (generateButton) {
                    generateButton.addEventListener('click', (e) => {
                        e.preventDefault();

                        if (!document.body.classList.contains("loading")) {
                            document.body.classList.add("loading")
                        }

                        setTimeout(() => {
                            html2canvas(generalDiagram, {
                                scale: 1
                            }).then(canvas => {
                                let width = canvas.width;
                                let height = canvas.height;
                                createCanvasAndDownload(canvas, 0, width, height, 1080, 1080, '<?php to_make_image_name($datos['categoria'], $datos['grupo']); ?>.jpg');
                            });
                        }, 100);
                    });
                }
            });
        </script>
    <?php endif; ?>

</body>

</html>