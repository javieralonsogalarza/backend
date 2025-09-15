<?php
$root = __DIR__;
$rootUrl = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];

foreach ($rii as $file) {
    if ($file->isDir()) continue;

    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file->getFilename())) {
        $relativePath = str_replace($root, '', $file->getPathname());
        $webPath = $rootUrl . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
        $folder = trim(dirname($relativePath), '/\\');
        if (!isset($files[$folder])) {
            $files[$folder] = [];
        }
        $files[$folder][] = $webPath;
    }
}

header('Content-Type: application/json');
echo json_encode($files);
