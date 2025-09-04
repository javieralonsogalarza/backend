<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Mostrar Datos CSV</title>
</head>
<body>
    
<?php

// Ruta al archivo CSV
$rutaArchivo = 'datos.csv';

// Leer el archivo CSV y almacenar los datos en un arreglo
$datos = [];
if (($gestor = fopen($rutaArchivo, 'r')) !== FALSE) {
    while (($fila = fgetcsv($gestor, 1000, ',')) !== FALSE) {
        $datos[] = $fila;
    }
    fclose($gestor);
}

array_shift($datos);

// Agrupar los datos por rondas
$rondas = [
    'Ronda de 32' => [],
    'Octavos de Final' => [],
    'Cuartos de Final' => [],
    'Semifinal' => [],
    'Final' => []
];

if(!empty($datos)){
    foreach($datos as $fila){
        $ronda = $fila[0];
        if(isset($rondas[$ronda])){
            $rondas[$ronda][] = $fila;
        }

        /*foreach($fila as $dato){
            echo htmlspecialchars($dato, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        }*/
    }
}

?>

<hr>

<?php if($datos && $rondas): ?>

<?php 
    $start_number = 1;
    if(!empty($rondas['Cuartos de Final'])){
        $start_number = 2;
    }
    if(!empty($rondas['Octavos de Final'])){
        $start_number = 4;
    }
    if(!empty($rondas['Ronda de 32'])){
        $start_number = 8;
    }
?>

<div class="canvas_scroll w-100">
    <div class="canvas" data-first-round="<?php echo $start_number; ?>" id="diagram">
    
        <?php if(!empty($rondas['Ronda de 32'])): 
            $mitad = count($rondas['Ronda de 32']) / 2;
        ?>
        <div class="canvas_col-8">
            <?php foreach($rondas['Ronda de 32'] as $n => $partido): ?>
            <div class="match">
                <div class="match_result">
                    <p><?php echo $partido[3] ?></p>
                </div>
                <div class="match_table">
                    <div class="match_player">
                        <p><?php echo $partido[1] ?></p>
                    </div>
                    <div class="match_player">
                        <p><?php echo $partido[2] ?></p>
                    </div>
                </div>
            </div>
            <?php if(($n + 1) == $mitad): ?>
            </div><div class="canvas_col-8">
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    
        <?php if(!empty($rondas['Octavos de Final'])): 
            $mitad = count($rondas['Octavos de Final']) / 2;    
        ?>
        <div class="canvas_col-4">
            <?php foreach($rondas['Octavos de Final'] as $n => $partido): ?>
            <div class="match">
                <div class="match_result">
                    <p><?php echo $partido[3] ?></p>
                </div>
                <div class="match_table">
                    <div class="match_player">
                        <p><?php echo $partido[1] ?></p>
                    </div>
                    <div class="match_player">
                        <p><?php echo $partido[2] ?></p>
                    </div>
                </div>
            </div>
            <?php if(($n + 1) == $mitad): ?>
            </div><div class="canvas_col-4">
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    
        <?php if(!empty($rondas['Cuartos de Final'])): 
            $mitad = count($rondas['Cuartos de Final']) / 2;
        ?>
        <div class="canvas_col-2">
            <?php foreach($rondas['Cuartos de Final'] as $n => $partido): ?>
            <div class="match">
                <div class="match_result">
                    <p><?php echo $partido[3] ?></p>
                </div>
                <div class="match_table">
                    <div class="match_player">
                        <p><?php echo $partido[1] ?></p>
                    </div>
                    <div class="match_player">
                        <p><?php echo $partido[2] ?></p>
                    </div>
                </div>
            </div>
            <?php if(($n + 1) == $mitad): ?>
            </div><div class="canvas_col-2">
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    
        <?php if(!empty($rondas['Semifinal'])): 
            $mitad = count($rondas['Semifinal']) / 2;    
        ?>
        <div class="canvas_col-1">
            <?php foreach($rondas['Semifinal'] as $n => $partido): ?>
            <div class="match">
                <div class="match_result">
                    <p><?php echo $partido[3] ?></p>
                </div>
                <div class="match_table">
                    <div class="match_player">
                        <p><?php echo $partido[1] ?></p>
                    </div>
                    <div class="match_player">
                        <p><?php echo $partido[2] ?></p>
                    </div>
                </div>
            </div>
            <?php if(($n + 1) == $mitad): ?>
            </div><div class="canvas_col-1">
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if(!empty($rondas['Final'])): ?>
        <div class="canvas_final">
            <?php foreach($rondas['Final'] as $n => $partido): ?>
            <div class="match">
                <div class="match_result">
                    <p><?php echo $partido[3] ?></p>
                </div>
                <div class="match_table">
                    <div class="match_player">
                        <p><?php echo $partido[1] ?></p>
                    </div>
                    <div class="match_player">
                        <p><?php echo $partido[2] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    
    </div>
</div>
<?php endif; ?>

<br><br><br><br>
<hr>

<button type="button" id="generate">Generar</button>

<script src="js/html2canvas.min.js"></script>
<script>
    let button = document.querySelector('#generate'),
        diagram = document.querySelector("#diagram");

    if(button && diagram){
        button.addEventListener('click', (e)=>{
            e.preventDefault();
            html2canvas(diagram).then(canvas => {
                let width = canvas.width;
                let height = canvas.height;
                let halfWidth = width / 2;

                // Tamaño de las nuevas imágenes
                const targetWidth = 1080;
                const targetHeight = 1350;

                // Crear canvas para la mitad izquierda
                let leftCanvas = document.createElement('canvas');
                leftCanvas.width = targetWidth;
                leftCanvas.height = targetHeight;
                let leftCtx = leftCanvas.getContext('2d');
                leftCtx.drawImage(canvas, 0, 0, halfWidth, height, 0, 0, targetWidth, targetHeight);

                // Crear canvas para la mitad derecha
                let rightCanvas = document.createElement('canvas');
                rightCanvas.width = targetWidth;
                rightCanvas.height = targetHeight;
                let rightCtx = rightCanvas.getContext('2d');
                rightCtx.drawImage(canvas, halfWidth, 0, halfWidth, height, 0, 0, targetWidth, targetHeight);

                // Convertir los canvas a imágenes y descargar
                let leftDataURL = leftCanvas.toDataURL('image/png');
                let rightDataURL = rightCanvas.toDataURL('image/png');

                downloadImage(leftDataURL, 'left_image.png');
                downloadImage(rightDataURL, 'right_image.png');
            });
        })
    }

    function downloadImage(data, filename) {
        let a = document.createElement('a');
        a.href = data;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
</script>

</body>
</html>