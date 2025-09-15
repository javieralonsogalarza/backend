<?php

// Dirección base del servidor remoto
$baseUrl = "https://laconfraternidaddeltenis.com/storage/segundo_modulo/uploads/";

// Extensiones válidas
$imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];

// Función que obtiene archivos desde una URL si el listado está habilitado
function getHtmlFromUrl($url) {
    $html = @file_get_contents($url);
    return $html ?: '';
}

// Función que detecta subdirectorios en una URL
function getSubdirectories($url) {
    $html = getHtmlFromUrl($url);
    preg_match_all('/href="([^"]+\/)"/i', $html, $matches);
    $dirs = array_unique($matches[1]);

    // Filtra los enlaces especiales (./ ../)
    return array_filter($dirs, function($dir) {
        return $dir !== './' && $dir !== '../';
    });
}

// Función que obtiene imágenes en una carpeta
function getImagesFromUrl($url, $extensions) {
    $html = getHtmlFromUrl($url);
    preg_match_all('/href="([^"]+\.(?:' . implode('|', $extensions) . '))"/i', $html, $matches);
    return array_map(function($file) use ($url) {
        return $url . $file;
    }, $matches[1]);
}

// Recolecta imágenes del directorio raíz
$allImages = getImagesFromUrl($baseUrl, $imageExtensions);

// Recolecta imágenes de subdirectorios
$subdirs = getSubdirectories($baseUrl);
foreach ($subdirs as $subdir) {
    $subUrl = $baseUrl . $subdir;
    $subImages = getImagesFromUrl($subUrl, $imageExtensions);
    $allImages = array_merge($allImages, $subImages);
}

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
        .galeria {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
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
    <div class="galeria">
        <?php if (count($allImages)): ?>
            <?php foreach ($allImages as $img): ?>
                <img src="<?= htmlspecialchars($img) ?>" alt="Imagen">
            <?php endforeach; ?>
        <?php else: ?>
            <p>No se encontraron imágenes en el directorio.</p>
        <?php endif; ?>
    </div>
</body>
</html>
