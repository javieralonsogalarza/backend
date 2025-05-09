<?php

function generateComposerJsonWithVersions($vendorDir = '../vendor', $lockFile = '../composer.lock', $outputFile = 'composer.json') {
    $dependencies = [];

    // Verificar si composer.lock existe
    if (file_exists($lockFile)) {
        $lockData = json_decode(file_get_contents($lockFile), true);
        if (isset($lockData['packages'])) {
            foreach ($lockData['packages'] as $package) {
                $dependencies[$package['name']] = $package['version'];
            }
        }
    } else {
        // Si no hay composer.lock, intentar leer directamente los composer.json en vendor
        if (!is_dir($vendorDir)) {
            echo "La carpeta '$vendorDir' no existe.\n";
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vendorDir));
        foreach ($iterator as $file) {
            if ($file->getFilename() === 'composer.json') {
                $composerPath = $file->getPathname();
                $composerData = json_decode(file_get_contents($composerPath), true);

                if (isset($composerData['name']) && isset($composerData['version'])) {
                    $dependencies[$composerData['name']] = $composerData['version'];
                } elseif (isset($composerData['name'])) {
                    $dependencies[$composerData['name']] = '*'; // Si no hay versión, usar wildcard
                }
            }
        }
    }

    // Crear el archivo composer.json
    $composerJson = [
        'require' => $dependencies
    ];

    file_put_contents($outputFile, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "Archivo '$outputFile' generado exitosamente.\n";
}

// Llamar a la función
generateComposerJsonWithVersions();
