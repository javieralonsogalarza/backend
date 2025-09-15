<?php

// Dirección del archivo scan.php en el servidor remoto
$scanUrl = "https://laconfraternidaddeltenis.com/storage/segundo_modulo/uploads/scan.php";

// Leer JSON usando cURL
function getJsonFromUrl($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'PHP-Gallery'
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

$gallery = getJsonFromUrl($scanUrl);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Galería de Fotos</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        h2 {
            margin-top: 40px;
            font-size: 1.5em;
            border-bottom: 2px solid #ccc;
            padding-bottom: 4px;
        }
        .galeria {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }
        .galeria img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            border: 2px solid #ccc;
            box-shadow: 2px 2px 6px rgba(0,0,0,0.2);
            object-fit: cover;
        }
    </style>
</head>
<body>
    <h1>Galería de Fotos</h1>
    <?php if (empty($gallery)): ?>
        <p>No se encontraron imágenes.</p>
    <?php else: ?>
        <?php foreach ($gallery as $folder => $images): ?>
            <h2><?= htmlspecialchars($folder === '' ? 'Principal' : $folder) ?></h2>
            <div class="galeria">
                <?php foreach ($images as $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" alt="Imagen">
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
