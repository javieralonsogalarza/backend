<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Mostrar Datos JSON</title>
</head>

<body>

    <?php
    $noAppear = false;

    // Leer el archivo JSON desde el archivo .txt
    $rutaArchivo = $_GET['json'] ?? 'json_ronda32.txt';
    $jsonData = file_get_contents($rutaArchivo);
    $datos = json_decode($jsonData, true);

    $validacion = 0;
    ?>

    <div class="hero_view wh-100vh">
        <div class="hero_view-grid">
            <div class="hero_view-content">
                <h1>Generador <br>de Im&aacute;genes</h1>
                <div class="hero_view-test">
                    <ul>
                        <?php if (!empty($datos)) : $validacion++; ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                </svg>
                                <p>JSON cargado y con datos</p>
                            </li>

                            <?php if (isset($datos['torneo']) && !empty($datos['torneo'])) : $validacion++; ?>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                    </svg>
                                    <p>El torneo es: <?php echo $datos['torneo']; ?></p>
                                </li>
                            <?php else : ?>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                    </svg>
                                    <p>Problemas al leer el nombre del torneo.</p>
                                </li>
                            <?php endif; ?>

                            <?php if (isset($datos['llaves']) && !empty($datos['llaves'])) : $validacion++;
                                $keys = $datos['llaves']; ?>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                    </svg>
                                    <p>Llaves llenas</p>
                                    <ul>
                                        <?php if (isset($keys['ronda32']) && !empty($keys['ronda32'])) : ?>
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                                </svg>
                                                <p>Tiene ronda de 32 jugadores</p>
                                            </li>
                                        <?php else : ?>
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                                </svg>
                                                <p>No tiene ronda de 32 jugadores</p>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (isset($keys['octavos']) && !empty($keys['octavos'])) : ?>
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                                </svg>
                                                <p>Tiene octavos de final</p>
                                            </li>
                                        <?php else : ?>
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                                </svg>
                                                <p>No tiene octavos de final</p>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (isset($keys['cuartos']) && !empty($keys['cuartos'])) : ?>
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                                </svg>
                                                <p>Tiene cuartos de final</p>
                                            </li>
                                        <?php else : ?>
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                                </svg>
                                                <p>No tiene cuartos de final</p>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (isset($keys['semifinal']) && !empty($keys['semifinal'])) : ?>
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                                </svg>
                                                <p>Tiene semifinal</p>
                                            </li>
                                        <?php else : ?>
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                                </svg>
                                                <p>No tiene semifinal</p>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (isset($keys['final']) && !empty($keys['final'])) : ?>
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                                </svg>
                                                <p>Tiene final</p>
                                            </li>
                                        <?php else : ?>
                                            <li>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                                </svg>
                                                <p>No tiene final</p>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            <?php else : ?>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                    </svg>
                                    <p>No se han encontrado llaves en el JSON</p>
                                </li>
                            <?php endif; ?>

                            <?php if (isset($datos['ronda']) && !empty($datos['ronda'])) : ?>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                    </svg>
                                    <p>Nro. de Llaves: <?php echo $datos['ronda']; ?></p>
                                </li>
                            <?php else : ?>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                    </svg>
                                    <p>Problemas al leer el n&uacute;mero de rondas.</p>
                                </li>
                            <?php endif; ?>

                            <?php if (isset($datos['ganador']) && !empty($datos['ganador'])) : ?>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
                                    </svg>
                                    <p>Existe un ganador del torneo (<?php echo $datos['ganador']; ?>)</p>
                                </li>
                            <?php else : ?>
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                    </svg>
                                    <p>No hay informaci&oacute;n acerca del ganador del torneo.</p>
                                </li>
                            <?php endif; ?>

                        <?php else : ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon uncheck" viewBox="0 0 512 512">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                                </svg>
                                <p>Problema al cargar el JSON</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="hero_view-selectable">
                    <div class="custom-select">
                        <select id="source">
                            <option value=" ">Seleccionar formato</option>
                            <option value="igl">Instagram Izquierda</option>
                            <option value="igr">Instagram Derecha</option>
                            <option value="fb">Facebook</option>
                        </select>
                    </div>
                    <p></p>
                </div>

                <div class="w-100 toggle">
                    <div class="hero_view-for_bg">
                        <button type="button" id="deleteBg" class="remove_bg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368" />
                            </svg>
                        </button>
                        <label for="file" class="labelFile">
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
                            <p>Arrastra y suelta tu fondo aquí o haz clic para seleccionar uno</p>
                        </label>
                        <input class="input" name="text" id="file" type="file" />
                    </div>
                    <div class="hero_view-button-generate">
                        <button type="button" id="generate_button">
                            <span>Descargar</span>
                        </button>
                    </div>
                </div>

                <?php if ($noAppear && !empty($datos) && $validacion > 2) : ?>
                    <div class="social-login-icons" style="gap:4px">
                        <button type="button" id="instagramLeft" class="socialcontainer" title="Instagram Izquierda">
                            <div class="social-icon-2">
                                <svg fill="white" class="svgIcon" viewBox="0 0 448 512" height="1.5em" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path>
                                </svg>
                            </div>
                            <div class="social-icon-2">
                                <svg fill="white" xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512" height="1.5em">
                                    <path d="M80 320V144a32 32 0 0132-32h0a32 32 0 0132 32v112M144 256V80a32 32 0 0132-32h0a32 32 0 0132 32v160M272 241V96a32 32 0 0132-32h0a32 32 0 0132 32v224M208 240V48a32 32 0 0132-32h0a32 32 0 0132 32v192" fill="none" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" />
                                    <path d="M80 320c0 117.4 64 176 152 176s123.71-39.6 144-88l52.71-144c6.66-18.05 3.64-34.79-11.87-43.6h0c-15.52-8.82-35.91-4.28-44.31 11.68L336 320" fill="none" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" />
                                </svg>
                            </div>
                        </button>
                        <button type="button" id="facebook" class="socialcontainer" title="Facebook">
                            <div class="social-icon-3">
                                <svg viewBox="0 0 384 512" fill="white" height="1.6em" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5c16.3 0 29.4 .4 37 1.2V7.9C291.4 4 256.4 0 236.2 0C129.3 0 80 50.5 80 159.4v42.1H14v97.8H80z"></path>
                                </svg>
                            </div>
                            <div class="social-icon-3">
                                <svg viewBox="0 0 384 512" fill="white" height="1.6em" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5c16.3 0 29.4 .4 37 1.2V7.9C291.4 4 256.4 0 236.2 0C129.3 0 80 50.5 80 159.4v42.1H14v97.8H80z"></path>
                                </svg>
                            </div>
                        </button>
                        <button type="button" id="instagramRight" class="socialcontainer" title="Instagram Derecha">
                            <div class="social-icon-2">
                                <svg fill="white" class="svgIcon" viewBox="0 0 448 512" height="1.5em" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path>
                                </svg>
                            </div>
                            <div class="social-icon-2">
                                <svg fill="white" xmlns="http://www.w3.org/2000/svg" class="ionicon" height="1.5em" viewBox="0 0 512 512">
                                    <path d="M432 320V144a32 32 0 00-32-32h0a32 32 0 00-32 32v112M368 256V80a32 32 0 00-32-32h0a32 32 0 00-32 32v160M240 241V96a32 32 0 00-32-32h0a32 32 0 00-32 32v224M304 240V48a32 32 0 00-32-32h0a32 32 0 00-32 32v192" fill="none" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" />
                                    <path d="M432 320c0 117.4-64 176-152 176s-123.71-39.6-144-88L83.33 264c-6.66-18.05-3.64-34.79 11.87-43.6h0c15.52-8.82 35.91-4.28 44.31 11.68L176 320" fill="none" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" />
                                </svg>
                            </div>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="hero_view-image">
                <img src="images/bg_mod1.jpeg">
            </div>
        </div>
    </div>

    <?php if (!empty($datos) && $validacion > 2) : ?>
        <?php
        // Obtener los datos de las llaves
        $llaves = $datos['llaves'];
        $start_number = 8;

        if (isset($datos['ronda']) && !empty($datos['ronda'])) {
            if ($datos['ronda'] == 32) {
                $start_number = 8;
            } elseif ($datos['ronda'] == 16) {
                $start_number = 4;
            } elseif ($datos['ronda'] == 8) {
                $start_number = 2;
            } elseif ($datos['ronda'] == 4) {
                $start_number = 1;
            }
        }

        $text_cat = $datos['categoria'];
        $canvas_class = '';
        $palabraCampeon = "Campeón";

        if (strpos($text_cat, "Doble")) {
            $canvas_class = 'doubles';
            $palabraCampeon = "Campeones";
            
            if (strpos($text_cat, "Dama")) {
                $palabraCampeon = "Campeonas";
            }
        }else{
            if (strpos($text_cat, "Dama")) {
                $palabraCampeon = "Campeona";
            }else{
                $palabraCampeon = "Campeón";
            }
        }


        ?>

        <div class="canvas_scroll w-100">
            <div class="canvas ig <?php echo $canvas_class ?? ''; ?>" data-first-round="<?php echo $start_number; ?>" id="diagram">

                <?php if ($start_number != 1) : ?>

                    <?php if (isset($llaves['ronda32']) && !empty($llaves['ronda32'])) : $n = 0; ?>
                        <div class="canvas_col-8">
                            <?php foreach ($llaves['ronda32'] as $bloque => $partidos) : ?>
                                <?php foreach ($partidos as $partido) : ?>
                                    <div class="match" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($n == 8) {
                                        echo '</div><div class="canvas_col-8">';
                                    } ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php if ($start_number == 8) : ?>
                            <div class="canvas_col-8">
                                <?php for ($i = 0; $i < 16; $i++) {
                                    echo '<div class="match" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 7) {
                                        echo '</div><div class="canvas_col-8">';
                                    }
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($llaves['octavos'])) : $n = 0; ?>
                        <div class="canvas_col-4">
                            <?php foreach ($llaves['octavos'] as $bloque => $partidos) : ?>
                                <?php foreach ($partidos as $partido) : ?>
                                    <div class="match" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($n == 4) {
                                        echo '</div><div class="canvas_col-4">';
                                    } ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php if ($start_number > 2) : ?>
                            <div class="canvas_col-4">
                                <?php for ($i = 0; $i < 8; $i++) {
                                    echo '<div class="match" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 3) {
                                        echo '</div><div class="canvas_col-4">';
                                    }
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($llaves['cuartos'])) : $n = 0; ?>
                        <div class="canvas_col-2">
                            <?php
                            $bloque_map = [
                                "bloque_uno" => 0,
                                "bloque_tres" => 2,
                                "bloque_dos" => 1,
                                "bloque_cuatro" => 3
                            ];

                            // Ordenar los bloques según el mapeo
                            $partidos_ordenados = [];
                            foreach ($bloque_map as $bloque => $index) {
                                if (isset($llaves['cuartos'][$bloque])) {
                                    $partidos_ordenados[$index] = $llaves['cuartos'][$bloque];
                                }
                            }

                            // var_dump($partidos_ordenados);
                            ?>
                            <?php foreach ($partidos_ordenados as $partidos) : ?>
                                <?php foreach ($partidos as $partido) : ?>
                                    <div class="match" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($n == 2) {
                                        echo '</div><div class="canvas_col-2">';
                                    } ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php if ($start_number > 1) : ?>
                            <div class="canvas_col-2">
                                <?php for ($i = 0; $i < 4; $i++) {
                                    echo '<div class="match" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 1) {
                                        echo '</div><div class="canvas_col-2">';
                                    }
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($llaves['semifinal'])) : $n = 0; ?>
                        <div class="canvas_col-1">
                            <?php foreach ($llaves['semifinal'] as $bloque => $partidos) : ?>
                                <?php if (!is_null($partidos)) : ?>
                                    <?php foreach ($partidos as $partido) : ?>
                                        <div class="match <?php if ($start_number == 1) {
                                                                echo 'separate';
                                                            } ?>" data-n="<?php echo $n++; ?>">
                                            <div class="match_result">
                                                <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_table">
                                                <div class="match_player">
                                                    <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                                </div>
                                                <div class="match_player">
                                                    <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($n == 1) {
                                            echo '</div><div class="canvas_col-1">';
                                        } ?>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="match separate" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p></p>
                                            </div>
                                            <div class="match_player">
                                                <p></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($n == 1) {
                                        echo '</div><div class="canvas_col-1">';
                                    } ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php if ($start_number == 1) : ?>
                            <div class="canvas_col-1">
                                <?php for ($i = 0; $i < 2; $i++) {
                                    echo '<div class="match" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 0) {
                                        echo '</div><div class="canvas_col-1">';
                                    }
                                } ?>
                            </div>
                        <?php else : ?>
                            <div class="canvas_col-1">
                                <?php for ($i = 0; $i < 2; $i++) {
                                    echo '<div class="match separate" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 0) {
                                        echo '</div><div class="canvas_col-1">';
                                    }
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php else : ?>

                    <?php if (!empty($llaves['semifinal'])) : $n = 0; ?>
                        <div class="canvas_col-2 last_format_round">
                            <?php foreach ($llaves['semifinal'] as $bloque => $partidos) : ?>
                                <?php if (!is_null($partidos)) : ?>
                                    <?php foreach ($partidos as $partido) : ?>
                                        <div class="match <?php if ($start_number == 1) {
                                                                echo 'separate';
                                                            } ?>" data-n="<?php echo $n++; ?>">
                                            <div class="match_result">
                                                <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_table">
                                                <div class="match_player">
                                                    <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="match <?php if ($start_number == 1) {
                                                                echo 'separate';
                                                            } ?>" data-n="<?php echo $n++; ?>">
                                            <div class="match_result">
                                                <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_table">
                                                <div class="match_player">
                                                    <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($n == 2) {
                                            echo '</div><div class="canvas_col-2 last_format_round">';
                                        } ?>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="match separate" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="match separate" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($n == 2) {
                                        echo '</div><div class="canvas_col-2 last_format_round">';
                                    } ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php if ($start_number == 1) : ?>
                            <div class="canvas_col-2 last_format_round">
                                <?php for ($i = 0; $i < 2; $i++) {
                                    echo '<div class="match separate" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>
                            <div class="match separate" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 0) {
                                        echo '</div><div class="canvas_col-2 last_format_round">';
                                    }
                                } ?>
                            </div>
                        <?php else : ?>
                            <div class="canvas_col-2 last_format_round">
                                <?php for ($i = 0; $i < 2; $i++) {
                                    echo '<div class="match separate" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>
                            <div class="match separate" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 0) {
                                        echo '</div><div class="canvas_col-2 last_format_round">';
                                    }
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if (!is_null($llaves['final']) && !empty($llaves['final'])) : ?>
                    <div class="canvas_final">
                        <?php $partido = $llaves['final']; ?>
                        <div class="match">
                            <div class="match_result">
                                <p><?php echo $partido['resultado'] ?? ''; ?></p>
                            </div>
                            <div class="match_table">
                                <div class="match_player">
                                    <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                </div>
                                <div class="match_player">
                                    <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="canvas_final">
                        <div class="match">
                            <div class="match_result"></div>
                            <div class="match_table">
                                <div class="match_player">
                                    <p></p>
                                </div>
                                <div class="match_player">
                                    <p></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <img src="images/logo.png" class="canvas_logo">
                <!-- <img src="images/trofeo.png" class="canvas_trophy"> -->

                <!-- <div class="canvas_information"> -->
                <?php if (isset($datos['titulo']) && !empty($datos['titulo'])) : ?>
                    <p class="canvas_tournament absolute"><?php echo $datos['titulo']; ?></p>
                <?php else : ?>
                    <p class="canvas_tournament absolute">La Confraternidad del Tenis</p>
                <?php endif; ?>

                <?php if (isset($datos['torneo']) && !empty($datos['torneo'])) : ?>
                    <p class="canvas_format absolute"><?php echo $datos['torneo']; ?></p>
                <?php else : ?>
                    <p class="canvas_format absolute">Copa Level Up 2024</p>
                <?php endif; ?>

                <?php if (isset($text_cat) && !empty($text_cat)) : ?>
                    <p class="canvas_cat absolute">Categor&iacute;a <?php echo $text_cat; ?></p>
                <?php else : ?>
                    <p class="canvas_cat absolute">-</p>
                <?php endif; ?>

                <?php $r = 0;
                if ($r == 1 && isset($datos['ronda']) && !empty($datos['ronda'])) : ?>
                    <p class="canvas_round absolute">Ronda: <?php echo $datos['ronda']; ?></p>
                <?php else : ?>
                    <p class="canvas_round absolute"></p>
                <?php endif; ?>
                <!-- </div> -->

                <?php if (isset($datos['ganador']) && !empty($datos['ganador'])) : ?>
                    <div class="canvas_winner absolute">
                        <img src="images/trofeo.png">
                        <p><?php echo $datos['ganador']; ?><small><?php echo $palabraCampeon; ?></small></p>
                    </div>
                <?php else : ?>
                    <!-- no winner -->
                <?php endif; ?>

            </div>
            <!-- ------------------------------------------ -->
            <div class="canvas fb <?php echo $canvas_class ?? ''; ?>" data-first-round="<?php echo $start_number; ?>" id="diagram">

                <?php if ($start_number != 1) : ?>

                    <?php if (isset($llaves['ronda32']) && !empty($llaves['ronda32'])) : $n = 0; ?>
                        <div class="canvas_col-8">
                            <?php foreach ($llaves['ronda32'] as $bloque => $partidos) : ?>
                                <?php foreach ($partidos as $partido) : ?>
                                    <div class="match" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($n == 8) {
                                        echo '</div><div class="canvas_col-8">';
                                    } ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php if ($start_number == 8) : ?>
                            <div class="canvas_col-8">
                                <?php for ($i = 0; $i < 16; $i++) {
                                    echo '<div class="match" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 7) {
                                        echo '</div><div class="canvas_col-8">';
                                    }
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($llaves['octavos'])) : $n = 0; ?>
                        <div class="canvas_col-4">
                            <?php foreach ($llaves['octavos'] as $bloque => $partidos) : ?>
                                <?php foreach ($partidos as $partido) : ?>
                                    <div class="match" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($n == 4) {
                                        echo '</div><div class="canvas_col-4">';
                                    } ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php if ($start_number > 2) : ?>
                            <div class="canvas_col-4">
                                <?php for ($i = 0; $i < 8; $i++) {
                                    echo '<div class="match" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 3) {
                                        echo '</div><div class="canvas_col-4">';
                                    }
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($llaves['cuartos'])) : $n = 0; ?>
                        <div class="canvas_col-2">
                            <?php
                            $bloque_map = [
                                "bloque_uno" => 0,
                                "bloque_tres" => 2,
                                "bloque_dos" => 1,
                                "bloque_cuatro" => 3
                            ];

                            // Ordenar los bloques según el mapeo
                            $partidos_ordenados = [];
                            foreach ($bloque_map as $bloque => $index) {
                                if (isset($llaves['cuartos'][$bloque])) {
                                    $partidos_ordenados[$index] = $llaves['cuartos'][$bloque];
                                }
                            }

                            // var_dump($partidos_ordenados);
                            ?>
                            <?php foreach ($partidos_ordenados as $partidos) : ?>
                                <?php foreach ($partidos as $partido) : ?>
                                    <div class="match" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_player">
                                                <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($n == 2) {
                                        echo '</div><div class="canvas_col-2">';
                                    } ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php if ($start_number > 1) : ?>
                            <div class="canvas_col-2">
                                <?php for ($i = 0; $i < 4; $i++) {
                                    echo '<div class="match" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 1) {
                                        echo '</div><div class="canvas_col-2">';
                                    }
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($llaves['semifinal'])) : $n = 0; ?>
                        <div class="canvas_col-1">
                            <?php foreach ($llaves['semifinal'] as $bloque => $partidos) : ?>
                                <?php if (!is_null($partidos)) : ?>
                                    <?php foreach ($partidos as $partido) : ?>
                                        <div class="match <?php if ($start_number == 1) {
                                                                echo 'separate';
                                                            } ?>" data-n="<?php echo $n++; ?>">
                                            <div class="match_result">
                                                <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_table">
                                                <div class="match_player">
                                                    <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                                </div>
                                                <div class="match_player">
                                                    <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($n == 1) {
                                            echo '</div><div class="canvas_col-1">';
                                        } ?>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="match separate" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p></p>
                                            </div>
                                            <div class="match_player">
                                                <p></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($n == 1) {
                                        echo '</div><div class="canvas_col-1">';
                                    } ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php if ($start_number == 1) : ?>
                            <div class="canvas_col-1">
                                <?php for ($i = 0; $i < 2; $i++) {
                                    echo '<div class="match" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 0) {
                                        echo '</div><div class="canvas_col-1">';
                                    }
                                } ?>
                            </div>
                        <?php else : ?>
                            <div class="canvas_col-1">
                                <?php for ($i = 0; $i < 2; $i++) {
                                    echo '<div class="match separate" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 0) {
                                        echo '</div><div class="canvas_col-1">';
                                    }
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php else : ?>

                    <?php if (!empty($llaves['semifinal'])) : $n = 0; ?>
                        <div class="canvas_col-2 last_format_round">
                            <?php foreach ($llaves['semifinal'] as $bloque => $partidos) : ?>
                                <?php if (!is_null($partidos)) : ?>
                                    <?php foreach ($partidos as $partido) : ?>
                                        <div class="match <?php if ($start_number == 1) {
                                                                echo 'separate';
                                                            } ?>" data-n="<?php echo $n++; ?>">
                                            <div class="match_result">
                                                <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_table">
                                                <div class="match_player">
                                                    <p><?php echo $partido['jugador_local'] ?? ''; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="match <?php if ($start_number == 1) {
                                                                echo 'separate';
                                                            } ?>" data-n="<?php echo $n++; ?>">
                                            <div class="match_result">
                                                <p><?php echo $partido['resultado'] ?? ''; ?></p>
                                            </div>
                                            <div class="match_table">
                                                <div class="match_player">
                                                    <p><?php echo $partido['jugador_rival'] ?? ''; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($n == 2) {
                                            echo '</div><div class="canvas_col-2 last_format_round">';
                                        } ?>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="match separate" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="match separate" data-n="<?php echo $n++; ?>">
                                        <div class="match_result">
                                            <p></p>
                                        </div>
                                        <div class="match_table">
                                            <div class="match_player">
                                                <p></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($n == 2) {
                                        echo '</div><div class="canvas_col-2 last_format_round">';
                                    } ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php if ($start_number == 1) : ?>
                            <div class="canvas_col-2 last_format_round">
                                <?php for ($i = 0; $i < 2; $i++) {
                                    echo '<div class="match separate" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>
                            <div class="match separate" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 0) {
                                        echo '</div><div class="canvas_col-2 last_format_round">';
                                    }
                                } ?>
                            </div>
                        <?php else : ?>
                            <div class="canvas_col-2 last_format_round">
                                <?php for ($i = 0; $i < 2; $i++) {
                                    echo '<div class="match separate" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>
                            <div class="match separate" data-n="' . $i . '">
                                <div class="match_result">
                                    <p></p>
                                </div>
                                <div class="match_table">
                                    <div class="match_player">
                                        <p></p>
                                    </div>
                                </div>
                            </div>';
                                    if ($i == 0) {
                                        echo '</div><div class="canvas_col-2 last_format_round">';
                                    }
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if (!is_null($llaves['final']) && !empty($llaves['final'])) : ?>
                    <div class="canvas_final">
                        <?php $partido = $llaves['final']; ?>
                        <div class="match">
                            <div class="match_result">
                                <p><?php echo $partido['resultado']; ?></p>
                            </div>
                            <div class="match_table">
                                <div class="match_player">
                                    <p><?php echo $partido['jugador_local']; ?></p>
                                </div>
                                <div class="match_player">
                                    <p><?php echo $partido['jugador_rival']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="canvas_final">
                        <div class="match">
                            <div class="match_result"></div>
                            <div class="match_table">
                                <div class="match_player">
                                    <p></p>
                                </div>
                                <div class="match_player">
                                    <p></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <img src="images/logo.png" class="canvas_logo">
                <!-- <img src="images/trofeo.png" class="canvas_trophy"> -->

                <!-- <div class="canvas_information"> -->
                <?php if (isset($datos['titulo']) && !empty($datos['titulo'])) : ?>
                    <p class="canvas_tournament absolute"><?php echo $datos['titulo']; ?></p>
                <?php else : ?>
                    <p class="canvas_tournament absolute">La Confraternidad del Tenis</p>
                <?php endif; ?>

                <?php if (isset($datos['torneo']) && !empty($datos['torneo'])) : ?>
                    <p class="canvas_format absolute"><?php echo $datos['torneo']; ?></p>
                <?php else : ?>
                    <p class="canvas_format absolute">Copa Level Up 2024</p>
                <?php endif; ?>

                <?php if (isset($text_cat) && !empty($text_cat)) : ?>
                    <p class="canvas_cat absolute">Categor&iacute;a <?php echo $text_cat; ?></p>
                <?php else : ?>
                    <p class="canvas_cat absolute">-</p>
                <?php endif; ?>

                <?php $r = 0;
                if ($r == 1 && isset($datos['ronda']) && !empty($datos['ronda'])) : ?>
                    <p class="canvas_round absolute">Ronda: <?php echo $datos['ronda']; ?></p>
                <?php else : ?>
                    <p class="canvas_round absolute"></p>
                <?php endif; ?>
                <!-- </div> -->

                <?php if (isset($datos['ganador']) && !empty($datos['ganador'])) : ?>
                    <div class="canvas_winner absolute">
                        <img src="images/trofeo.png">
                        <p><?php echo $datos['ganador']; ?><small><?php echo $palabraCampeon; ?></small></p>
                    </div>
                <?php else : ?>
                    <!-- no winner -->
                <?php endif; ?>

            </div>
        </div>
    <?php endif; ?>

    <script src="js/html2canvas.min.js"></script>
    <?php if ($noAppear && !empty($datos) && $validacion > 2) : ?>
        <script src="js/main.js"></script>
    <?php endif; ?>
    <script src="js/selectable.js"></script>

</body>

</html>